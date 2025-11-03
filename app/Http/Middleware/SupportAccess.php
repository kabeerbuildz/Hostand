<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SupportAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // Check if user has access to support module
        if (!$user || !in_array($user->type, ['super admin', 'admin', 'owner', 'tenant', 'maintainer'])) {
            return redirect()->route('dashboard')->with('error', 'You do not have access to the support module.');
        }
        
        return $next($request);
    }
} 