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
        // 1. Si el usuario está autenticado en la sesión
        if (auth()->check()) {
            $usuario = auth()->user();

            // Super Administrador navegando globalmente: no se restringe a menos que haya fijado un taller activo
            if ($usuario->esSuperAdmin()) {
                if (session()->has('taller_id_activo')) {
                    $builder->where($model->getTable() . '.taller_id', session('taller_id_activo'));
                }
                return;
            }

            // Usuario del taller (Administrador o Técnico): filtro estricto por su taller_id
            if (!empty($usuario->taller_id)) {
                $builder->where($model->getTable() . '.taller_id', $usuario->taller_id);
                return;
            }

            // Si está autenticado pero no tiene ningún taller asignado: FAIL-CLOSED (no exponer datos)
            $builder->whereRaw('1 = 0');
            return;
        }

        // 2. Si es una petición no autenticada pero cuenta con taller activo en sesión
        if (session()->has('taller_id_activo')) {
            $builder->where($model->getTable() . '.taller_id', session('taller_id_activo'));
            return;
        }

        // 3. Consulta sin autenticación y sin sesión de inquilino: FAIL-CLOSED por principio de mínimo privilegio.
        // Las consultas públicas legítimas (Portal o Reportes PDF) invocan withoutGlobalScopes() explícitamente.
        $builder->whereRaw('1 = 0');
    }
}
