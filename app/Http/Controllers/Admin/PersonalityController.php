<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonalityRule;
use App\Models\Population;
use App\Services\Personas\PersonalityRuleCatalog;
use App\Services\Personas\PersonalityRuleRenderer;
use Illuminate\Http\Request;

class PersonalityController extends Controller
{
    public function index(Population $population)
    {
        $this->ensureSeeded();
        $groups = PersonalityRuleCatalog::groups();
        $rules  = PersonalityRule::all()->keyBy(fn ($r) => $r->attribute.'::'.$r->value);
        return view('admin.personas.personlighed', compact('population', 'groups', 'rules'));
    }

    public function update(Request $request, Population $population, PersonalityRule $rule)
    {
        $data = $request->validate([
            'rule_text' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ]);
        $rule->update(array_filter($data, fn ($v) => $v !== null));
        PersonalityRuleRenderer::clearCache();
        return response()->json([
            'ok' => true,
            'rule_text' => $rule->rule_text,
            'is_active' => $rule->is_active,
        ]);
    }

    public function reset(Population $population, PersonalityRule $rule)
    {
        $default = $this->defaultFor($rule->attribute, $rule->value);
        $rule->update(['rule_text' => $default, 'is_active' => true]);
        PersonalityRuleRenderer::clearCache();
        return response()->json(['ok' => true, 'rule_text' => $rule->rule_text]);
    }

    public function resetAll(Population $population)
    {
        foreach (PersonalityRuleCatalog::flatten() as $row) {
            PersonalityRule::where('attribute', $row['attribute'])
                ->where('value', $row['value'])
                ->update(['rule_text' => $row['rule_text'], 'is_active' => true]);
        }
        PersonalityRuleRenderer::clearCache();
        return redirect()->back()->with('success', 'Alle regler nulstillet til defaults.');
    }

    private function ensureSeeded(): void
    {
        if (PersonalityRule::count() > 0) {
            // Still ensure new catalog entries get added if code was updated
            $existing = PersonalityRule::all()->map(fn ($r) => $r->attribute.'::'.$r->value)->all();
            foreach (PersonalityRuleCatalog::flatten() as $row) {
                $key = $row['attribute'].'::'.$row['value'];
                if (!in_array($key, $existing)) {
                    PersonalityRule::create($row + ['is_active' => true]);
                }
            }
            return;
        }
        foreach (PersonalityRuleCatalog::flatten() as $row) {
            PersonalityRule::create($row + ['is_active' => true]);
        }
    }

    private function defaultFor(string $attribute, string $value): string
    {
        foreach (PersonalityRuleCatalog::groups() as $cat) {
            if (isset($cat['attributes'][$attribute]['values'][$value])) {
                return $cat['attributes'][$attribute]['values'][$value];
            }
        }
        return '';
    }
}
