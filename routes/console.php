<?php

use App\Models\Usuario;
use App\Models\RegistroAcceso;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Comando para cerrar sesiones y registros de auditoría que quedaron huérfanos por inactividad (> 15 minutos).
 */
Artisan::command('sesiones:limpiar-inactivas', function () {
    $limiteInactividad = now()->subMinutes(15);

    // 1. Usuarios con sesión abierta que llevan más de 15 minutos sin registrar actividad
    $usuariosInactivos = Usuario::whereNotNull('current_session_id')
        ->where(function ($query) use ($limiteInactividad) {
            $query->where('ultima_actividad_at', '<', $limiteInactividad)
                  ->orWhere(function ($q) use ($limiteInactividad) {
                      $q->whereNull('ultima_actividad_at')
                        ->where('ultimo_login_at', '<', $limiteInactividad);
                  });
        })
        ->get();

    $cerradas = 0;
    foreach ($usuariosInactivos as $usuario) {
        $sessionId = $usuario->current_session_id;

        // Liberar sesión única en la tabla de usuarios
        $usuario->update(['current_session_id' => null]);

        // Cerrar el registro de auditoría correspondiente
        RegistroAcceso::where('usuario_id', $usuario->id)
            ->where(function ($q) use ($sessionId) {
                if ($sessionId) {
                    $q->where('session_id', $sessionId);
                }
            })
            ->whereNull('fecha_cierre')
            ->update([
                'fecha_cierre' => $usuario->ultima_actividad_at ?? now(),
                'estado' => 'cerrado_por_inactividad',
            ]);

        $cerradas++;
    }

    // 2. Limpieza de registros antiguos que hayan quedado abiertos por más de 24 horas
    $antiguosCerrados = RegistroAcceso::whereNull('fecha_cierre')
        ->where('fecha_ingreso', '<', now()->subHours(24))
        ->update([
            'fecha_cierre' => now(),
            'estado' => 'cerrado_por_inactividad',
        ]);

    $this->info("✅ Limpieza completada: {$cerradas} sesiones inactivas cerradas, {$antiguosCerrados} registros huérfanos regularizados.");
})->purpose('Cierra las sesiones y registros de acceso sin actividad durante más de 15 minutos');

// Programar la ejecución automática periódica cada 5 minutos
Schedule::command('sesiones:limpiar-inactivas')->everyFiveMinutes();
