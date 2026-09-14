<?php

declare(strict_types=1);

namespace App\Actions\Equipos;

use App\Models\Categoria;
use App\Models\Equipo;

final class CrearEquipoAction
{
    /**
     * Crea un equipo y calcula automáticamente su fecha de mantenimiento si aplica.
     */
    public function execute(array $datos): Equipo
    {
        if (empty($datos['fecha_proximo_mantenimiento']) && !empty($datos['categoria_id'])) {
            $categoria = Categoria::find($datos['categoria_id']);
            if ($categoria && $categoria->requiere_mantenimiento_preventivo && $categoria->intervalo_mantenimiento_dias) {
                $fechaBase = !empty($datos['fecha_ultimo_servicio']) ? $datos['fecha_ultimo_servicio'] : now()->toDateString();
                $datos['fecha_proximo_mantenimiento'] = \Carbon\Carbon::parse($fechaBase)->addDays($categoria->intervalo_mantenimiento_dias)->toDateString();
            }
        }

        return Equipo::create($datos);
    }
}
