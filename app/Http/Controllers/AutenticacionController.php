<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\AuditoriaAccesoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AutenticacionController extends Controller
{
    public function __construct(
        protected AuditoriaAccesoService $auditoriaService
    ) {}

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
        $forzarCierre = $request->boolean('forzar_cierre');

        $usuario = Usuario::where('email', $credenciales['email'])->first();

        // 1. Validar si el usuario existe, su contraseña es correcta y está activo
        if (!$usuario || !Hash::check($credenciales['password'], $usuario->password) || !$usuario->esta_activo) {
            $this->auditoriaService->registrarLoginFallido($credenciales['email'], $request, 'Credenciales incorrectas o cuenta inactiva');

            return back()->withErrors([
                'email' => 'Las credenciales proporcionadas no coinciden o la cuenta está inactiva.',
            ])->onlyInput('email');
        }

        // 2. Si el usuario ya tiene una sesión activa y aún no ha confirmado forzar el cierre
        if ($usuario->estaEnLinea() && !$forzarCierre) {
            $ultimoAcceso = $this->auditoriaService->obtenerUltimoAccesoActivo($usuario);

            return back()->with('sesion_activa_detectada', [
                'email' => $usuario->email,
                'password_temp' => $credenciales['password'],
                'dispositivo' => $ultimoAcceso?->dispositivo ?? 'Otro dispositivo',
                'navegador' => $ultimoAcceso?->navegador ?? 'Navegador Web',
                'ubicacion' => $ultimoAcceso?->ciudad ? ($ultimoAcceso->ciudad . ', ' . $ultimoAcceso->pais) : 'Ubicación remota',
                'ip' => $ultimoAcceso?->ip_address ?? $usuario->ultimo_login_ip ?? 'IP Desconocida',
                'fecha' => $ultimoAcceso?->fecha_ingreso?->format('d/m/Y h:i A') ?? $usuario->ultimo_login_at?->format('d/m/Y h:i A') ?? 'Recientemente',
            ])->onlyInput('email');
        }

        // 3. Autenticar e invalidar sesión previa
        Auth::login($usuario, $recordar);
        $request->session()->regenerate();
        $sessionId = $request->session()->getId();

        $usuario->update([
            'current_session_id' => $sessionId,
            'ultimo_login_at' => now(),
            'ultimo_login_ip' => $request->ip(),
        ]);

        $this->auditoriaService->registrarLoginExitoso($usuario, $request, $sessionId);

        // Si es SuperAdmin que ingresó por el login general, redirige a su panel maestro
        if ($usuario->esSuperAdmin()) {
            return redirect()->intended(route('superadmin.talleres.index'));
        }

        return redirect()->intended(route('panel.index'));
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

        $forzarCierre = $request->boolean('forzar_cierre');
        $usuario = Usuario::where('email', $credenciales['email'])->where('rol', 'super_administrador')->first();

        if (!$usuario || !Hash::check($credenciales['password'], $usuario->password) || !$usuario->esta_activo) {
            $this->auditoriaService->registrarLoginFallido($credenciales['email'], $request, 'Credenciales de SuperAdmin inválidas');

            return back()->withErrors([
                'email' => 'Acceso denegado. Credenciales de Super Administrador inválidas.',
            ])->onlyInput('email');
        }

        if ($usuario->estaEnLinea() && !$forzarCierre) {
            $ultimoAcceso = $this->auditoriaService->obtenerUltimoAccesoActivo($usuario);

            return back()->with('sesion_activa_detectada', [
                'email' => $usuario->email,
                'password_temp' => $credenciales['password'],
                'dispositivo' => $ultimoAcceso?->dispositivo ?? 'Otro dispositivo',
                'navegador' => $ultimoAcceso?->navegador ?? 'Navegador Web',
                'ubicacion' => $ultimoAcceso?->ciudad ? ($ultimoAcceso->ciudad . ', ' . $ultimoAcceso->pais) : 'Ubicación remota',
                'ip' => $ultimoAcceso?->ip_address ?? $usuario->ultimo_login_ip ?? 'IP Desconocida',
                'fecha' => $ultimoAcceso?->fecha_ingreso?->format('d/m/Y h:i A') ?? $usuario->ultimo_login_at?->format('d/m/Y h:i A') ?? 'Recientemente',
            ])->onlyInput('email');
        }

        Auth::login($usuario, $request->boolean('recordar'));
        $request->session()->regenerate();
        $sessionId = $request->session()->getId();

        $usuario->update([
            'current_session_id' => $sessionId,
            'ultimo_login_at' => now(),
            'ultimo_login_ip' => $request->ip(),
        ]);

        $this->auditoriaService->registrarLoginExitoso($usuario, $request, $sessionId);

        return redirect()->intended(route('superadmin.talleres.index'));
    }

    /**
     * Cierra la sesión activa.
     */
    public function cerrarSesion(Request $request)
    {
        $usuario = Auth::user();
        $sessionId = $request->session()->getId();
        $esSuperAdmin = $usuario && $usuario->esSuperAdmin();
        $motivo = $request->input('motivo');

        if ($usuario) {
            $estadoCierre = ($motivo === 'inactividad') ? 'inactividad' : 'logout';
            $this->auditoriaService->registrarCierreSesion($usuario, $sessionId, $estadoCierre);

            $usuario->update([
                'current_session_id' => null,
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($motivo === 'inactividad') {
            $mensaje = 'Tu sesión se cerró automáticamente por 15 minutos de inactividad por motivos de seguridad.';
            if ($esSuperAdmin) {
                return redirect()->route('superadmin.login')->with('error', $mensaje);
            }
            return redirect()->route('login')->with('error', $mensaje);
        }

        if ($esSuperAdmin) {
            return redirect()->route('superadmin.login')->with('exito', 'Sesión de Super Administrador finalizada.');
        }

        return redirect()->route('login')->with('exito', 'Sesión finalizada exitosamente.');
    }
}
