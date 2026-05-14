<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Services\Personas\PersonaRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            // Prefer the latest message that has actual content. A pending persona
            // reply would render as an empty preview line otherwise.
            $last = ConversationMessage::where('conversation_id', $c->id)
                ->where('status', '!=', 'pending')
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

    // AJAX: send a user message, dispatch persona reply to a job, return immediately.
    // Frontend polls /poll-message/{message} until status flips off 'pending'.
    public function send(Request $request, Conversation $conversation)
    {
        $this->authorizeConversation($conversation);

        $data = $request->validate([
            'body' => 'required|string|max:4000',
        ]);

        $persona = $this->personas->find($conversation->persona_id);
        abort_unless($persona, 410, 'Personaen findes ikke længere.');

        $userMessage = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'role'            => 'user',
            'body'            => trim($data['body']),
            'status'          => 'complete',
        ]);

        $pendingPersonaMessage = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'role'            => 'persona',
            'body'            => '',
            'status'          => 'pending',
        ]);

        \App\Jobs\GenerateChatReplyJob::dispatch($pendingPersonaMessage->id);

        $conversation->update([
            'last_message_at' => $userMessage->created_at,
            'last_seen_at'    => $userMessage->created_at,
        ]);

        return response()->json([
            'user_message' => [
                'id'         => $userMessage->id,
                'role'       => 'user',
                'body'       => $userMessage->body,
                'status'     => 'complete',
                'created_at' => $userMessage->created_at->toIso8601String(),
            ],
            'persona_message' => [
                'id'         => $pendingPersonaMessage->id,
                'role'       => 'persona',
                'body'       => '',
                'status'     => 'pending',
                'created_at' => $pendingPersonaMessage->created_at->toIso8601String(),
            ],
        ]);
    }

    // AJAX poll: returns the latest state of a single message — used by the
    // chat UI to swap the typing indicator for the actual reply.
    public function pollMessage(Conversation $conversation, ConversationMessage $message)
    {
        $this->authorizeConversation($conversation);
        abort_unless($message->conversation_id === $conversation->id, 404);

        return response()->json([
            'id'            => $message->id,
            'role'          => $message->role,
            'body'          => $message->body,
            'status'        => $message->status,
            'error_message' => $message->error_message,
            'created_at'    => $message->created_at->toIso8601String(),
        ]);
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
