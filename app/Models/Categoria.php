<?php

namespace App\Models;

use App\Traits\TieneTaller;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use HasFactory, TieneTaller, SoftDeletes;

    protected $table = 'categorias';

    protected $fillable = [
        'taller_id',
        'nombre',
        'descripcion',
        'intervalo_mantenimiento_dias',
        'requiere_mantenimiento_preventivo',
    ];

    protected $casts = [
        'intervalo_mantenimiento_dias' => 'integer',
        'requiere_mantenimiento_preventivo' => 'boolean',
    ];

    public function taller(): BelongsTo
    {
        return $this->belongsTo(Taller::class, 'taller_id');
    }

    public function equipos(): HasMany
    {
        return $this->hasMany(Equipo::class, 'categoria_id');
    }
}
