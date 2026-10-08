<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\TieneTaller;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagoOrden extends Model
{
    use HasFactory, TieneTaller, SoftDeletes;

    protected $table = 'pagos_ordenes';

    protected $fillable = [
        'taller_id',
        'orden_trabajo_id',
        'usuario_id',
        'monto',
        'metodo_pago',
        'referencia',
        'notas',
        'fecha_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public const METODOS = [
        'efectivo' => [
            'nombre' => 'Efectivo',
            'icono' => 'fa-money-bill-wave',
            'color' => '#10b981',
            'bg' => '#ecfdf5',
        ],
        'nequi' => [
            'nombre' => 'Nequi',
            'icono' => 'fa-mobile-screen-button',
            'color' => '#9333ea',
            'bg' => '#faf5ff',
        ],
        'daviplata' => [
            'nombre' => 'Daviplata',
            'icono' => 'fa-mobile-screen-button',
            'color' => '#dc2626',
            'bg' => '#fef2f2',
        ],
        'transferencia_bancaria' => [
            'nombre' => 'Transferencia Bancaria',
            'icono' => 'fa-building-columns',
            'color' => '#0284c7',
            'bg' => '#f0f9ff',
        ],
        'tarjeta' => [
            'nombre' => 'Tarjeta / Datáfono',
            'icono' => 'fa-credit-card',
            'color' => '#2563eb',
            'bg' => '#eff6ff',
        ],
        'otro' => [
            'nombre' => 'Otro Medio',
            'icono' => 'fa-receipt',
            'color' => '#64748b',
            'bg' => '#f8fafc',
        ],
    ];

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function getNombreMetodoAttribute(): string
    {
        return self::METODOS[$this->metodo_pago]['nombre'] ?? ucfirst(str_replace('_', ' ', $this->metodo_pago));
    }

    public function getIconoMetodoAttribute(): string
    {
        return self::METODOS[$this->metodo_pago]['icono'] ?? 'fa-receipt';
    }

    public function getColorMetodoAttribute(): string
    {
        return self::METODOS[$this->metodo_pago]['color'] ?? '#64748b';
    }

    public function getBgMetodoAttribute(): string
    {
        return self::METODOS[$this->metodo_pago]['bg'] ?? '#f8fafc';
    }
}
