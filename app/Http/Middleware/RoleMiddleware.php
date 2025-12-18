<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // ambil role dari relasi (roles.role) atau kolom role lama
        $roleRaw = $user?->role?->role ?? $user?->role ?? '';
        $userRole = strtolower(str_replace(' ', '_', trim((string) $roleRaw)));

        foreach ($roles as $allowed) {
            $allowed = strtolower(str_replace(' ', '_', trim((string) $allowed)));

            // support wildcard: dokter_*
            if (str_contains($allowed, '*')) {
                $prefix = rtrim($allowed, '*');
                if ($prefix !== '' && str_starts_with($userRole, $prefix)) {
                    return $next($request);
                }
            } else {
                if ($userRole === $allowed) {
                    return $next($request);
                }
            }
        }

        return redirect()->route('home');
    }
}
