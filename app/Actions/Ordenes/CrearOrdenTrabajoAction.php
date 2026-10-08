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
                $clienteId = $orden->cliente_id ?: 'general';
                $carpetaDestino = "servigest/talleres/taller_{$orden->taller_id}/clientes/cliente_{$clienteId}/ordenes/orden_{$orden->id}/evidencias";
                $rutaOptimizada = OptimizadorImagenes::optimizarYGuardar($fotoInicial, $carpetaDestino);

                EvidenciaFotografica::create([
                    'taller_id' => $orden->taller_id,
                    'orden_trabajo_id' => $orden->id,
                    'ruta_imagen' => $rutaOptimizada,
                    'etiqueta' => 'como_se_recibe',
                    'descripcion' => 'Foto inicial de recepción del equipo.',
                ]);
            }

            // Registrar abono o anticipo inicial si el cliente dejó un pago al ingresar
            $abonoInicial = (float)($datos['abono_inicial'] ?? 0);
            if ($abonoInicial > 0) {
                \App\Models\PagoOrden::create([
                    'taller_id' => $orden->taller_id,
                    'orden_trabajo_id' => $orden->id,
                    'usuario_id' => auth()->id(),
                    'monto' => $abonoInicial,
                    'metodo_pago' => $datos['metodo_pago_abono'] ?? 'efectivo',
                    'referencia' => $datos['referencia_abono'] ?? null,
                    'notas' => 'Anticipo inicial recibido al ingreso del equipo.',
                    'fecha_pago' => now(),
                ]);
            }

            // Notificación automática si está configurada (despachada fuera de la transacción para no retener bloqueos de BD)
            DB::afterCommit(function () use ($orden) {
                \App\Services\WhatsAppNotificationService::enviarNotificacionAutomatica($orden, 'creada');
            });

            return $orden;
        });
    }
}
