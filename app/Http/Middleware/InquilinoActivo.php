<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InquilinoActivo
{
    /**
     * Valida que el usuario pertenezca a un taller con suscripción activa.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        // Si es SuperAdmin, no requiere validación de suscripción de taller individual
        if ($usuario->esSuperAdmin()) {
            return $next($request);
        }

        $taller = $usuario->taller;

        if (!$taller) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Su usuario no está vinculado a ningún taller activo.');
        }

        if (!$taller->estaSuscripcionActiva()) {
            return response()->view('errores.suscripcion_vencida', [
                'taller' => $taller
            ], 403);
        }

        return $next($request);
    }
}
