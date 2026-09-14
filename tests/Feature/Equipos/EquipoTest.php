<?php

declare(strict_types=1);

namespace Tests\Feature\Equipos;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\Taller;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EquipoTest extends TestCase
{
    use RefreshDatabase;

    private Taller $taller;
    private Usuario $admin;
    private Cliente $cliente;
    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taller = Taller::create([
            'nombre_comercial' => 'Taller Equipo Test',
            'email' => 'taller_eq@test.com',
            'telefono' => '12345678',
            'estado_suscripcion' => 'activo',
        ]);

        $this->admin = Usuario::create([
            'taller_id' => $this->taller->id,
            'nombre' => 'Admin',
            'apellido' => 'Test',
            'email' => 'admin_eq@test.com',
            'password' => Hash::make('password123'),
            'rol' => 'administrador',
            'esta_activo' => true,
        ]);

        $this->cliente = Cliente::create([
            'taller_id' => $this->taller->id,
            'nombre_completo' => 'Cliente Equipo Test',
            'telefono' => '3001234567',
            'direccion' => 'Calle 1 # 2-3',
        ]);

        $this->categoria = Categoria::create([
            'taller_id' => $this->taller->id,
            'nombre' => 'Neveras Inverter',
            'requiere_mantenimiento_preventivo' => true,
            'intervalo_mantenimiento_dias' => 365,
        ]);
    }

    public function test_registra_equipo_y_calcula_proximo_mantenimiento_automaticamente(): void
    {
        $response = $this->actingAs($this->admin)->post(route('equipos.store'), [
            'cliente_id' => $this->cliente->id,
            'categoria_id' => $this->categoria->id,
            'marca' => 'Whirlpool',
            'modelo' => 'No-Frost 420L',
            'numero_serie' => 'WP-123456',
            'fecha_ultimo_servicio' => '2026-01-01',
        ]);

        $this->assertDatabaseHas('equipos', [
            'taller_id' => $this->taller->id,
            'cliente_id' => $this->cliente->id,
            'categoria_id' => $this->categoria->id,
            'marca' => 'Whirlpool',
            'modelo' => 'No-Frost 420L',
        ]);

        $equipo = Equipo::first();
        $this->assertEquals('2027-01-01', $equipo->fecha_proximo_mantenimiento?->toDateString());
        $response->assertRedirect(route('equipos.show', $equipo));
    }

    public function test_bloquea_registro_con_categoria_de_otro_taller(): void
    {
        $otroTaller = Taller::create([
            'nombre_comercial' => 'Otro Taller',
            'email' => 'otro_eq@test.com',
            'telefono' => '9999999',
            'estado_suscripcion' => 'activo',
        ]);

        $categoriaForanea = Categoria::create([
            'taller_id' => $otroTaller->id,
            'nombre' => 'Categoria Invasora',
        ]);

        $response = $this->actingAs($this->admin)->post(route('equipos.store'), [
            'cliente_id' => $this->cliente->id,
            'categoria_id' => $categoriaForanea->id, // IDOR
            'marca' => 'Samsung',
            'modelo' => 'Smart TV',
        ]);

        $response->assertSessionHasErrors('categoria_id');
    }
}
