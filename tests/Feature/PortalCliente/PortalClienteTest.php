<?php

declare(strict_types=1);

namespace Tests\Feature\PortalCliente;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\OrdenTrabajo;
use App\Models\Taller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalClienteTest extends TestCase
{
    use RefreshDatabase;

    private Taller $taller;
    private Cliente $cliente;
    private Equipo $equipo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taller = Taller::create([
            'nombre_comercial' => 'Taller Portal Test',
            'email' => 'taller_portal@test.com',
            'telefono' => '12345678',
            'estado_suscripcion' => 'activo',
        ]);

        $this->cliente = Cliente::create([
            'taller_id' => $this->taller->id,
            'nombre_completo' => 'Empresa Hotelera Caribe',
            'identificacion' => 'NIT 900.555.444-1',
            'telefono' => '3009998877',
            'direccion' => 'Av Santander # 15-20',
            'token_portal' => 'token-hotel-caribe-123456',
        ]);

        $categoria = Categoria::create([
            'taller_id' => $this->taller->id,
            'nombre' => 'Aires Acondicionados',
            'requiere_mantenimiento_preventivo' => true,
            'intervalo_mantenimiento_dias' => 90,
        ]);

        $this->equipo = Equipo::create([
            'taller_id' => $this->taller->id,
            'cliente_id' => $this->cliente->id,
            'categoria_id' => $categoria->id,
            'marca' => 'Carrier',
            'modelo' => 'Inverter 24000 BTU',
            'numero_serie' => 'CAR-987654',
        ]);
    }

    public function test_cliente_accede_a_su_portal_b2b_con_su_token_permanente(): void
    {
        $response = $this->get(route('portal.cliente', 'token-hotel-caribe-123456'));

        $response->assertStatus(200);
        $response->assertSee('Empresa Hotelera Caribe');
        $response->assertSee('Taller Portal Test');
        $response->assertSee('Carrier Inverter 24000 BTU');
    }

    public function test_cliente_solicita_mantenimiento_express_y_crea_orden_en_el_taller(): void
    {
        $response = $this->post(route('portal.cliente.solicitar', 'token-hotel-caribe-123456'), [
            'equipo_id' => $this->equipo->id,
            'tipo_ubicacion' => 'servicio_en_domicilio',
            'problema_reportado' => 'Mantenimiento preventivo suite 301',
        ]);

        $response->assertRedirect(route('portal.cliente', 'token-hotel-caribe-123456'));
        $response->assertSessionHas('exito');

        $this->assertDatabaseHas('ordenes_trabajo', [
            'taller_id' => $this->taller->id,
            'cliente_id' => $this->cliente->id,
            'equipo_id' => $this->equipo->id,
            'estado' => 'pendiente',
            'tipo_ubicacion' => 'servicio_en_domicilio',
            'problema_reportado' => 'Mantenimiento preventivo suite 301',
        ]);
    }
}
