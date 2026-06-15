<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyServiceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('services.stats_service.token');

        if (! is_string($configuredToken) || trim($configuredToken) === '') {
            if (config('services.stats_service.require_token')) {
                abort(503, 'Service token is not configured.');
            }

            return $next($request);
        }

        $configuredToken = trim($configuredToken);
        $providedToken = $request->header('X-Service-Token');
        $providedToken = is_string($providedToken) ? trim($providedToken) : '';

        if ($providedToken === '' || ! hash_equals($configuredToken, $providedToken)) {
            abort(401, 'Invalid service token.');
        }

        return $next($request);
    }
}
