<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroAcceso extends Model
{
    use HasFactory;

    protected $table = 'registros_acceso';

    protected $fillable = [
        'usuario_id',
        'taller_id',
        'email_ingresado',
        'ip_address',
        'dispositivo',
        'navegador',
        'sistema_operativo',
        'pais',
        'ciudad',
        'estado',
        'session_id',
        'fecha_ingreso',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function taller(): BelongsTo
    {
        return $this->belongsTo(Taller::class, 'taller_id');
    }

    /**
     * Calcula la duración formateada de la sesión si ya finalizó.
     */
    public function getDuracionFormateadaAttribute(): ?string
    {
        return $this->obtenerDuracionFormateada();
    }

    /**
     * Retorna la duración formateada en texto legible.
     */
    public function obtenerDuracionFormateada(): string
    {
        if (!$this->fecha_cierre || !$this->fecha_ingreso) {
            return 'En curso / Activa';
        }

        $minutos = $this->fecha_ingreso->diffInMinutes($this->fecha_cierre);
        if ($minutos < 1) {
            return '< 1 min';
        }
        if ($minutos < 60) {
            return "{$minutos} min";
        }

        $horas = intdiv($minutos, 60);
        $restoMinutos = $minutos % 60;
        return "{$horas}h {$restoMinutos}m";
    }
}
