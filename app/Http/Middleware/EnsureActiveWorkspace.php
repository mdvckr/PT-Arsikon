<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveWorkspace
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $activeWarehouse = $user->activeWarehouse();

            if ($activeWarehouse) {
                session(['active_warehouse_id' => $activeWarehouse->id]);
                view()->share('activeWarehouse', $activeWarehouse);
            }
        }

        return $next($request);
    }
}
