<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Taller extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'talleres';

    protected $fillable = [
        'plan_licencia_id',
        'nombre_comercial',
        'identificacion_fiscal',
        'telefono',
        'email',
        'direccion',
        'ciudad',
        'texto_garantia',
        'prefijo_orden',
        'logo_ruta',
        'estado_suscripcion',
        'fecha_vencimiento_suscripcion',
        'whatsapp_auto_notify_enabled',
        'whatsapp_api_provider',
        'whatsapp_api_token',
        'whatsapp_phone_number_id',
        'whatsapp_webhook_url',
        'whatsapp_template_creada',
        'whatsapp_template_en_proceso',
        'whatsapp_template_finalizada',
        'whatsapp_template_entregada',
    ];

    protected $casts = [
        'fecha_vencimiento_suscripcion' => 'date',
        'whatsapp_auto_notify_enabled' => 'boolean',
    ];

    public function planLicencia(): BelongsTo
    {
        return $this->belongsTo(PlanLicencia::class, 'plan_licencia_id');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'taller_id');
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'taller_id');
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'taller_id');
    }

    public function equipos(): HasMany
    {
        return $this->hasMany(Equipo::class, 'taller_id');
    }

    public function ordenesTrabajo(): HasMany
    {
        return $this->hasMany(OrdenTrabajo::class, 'taller_id');
    }

    public function estaSuscripcionActiva(): bool
    {
        if ($this->estado_suscripcion === 'suspendido') {
            return false;
        }

        if ($this->fecha_vencimiento_suscripcion && $this->fecha_vencimiento_suscripcion->isPast() && !$this->fecha_vencimiento_suscripcion->isToday()) {
            return false;
        }

        return true;
    }

    public function suscripcionVencida(): bool
    {
        return !$this->estaSuscripcionActiva();
    }

    public function getUrlLogoAttribute(): ?string
    {
        if (!$this->logo_ruta) {
            return null;
        }

        if (str_starts_with($this->logo_ruta, 'http://') || str_starts_with($this->logo_ruta, 'https://')) {
            return $this->logo_ruta;
        }

        if (Storage::disk('public')->exists($this->logo_ruta)) {
            return Storage::url($this->logo_ruta);
        }

        return asset('storage/' . $this->logo_ruta);
    }
}
