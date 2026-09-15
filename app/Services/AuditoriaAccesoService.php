<?php

namespace App\Services;

use App\Models\RegistroAcceso;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuditoriaAccesoService
{
    /**
     * Registra un inicio de sesión exitoso.
     */
    public function registrarLoginExitoso(Usuario $usuario, Request $request, string $sessionId): RegistroAcceso
    {
        $infoDispositivo = $this->analizarUserAgent($request->userAgent());
        $ubicacion = $this->obtenerUbicacionIp($request->ip());

        // Cerrar registros previos que hayan quedado abiertos
        RegistroAcceso::where('usuario_id', $usuario->id)
            ->whereNull('fecha_cierre')
            ->where('estado', 'exitoso')
            ->update([
                'fecha_cierre' => now(),
                'estado' => 'cerrado_por_otra_sesion',
            ]);

        return RegistroAcceso::create([
            'usuario_id' => $usuario->id,
            'taller_id' => $usuario->taller_id,
            'email_ingresado' => $usuario->email,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'dispositivo' => $infoDispositivo['dispositivo'],
            'navegador' => $infoDispositivo['navegador'],
            'sistema_operativo' => $infoDispositivo['sistema_operativo'],
            'pais' => $ubicacion['pais'],
            'ciudad' => $ubicacion['ciudad'],
            'estado' => 'exitoso',
            'session_id' => $sessionId,
            'fecha_ingreso' => now(),
        ]);
    }

    /**
     * Registra un intento de inicio de sesión fallido.
     */
    public function registrarLoginFallido(string $email, Request $request, string $motivo = 'Credenciales inválidas'): RegistroAcceso
    {
        $usuario = Usuario::where('email', $email)->first();
        $infoDispositivo = $this->analizarUserAgent($request->userAgent());
        $ubicacion = $this->obtenerUbicacionIp($request->ip());

        return RegistroAcceso::create([
            'usuario_id' => $usuario?->id,
            'taller_id' => $usuario?->taller_id,
            'email_ingresado' => $email,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'dispositivo' => $infoDispositivo['dispositivo'],
            'navegador' => $infoDispositivo['navegador'],
            'sistema_operativo' => $infoDispositivo['sistema_operativo'],
            'pais' => $ubicacion['pais'],
            'ciudad' => $ubicacion['ciudad'],
            'estado' => 'fallido',
            'session_id' => null,
            'fecha_ingreso' => now(),
            'fecha_cierre' => now(),
        ]);
    }

    /**
     * Registra el cierre de sesión voluntario o por inactividad.
     */
    public function registrarCierreSesion(?Usuario $usuario, ?string $sessionId, string $estado = 'logout'): void
    {
        if ($sessionId) {
            RegistroAcceso::where('session_id', $sessionId)
                ->whereNull('fecha_cierre')
                ->update([
                    'fecha_cierre' => now(),
                    'estado' => $estado,
                ]);
        } elseif ($usuario) {
            RegistroAcceso::where('usuario_id', $usuario->id)
                ->whereNull('fecha_cierre')
                ->latest('fecha_ingreso')
                ->first()
                ?->update([
                    'fecha_cierre' => now(),
                    'estado' => $estado,
                ]);
        }
    }

    /**
     * Fuerza el cierre de la sesión activa de un usuario por parte del administrador.
     */
    public function forzarCierreSesion(Usuario $usuario): void
    {
        $this->registrarCierreSesion($usuario, $usuario->current_session_id, 'cerrado_por_administrador');

        $usuario->update([
            'current_session_id' => null,
        ]);
    }

    /**
     * Obtiene el último registro de acceso activo de un usuario.
     */
    public function obtenerUltimoAccesoActivo(Usuario $usuario): ?RegistroAcceso
    {
        return RegistroAcceso::where('usuario_id', $usuario->id)
            ->where('estado', 'exitoso')
            ->latest('fecha_ingreso')
            ->first();
    }

    /**
     * Analiza el User-Agent para extraer Sistema Operativo, Dispositivo y Navegador.
     */
    public function analizarUserAgent(?string $userAgent): array
    {
        $ua = $userAgent ?? '';
        
        // 1. Detección de Dispositivo y SO
        $so = 'Desconocido';
        $dispositivo = 'Computadora';

        if (preg_match('/iPhone/i', $ua)) {
            $dispositivo = 'iPhone';
            $so = 'iOS';
        } elseif (preg_match('/iPad/i', $ua)) {
            $dispositivo = 'iPad (Tablet)';
            $so = 'iPadOS';
        } elseif (preg_match('/Android/i', $ua)) {
            $dispositivo = preg_match('/Mobile/i', $ua) ? 'Celular Android' : 'Tablet Android';
            $so = 'Android';
        } elseif (preg_match('/Windows NT 10.0/i', $ua)) {
            $so = 'Windows 10 / 11';
            $dispositivo = 'PC Windows';
        } elseif (preg_match('/Windows/i', $ua)) {
            $so = 'Windows';
            $dispositivo = 'PC Windows';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) {
            $so = 'macOS';
            $dispositivo = 'Mac / Apple';
        } elseif (preg_match('/Linux/i', $ua)) {
            $so = 'Linux';
            $dispositivo = 'PC Linux';
        }

        // 2. Detección de Navegador
        $navegador = 'Navegador Web';
        if (preg_match('/Edg\/([0-9\.]+)/i', $ua)) {
            $navegador = 'Microsoft Edge';
        } elseif (preg_match('/Chrome\/([0-9\.]+)/i', $ua) && !preg_match('/Edg/i', $ua)) {
            $navegador = 'Google Chrome';
        } elseif (preg_match('/Safari\/([0-9\.]+)/i', $ua) && !preg_match('/Chrome/i', $ua)) {
            $navegador = 'Apple Safari';
        } elseif (preg_match('/Firefox\/([0-9\.]+)/i', $ua)) {
            $navegador = 'Mozilla Firefox';
        } elseif (preg_match('/Opera|OPR\//i', $ua)) {
            $navegador = 'Opera';
        }

        return [
            'dispositivo' => $dispositivo,
            'sistema_operativo' => $so,
            'navegador' => $navegador,
        ];
    }

    /**
     * Obtiene la ciudad y país a partir de la IP (con timeout ultracorto y caché).
     */
    public function obtenerUbicacionIp(?string $ip): array
    {
        if (empty($ip) || in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.') || str_starts_with($ip, '172.')) {
            return [
                'pais' => 'Colombia',
                'ciudad' => 'Red Local / Taller',
            ];
        }

        return Cache::remember("geoip_{$ip}", 86400, function () use ($ip) {
            try {
                $response = Http::timeout(1.2)->get("http://ip-api.com/json/{$ip}?fields=status,country,city,regionName");
                if ($response->successful() && $response->json('status') === 'success') {
                    return [
                        'pais' => $response->json('country') ?? 'Colombia',
                        'ciudad' => $response->json('city') ?? ($response->json('regionName') ?? 'Colombia'),
                    ];
                }
            } catch (\Throwable $e) {
                // Si falla el servicio externo o timeout, no interrumpir
            }

            return [
                'pais' => 'Colombia',
                'ciudad' => 'Colombia',
            ];
        });
    }
}
