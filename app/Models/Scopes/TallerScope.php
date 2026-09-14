<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TallerScope implements Scope
{
    /**
     * Aplica el scope global de aislamiento por taller_id a la consulta Eloquent.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Si el usuario es SuperAdmin navegando globalmente, no se restringe por scope a menos que haya seleccionado un taller activo
        if (auth()->check()) {
            $usuario = auth()->user();

            if ($usuario->esSuperAdmin()) {
                if (session()->has('taller_id_activo')) {
                    $builder->where($model->getTable() . '.taller_id', session('taller_id_activo'));
                }
                return;
            }

            if ($usuario->taller_id) {
                $builder->where($model->getTable() . '.taller_id', $usuario->taller_id);
            }
        } elseif (session()->has('taller_id_activo')) {
            $builder->where($model->getTable() . '.taller_id', session('taller_id_activo'));
        }
    }
}
