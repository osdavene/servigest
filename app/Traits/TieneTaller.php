<?php

namespace App\Traits;

use App\Models\Scopes\TallerScope;
use App\Models\Taller;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait TieneTaller
{
    /**
     * Boot del Trait para registrar el Global Scope y asignar taller_id en creating.
     */
    protected static function bootTieneTaller(): void
    {
        static::addGlobalScope(new TallerScope());

        static::creating(function ($modelo) {
            if (auth()->check() && auth()->user()->taller_id && empty($modelo->taller_id)) {
                $modelo->taller_id = auth()->user()->taller_id;
            } elseif (session()->has('taller_id_activo') && empty($modelo->taller_id)) {
                $modelo->taller_id = session('taller_id_activo');
            }
        });
    }

    /**
     * Relación con el Taller propietario del registro.
     */
    public function taller(): BelongsTo
    {
        return $this->belongsTo(Taller::class, 'taller_id');
    }
}
