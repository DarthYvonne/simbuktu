<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blueprint;
use App\Models\LibraryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlueprintController extends Controller
{
    public function index()
    {
        $blueprints = Blueprint::orderBy('name')->get();
        $course = \Illuminate\Support\Facades\Auth::user()?->currentCourse();
        return view('admin.blueprints.index', compact('blueprints', 'course'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $defaults = LibraryParameter::where('default_in_new_blueprints', true)
            ->orderBy('sort_order')->orderBy('name')
            ->get(['id', 'name', 'description', 'type', 'facets']);
        $data['parameters'] = $defaults->map(fn ($lib) => [
            'id'                   => (string) Str::uuid(),
            'name'                 => $lib->name,
            'description'          => $lib->description,
            'type'                 => $lib->type ?? 'personality',
            'tier'                 => 'primary',
            'library_parameter_id' => $lib->id,
            'show_on_profile'      => false,
            'facets'               => array_map(
                fn ($f) => [
                    'id'     => (string) Str::uuid(),
                    'name'   => $f['name'] ?? '',
                    'text'   => $f['text'] ?? '',
                    'weight' => (int) ($f['weight'] ?? 0),
                ],
                $lib->facets ?? []
            ),
        ])->all();
        $data['created_by'] = Auth::id();
        $blueprint = Blueprint::create($data);
        return redirect("/simulation/admin/blueprints/{$blueprint->id}")->with('success', 'Personlighed oprettet.');
    }

    public function edit(Blueprint $blueprint)
    {
        $library = LibraryParameter::orderBy('sort_order')->orderBy('name')
            ->get(['id', 'category', 'name', 'description', 'facets']);
        return view('admin.blueprints.edit', compact('blueprint', 'library'));
    }

    public function update(Request $request, Blueprint $blueprint)
    {
        $data = $request->validate([
            'name'                              => 'required|string|max:255',
            'description'                       => 'nullable|string',
            'parameters'                        => 'array',
            'parameters.*.id'                   => 'nullable|string|max:64',
            'parameters.*.name'                 => 'required|string|max:64',
            'parameters.*.description'          => 'nullable|string|max:500',
            'parameters.*.type'                 => 'nullable|in:personality,demographic',
            'parameters.*.tier'                 => 'nullable|in:primary,secondary',
            'parameters.*.library_parameter_id' => 'nullable|integer',
            'parameters.*.show_on_profile'      => 'nullable|boolean',
            'parameters.*.facets'               => 'required|array|min:1',
            'parameters.*.facets.*.id'          => 'nullable|string|max:64',
            'parameters.*.facets.*.name'        => 'required|string|max:64',
            'parameters.*.facets.*.text'        => 'nullable|string|max:5000',
            'parameters.*.facets.*.weight'      => 'nullable|integer|min:0|max:100',
            'parameters.*.facets.*.value'       => 'nullable|string|max:64',
        ]);
        $errors = [];
        $data['parameters'] = array_values(array_map(function ($p, $i) use (&$errors) {
            $facets = array_values(array_map(
                fn ($f) => [
                    'id'     => $f['id'] ?? (string) Str::uuid(),
                    'name'   => trim($f['name']),
                    'text'   => trim((string) ($f['text'] ?? '')),
                    'weight' => (int) ($f['weight'] ?? 0),
                    'value'  => trim((string) ($f['value'] ?? '')) ?: null,
                ],
                $p['facets']
            ));
            $sum = array_sum(array_column($facets, 'weight'));
            if ($sum > 100) {
                $errors["parameters.$i.facets"] = 'Summen af vægte for "'.($p['name'] ?: 'unavngivet').'" overstiger 100 % (er '.$sum.' %).';
            }
            return [
                'id'                   => $p['id'] ?? (string) Str::uuid(),
                'name'                 => trim($p['name']),
                'description'          => $p['description'] ?? null,
                'type'                 => $p['type'] ?? 'personality',
                'tier'                 => $p['tier'] ?? 'primary',
                'library_parameter_id' => $p['library_parameter_id'] ?? null,
                'show_on_profile'      => (bool) ($p['show_on_profile'] ?? false),
                'facets'               => $facets,
            ];
        }, $data['parameters'] ?? [], array_keys($data['parameters'] ?? [])));
        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
        $blueprint->update($data);
        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Personlighed gemt.');
    }

    public function destroy(Blueprint $blueprint)
    {
        $blueprint->delete();
        return redirect('/simulation/admin/blueprints')->with('success', 'Personlighed slettet.');
    }

    public function editOm(Blueprint $blueprint)
    {
        return view('admin.blueprints.om', compact('blueprint'));
    }

    public function updateMeta(Request $request, Blueprint $blueprint)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $blueprint->update($data);
        return redirect("/simulation/admin/blueprints/{$blueprint->id}/om")->with('success', 'Gemt.');
    }

    public function editPrompts(Blueprint $blueprint)
    {
        $defaults = (new \App\Services\PromptRepository())->defaults();
        $rows = [];
        foreach (Blueprint::PROMPT_KEYS as $key) {
            $def = $defaults[$key] ?? null;
            if (!$def) continue;
            $override = $blueprint->prompt_overrides[$key] ?? null;
            $rows[] = [
                'key'         => $key,
                'name'        => $def['name'] ?? $key,
                'description' => $def['description'] ?? '',
                'default'     => $def['body'] ?? '',
                'current'     => $override ?: ($def['body'] ?? ''),
                'overridden'  => is_string($override) && trim($override) !== '',
                'placeholders'=> $def['placeholders'] ?? [],
            ];
        }
        return view('admin.blueprints.prompts', compact('blueprint', 'rows'));
    }

    public function updatePrompts(Request $request, Blueprint $blueprint)
    {
        $data = $request->validate([
            'prompts'   => 'array',
            'prompts.*' => 'nullable|string|max:20000',
        ]);
        $defaults = (new \App\Services\PromptRepository())->defaults();
        $overrides = $blueprint->prompt_overrides ?? [];
        foreach ($data['prompts'] ?? [] as $key => $body) {
            if (!in_array($key, Blueprint::PROMPT_KEYS, true)) continue;
            $body = trim((string) $body);
            $defaultBody = $defaults[$key]['body'] ?? '';
            if ($body === '' || $body === $defaultBody) {
                unset($overrides[$key]);
            } else {
                $overrides[$key] = $body;
            }
        }
        $blueprint->update(['prompt_overrides' => $overrides ?: null]);
        return back()->with('success', 'Prompts gemt.');
    }

    /**
     * Lille Sokrates — sparring-partner chat for the blueprint editor.
     * Sees the full current (possibly unsaved) state and the conversation so far,
     * returns prose + optional structured operations the frontend can apply.
     */
    public function chat(Request $request, Blueprint $blueprint)
    {
        $data = $request->validate([
            'messages'                => 'array',
            'messages.*.role'         => 'required|in:user,assistant',
            'messages.*.content'      => 'required|string',
            'state'                   => 'array',
            'state.*.id'              => 'nullable|string',
            'state.*.name'            => 'nullable|string',
            'state.*.description'     => 'nullable|string',
            'state.*.type'            => 'nullable|string',
            'state.*.tier'            => 'nullable|string',
            'state.*.show_on_profile' => 'nullable|boolean',
            'state.*.facets'          => 'nullable|array',
            'selected_index'          => 'nullable|integer',
        ]);

        $params = $data['state'] ?? [];
        $selectedIndex = $data['selected_index'] ?? 0;
        $selectedDimId = $params[$selectedIndex]['id'] ?? '(ingen valgt)';

        $dimensionsBlock = $this->renderDimensionsBlockForChat($params);
        $conversation = $this->renderConversation($data['messages'] ?? []);

        $prompts = new \App\Services\PromptRepository();
        $prompt = $prompts->render('blueprint.dimension_chat', [
            'blueprint_name'        => $blueprint->name,
            'blueprint_description' => $blueprint->description ?? '',
            'dimensions_block'      => $dimensionsBlock ?: '(ingen dimensioner endnu)',
            'selected_dimension_id' => $selectedDimId,
            'conversation'          => $conversation ?: '(ingen samtale endnu — eksperten har lige åbnet chatten)',
        ]);

        try {
            $raw = app(\App\Services\Llm\LlmRouter::class)->generateText($prompt, 'gemini-2.5-flash', [
                'prompt_key'   => 'blueprint.dimension_chat',
                'blueprint_id' => $blueprint->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message'    => 'Forbindelsesfejl: ' . $e->getMessage(),
                'operations' => [],
            ], 200); // 200 so the frontend can render the error inline as a message
        }

        $parsed = $this->parseChatResponse($raw);
        return response()->json($parsed);
    }

    private function renderDimensionsBlockForChat(array $params): string
    {
        if (empty($params)) return '';
        $lines = [];
        foreach ($params as $p) {
            $id = $p['id'] ?? '?';
            $name = $p['name'] ?? '(unavngivet)';
            $type = $p['type'] ?? 'personality';
            $tier = $p['tier'] ?? 'primary';
            $desc = trim((string) ($p['description'] ?? ''));
            $lines[] = "## [id={$id}] {$name} (type={$type}, tier={$tier})" . ($desc !== '' ? " — {$desc}" : '');
            foreach (($p['facets'] ?? []) as $f) {
                $fid = $f['id'] ?? '?';
                $fname = $f['name'] ?? '';
                $weight = (int) ($f['weight'] ?? 0);
                $value = trim((string) ($f['value'] ?? ''));
                $text = trim((string) ($f['text'] ?? ''));
                $textSnippet = mb_strlen($text) > 120 ? mb_substr($text, 0, 117) . '…' : $text;
                $valSuffix = $value !== '' ? " (værdi={$value})" : '';
                $lines[] = "  - [id={$fid}] {$fname} ({$weight}%){$valSuffix}: {$textSnippet}";
            }
        }
        return implode("\n", $lines);
    }

    private function renderConversation(array $messages): string
    {
        if (empty($messages)) return '';
        $lines = [];
        foreach ($messages as $m) {
            $role = $m['role'] === 'user' ? 'EKSPERT' : 'SOKRATES';
            $lines[] = "{$role}: " . trim($m['content']);
        }
        return implode("\n\n", $lines);
    }

    /** Tolerant JSON parser — strip fences and isolate the first {...} block. */
    private function parseChatResponse(string $raw): array
    {
        $raw = trim($raw);
        $stripped = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $raw);
        $start = strpos($stripped, '{');
        $end   = strrpos($stripped, '}');
        if ($start === false || $end === false || $end <= $start) {
            return ['message' => $raw, 'operations' => []];
        }
        $json = substr($stripped, $start, $end - $start + 1);
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return ['message' => $raw, 'operations' => []];
        }
        $message = isset($decoded['message']) && is_string($decoded['message']) ? $decoded['message'] : '';
        $operations = isset($decoded['operations']) && is_array($decoded['operations']) ? $decoded['operations'] : [];
        if ($message === '' && empty($operations)) {
            return ['message' => $raw, 'operations' => []];
        }
        return ['message' => $message ?: '(intet svar)', 'operations' => $operations];
    }

    public function promote(Request $request, Blueprint $blueprint)
    {
        $data = $request->validate([
            'parameter_index' => 'required|integer|min:0',
            'mode'            => 'required|in:new,update',
            'target_id'       => 'nullable|integer|exists:library_parameters,id',
        ]);
        $params = $blueprint->parameters ?? [];
        $p = $params[$data['parameter_index']] ?? null;
        if (!$p) return response()->json(['ok' => false, 'error' => 'parameter not found'], 404);

        $payload = [
            'name'        => $p['name'],
            'description' => $p['description'] ?? null,
            'facets'      => $p['facets'],
        ];

        if ($data['mode'] === 'update' && !empty($data['target_id'])) {
            $lib = LibraryParameter::findOrFail($data['target_id']);
            $lib->update($payload);
        } else {
            if (LibraryParameter::where('name', $payload['name'])->exists()) {
                return response()->json(['ok' => false, 'error' => 'En dimension med dette navn findes allerede i biblioteket.'], 422);
            }
            $lib = LibraryParameter::create($payload);
        }

        $params[$data['parameter_index']]['library_parameter_id'] = $lib->id;
        $blueprint->update(['parameters' => $params]);

        return response()->json(['ok' => true, 'library_parameter_id' => $lib->id, 'name' => $lib->name]);
    }
}
