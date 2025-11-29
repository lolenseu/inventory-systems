<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        
        if (in_array($user->user_role, $roles)) {
            return $next($request);
        }

        // If user doesn't have the required role, redirect to their appropriate dashboard
        if ($user->user_role === 'officer') {
            return redirect()->route('officer.dashboard');
        } else {
            return redirect()->route('user.dashboard');
        }
    }
}