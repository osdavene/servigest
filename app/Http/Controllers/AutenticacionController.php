<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutenticacionController extends Controller
{
    /**
     * Muestra el formulario de acceso estándar para Talleres y Técnicos.
     */
    public function mostrarFormularioLogin()
    {
        if (Auth::check()) {
            return redirect()->route('panel.index');
        }

        return view('auth.login');
    }

    /**
     * Procesa el inicio de sesión de Talleres y Técnicos.
     */
    public function iniciarSesion(Request $request)
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $recordar = $request->boolean('recordar');

        if (Auth::attempt(['email' => $credenciales['email'], 'password' => $credenciales['password'], 'esta_activo' => true], $recordar)) {
            $request->session()->regenerate();

            $usuario = Auth::user();

            // Si es SuperAdmin que ingresó por el login general, redirige a su panel maestro
            if ($usuario->esSuperAdmin()) {
                return redirect()->intended(route('superadmin.talleres.index'));
            }

            return redirect()->intended(route('panel.index'));
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden o la cuenta está inactiva.',
        ])->onlyInput('email');
    }

    /**
     * Muestra el portal exclusivo de acceso para el Super Administrador del SaaS.
     */
    public function mostrarFormularioLoginSuperAdmin()
    {
        if (Auth::check() && Auth::user()->esSuperAdmin()) {
            return redirect()->route('superadmin.talleres.index');
        }

        return view('auth.superadmin_login');
    }

    /**
     * Procesa el login exclusivo de SuperAdmin.
     */
    public function iniciarSesionSuperAdmin(Request $request)
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['email' => $credenciales['email'], 'password' => $credenciales['password'], 'rol' => 'super_administrador', 'esta_activo' => true], $request->boolean('recordar'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('superadmin.talleres.index'));
        }

        return back()->withErrors([
            'email' => 'Acceso denegado. Credenciales de Super Administrador inválidas.',
        ])->onlyInput('email');
    }

    /**
     * Cierra la sesión activa.
     */
    public function cerrarSesion(Request $request)
    {
        $esSuperAdmin = Auth::check() && Auth::user()->esSuperAdmin();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($esSuperAdmin) {
            return redirect()->route('superadmin.login')->with('exito', 'Sesión de Super Administrador finalizada.');
        }

        return redirect()->route('login')->with('exito', 'Sesión finalizada exitosamente.');
    }
}
