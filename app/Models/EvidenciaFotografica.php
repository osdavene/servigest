<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvidenciaFotografica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evidencias_fotograficas';

    protected $fillable = [
        'taller_id',
        'orden_trabajo_id',
        'ruta_imagen',
        'etiqueta',
        'descripcion',
    ];

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    /**
     * Devuelve el texto amigable de la etiqueta técnica.
     */
    public function getNombreEtiquetaAttribute(): string
    {
        return match($this->etiqueta) {
            'como_se_recibe', 'antes', 'recepcion' => '📥 Cómo se recibe (Recepción)',
            'falla_en_placa', 'falla_detectada', 'diagnostico' => '🔍 Falla detectada / Diagnóstico',
            'durante', 'durante_reparacion', 'proceso' => '🛠️ Durante la reparación',
            'como_se_devuelve', 'despues', 'entrega' => '📤 Cómo se devuelve (Entrega)',
            default => '📎 ' . ucfirst(str_replace('_', ' ', $this->etiqueta)),
        };
    }

    /**
     * URL pública de la imagen (Cloudinary o Storage local).
     */
    public function getUrlImagenAttribute(): string
    {
        if (empty($this->ruta_imagen)) {
            return '';
        }

        if (str_starts_with($this->ruta_imagen, 'http://') || str_starts_with($this->ruta_imagen, 'https://')) {
            return $this->ruta_imagen;
        }

        return asset('storage/' . $this->ruta_imagen);
    }
}
