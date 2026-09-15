<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarSesionUnica
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $usuario = Auth::user();
            $currentSessionId = $request->session()->getId();

            // Si el usuario tiene un ID de sesión registrado y no coincide con la sesión de esta petición
            if (!empty($usuario->current_session_id) && $usuario->current_session_id !== $currentSessionId) {
                $esSuperAdmin = $usuario->esSuperAdmin();

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $mensaje = 'Tu sesión fue cerrada porque se inició sesión en otro dispositivo o navegador.';

                if ($esSuperAdmin) {
                    return redirect()->route('superadmin.login')->with('error', $mensaje);
                }

                return redirect()->route('login')->with('error', $mensaje);
            }

            // Actualizar timestamp de última actividad si han pasado más de 2 minutos
            if (empty($usuario->ultimo_login_at) || $usuario->ultimo_login_at->diffInMinutes(now()) >= 2) {
                $usuario->timestamps = false;
                $usuario->update([
                    'ultimo_login_at' => now(),
                ]);
                $usuario->timestamps = true;
            }
        }

        return $next($request);
    }
}
