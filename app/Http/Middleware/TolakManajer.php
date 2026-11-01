<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menolak operasi tulis untuk role Manajer (read-only, PRD §2).
 *
 * Dipasang pada route group yang mengubah data.
 */
class TolakManajer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Manajer tidak boleh menulis apa pun; GET/HEAD tetap diizinkan.
        if ($user && ! $user->canWrite() && ! $request->isMethodSafe()) {
            abort(403, 'Manajer hanya memiliki akses baca (read-only).');
        }

        return $next($request);
    }
}
