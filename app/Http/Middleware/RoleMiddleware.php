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
     * @param  string ...$roles Allowed roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
{
    // ✅ Allow login route always
    if ($request->routeIs('login')) {
        return $next($request);
    }

    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    if (!in_array($user->role, $roles)) {
        abort(403, 'Unauthorized');
    }

    if ($user->role === 'admin' && $request->route('category')) {
        if ($user->category !== $request->route('category')) {
            abort(403, 'Unauthorized: Admin category mismatch');
        }
    }

    return $next($request);
}
}