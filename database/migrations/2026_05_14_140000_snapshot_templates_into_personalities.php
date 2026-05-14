<?php

use App\Models\Blueprint;
use App\Models\Prompt;
use App\Services\PromptRepository;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // For each existing personality, snapshot the current effective body of each
        // template prompt into its prompt_overrides. This locks in current behavior
        // before the runtime stops falling back through hardcoded defaults.
        $defaults = (new PromptRepository())->defaults();

        // Build a key -> effective body map (DB row if present, else hardcoded default).
        $effective = [];
        $dbBodies = Prompt::pluck('body', 'key')->all();
        foreach (Blueprint::PROMPT_KEYS as $key) {
            $effective[$key] = $dbBodies[$key] ?? ($defaults[$key]['body'] ?? '');
        }

        foreach (Blueprint::query()->get() as $bp) {
            $overrides = $bp->prompt_overrides ?? [];
            $changed = false;
            foreach (Blueprint::PROMPT_KEYS as $key) {
                $existing = $overrides[$key] ?? null;
                if (is_string($existing) && trim($existing) !== '') continue;
                $body = $effective[$key] ?? '';
                if ($body === '') continue;
                $overrides[$key] = $body;
                $changed = true;
            }
            if ($changed) {
                $bp->prompt_overrides = $overrides;
                $bp->save();
            }
        }
    }

    public function down(): void
    {
        // Non-reversible: removing the snapshotted overrides would expose missing
        // global fallbacks. If you need to roll back the data, do it manually.
    }
};
