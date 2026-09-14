<?php

namespace App\Models;

use App\Traits\TieneTaller;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, TieneTaller, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'taller_id',
        'nombre_completo',
        'identificacion',
        'telefono',
        'telefono_secundario',
        'email',
        'direccion',
        'barrio',
        'ciudad',
        'latitud',
        'longitud',
        'notas_adicionales',
    ];

    public function taller(): BelongsTo
    {
        return $this->belongsTo(Taller::class, 'taller_id');
    }

    public function equipos(): HasMany
    {
        return $this->hasMany(Equipo::class, 'cliente_id');
    }

    public function ordenesTrabajo(): HasMany
    {
        return $this->hasMany(OrdenTrabajo::class, 'cliente_id');
    }

    public function getNombreAttribute(): string
    {
        return explode(' ', trim($this->nombre_completo))[0] ?? $this->nombre_completo;
    }

    public function getEnlaceWhatsappAttribute(): string
    {
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $this->telefono);
        return "https://wa.me/{$telefonoLimpio}";
    }

    public function getEnlaceGoogleMapsAttribute(): string
    {
        if ($this->latitud && $this->longitud) {
            return "https://www.google.com/maps/search/?api=1&query={$this->latitud},{$this->longitud}";
        }

        $direccionCompleta = urlencode("{$this->direccion}, {$this->barrio}, {$this->ciudad}");
        return "https://www.google.com/maps/search/?api=1&query={$direccionCompleta}";
    }

    public function getEnlaceWazeAttribute(): string
    {
        if ($this->latitud && $this->longitud) {
            return "https://waze.com/ul?ll={$this->latitud},{$this->longitud}&navigate=yes";
        }

        $direccionCompleta = urlencode("{$this->direccion}, {$this->ciudad}");
        return "https://waze.com/ul?q={$direccionCompleta}";
    }
}
