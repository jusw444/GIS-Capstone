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
        // 1️⃣ Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 2️⃣ Check if user role matches allowed roles
        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized'); // forbidden
        }

        // 3️⃣ Optional: Check category if route has 'category' parameter
        if ($user->role === 'admin' && $request->route('category')) {
            if ($user->category !== $request->route('category')) {
                abort(403, 'Unauthorized: Admin category mismatch');
            }
        }

        return $next($request);
    }
}