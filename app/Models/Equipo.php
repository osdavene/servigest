<?php

namespace App\Models;

use App\Traits\TieneTaller;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipo extends Model
{
    use HasFactory, TieneTaller, SoftDeletes;

    protected $table = 'equipos';

    protected $fillable = [
        'taller_id',
        'cliente_id',
        'categoria_id',
        'marca',
        'modelo',
        'numero_serie',
        'observaciones_fisicas',
        'fecha_ultimo_servicio',
        'fecha_proximo_mantenimiento',
    ];

    protected $casts = [
        'fecha_ultimo_servicio' => 'date',
        'fecha_proximo_mantenimiento' => 'date',
    ];

    public function taller(): BelongsTo
    {
        return $this->belongsTo(Taller::class, 'taller_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function ordenesTrabajo(): HasMany
    {
        return $this->hasMany(OrdenTrabajo::class, 'equipo_id');
    }

    public function getRequiereMantenimientoAttribute(): bool
    {
        if (!$this->fecha_proximo_mantenimiento) {
            return false;
        }

        return $this->fecha_proximo_mantenimiento->isPast() || $this->fecha_proximo_mantenimiento->isToday();
    }

    public function getDiasParaMantenimientoAttribute(): ?int
    {
        if (!$this->fecha_proximo_mantenimiento) {
            return null;
        }

        return (int) now()->diffInDays($this->fecha_proximo_mantenimiento, false);
    }
}
