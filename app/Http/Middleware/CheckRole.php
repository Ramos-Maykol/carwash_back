<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Verificar si está logueado
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = $request->user();

        // 2. VERIFICACIÓN CORRECTA CON SPATIE
        // El método hasRole() verifica automáticamente si el usuario tiene el rol,
        // ya sea que lo pases como string o array, y maneja la lógica interna.
        if (! $user->hasRole($role)) { 
            return response()->json(['message' => 'Unauthorized. Role mismatch.'], 403);
        }

        return $next($request);
    }
}