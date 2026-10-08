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

    /**
     * Genera de forma segura el siguiente código correlativo único por taller.
     */
    public static function generarSiguienteCodigo(int $tallerId, string $prefijo = 'OT'): string
    {
        $ultimaOrden = static::withoutGlobalScopes()
            ->withTrashed()
            ->where('taller_id', $tallerId)
            ->latest('id')
            ->first();

        $siguienteNumero = 1;

        if ($ultimaOrden && !empty($ultimaOrden->codigo_orden)) {
            if (preg_match('/(\d+)$/', $ultimaOrden->codigo_orden, $matches)) {
                $siguienteNumero = ((int) $matches[1]) + 1;
            } else {
                $siguienteNumero = static::withoutGlobalScopes()->withTrashed()->where('taller_id', $tallerId)->count() + 1;
            }
        }

        do {
            $codigoCandidato = sprintf('%s-%05d', $prefijo, $siguienteNumero);
            $existe = static::withoutGlobalScopes()
                ->withTrashed()
                ->where('taller_id', $tallerId)
                ->where('codigo_orden', $codigoCandidato)
                ->exists();

            if ($existe) {
                $siguienteNumero++;
            }
        } while ($existe);

        return $codigoCandidato;
    }

    protected static function booted()
    {
        static::creating(function ($orden) {
            if (empty($orden->token_publico_pdf)) {
                $orden->token_publico_pdf = (string) Str::uuid();
            }

            if (empty($orden->codigo_orden)) {
                $prefijo = $orden->taller?->prefijo_orden ?: 'OT';
                $orden->codigo_orden = static::generarSiguienteCodigo((int) $orden->taller_id, $prefijo);
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

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoOrden::class, 'orden_trabajo_id')->orderBy('fecha_pago', 'desc');
    }

    public function getTotalAbonadoAttribute(): float
    {
        return (float) ($this->relationLoaded('pagos') ? $this->pagos->sum('monto') : $this->pagos()->sum('monto'));
    }

    public function getSaldoPendienteAttribute(): float
    {
        return max(0, (float) $this->costo_total - $this->total_abonado);
    }

    public function getEstadoPagoAttribute(): string
    {
        $costo = (float) $this->costo_total;
        $abonado = $this->total_abonado;

        if ($costo <= 0) {
            return 'sin_costo';
        }
        if ($abonado <= 0) {
            return 'pendiente';
        }
        if ($abonado < $costo) {
            return 'abono_parcial';
        }
        return 'pagado';
    }

    public function getTextoEstadoPagoAttribute(): string
    {
        return match ($this->estado_pago) {
            'sin_costo' => 'Sin Costo',
            'pendiente' => 'Pendiente de Pago',
            'abono_parcial' => 'Abono Parcial',
            'pagado' => 'Pagado Totalmente',
            default => 'Pendiente',
        };
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

    public function getUrlFirmaClienteAttribute(): ?string
    {
        if (!$this->ruta_firma_cliente) {
            return null;
        }

        if (str_starts_with($this->ruta_firma_cliente, 'http://') || str_starts_with($this->ruta_firma_cliente, 'https://')) {
            return $this->ruta_firma_cliente;
        }

        return asset('storage/' . $this->ruta_firma_cliente);
    }
}
