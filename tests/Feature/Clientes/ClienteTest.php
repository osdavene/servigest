<?php

declare(strict_types=1);

namespace Tests\Feature\Clientes;

use App\Models\Cliente;
use App\Models\Taller;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    private Taller $taller;
    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taller = Taller::create([
            'nombre_comercial' => 'Taller Cliente Test',
            'email' => 'taller_cli@test.com',
            'telefono' => '12345678',
            'estado_suscripcion' => 'activo',
        ]);

        $this->admin = Usuario::create([
            'taller_id' => $this->taller->id,
            'nombre' => 'Admin',
            'apellido' => 'Test',
            'email' => 'admin_cli@test.com',
            'password' => Hash::make('password123'),
            'rol' => 'administrador',
            'esta_activo' => true,
        ]);
    }

    public function test_registra_cliente_con_documento_unico_por_taller(): void
    {
        $response = $this->actingAs($this->admin)->post(route('clientes.store'), [
            'nombre_completo' => 'Carlos Delgado',
            'identificacion' => 'CC 1098765432',
            'telefono' => '3109876543',
            'direccion' => 'Carrera 15 # 45-20',
        ]);

        $this->assertDatabaseHas('clientes', [
            'taller_id' => $this->taller->id,
            'nombre_completo' => 'Carlos Delgado',
            'identificacion' => 'CC 1098765432',
        ]);

        $cliente = Cliente::first();
        $response->assertRedirect(route('clientes.show', $cliente));
    }

    public function test_evita_duplicidad_de_identificacion_en_el_mismo_taller(): void
    {
        Cliente::create([
            'taller_id' => $this->taller->id,
            'nombre_completo' => 'Carlos Delgado',
            'identificacion' => 'CC 1098765432',
            'telefono' => '3109876543',
            'direccion' => 'Carrera 15 # 45-20',
        ]);

        $response = $this->actingAs($this->admin)->post(route('clientes.store'), [
            'nombre_completo' => 'Otro Carlos',
            'identificacion' => 'CC 1098765432',
            'telefono' => '3110000000',
            'direccion' => 'Calle 100',
        ]);

        $response->assertSessionHasErrors('identificacion');
    }
}
