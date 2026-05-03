<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Post;
use App\Services\Llm\DirectMessagePromptBuilder;
use App\Services\Llm\LlmRouter;
use App\Services\News\NewsContextService;
use App\Models\CourseMembership;
use App\Services\Personas\PersonaRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function __construct(private PersonaRepository $personas)
    {
    }

    public function index()
    {
        $user = Auth::user();
        $course = $user->currentCourse();
        abort_unless($course, 403, 'Intet aktivt kursus.');

        $conversations = Conversation::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->has('messages')
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        $previews = [];
        foreach ($conversations as $c) {
            $last = ConversationMessage::where('conversation_id', $c->id)
                ->orderByDesc('created_at')->first();
            $previews[$c->id] = $last;
        }

        return view('messages.index', [
            'conversations' => $conversations,
            'previews' => $previews,
        ]);
    }

    public function show(Conversation $conversation)
    {
        $this->authorizeConversation($conversation);
        $persona = $this->personas->find($conversation->persona_id);
        $messages = $conversation->messages()->get();
        $conversation->update(['last_seen_at' => now()]);
        return view('messages.show', [
            'conversation' => $conversation,
            'persona' => $persona,
            'messages' => $messages,
        ]);
    }

    // AJAX: open (or create) conversation with persona, return history
    public function open(string $personaId)
    {
        $user = Auth::user();
        $course = $user->currentCourse();
        abort_unless($course, 403);

        $persona = $this->personas->find($personaId);
        abort_unless($persona, 404);

        $conversation = Conversation::firstOrCreate(
            [
                'course_id' => $course->id,
                'user_id' => $user->id,
                'persona_id' => $personaId,
            ],
            [
                'persona_name' => $persona['name'],
            ]
        );

        $messages = $conversation->messages()->get()->map(fn ($m) => [
            'id' => $m->id,
            'role' => $m->role,
            'body' => $m->body,
            'created_at' => $m->created_at->toIso8601String(),
        ]);

        $conversation->update(['last_seen_at' => now()]);

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'persona_id' => $conversation->persona_id,
                'persona_name' => $conversation->persona_name,
                'persona_thumb' => url('/simulation/profiler/' . $personaId . '/thumb'),
                'persona_url' => url('/simulation/profiler/' . $personaId),
            ],
            'messages' => $messages,
        ]);
    }

    // AJAX: send a message, return persona reply
    public function send(Request $request, Conversation $conversation)
    {
        $this->authorizeConversation($conversation);

        $data = $request->validate([
            'body' => 'required|string|max:4000',
        ]);

        $persona = $this->personas->find($conversation->persona_id);
        abort_unless($persona, 410, 'Personaen findes ikke længere.');

        $user = Auth::user();
        $course = $conversation->course;

        $history = $conversation->messages()->get()->map(fn ($m) => [
            'role' => $m->role,
            'body' => $m->body,
        ])->toArray();

        $userMessage = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'body' => trim($data['body']),
        ]);

        $membership = CourseMembership::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
        $senderName = $membership?->poster_name ?: ($user->name ?: 'Brugeren');
        $currentContext = $course
            ? app(NewsContextService::class)->current($course)
            : null;

        $activityContext = $this->buildPersonaActivityContext(
            $conversation->persona_id,
            $course->id,
        );

        $prompt = app(DirectMessagePromptBuilder::class)->build(
            persona: $persona,
            history: $history,
            newMessage: $userMessage->body,
            senderName: $senderName,
            currentContext: $currentContext,
            activityContext: $activityContext,
        );

        try {
            $reply = app(LlmRouter::class)->generateText($prompt, null, [
                'course_id' => $course?->id,
                'persona_id' => $conversation->persona_id,
                'prompt_key' => 'persona.dm',
            ]);
            $reply = trim($reply);
            $reply = trim($reply, " \t\n\r\0\x0B\"'");
        } catch (\Throwable $e) {
            $userMessage->delete();
            return response()->json(['error' => 'LLM-fejl: ' . $e->getMessage()], 500);
        }

        $personaMessage = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'persona',
            'body' => $reply,
        ]);

        $conversation->update([
            'last_message_at' => $personaMessage->created_at,
            'last_seen_at' => $personaMessage->created_at,
        ]);

        return response()->json([
            'user_message' => [
                'id' => $userMessage->id,
                'role' => 'user',
                'body' => $userMessage->body,
                'created_at' => $userMessage->created_at->toIso8601String(),
            ],
            'persona_message' => [
                'id' => $personaMessage->id,
                'role' => 'persona',
                'body' => $personaMessage->body,
                'created_at' => $personaMessage->created_at->toIso8601String(),
            ],
        ]);
    }

    private function buildPersonaActivityContext(string $personaId, int $courseId): string
    {
        $postIds = Comment::where('persona_id', $personaId)
            ->whereHas('post', fn ($q) => $q->where('course_id', $courseId))
            ->pluck('post_id')
            ->unique();

        if ($postIds->isEmpty()) {
            return '';
        }

        $posts = Post::whereIn('id', $postIds)
            ->with(['comments' => fn ($q) => $q->orderBy('created_at')])
            ->latest()
            ->limit(15)
            ->get();

        $lines = [];
        foreach ($posts as $post) {
            $authorName = $post->currentAuthorName();
            $postBody = mb_strimwidth($post->body, 0, 500, '…');
            $lines[] = "--- OPSLAG af {$authorName} ---";
            $lines[] = $postBody;
            $lines[] = '';

            $comments = $post->comments->take(30);
            if ($comments->isNotEmpty()) {
                $lines[] = 'Kommentarer:';
                foreach ($comments as $comment) {
                    $name = $comment->persona_name ?: ($comment->user?->name ?? 'Anonym');
                    $tag = $comment->persona_id === $personaId ? ' (DIG)' : '';
                    $body = mb_strimwidth($comment->body, 0, 300, '…');
                    $lines[] = "- {$name}{$tag}: {$body}";
                }
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function authorizeConversation(Conversation $conversation): void
    {
        $user = Auth::user();
        $course = $user->currentCourse();
        if (!$course || $conversation->user_id !== $user->id || $conversation->course_id !== $course->id) {
            abort(403, 'Samtalen tilhører dig ikke i dette kursus.');
        }
    }
}
