<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'taller_id',
        'nombre',
        'apellido',
        'email',
        'telefono',
        'password',
        'rol',
        'esta_activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'esta_activo' => 'boolean',
    ];

    public function taller(): BelongsTo
    {
        return $this->belongsTo(Taller::class, 'taller_id');
    }

    public function ordenesAsignadas(): HasMany
    {
        return $this->hasMany(OrdenTrabajo::class, 'tecnico_asignado_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    public function esSuperAdmin(): bool
    {
        return $this->rol === 'super_administrador';
    }

    public function esAdminTaller(): bool
    {
        return $this->rol === 'administrador';
    }

    public function esTecnico(): bool
    {
        return $this->rol === 'tecnico';
    }
}
