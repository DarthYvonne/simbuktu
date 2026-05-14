<?php

namespace App\Services\News;

use App\Models\Course;

class NewsContextService
{
    /**
     * The active "current context" injected into persona prompts.
     * Owned per-population: each population is one simulation project and carries
     * its own backdrop text. Empty by default; the user fills it in on the population's
     * Kontekst tab.
     */
    public function current(?Course $course): string
    {
        return trim((string) ($course?->population?->manual_context ?? ''));
    }
}
