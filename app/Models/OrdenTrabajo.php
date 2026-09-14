<?php

namespace App\Models;

use App\Traits\TieneTaller;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OrdenTrabajo extends Model
{
    use HasFactory, TieneTaller, SoftDeletes;

    protected $table = 'ordenes_trabajo';

    protected $fillable = [
        'taller_id',
        'codigo_orden',
        'equipo_id',
        'cliente_id',
        'tecnico_asignado_id',
        'tipo_ubicacion',
        'estado',
        'problema_reportado',
        'diagnostico',
        'procedimiento_realizado',
        'repuestos_usados',
        'costo_mano_obra',
        'costo_repuestos',
        'costo_total',
        'ruta_firma_cliente',
        'nombre_firmante',
        'fecha_firma',
        'token_publico_pdf',
        'fecha_ingreso',
        'fecha_promesa',
        'fecha_finalizacion',
    ];

    protected $casts = [
        'costo_mano_obra' => 'decimal:2',
        'costo_repuestos' => 'decimal:2',
        'costo_total' => 'decimal:2',
        'fecha_ingreso' => 'datetime',
        'fecha_promesa' => 'date',
        'fecha_finalizacion' => 'datetime',
        'fecha_firma' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($orden) {
            if (empty($orden->token_publico_pdf)) {
                $orden->token_publico_pdf = (string) Str::uuid();
            }

            if (empty($orden->codigo_orden)) {
                $prefijo = $orden->taller?->prefijo_orden ?? 'OT';
                $ultimoNumero = static::withoutGlobalScopes()
                    ->where('taller_id', $orden->taller_id)
                    ->max('id') ?? 0;
                $consecutivo = str_pad($ultimoNumero + 1, 5, '0', STR_PAD_LEFT);
                $orden->codigo_orden = "{$prefijo}-{$consecutivo}";
            }

            if (empty($orden->fecha_ingreso)) {
                $orden->fecha_ingreso = now();
            }
        });
    }

    public function taller(): BelongsTo
    {
        return $this->belongsTo(Taller::class, 'taller_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'tecnico_asignado_id');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(EvidenciaFotografica::class, 'orden_trabajo_id');
    }

    public function getUrlPublicaReporteAttribute(): string
    {
        $token = $this->token_publico_pdf ?: 'sin-token';
        return route('ordenes.pdf.publico', ['token' => $token]);
    }

    public function getEnlaceWhatsappReporteAttribute(): string
    {
        $telefono = preg_replace('/[^0-9]/', '', $this->cliente?->telefono ?? '');
        $url = $this->url_publica_reporte;
        $tallerNombre = $this->taller?->nombre_comercial ?? 'Servicio Técnico';
        $mensaje = urlencode("Hola {$this->cliente?->nombre}, le compartimos el informe técnico de su orden de servicio {$this->codigo_orden} en {$tallerNombre}. Puede consultarlo y descargarlo aquí: {$url}");

        return "https://wa.me/{$telefono}?text={$mensaje}";
    }
}
