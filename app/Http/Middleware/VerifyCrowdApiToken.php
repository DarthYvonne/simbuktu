<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the Crowd API with a single shared secret. Callers send the token in
 * an `X-Api-Token` header (or as a Bearer token). Constant-time comparison so
 * the check can't be timed. A missing server-side token is a misconfiguration,
 * not an auth failure — fail loud with 503 rather than silently allowing all.
 */
class VerifyCrowdApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.crowd_api.token');
        if ($expected === '') {
            abort(503, 'Crowd API token is not configured (set CROWD_API_TOKEN).');
        }

        $provided = $request->header('X-Api-Token') ?: $request->bearerToken();
        if (!is_string($provided) || !hash_equals($expected, $provided)) {
            abort(401, 'Invalid or missing API token.');
        }

        return $next($request);
    }
}
