<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckApiPermission
{
    private const WRITE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        $client = $this->resolveClient($request);

        if ($client === null) {
            abort(401, 'Unauthenticated.');
        }

        $permissions = json_decode($client->permissions ?? '["*"]', true);

        // wildcard grants everything
        if (in_array('*', $permissions, true)) {
            return $next($request);
        }

        // route-level explicit abilities required
        if (!empty($abilities)) {
            foreach ($abilities as $ability) {
                if (!in_array($ability, $permissions, true)) {
                    abort(403, "Missing required permission: {$ability}");
                }
            }
            return $next($request);
        }

        $resource = $request->segment(2);
        $isWrite  = in_array($request->method(), self::WRITE_METHODS, true);

        if ($isWrite) {
            $allowed = in_array('write', $permissions, true)
                || in_array("{$resource}:write", $permissions, true);

            if (!$allowed) {
                abort(403, 'Write access not permitted for this token.');
            }

            return $next($request);
        }

        // GET request — check read permissions
        $hasGlobalRead   = in_array('read', $permissions, true);
        $hasResourceRead = in_array("{$resource}:read", $permissions, true);

        if (!$hasGlobalRead && !$hasResourceRead) {
            abort(403, 'Read access not permitted for this token.');
        }

        return $next($request);
    }

    private function resolveClient(Request $request): ?object
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken === null) {
            return null;
        }

        return DB::table('api_clients')
            ->where('api_token', hash('sha256', $bearerToken))
            ->first();
    }
}
