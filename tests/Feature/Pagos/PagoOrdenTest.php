<?php

declare(strict_types=1);

namespace Tests\Feature\Pagos;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\OrdenTrabajo;
use App\Models\PagoOrden;
use App\Models\Taller;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoOrdenTest extends TestCase
{
    use RefreshDatabase;

    private Taller $taller;
    private Usuario $admin;
    private OrdenTrabajo $orden;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taller = Taller::create([
            'nombre_comercial' => 'ElectroTech Test',
            'email' => 'admin@electrotech-test.com',
            'telefono' => '3001234567',
            'estado_suscripcion' => 'activo',
        ]);

        $this->admin = Usuario::create([
            'taller_id' => $this->taller->id,
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza',
            'email' => 'admin@electrotech-test.com',
            'password' => bcrypt('password123'),
            'rol' => 'administrador',
            'esta_activo' => true,
        ]);

        $cliente = Cliente::create([
            'taller_id' => $this->taller->id,
            'nombre_completo' => 'Maria Gomez',
            'identificacion' => '10203040',
            'telefono' => '3109876543',
            'direccion' => 'Calle 100 # 15-20',
        ]);

        $categoria = \App\Models\Categoria::create([
            'taller_id' => $this->taller->id,
            'nombre' => 'Laptops',
        ]);

        $equipo = Equipo::create([
            'taller_id' => $this->taller->id,
            'cliente_id' => $cliente->id,
            'categoria_id' => $categoria->id,
            'marca' => 'Dell',
            'modelo' => 'Inspiron 15',
        ]);

        $this->orden = OrdenTrabajo::create([
            'taller_id' => $this->taller->id,
            'cliente_id' => $cliente->id,
            'equipo_id' => $equipo->id,
            'codigo_orden' => 'OT-00001',
            'tipo_ubicacion' => 'ingresado_al_taller',
            'estado' => 'en_proceso',
            'problema_reportado' => 'Pantalla rota',
            'costo_mano_obra' => 50000,
            'costo_repuestos' => 150000,
            'costo_total' => 200000,
            'token_publico_pdf' => 'token-test-12345',
        ]);
    }

    public function test_registra_abono_parcial_en_orden_y_actualiza_saldo(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('ordenes.pagos.store', $this->orden), [
            'monto' => 80000,
            'metodo_pago' => 'nequi',
            'referencia' => 'NEQ-987654',
            'notas' => 'Anticipo 40% compra de pantalla',
        ]);

        $response->assertRedirect(route('ordenes.show', $this->orden));
        $response->assertSessionHas('exito');

        $this->assertDatabaseHas('pagos_ordenes', [
            'taller_id' => $this->taller->id,
            'orden_trabajo_id' => $this->orden->id,
            'monto' => 80000,
            'metodo_pago' => 'nequi',
            'referencia' => 'NEQ-987654',
        ]);

        $this->orden->refresh();
        $this->assertEquals(80000, $this->orden->total_abonado);
        $this->assertEquals(120000, $this->orden->saldo_pendiente);
        $this->assertEquals('abono_parcial', $this->orden->estado_pago);
    }

    public function test_liquidacion_total_cambia_estado_a_pagado(): void
    {
        $this->actingAs($this->admin);

        // Primer abono: 100.000 Nequi
        $this->post(route('ordenes.pagos.store', $this->orden), [
            'monto' => 100000,
            'metodo_pago' => 'nequi',
        ]);

        // Segundo abono: 100.000 Efectivo
        $this->post(route('ordenes.pagos.store', $this->orden), [
            'monto' => 100000,
            'metodo_pago' => 'efectivo',
            'notas' => 'Pago final contra entrega',
        ]);

        $this->orden->refresh();
        $this->assertEquals(200000, $this->orden->total_abonado);
        $this->assertEquals(0, $this->orden->saldo_pendiente);
        $this->assertEquals('pagado', $this->orden->estado_pago);
    }

    public function test_bloquea_registro_de_pago_de_otro_taller(): void
    {
        $otroTaller = Taller::create([
            'nombre_comercial' => 'Taller Invasor',
            'email' => 'hacker@taller.com',
            'telefono' => '000000',
            'estado_suscripcion' => 'activo',
        ]);

        $usuarioInvasor = Usuario::create([
            'taller_id' => $otroTaller->id,
            'nombre' => 'Invasor',
            'apellido' => 'Perez',
            'email' => 'invasor@taller.com',
            'password' => bcrypt('password123'),
            'rol' => 'administrador',
            'esta_activo' => true,
        ]);

        $this->actingAs($usuarioInvasor);

        // Intentar registrar pago en orden de ElectroTech
        $response = $this->post(route('ordenes.pagos.store', $this->orden), [
            'monto' => 50000,
            'metodo_pago' => 'efectivo',
        ]);

        // Debe ser rechazado (403 o 404 por scope de inquilino)
        $this->assertTrue(in_array($response->status(), [403, 404]));
        $this->assertDatabaseMissing('pagos_ordenes', [
            'orden_trabajo_id' => $this->orden->id,
            'monto' => 50000,
        ]);
    }

    public function test_anulacion_de_pago_restaura_saldo_pendiente(): void
    {
        $this->actingAs($this->admin);

        $pago = PagoOrden::create([
            'taller_id' => $this->taller->id,
            'orden_trabajo_id' => $this->orden->id,
            'usuario_id' => $this->admin->id,
            'monto' => 50000,
            'metodo_pago' => 'efectivo',
            'fecha_pago' => now(),
        ]);

        $this->orden->refresh();
        $this->assertEquals(150000, $this->orden->saldo_pendiente);

        // Anular pago
        $response = $this->delete(route('ordenes.pagos.destroy', $pago));
        $response->assertRedirect(route('ordenes.show', $this->orden));

        $this->orden->refresh();
        $this->assertEquals(0, $this->orden->total_abonado);
        $this->assertEquals(200000, $this->orden->saldo_pendiente);
        $this->assertEquals('pendiente', $this->orden->estado_pago);
    }
}
