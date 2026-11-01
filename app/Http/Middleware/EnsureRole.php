<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi akses berdasarkan role (PRD §2 & §9 DoD F1).
 *
 * Pemakaian: ->middleware('role:admin') atau 'role:admin,operator'.
 * Manajer selalu read-only, sehingga method tulis ditolak lebih awal.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->guard()->logout();

            return redirect()->route('login')
                ->withErrors(['username' => 'Akun Anda dinonaktifkan. Hubungi Admin Gudang.']);
        }

        if (! in_array($user->role->value, $roles, true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
