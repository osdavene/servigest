<?php

namespace App\Http\Controllers;

use App\Models\EvidenciaFotografica;
use App\Models\OrdenTrabajo;
use App\Services\OptimizadorImagenes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvidenciaFotograficaController extends Controller
{
    /**
     * Sube y comprime automáticamente la foto de evidencia técnica estilo WhatsApp.
     */
    public function store(Request $request, OrdenTrabajo $orden)
    {
        $request->validate([
            'foto' => ['required', 'image', 'max:15360'], // Permite hasta 15MB desde celulares
            'etiqueta' => ['required', 'in:como_se_recibe,antes,falla_detectada,falla_en_placa,durante_reparacion,durante,como_se_devuelve,despues,otra'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], [
            'foto.required' => 'Debe seleccionar o tomar una fotografía.',
            'foto.image' => 'El archivo debe ser una imagen válida (JPG, PNG, WEBP).',
            'foto.max' => 'La imagen no debe superar los 15MB.',
            'etiqueta.required' => 'Seleccione una clasificación para la evidencia.',
        ]);

        // Compresión y almacenamiento organizado por taller y cliente
        $clienteId = $orden->cliente_id ?: 'general';
        $carpetaDestino = "servigest/talleres/taller_{$orden->taller_id}/clientes/cliente_{$clienteId}/ordenes/orden_{$orden->id}/evidencias";
        $rutaOptimizada = OptimizadorImagenes::optimizarYGuardar($request->file('foto'), $carpetaDestino);

        EvidenciaFotografica::create([
            'taller_id' => $orden->taller_id,
            'orden_trabajo_id' => $orden->id,
            'ruta_imagen' => $rutaOptimizada,
            'etiqueta' => $request->input('etiqueta'),
            'descripcion' => $request->input('descripcion'),
        ]);

        return back()->with('exito', 'Evidencia fotográfica optimizada y cargada correctamente.');
    }

    /**
     * Elimina una evidencia y su archivo (Cloudinary o almacenamiento local).
     */
    public function destroy(EvidenciaFotografica $evidencia)
    {
        if ($evidencia->ruta_imagen) {
            OptimizadorImagenes::eliminar($evidencia->ruta_imagen);
        }

        $evidencia->delete();

        return back()->with('exito', 'Evidencia fotográfica eliminada.');
    }
}
