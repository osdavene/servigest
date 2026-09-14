<?php

declare(strict_types=1);

namespace App\Actions\Ordenes;

use App\Models\EvidenciaFotografica;
use App\Models\OrdenTrabajo;
use App\Services\OptimizadorImagenes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CrearOrdenTrabajoAction
{
    /**
     * Crea una orden de trabajo de manera atómica con cálculo de costos, token y evidencia inicial si aplica.
     */
    public function execute(array $datos, ?UploadedFile $fotoInicial = null): OrdenTrabajo
    {
        return DB::transaction(function () use ($datos, $fotoInicial) {
            $manoObra = (float)($datos['costo_mano_obra'] ?? 0);
            $repuestos = (float)($datos['costo_repuestos'] ?? 0);
            
            $datos['costo_mano_obra'] = $manoObra;
            $datos['costo_repuestos'] = $repuestos;
            $datos['costo_total'] = $manoObra + $repuestos;
            $datos['estado'] = 'pendiente';
            $datos['token_publico_pdf'] = (string) Str::uuid();
            $datos['fecha_ingreso'] = now();

            $orden = OrdenTrabajo::create($datos);

            // Si se incluyó foto inicial de recepción
            if ($fotoInicial !== null) {
                $carpetaDestino = "evidencias/{$orden->taller_id}/{$orden->id}";
                $rutaOptimizada = OptimizadorImagenes::optimizarYGuardar($fotoInicial, $carpetaDestino);

                EvidenciaFotografica::create([
                    'taller_id' => $orden->taller_id,
                    'orden_trabajo_id' => $orden->id,
                    'ruta_imagen' => $rutaOptimizada,
                    'etiqueta' => 'como_se_recibe',
                    'descripcion' => 'Foto inicial de recepción del equipo.',
                ]);
            }

            // Notificación automática si está configurada
            \App\Services\WhatsAppNotificationService::enviarNotificacionAutomatica($orden, 'creada');

            return $orden;
        });
    }
}
