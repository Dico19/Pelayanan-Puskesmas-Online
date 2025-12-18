<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DokterOnly
{
    public function handle(Request $request, Closure $next)
    {
        $role = $request->user()?->role?->role;

        // hanya role yang diawali "dokter_"
        if (!str_starts_with((string) $role, 'dokter_')) {
            abort(403, 'Akses khusus dokter.');
        }

        return $next($request);
    }
}
