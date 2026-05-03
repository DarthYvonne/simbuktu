<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Llm\GeminiClient;
use App\Services\Llm\GrokClient;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\DB;

class ApiCheckController extends Controller
{
    public function index()
    {
        $checks = [
            $this->run('Database',          fn () => $this->checkDatabase()),
            $this->run('Gemini (LLM)',      fn () => $this->checkGemini()),
            $this->run('Gemini (image)',    fn () => $this->checkGeminiImage()),
            $this->run('Grok (LLM)',        fn () => $this->checkGrok()),
            $this->run('Unsplash',          fn () => $this->checkUnsplash()),
            $this->run('Mail (Resend)',     fn () => $this->checkResend()),
        ];

        return view('admin.api-check.index', ['checks' => $checks]);
    }

    private function run(string $name, callable $fn): array
    {
        $start = microtime(true);
        try {
            $result = $fn();
            return array_merge([
                'name'       => $name,
                'ms'         => (int) round((microtime(true) - $start) * 1000),
            ], $result);
        } catch (\Throwable $e) {
            return [
                'name'   => $name,
                'status' => 'error',
                'detail' => substr($e->getMessage(), 0, 300),
                'ms'     => (int) round((microtime(true) - $start) * 1000),
            ];
        }
    }

    private function checkDatabase(): array
    {
        DB::connection()->getPdo();
        $driver = DB::connection()->getDriverName();
        return ['status' => 'ok', 'detail' => "{$driver} forbundet"];
    }

    private function checkGemini(): array
    {
        $key = config('gemini.api_key');
        if (!$key) return ['status' => 'missing', 'detail' => 'GEMINI_API_KEY ikke sat'];

        $model = 'gemini-2.5-flash-lite';
        $url = config('gemini.base_url') . "/{$model}:generateContent?key={$key}";
        $resp = (new HttpFactory)->timeout(20)->post($url, [
            'contents' => [['parts' => [['text' => 'svar kun "ok"']]]],
            'generationConfig' => ['maxOutputTokens' => 5, 'thinkingConfig' => ['thinkingBudget' => 0]],
        ]);
        if (!$resp->successful()) {
            return ['status' => 'error', 'detail' => "HTTP {$resp->status()} — " . substr((string) $resp->body(), 0, 200)];
        }
        return ['status' => 'ok', 'detail' => "{$model} svarede"];
    }

    private function checkGeminiImage(): array
    {
        $key = config('gemini.api_key');
        if (!$key) return ['status' => 'missing', 'detail' => 'GEMINI_API_KEY ikke sat'];

        $model = config('gemini.image_model', 'gemini-2.5-flash-image');
        $url = config('gemini.base_url') . "/{$model}?key={$key}";
        $resp = (new HttpFactory)->timeout(15)->get($url);
        if (!$resp->successful()) {
            return ['status' => 'error', 'detail' => "HTTP {$resp->status()} på model-info — " . substr((string) $resp->body(), 0, 200)];
        }
        return ['status' => 'ok', 'detail' => "{$model} tilgængelig"];
    }

    private function checkGrok(): array
    {
        $key = config('services.grok.api_key');
        if (!$key) return ['status' => 'missing', 'detail' => 'GROK_API_KEY ikke sat'];

        $resp = (new HttpFactory)
            ->withToken($key)
            ->timeout(20)
            ->post('https://api.x.ai/v1/chat/completions', [
                'model' => 'grok-3-mini',
                'messages' => [['role' => 'user', 'content' => 'svar kun "ok"']],
                'max_tokens' => 5,
            ]);
        if (!$resp->successful()) {
            return ['status' => 'error', 'detail' => "HTTP {$resp->status()} — " . substr((string) $resp->body(), 0, 200)];
        }
        return ['status' => 'ok', 'detail' => 'grok-3-mini svarede'];
    }

    private function checkUnsplash(): array
    {
        $key = config('services.unsplash.access_key');
        if (!$key) return ['status' => 'missing', 'detail' => 'UNSPLASH_ACCESS_KEY ikke sat'];

        $resp = (new HttpFactory)
            ->withHeaders(['Authorization' => "Client-ID {$key}"])
            ->timeout(15)
            ->get('https://api.unsplash.com/photos/random');
        if (!$resp->successful()) {
            return ['status' => 'error', 'detail' => "HTTP {$resp->status()} — " . substr((string) $resp->body(), 0, 200)];
        }
        return ['status' => 'ok', 'detail' => 'tilfældigt billede hentet'];
    }

    private function checkResend(): array
    {
        $key = config('services.resend.key');
        if (!$key) return ['status' => 'missing', 'detail' => 'RESEND_API_KEY ikke sat'];

        $resp = (new HttpFactory)
            ->withToken($key)
            ->timeout(10)
            ->get('https://api.resend.com/domains');
        if (!$resp->successful()) {
            return ['status' => 'error', 'detail' => "HTTP {$resp->status()} — " . substr((string) $resp->body(), 0, 200)];
        }
        return ['status' => 'ok', 'detail' => 'nøgle gyldig'];
    }
}
