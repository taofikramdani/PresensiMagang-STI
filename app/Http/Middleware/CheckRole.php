<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // If no roles specified, allow all authenticated users
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has one of the required roles
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // If user doesn't have required role, return 403 or redirect to their dashboard
        if ($user->role === 'peserta') {
            // Only redirect if not already on peserta routes
            if (!$request->is('peserta/*')) {
                return redirect('/peserta/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
        } elseif (in_array($user->role, ['admin', 'pembimbing'])) {
            // Only redirect if not already on admin routes
            if (!$request->is('dashboard') && !$request->is('peserta') && !$request->is('pembimbing') && !$request->is('jam-kerja') && !$request->is('lokasi')) {
                return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
        }

        // If we reach here, return 403
        abort(403, 'Akses ditolak.');
    }
}
