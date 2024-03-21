<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CustomRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated.'], 403);
        }

        // Get the authenticated user
        $user = Auth::user();

        $userRole = $this->checkUserRole($user);

        if (!$userRole) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        return $next($request);

    }

    private function checkUserRole($user)
    {
        switch (true) {
        case $user->hasRole('superadmin'):
            return 'superadmin';
            break;

        case $user->hasRole('admin'):
            return 'admin';
            break;

        case $user->hasRole('manager'):
            return 'manager';
            break;

        return false;
    }
}
