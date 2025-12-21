<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RolePrefix
{
    public function handle(Request $request, Closure $next, string $prefix)
    {
        $user = $request->user();
        if (!$user) abort(401);

        // ambil role dari Spatie / kolom / relasi
        $roleRaw = '';

        if (method_exists($user, 'getRoleNames')) {
            $roleRaw = (string) ($user->getRoleNames()->first() ?? '');
        }

        if ($roleRaw === '') {
            $roleRaw = (string) (data_get($user, 'role.role') ?? data_get($user, 'role') ?? '');
        }

        $role = strtolower(str_replace(' ', '_', trim($roleRaw)));

        if ($prefix !== '' && str_starts_with($role, strtolower($prefix))) {
            return $next($request);
        }

        abort(403, 'Forbidden');
    }
}
