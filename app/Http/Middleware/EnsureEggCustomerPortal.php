<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEggCustomerPortal
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('egg_customer_id')) {
            return response()->json([
                'message' => 'Acesso ao portal não autorizado. Introduza o código de acesso.',
            ], 401);
        }

        return $next($request);
    }
}
