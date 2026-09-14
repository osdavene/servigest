<?php

declare(strict_types=1);

namespace Tests\Feature\Tickets;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\OrdenTrabajo;
use App\Models\Taller;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TicketPosTest extends TestCase
{
    use RefreshDatabase;

    private Taller $taller;
    private Usuario $admin;
    private OrdenTrabajo $orden;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taller = Taller::create([
            'nombre_comercial' => 'Taller Tickets Test',
            'email' => 'tickets@test.com',
            'telefono' => '12345678',
            'estado_suscripcion' => 'activo',
        ]);

        $this->admin = Usuario::create([
            'taller_id' => $this->taller->id,
            'nombre' => 'Admin',
            'apellido' => 'Test',
            'email' => 'admin_tickets@test.com',
            'password' => Hash::make('password123'),
            'rol' => 'administrador',
            'esta_activo' => true,
        ]);

        $cliente = Cliente::create([
            'taller_id' => $this->taller->id,
            'nombre_completo' => 'Cliente POS',
            'telefono' => '3001234567',
            'direccion' => 'Calle 10 # 20-30',
        ]);

        $categoria = Categoria::create([
            'taller_id' => $this->taller->id,
            'nombre' => 'Audio & Video',
        ]);

        $equipo = Equipo::create([
            'taller_id' => $this->taller->id,
            'cliente_id' => $cliente->id,
            'categoria_id' => $categoria->id,
            'marca' => 'Sony',
            'modelo' => 'Bravia 55',
            'numero_serie' => 'SN-998877',
        ]);

        $this->orden = OrdenTrabajo::create([
            'taller_id' => $this->taller->id,
            'cliente_id' => $cliente->id,
            'equipo_id' => $equipo->id,
            'codigo_orden' => 'OT-00100',
            'tipo_ubicacion' => 'ingresado_al_taller',
            'estado' => 'pendiente',
            'problema_reportado' => 'Sin audio',
            'costo_mano_obra' => 45000,
            'costo_repuestos' => 15000,
            'costo_total' => 60000,
            'token_publico_pdf' => 'token-pos-test-123',
            'fecha_ingreso' => now(),
        ]);
    }

    public function test_genera_vista_de_ticket_termico_pos(): void
    {
        $response = $this->actingAs($this->admin)->get(route('ordenes.ticket.pos', $this->orden));

        $response->assertStatus(200);
        $response->assertSee('OT-00100');
        $response->assertSee('TALLER TICKETS TEST');
        $response->assertSee('Sony Bravia 55');
        $response->assertSee('60.000');
    }

    public function test_genera_etiqueta_adhesiva_qr(): void
    {
        $response = $this->actingAs($this->admin)->get(route('ordenes.etiqueta.qr', $this->orden));

        $response->assertStatus(200);
        $response->assertSee('OT-00100');
        $response->assertSee('Sony Bravia 55');
        $response->assertSee('Escanear OT');
    }
}
