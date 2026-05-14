<?php

namespace App\Jobs;

use App\Models\Persona;
use App\Services\Llm\LlmRouter;
use App\Services\PromptRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as FoundationQueueable;
use Illuminate\Support\Facades\Log;

/**
 * After a persona is generated, ask the LLM whether any of its SECONDARY
 * facets look implausible given its PRIMARY facets. The LLM only advises —
 * we never re-roll or change anything automatically. Distributions are
 * preserved; flagged personas are surfaced in admin for human review.
 */
class CoherenceCheckJob implements ShouldQueue
{
    use FoundationQueueable;

    public int $tries = 1;
    public int $timeout = 60;

    public function __construct(public string $personaId)
    {
    }

    public function handle(LlmRouter $router, PromptRepository $prompts): void
    {
        $persona = Persona::find($this->personaId);
        if (!$persona) return;

        $data = $persona->persona_data ?? [];
        $dimensions = $data['dimensions'] ?? [];
        if (empty($dimensions)) return;

        $primary   = array_filter($dimensions, fn ($d) => ($d['tier'] ?? 'primary') === 'primary');
        $secondary = array_filter($dimensions, fn ($d) => ($d['tier'] ?? 'primary') === 'secondary');

        // No secondaries → nothing to check.
        if (empty($secondary)) {
            $data['coherence_flags'] = [];
            $persona->persona_data = $data;
            $persona->save();
            return;
        }

        $primaryBlock   = $this->renderBlock($primary, includeId: false);
        $secondaryBlock = $this->renderBlock($secondary, includeId: true);

        $prompt = $prompts->render('coherence.check', [
            'primary_block'   => $primaryBlock,
            'secondary_block' => $secondaryBlock,
        ]);

        try {
            $raw = $router->generateText($prompt, 'gemini-2.5-flash-lite', [
                'prompt_key' => 'coherence.check',
                'persona_id' => $this->personaId,
            ]);
        } catch (\Throwable $e) {
            Log::warning("CoherenceCheckJob LLM call failed for persona {$this->personaId}: " . $e->getMessage());
            return;
        }

        $flags = $this->parseFlags($raw, $secondary);
        $data['coherence_flags'] = $flags;
        $persona->persona_data = $data;
        $persona->save();
    }

    /** Render dimensions as a bullet list. With includeId, prefix each line with [dimension_id=…]. */
    private function renderBlock(array $dims, bool $includeId): string
    {
        $lines = [];
        foreach ($dims as $d) {
            $facet = trim((string) ($d['facet'] ?? ''));
            $dimName = trim((string) ($d['dimension'] ?? ''));
            if ($facet === '' || $dimName === '') continue;
            $value = $d['value'] ?? null;
            $display = ($value !== null && $value !== '') ? (string) $value : $facet;
            $prefix = $includeId && !empty($d['dimension_id']) ? "[id={$d['dimension_id']}] " : '';
            $lines[] = "- {$prefix}{$dimName}: {$display}";
        }
        return implode("\n", $lines);
    }

    /** Pull {"flags": [...]} from the LLM response. Tolerate markdown fences and stray prose. */
    private function parseFlags(string $raw, array $validSecondaries): array
    {
        $raw = trim($raw);
        // Strip ```json fences if present.
        $raw = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $raw);
        // Find the first { and last } to isolate the JSON object if the LLM added prose.
        $start = strpos($raw, '{');
        $end   = strrpos($raw, '}');
        if ($start === false || $end === false || $end <= $start) return [];
        $json = substr($raw, $start, $end - $start + 1);

        $decoded = json_decode($json, true);
        if (!is_array($decoded) || !isset($decoded['flags']) || !is_array($decoded['flags'])) return [];

        $validIds = [];
        foreach ($validSecondaries as $d) {
            if (!empty($d['dimension_id'])) {
                $validIds[$d['dimension_id']] = [
                    'dimension' => $d['dimension'] ?? '',
                    'facet'     => $d['facet'] ?? '',
                ];
            }
        }

        $out = [];
        foreach ($decoded['flags'] as $f) {
            $dimId = $f['dimension_id'] ?? null;
            if (!$dimId || !isset($validIds[$dimId])) continue;
            $reason = trim((string) ($f['reason'] ?? ''));
            if ($reason === '') continue;
            $out[] = [
                'dimension_id' => $dimId,
                'dimension'    => $validIds[$dimId]['dimension'],
                'facet'        => $validIds[$dimId]['facet'],
                'reason'       => $reason,
            ];
        }
        return $out;
    }
}
