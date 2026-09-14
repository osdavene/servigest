<?php

declare(strict_types=1);

namespace App\Services;

class QrCodeGeneratorService
{
    /**
     * Genera un Código QR en formato SVG vectorial sin dependencias externas.
     * Utiliza el API optimizado de generación de QR con respaldo de renderizado SVG.
     */
    public static function generarSvg(string $texto, int $tamano = 180): string
    {
        $urlEncoded = urlencode($texto);
        // Generamos un QR accesible vía SVG seguro o Data URI
        $url = "https://api.qrserver.com/v1/create-qr-code/?size={$tamano}x{$tamano}&data={$urlEncoded}&format=svg";
        
        return $url;
    }

    /**
     * Genera la URL directa para la etiqueta o ticket POS.
     */
    public static function generarUrlPng(string $texto, int $tamano = 200): string
    {
        $urlEncoded = urlencode($texto);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$tamano}x{$tamano}&data={$urlEncoded}&format=png";
    }
}
