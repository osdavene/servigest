<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanLicencia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'planes_licencia';

    protected $fillable = [
        'nombre',
        'dias_duracion',
        'precio',
        'limite_usuarios',
        'descripcion',
        'es_prueba',
        'esta_activo',
    ];

    protected $casts = [
        'dias_duracion' => 'integer',
        'precio' => 'decimal:2',
        'limite_usuarios' => 'integer',
        'es_prueba' => 'boolean',
        'esta_activo' => 'boolean',
    ];

    public function talleres(): HasMany
    {
        return $this->hasMany(Taller::class, 'plan_licencia_id');
    }

    public function getPrecioFormateadoAttribute(): string
    {
        return '$' . number_format($this->precio, 0, ',', '.');
    }
}
