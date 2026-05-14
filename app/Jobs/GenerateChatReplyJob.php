<?php

namespace App\Jobs;

use App\Models\ConversationMessage;
use App\Models\CourseMembership;
use App\Services\Llm\DirectMessagePromptBuilder;
use App\Services\Llm\LlmRouter;
use App\Services\News\NewsContextService;
use App\Services\Personas\PersonaActivityContext;
use App\Services\Personas\PersonaRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Generates a persona's chat reply asynchronously.
 *
 * MessageController::send creates a 'pending' persona message and dispatches
 * this job. The frontend polls and shows the reply when status flips to
 * 'complete'. This frees the PHP-FPM worker that would otherwise block for
 * 5–30s on every chat send.
 *
 * The pending message id is the only state we carry — everything else is
 * resolved fresh inside the job (so the conversation history is current
 * even if more messages arrived in the meantime).
 */
class GenerateChatReplyJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;          // chat replies aren't safe to retry — would post twice
    public int $timeout = 180;

    public function __construct(public int $pendingMessageId)
    {
    }

    public function handle(
        PersonaRepository $personas,
        DirectMessagePromptBuilder $promptBuilder,
        NewsContextService $newsContext,
        LlmRouter $llm,
        PersonaActivityContext $activityContext,
    ): void {
        $pending = ConversationMessage::find($this->pendingMessageId);
        if (!$pending || $pending->status !== 'pending') return;

        $conversation = $pending->conversation;
        if (!$conversation) {
            $this->fail($pending, 'Samtalen er forsvundet.');
            return;
        }

        $persona = $personas->find($conversation->persona_id);
        if (!$persona) {
            $this->fail($pending, 'Personaen findes ikke længere.');
            return;
        }

        $course = $conversation->course;
        $user = $conversation->user;
        $membership = $user
            ? CourseMembership::where('user_id', $user->id)->where('course_id', $course?->id)->first()
            : null;
        $senderName = $membership?->poster_name ?: ($user?->name ?: 'Brugeren');

        // History excludes the pending message itself. Capped to the most
        // recent 50 turns — older context wouldn't fit in the prompt anyway
        // and is unlikely to influence the next reply.
        $history = $conversation->messages()
            ->where('id', '<', $pending->id)
            ->latest('id')->limit(50)->get()
            ->reverse()->values()
            ->map(fn ($m) => ['role' => $m->role, 'body' => $m->body])
            ->toArray();

        // The "new message" is the most recent user message in the history.
        $newMessage = collect($history)->reverse()->first(fn ($m) => $m['role'] === 'user')['body'] ?? '';

        $currentContext = $course ? $newsContext->current($course) : null;
        $activityText = $activityContext->build($conversation->persona_id, $course?->id ?? 0);

        $prompt = $promptBuilder->build(
            persona: $persona,
            history: $history,
            newMessage: $newMessage,
            senderName: $senderName,
            currentContext: $currentContext,
            activityContext: $activityText,
        );

        try {
            $reply = $llm->generateText($prompt, null, [
                'course_id'  => $course?->id,
                'persona_id' => $conversation->persona_id,
                'prompt_key' => 'persona.dm',
            ]);
            $reply = trim(trim($reply), " \t\n\r\0\x0B\"'");
        } catch (\Throwable $e) {
            Log::warning('GenerateChatReplyJob LLM failed', [
                'message_id' => $pending->id,
                'error' => $e->getMessage(),
            ]);
            $this->fail($pending, 'LLM-fejl: ' . $e->getMessage());
            return;
        }

        $pending->update([
            'status' => 'complete',
            'body'   => $reply,
        ]);

        $conversation->update([
            'last_message_at' => $pending->fresh()->updated_at,
        ]);
    }

    private function fail(ConversationMessage $pending, string $reason): void
    {
        $pending->update([
            'status'        => 'failed',
            'error_message' => $reason,
        ]);
    }

}
