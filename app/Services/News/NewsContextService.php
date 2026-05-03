<?php

namespace App\Services\News;

use App\Models\Course;

class NewsContextService
{
    public function __construct(private NewsDigester $digester)
    {
    }

    public function current(?Course $course): string
    {
        if (!$course) return '';
        if (($course->context_mode ?? 'auto') === 'manual') {
            return trim((string) ($course->manual_context ?? ''));
        }
        $digest = $this->digester->current();
        if (!$digest) return '';
        return NewsDigester::formatForPrompt($digest);
    }
}
