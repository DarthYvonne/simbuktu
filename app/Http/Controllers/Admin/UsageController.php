<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LlmCall;
use App\Services\PromptRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsageController extends Controller
{
    private const DKK_PER_USD = 6.9;

    public function index(Request $request)
    {
        $user = Auth::user();
        $course = $user->currentCourse();
        $scope = $request->query('scope', 'course');
        $useAll = $scope === 'all';

        $base = fn () => $useAll || !$course
            ? LlmCall::query()
            : LlmCall::where(function ($q) use ($course) {
                $q->where('course_id', $course->id)->orWhereNull('course_id');
            });

        $now = now();
        $cards = [
            'today' => $this->summarise($base()->where('created_at', '>=', $now->copy()->startOfDay())),
            'week'  => $this->summarise($base()->where('created_at', '>=', $now->copy()->subDays(7))),
            'month' => $this->summarise($base()->where('created_at', '>=', $now->copy()->subDays(30))),
            'all'   => $this->summarise($base()),
        ];

        $promptNames = collect((new PromptRepository())->defaults())
            ->map(fn ($d) => $d['name'])->all();

        $perPrompt = $base()
            ->selectRaw('prompt_key, COUNT(*) as calls, SUM(input_tokens) as in_tok, SUM(output_tokens) as out_tok, SUM(cost_usd) as usd')
            ->groupBy('prompt_key')
            ->orderByDesc('usd')
            ->get()
            ->map(fn ($r) => [
                'key' => $r->prompt_key,
                'name' => $promptNames[$r->prompt_key] ?? $r->prompt_key,
                'calls' => (int) $r->calls,
                'in_tok' => (int) $r->in_tok,
                'out_tok' => (int) $r->out_tok,
                'usd' => (float) $r->usd,
                'dkk' => (float) $r->usd * self::DKK_PER_USD,
            ]);

        $perModel = $base()
            ->selectRaw('model, provider, COUNT(*) as calls, SUM(input_tokens) as in_tok, SUM(output_tokens) as out_tok, SUM(cost_usd) as usd')
            ->groupBy('model', 'provider')
            ->orderByDesc('usd')
            ->get()
            ->map(fn ($r) => [
                'model' => $r->model,
                'provider' => $r->provider,
                'calls' => (int) $r->calls,
                'in_tok' => (int) $r->in_tok,
                'out_tok' => (int) $r->out_tok,
                'usd' => (float) $r->usd,
                'dkk' => (float) $r->usd * self::DKK_PER_USD,
            ]);

        $daily = $base()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as calls, SUM(cost_usd) as usd')
            ->where('created_at', '>=', $now->copy()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($r) => [
                'day' => (string) $r->day,
                'calls' => (int) $r->calls,
                'dkk' => (float) $r->usd * self::DKK_PER_USD,
            ]);

        $latest = $base()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($r) => [
                'when' => $r->created_at->format('Y-m-d H:i'),
                'prompt' => $promptNames[$r->prompt_key] ?? $r->prompt_key,
                'model' => $r->model,
                'in_tok' => $r->input_tokens,
                'out_tok' => $r->output_tokens,
                'dkk' => $r->cost_usd * self::DKK_PER_USD,
                'latency' => $r->latency_ms,
            ]);

        return view('admin.usage.index', [
            'course' => $course,
            'scope' => $scope,
            'cards' => $cards,
            'perPrompt' => $perPrompt,
            'perModel' => $perModel,
            'daily' => $daily,
            'latest' => $latest,
            'dkkRate' => self::DKK_PER_USD,
        ]);
    }

    private function summarise($q): array
    {
        $row = $q->selectRaw('COUNT(*) as calls, SUM(input_tokens + output_tokens) as tokens, SUM(cost_usd) as usd')->first();
        return [
            'calls' => (int) ($row->calls ?? 0),
            'tokens' => (int) ($row->tokens ?? 0),
            'dkk' => (float) ($row->usd ?? 0) * self::DKK_PER_USD,
        ];
    }
}
