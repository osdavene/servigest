<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OrdenTrabajo;
use App\Models\Usuario;

class OrdenTrabajoPolicy
{
    /**
     * El Super Administrador tiene acceso total a todos los recursos.
     */
    public function before(Usuario $usuario, string $ability): ?bool
    {
        if ($usuario->esSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->taller_id !== null;
    }

    public function view(Usuario $usuario, OrdenTrabajo $orden): bool
    {
        return $usuario->taller_id === $orden->taller_id;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->taller_id !== null && in_array($usuario->rol, ['administrador', 'tecnico'], true);
    }

    public function update(Usuario $usuario, OrdenTrabajo $orden): bool
    {
        return $usuario->taller_id === $orden->taller_id && in_array($usuario->rol, ['administrador', 'tecnico'], true);
    }

    public function delete(Usuario $usuario, OrdenTrabajo $orden): bool
    {
        return $usuario->taller_id === $orden->taller_id && $usuario->esAdministrador();
    }
}
