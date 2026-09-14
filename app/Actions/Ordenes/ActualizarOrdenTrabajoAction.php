<?php

declare(strict_types=1);

namespace App\Actions\Ordenes;

use App\Models\EvidenciaFotografica;
use App\Models\OrdenTrabajo;
use App\Services\OptimizadorImagenes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ActualizarOrdenTrabajoAction
{
    /**
     * Ejecuta la actualización atómica de la orden de trabajo, firma digital, fotos y ciclos de mantenimiento.
     */
    public function execute(
        OrdenTrabajo $orden,
        array $datos,
        ?string $firmaBase64 = null,
        ?UploadedFile $fotoCierre = null,
        ?string $descripcionFoto = null
    ): OrdenTrabajo {
        return DB::transaction(function () use ($orden, $datos, $firmaBase64, $fotoCierre, $descripcionFoto) {
            $manoObra = (float)($datos['costo_mano_obra'] ?? 0);
            $repuestos = (float)($datos['costo_repuestos'] ?? 0);
            
            $datos['costo_mano_obra'] = $manoObra;
            $datos['costo_repuestos'] = $repuestos;
            $datos['costo_total'] = $manoObra + $repuestos;

            // 1. Manejo automático de fecha de finalización
            if ($datos['estado'] === 'finalizado' && empty($orden->fecha_finalizacion) && empty($datos['fecha_finalizacion'])) {
                $datos['fecha_finalizacion'] = now();
            }

            // 2. Procesamiento de Firma Digital
            if (!empty($firmaBase64) && preg_match('/^data:image\/(\w+);base64,/', $firmaBase64, $tipo)) {
                $datosDecodificados = base64_decode(substr($firmaBase64, strpos($firmaBase64, ',') + 1));
                $extension = strtolower($tipo[1]);
                $nombreArchivo = "firmas/{$orden->taller_id}/orden_{$orden->id}_firma_" . time() . ".{$extension}";

                Storage::disk('public')->put($nombreArchivo, $datosDecodificados);
                $datos['ruta_firma_cliente'] = $nombreArchivo;
                $datos['fecha_firma'] = now();
            }

            // 3. Actualizar la orden
            $orden->update($datos);

            // 4. Side-Effect: Ciclo de mantenimiento en equipo
            if (in_array($orden->estado, ['finalizado', 'entregado'], true)) {
                $this->actualizarCicloMantenimientoEquipo($orden);
            }

            // 5. Procesamiento de Foto de Cierre
            if ($fotoCierre !== null) {
                $this->procesarFotoCierre($orden, $fotoCierre, $descripcionFoto);
            }

            return $orden->fresh(['cliente', 'equipo', 'tecnico', 'evidencias']);
        });
    }

    private function actualizarCicloMantenimientoEquipo(OrdenTrabajo $orden): void
    {
        $equipo = $orden->equipo;
        if (!$equipo) {
            return;
        }

        $equipo->fecha_ultimo_servicio = now()->toDateString();
        
        $categoria = $equipo->categoria;
        if ($categoria?->requiere_mantenimiento_preventivo && $categoria->intervalo_mantenimiento_dias) {
            $equipo->fecha_proximo_mantenimiento = now()->addDays($categoria->intervalo_mantenimiento_dias)->toDateString();
        }

        $equipo->save();
    }

    private function procesarFotoCierre(OrdenTrabajo $orden, UploadedFile $foto, ?string $descripcion): void
    {
        $carpetaDestino = "evidencias/{$orden->taller_id}/{$orden->id}";
        $rutaOptimizada = OptimizadorImagenes::optimizarYGuardar($foto, $carpetaDestino);

        EvidenciaFotografica::create([
            'taller_id' => $orden->taller_id,
            'orden_trabajo_id' => $orden->id,
            'ruta_imagen' => $rutaOptimizada,
            'etiqueta' => 'como_se_devuelve',
            'descripcion' => $descripcion ?: 'Estado final del equipo al momento de entrega y cierre.',
        ]);
    }
}
