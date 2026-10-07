<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\AuditoriaAccesoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        // 0. Si se confirmó el desalojo de la sesión previa mediante token de un solo uso
        if ($request->boolean('forzar_cierre')) {
            $tokenDesalojo = (string) $request->input('token_desalojo');
            $datosDesalojo = $tokenDesalojo ? Cache::pull('desalojo_' . $tokenDesalojo) : null;

            if (!$datosDesalojo || empty($datosDesalojo['usuario_id'])) {
                return redirect()->route('login')->with('error', 'El tiempo de confirmación expiró o el enlace no es válido. Por favor ingrese sus credenciales nuevamente.');
            }

            $usuario = Usuario::find($datosDesalojo['usuario_id']);
            if (!$usuario || !$usuario->esta_activo) {
                return redirect()->route('login')->with('error', 'La cuenta se encuentra inactiva o fue deshabilitada.');
            }

            Auth::login($usuario, (bool) ($datosDesalojo['recordar'] ?? false));
            $request->session()->regenerate();
            $sessionId = $request->session()->getId();

            $usuario->update([
                'current_session_id' => $sessionId,
                'ultimo_login_at' => now(),
                'ultimo_login_ip' => $request->ip(),
            ]);

            $this->auditoriaService->registrarLoginExitoso($usuario, $request, $sessionId);

            if ($usuario->esSuperAdmin()) {
                return redirect()->intended(route('superadmin.talleres.index'));
            }

            return redirect()->intended(route('panel.index'));
        }

        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $recordar = $request->boolean('recordar');

        $usuario = Usuario::where('email', $credenciales['email'])->first();

        // 1. Validar si el usuario existe, su contraseña es correcta y está activo
        if (!$usuario || !Hash::check($credenciales['password'], $usuario->password) || !$usuario->esta_activo) {
            $this->auditoriaService->registrarLoginFallido($credenciales['email'], $request, 'Credenciales incorrectas o cuenta inactiva');

            return back()->withErrors([
                'email' => 'Las credenciales proporcionadas no coinciden o la cuenta está inactiva.',
            ])->onlyInput('email');
        }

        // 2. Si el usuario ya tiene una sesión activa: generar token seguro de desalojo (TTL: 2 minutos)
        if ($usuario->estaEnLinea()) {
            $ultimoAcceso = $this->auditoriaService->obtenerUltimoAccesoActivo($usuario);
            $tokenDesalojo = Str::random(40);

            Cache::put('desalojo_' . $tokenDesalojo, [
                'usuario_id' => $usuario->id,
                'email' => $usuario->email,
                'recordar' => $recordar,
            ], now()->addMinutes(2));

            return back()->with('sesion_activa_detectada', [
                'email' => $usuario->email,
                'token_desalojo' => $tokenDesalojo,
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
        // 0. Confirmación de desalojo con token seguro de un solo uso
        if ($request->boolean('forzar_cierre')) {
            $tokenDesalojo = (string) $request->input('token_desalojo');
            $datosDesalojo = $tokenDesalojo ? Cache::pull('desalojo_' . $tokenDesalojo) : null;

            if (!$datosDesalojo || empty($datosDesalojo['usuario_id'])) {
                return redirect()->route('superadmin.login')->with('error', 'El tiempo de confirmación expiró o el enlace no es válido. Por favor ingrese de nuevo.');
            }

            $usuario = Usuario::where('id', $datosDesalojo['usuario_id'])->where('rol', 'super_administrador')->first();
            if (!$usuario || !$usuario->esta_activo) {
                return redirect()->route('superadmin.login')->with('error', 'La cuenta de Super Administrador está inactiva o no existe.');
            }

            Auth::login($usuario, (bool) ($datosDesalojo['recordar'] ?? false));
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

        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $recordar = $request->boolean('recordar');
        $usuario = Usuario::where('email', $credenciales['email'])->where('rol', 'super_administrador')->first();

        if (!$usuario || !Hash::check($credenciales['password'], $usuario->password) || !$usuario->esta_activo) {
            $this->auditoriaService->registrarLoginFallido($credenciales['email'], $request, 'Credenciales de SuperAdmin inválidas');

            return back()->withErrors([
                'email' => 'Acceso denegado. Credenciales de Super Administrador inválidas.',
            ])->onlyInput('email');
        }

        if ($usuario->estaEnLinea()) {
            $ultimoAcceso = $this->auditoriaService->obtenerUltimoAccesoActivo($usuario);
            $tokenDesalojo = Str::random(40);

            Cache::put('desalojo_' . $tokenDesalojo, [
                'usuario_id' => $usuario->id,
                'email' => $usuario->email,
                'recordar' => $recordar,
            ], now()->addMinutes(2));

            return back()->with('sesion_activa_detectada', [
                'email' => $usuario->email,
                'token_desalojo' => $tokenDesalojo,
                'dispositivo' => $ultimoAcceso?->dispositivo ?? 'Otro dispositivo',
                'navegador' => $ultimoAcceso?->navegador ?? 'Navegador Web',
                'ubicacion' => $ultimoAcceso?->ciudad ? ($ultimoAcceso->ciudad . ', ' . $ultimoAcceso->pais) : 'Ubicación remota',
                'ip' => $ultimoAcceso?->ip_address ?? $usuario->ultimo_login_ip ?? 'IP Desconocida',
                'fecha' => $ultimoAcceso?->fecha_ingreso?->format('d/m/Y h:i A') ?? $usuario->ultimo_login_at?->format('d/m/Y h:i A') ?? 'Recientemente',
            ])->onlyInput('email');
        }

        Auth::login($usuario, $recordar);
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
