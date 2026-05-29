<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Destination;

class DestinationAccess
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
        
        // Verificar se o usuário tem role de administrador de destino
        if (!$user->role || $user->role->name !== 'administrador de destino') {
            return response()->json(['message' => 'Acesso negado. Apenas administradores de destino podem acessar.'], 403);
        }
        
        // Verificar se existe um destino associado ao usuário
        $destination = Destination::where('user_id', $user->id)->first();
        
        if (!$destination) {
            return response()->json(['message' => 'Destino não encontrado para este usuário.'], 404);
        }
        
        // Adicionar o destino ao request para uso nos controllers
        $request->attributes->add(['destination' => $destination]);
        
        return $next($request);
    }
}