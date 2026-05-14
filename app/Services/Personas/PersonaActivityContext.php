<?php

namespace App\Services\Personas;

use App\Models\Comment;
use App\Models\Post;

/**
 * Builds the "what has this persona been doing in the course feed?"
 * text block fed to the DM prompt so the persona can stay coherent
 * with their own past comments and reactions.
 *
 * Used by both MessageController (for the synchronous old path, if
 * still reached) and GenerateChatReplyJob (for the async path).
 */
class PersonaActivityContext
{
    public function build(string $personaId, int $courseId): string
    {
        if (!$courseId) return '';

        // Pull the 15 most recent posts the persona has commented on.
        // Old approach plucked every post id then re-queried with whereIn —
        // unbounded, and the IN list could have hundreds of values.
        $posts = Post::where('course_id', $courseId)
            ->whereHas('comments', fn ($q) => $q->where('persona_id', $personaId))
            ->with(['comments' => fn ($q) => $q->orderBy('created_at')])
            ->latest()
            ->limit(15)
            ->get();

        if ($posts->isEmpty()) return '';

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
}
