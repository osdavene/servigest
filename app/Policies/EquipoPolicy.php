<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Equipo;
use App\Models\Usuario;

class EquipoPolicy
{
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

    public function view(Usuario $usuario, Equipo $equipo): bool
    {
        return $usuario->taller_id === $equipo->taller_id;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->taller_id !== null;
    }

    public function update(Usuario $usuario, Equipo $equipo): bool
    {
        return $usuario->taller_id === $equipo->taller_id;
    }

    public function delete(Usuario $usuario, Equipo $equipo): bool
    {
        return $usuario->taller_id === $equipo->taller_id && $usuario->esAdministrador();
    }
}
