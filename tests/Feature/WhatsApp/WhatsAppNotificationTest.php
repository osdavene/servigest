<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\OrdenTrabajo;
use App\Models\Taller;
use App\Services\WhatsAppNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_despacha_notificacion_automatica_via_webhook(): void
    {
        Http::fake([
            'https://webhook-test.com/servigest' => Http::response(['status' => 'success'], 200),
        ]);

        $taller = Taller::create([
            'nombre_comercial' => 'ElectroTech',
            'email' => 'electrotech@test.com',
            'telefono' => '12345678',
            'estado_suscripcion' => 'activo',
            'whatsapp_auto_notify_enabled' => true,
            'whatsapp_api_provider' => 'webhook_personalizado',
            'whatsapp_webhook_url' => 'https://webhook-test.com/servigest',
        ]);

        $cliente = Cliente::create([
            'taller_id' => $taller->id,
            'nombre_completo' => 'Carlos Gomez',
            'telefono' => '573105554433',
            'direccion' => 'Calle 80 # 45-10',
        ]);

        $categoria = \App\Models\Categoria::create([
            'taller_id' => $taller->id,
            'nombre' => 'Laptops',
        ]);

        $equipo = Equipo::create([
            'taller_id' => $taller->id,
            'cliente_id' => $cliente->id,
            'categoria_id' => $categoria->id,
            'marca' => 'Asus',
            'modelo' => 'ZenBook',
        ]);

        $orden = OrdenTrabajo::create([
            'taller_id' => $taller->id,
            'cliente_id' => $cliente->id,
            'equipo_id' => $equipo->id,
            'codigo_orden' => 'OT-00050',
            'tipo_ubicacion' => 'ingresado_al_taller',
            'estado' => 'finalizado',
            'problema_reportado' => 'Pantalla rota',
            'costo_total' => 150000,
            'token_publico_pdf' => 'test-ot-token',
        ]);

        $resultado = WhatsAppNotificationService::enviarNotificacionAutomatica($orden, 'finalizada');

        $this->assertTrue($resultado);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://webhook-test.com/servigest' &&
                   $request['codigo_orden'] === 'OT-00050';
        });
    }
}
