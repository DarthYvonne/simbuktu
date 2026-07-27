<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the new public site behind HTTP Basic while it is being built.
 * Switch it off with SITE_AUTH_ENABLED=false when the site goes live.
 */
class EnsureSitePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('site.auth.enabled')) {
            return $next($request);
        }

        $user = (string) config('site.auth.user');
        $password = (string) config('site.auth.password');

        $givenUser = $request->getUser();
        $givenPassword = $request->getPassword();

        // hash_equals on both so a wrong username can't be found by timing.
        if (is_string($givenUser) && is_string($givenPassword)
            && hash_equals($user, $givenUser)
            && hash_equals($password, $givenPassword)) {
            return $next($request);
        }

        return response('Simbuktu — siden er ikke åben endnu.', 401, [
            'WWW-Authenticate' => 'Basic realm="Simbuktu", charset="UTF-8"',
        ]);
    }
}
