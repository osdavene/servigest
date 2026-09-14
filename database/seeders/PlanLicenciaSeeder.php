<?php

namespace Database\Seeders;

use App\Models\PlanLicencia;
use Illuminate\Database\Seeder;

class PlanLicenciaSeeder extends Seeder
{
    public function run(): void
    {
        PlanLicencia::firstOrCreate(['nombre' => 'Prueba Gratuita (15 Días)'], [
            'dias_duracion' => 15,
            'precio' => 0,
            'limite_usuarios' => 2,
            'descripcion' => 'Acceso de demostración para nuevos talleres.',
            'es_prueba' => true,
            'esta_activo' => true
        ]);

        PlanLicencia::firstOrCreate(['nombre' => 'Licencia Mensual Pyme (30 Días)'], [
            'dias_duracion' => 30,
            'precio' => 49000,
            'limite_usuarios' => 5,
            'descripcion' => 'Plan mensual para talleres en crecimiento.',
            'es_prueba' => false,
            'esta_activo' => true
        ]);

        PlanLicencia::firstOrCreate(['nombre' => 'Licencia Semestral Pro (180 Días)'], [
            'dias_duracion' => 180,
            'precio' => 249000,
            'limite_usuarios' => 10,
            'descripcion' => 'Plan semestral con descuento del 15%.',
            'es_prueba' => false,
            'esta_activo' => true
        ]);

        PlanLicencia::firstOrCreate(['nombre' => 'Licencia Anual VIP (365 Días)'], [
            'dias_duracion' => 365,
            'precio' => 450000,
            'limite_usuarios' => null,
            'descripcion' => 'Plan anual completo con usuarios y reportes ilimitados.',
            'es_prueba' => false,
            'esta_activo' => true
        ]);
    }
}
