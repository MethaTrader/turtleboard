<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAccountManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || ($request->user()->role !== 'account_manager' && $request->user()->role !== 'administrator')) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have the required permissions.');
        }

        return $next($request);
    }
}