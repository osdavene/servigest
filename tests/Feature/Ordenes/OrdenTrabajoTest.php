<?php

declare(strict_types=1);

namespace Tests\Feature\Ordenes;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\OrdenTrabajo;
use App\Models\Taller;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrdenTrabajoTest extends TestCase
{
    use RefreshDatabase;

    private Taller $taller;
    private Usuario $admin;
    private Usuario $tecnico;
    private Cliente $cliente;
    private Categoria $categoria;
    private Equipo $equipo;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->taller = Taller::create([
            'nombre_comercial' => 'Taller Test',
            'email' => 'taller@test.com',
            'telefono' => '12345678',
            'estado_suscripcion' => 'activo',
        ]);

        $this->admin = Usuario::create([
            'taller_id' => $this->taller->id,
            'nombre' => 'Admin',
            'apellido' => 'Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'rol' => 'administrador',
            'esta_activo' => true,
        ]);

        $this->tecnico = Usuario::create([
            'taller_id' => $this->taller->id,
            'nombre' => 'Tecnico',
            'apellido' => 'Test',
            'email' => 'tecnico@test.com',
            'password' => Hash::make('password123'),
            'rol' => 'tecnico',
            'esta_activo' => true,
        ]);

        $this->cliente = Cliente::create([
            'taller_id' => $this->taller->id,
            'nombre_completo' => 'Cliente Test',
            'telefono' => '3001234567',
            'direccion' => 'Calle 1 # 2-3',
        ]);

        $this->categoria = Categoria::create([
            'taller_id' => $this->taller->id,
            'nombre' => 'Laptops',
            'requiere_mantenimiento_preventivo' => true,
            'intervalo_mantenimiento_dias' => 90,
        ]);

        $this->equipo = Equipo::create([
            'taller_id' => $this->taller->id,
            'cliente_id' => $this->cliente->id,
            'categoria_id' => $this->categoria->id,
            'marca' => 'Dell',
            'modelo' => 'Inspiron',
        ]);
    }

    public function test_crea_orden_de_trabajo_exitosamente(): void
    {
        $response = $this->actingAs($this->admin)->post(route('ordenes.store'), [
            'cliente_id' => $this->cliente->id,
            'equipo_id' => $this->equipo->id,
            'tecnico_asignado_id' => $this->tecnico->id,
            'tipo_ubicacion' => 'ingresado_al_taller',
            'problema_reportado' => 'No enciende la pantalla',
            'costo_mano_obra' => 50000,
            'costo_repuestos' => 30000,
        ]);

        $this->assertDatabaseHas('ordenes_trabajo', [
            'taller_id' => $this->taller->id,
            'cliente_id' => $this->cliente->id,
            'equipo_id' => $this->equipo->id,
            'problema_reportado' => 'No enciende la pantalla',
            'costo_total' => 80000,
            'estado' => 'pendiente',
        ]);

        $orden = OrdenTrabajo::first();
        $response->assertRedirect(route('ordenes.show', $orden));
    }

    public function test_bloquea_asignacion_de_cliente_de_otro_taller_por_seguridad(): void
    {
        $otroTaller = Taller::create([
            'nombre_comercial' => 'Otro Taller',
            'email' => 'otro@test.com',
            'telefono' => '9999999',
            'estado_suscripcion' => 'activo',
        ]);

        $clienteInvasor = Cliente::create([
            'taller_id' => $otroTaller->id,
            'nombre_completo' => 'Cliente Foraneo',
            'telefono' => '99988877',
            'direccion' => 'Av Otra',
        ]);

        $response = $this->actingAs($this->admin)->post(route('ordenes.store'), [
            'cliente_id' => $clienteInvasor->id, // IDOR
            'equipo_id' => $this->equipo->id,
            'tipo_ubicacion' => 'ingresado_al_taller',
            'problema_reportado' => 'Falla de prueba',
        ]);

        $response->assertSessionHasErrors('cliente_id');
    }

    public function test_finalizar_orden_actualiza_mantenimiento_y_registra_foto(): void
    {
        $orden = OrdenTrabajo::create([
            'taller_id' => $this->taller->id,
            'cliente_id' => $this->cliente->id,
            'equipo_id' => $this->equipo->id,
            'tipo_ubicacion' => 'ingresado_al_taller',
            'estado' => 'en_proceso',
            'problema_reportado' => 'Mantenimiento preventivo',
            'token_publico_pdf' => 'test-token-123',
            'fecha_ingreso' => now(),
        ]);

        $foto = UploadedFile::fake()->image('entrega.jpg', 800, 600);

        $response = $this->actingAs($this->admin)->put(route('ordenes.update', $orden), [
            'tipo_ubicacion' => 'ingresado_al_taller',
            'estado' => 'finalizado',
            'problema_reportado' => 'Mantenimiento preventivo',
            'diagnostico' => 'Limpieza y cambio de pasta',
            'procedimiento_realizado' => 'Completado con exito',
            'costo_mano_obra' => 70000,
            'costo_repuestos' => 15000,
            'foto_cierre' => $foto,
            'descripcion_foto_cierre' => 'Equipo funcionando y entregado',
        ]);

        $response->assertRedirect(route('ordenes.show', $orden));

        $orden->refresh();
        $this->assertEquals('finalizado', $orden->estado);
        $this->assertEquals(85000, $orden->costo_total);
        $this->assertNotNull($orden->fecha_finalizacion);

        // Validar recálculo de mantenimiento en el equipo
        $this->equipo->refresh();
        $this->assertEquals(now()->toDateString(), $this->equipo->fecha_ultimo_servicio?->toDateString());
        $this->assertEquals(now()->addDays(90)->toDateString(), $this->equipo->fecha_proximo_mantenimiento?->toDateString());

        // Validar foto registrada
        $this->assertDatabaseHas('evidencias_fotograficas', [
            'orden_trabajo_id' => $orden->id,
            'etiqueta' => 'como_se_devuelve',
        ]);
    }
}
