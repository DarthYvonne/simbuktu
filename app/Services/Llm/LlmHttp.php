<?php

namespace App\Services\Llm;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;

/**
 * Retry policy shared by every LLM client.
 *
 * Why: vendor APIs (OpenAI, Gemini, Anthropic, Grok) routinely return
 * transient 429 / 5xx during peak hours. Without retries a single hiccup
 * fails every active conversation in the app simultaneously.
 */
class LlmHttp
{
    /**
     * Execute the HTTP request and retry on transient failures
     * (connection errors, 429 rate limit, 5xx server errors).
     * 4xx errors (auth, bad request) are returned to the caller as-is —
     * retrying them is pointless and burns quota.
     */
    public static function send(Closure $makeRequest, int $maxAttempts = 3): Response
    {
        $attempt = 0;
        while (true) {
            $attempt++;
            try {
                /** @var Response $response */
                $response = $makeRequest();
                if ($response->successful()) return $response;

                $transient = $response->status() === 429 || $response->status() >= 500;
                if ($transient && $attempt < $maxAttempts) {
                    usleep($attempt * 400_000); // 400ms, 800ms
                    continue;
                }
                return $response;
            } catch (ConnectionException $e) {
                if ($attempt < $maxAttempts) {
                    usleep($attempt * 400_000);
                    continue;
                }
                throw $e;
            }
        }
    }
}
