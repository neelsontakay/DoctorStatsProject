<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrgAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $organization = $request->route('organization');

        if (! $organization instanceof Organization) {
            $organization = Organization::query()->findOrFail($organization);
        }

        $user = $request->user();

        if ($user === null || ! $user->isAdminOf($organization)) {
            abort(403, 'Organization administrator access required.');
        }

        $request->attributes->set('organization', $organization);
        $request->attributes->set('organization_membership', $user->membershipFor($organization));

        return $next($request);
    }
}
