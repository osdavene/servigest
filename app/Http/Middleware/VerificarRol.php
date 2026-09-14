<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Valida que el usuario tenga alguno de los roles permitidos.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        if (in_array($usuario->rol, $roles, true)) {
            return $next($request);
        }

        abort(403, 'Acceso no autorizado para su perfil de usuario.');
    }
}
