<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OrdenTrabajo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * Envía una notificación automática de WhatsApp al cliente según el evento / estado de la orden.
     */
    public static function enviarNotificacionAutomatica(OrdenTrabajo $orden, string $evento = 'cambio_estado'): bool
    {
        $taller = $orden->taller;
        $cliente = $orden->cliente;

        if (!$taller || !$cliente || !$taller->whatsapp_auto_notify_enabled || empty($cliente->telefono)) {
            return false;
        }

        $mensaje = self::construirMensaje($orden, $evento);
        if (empty($mensaje)) {
            return false;
        }

        $telefonoLimpio = preg_replace('/[^0-9]/', '', $cliente->telefono);
        if (strlen($telefonoLimpio) < 8) {
            return false;
        }

        try {
            switch ($taller->whatsapp_api_provider) {
                case 'whatsapp_cloud_api':
                    return self::enviarMetaCloudApi($taller, $telefonoLimpio, $mensaje);

                case 'ultramsg':
                    return self::enviarUltraMsg($taller, $telefonoLimpio, $mensaje);

                case 'webhook_personalizado':
                default:
                    return self::enviarWebhook($taller, $telefonoLimpio, $mensaje, $orden);
            }
        } catch (\Throwable $e) {
            Log::warning("Error al enviar notificación WhatsApp automática para la orden {$orden->codigo_orden}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Construye el texto del mensaje reemplazando las variables dinámicas.
     */
    public static function construirMensaje(OrdenTrabajo $orden, string $evento): string
    {
        $taller = $orden->taller;
        $cliente = $orden->cliente;
        $equipo = $orden->equipo;

        $plantilla = match ($orden->estado) {
            'en_proceso' => $taller->whatsapp_template_en_proceso ?: 'Hola {cliente}, su equipo {equipo} ya está *EN REVISIÓN TÉCNICA* en {taller}. Orden #{codigo_orden}. Puede ver el avance aquí: {enlace_seguimiento}',
            'finalizado' => $taller->whatsapp_template_finalizada ?: '¡Buenas noticias {cliente}! Su equipo {equipo} está *LISTO PARA ENTREGA* en {taller}. Orden #{codigo_orden}. Total: ${total}. Detalles: {enlace_seguimiento}',
            'entregado' => $taller->whatsapp_template_entregada ?: 'Hola {cliente}, su orden #{codigo_orden} ha sido *ENTREGADA*. Gracias por confiar en {taller}. Garantía y comprobante: {enlace_seguimiento}',
            default => $taller->whatsapp_template_creada ?: 'Hola {cliente}, se ha registrado su equipo {equipo} en {taller}. Orden de Servicio: #{codigo_orden}. Consulte el estado en vivo aquí: {enlace_seguimiento}',
        };

        $urlSeguimiento = route('ordenes.publico', $orden->token_publico_pdf);

        $reemplazos = [
            '{cliente}' => $cliente?->nombre_completo ?? 'Cliente',
            '{codigo_orden}' => $orden->codigo_orden,
            '{equipo}' => ($equipo?->marca . ' ' . $equipo?->modelo),
            '{estado}' => ucfirst(str_replace('_', ' ', $orden->estado)),
            '{total}' => number_format((float)($orden->costo_total ?? 0), 0, ',', '.'),
            '{enlace_seguimiento}' => $urlSeguimiento,
            '{taller}' => $taller->nombre_comercial,
        ];

        return str_replace(array_keys($reemplazos), array_values($reemplazos), $plantilla);
    }

    private static function enviarWebhook($taller, string $telefono, string $mensaje, OrdenTrabajo $orden): bool
    {
        if (empty($taller->whatsapp_webhook_url)) {
            return false;
        }

        $payload = [
            'telefono' => $telefono,
            'mensaje' => $mensaje,
            'codigo_orden' => $orden->codigo_orden,
            'estado' => $orden->estado,
            'taller_id' => $taller->id,
            'url_seguimiento' => route('ordenes.publico', $orden->token_publico_pdf),
        ];

        $response = Http::timeout(5)->post($taller->whatsapp_webhook_url, $payload);
        return $response->successful();
    }

    private static function enviarMetaCloudApi($taller, string $telefono, string $mensaje): bool
    {
        if (empty($taller->whatsapp_api_token) || empty($taller->whatsapp_phone_number_id)) {
            return false;
        }

        $url = "https://graph.facebook.com/v19.0/{$taller->whatsapp_phone_number_id}/messages";

        $response = Http::withToken($taller->whatsapp_api_token)->timeout(5)->post($url, [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $telefono,
            'type' => 'text',
            'text' => ['preview_url' => true, 'body' => $mensaje],
        ]);

        return $response->successful();
    }

    private static function enviarUltraMsg($taller, string $telefono, string $mensaje): bool
    {
        if (empty($taller->whatsapp_api_token) || empty($taller->whatsapp_phone_number_id)) {
            return false;
        }

        $instanceId = $taller->whatsapp_phone_number_id;
        $url = "https://api.ultramsg.com/{$instanceId}/messages/chat";

        $response = Http::timeout(5)->post($url, [
            'token' => $taller->whatsapp_api_token,
            'to' => $telefono,
            'body' => $mensaje,
            'priority' => 10,
        ]);

        return $response->successful();
    }
}
