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

        $postIds = Comment::where('persona_id', $personaId)
            ->whereHas('post', fn ($q) => $q->where('course_id', $courseId))
            ->pluck('post_id')
            ->unique();

        if ($postIds->isEmpty()) return '';

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
}
