<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    private function roleSlug($user): string
    {
        // dukung role string langsung ATAU relasi role->role
        $roleRaw = $user?->role?->role ?? $user?->role ?? '';
        return strtolower(str_replace(' ', '_', trim((string) $roleRaw)));
    }

    /**
     * Paksa redirect setelah login sesuai role
     * (bypass redirect()->intended supaya tidak nyasar ke halaman pasien).
     */
    protected function authenticated(Request $request, $user)
    {
        // optional tapi sangat membantu biar tidak balik ke halaman terakhir (pasien)
        $request->session()->forget('url.intended');

        $role = $this->roleSlug($user);

        if ($role === 'super_admin') {
            return redirect()->route('admin.dashboard');
        }

        if (str_starts_with($role, 'dokter_')) {
            return redirect()->route('dokter.dashboard');
        }

        return redirect()->route('home');
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
