<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OptimizadorImagenes
{
    /**
     * Procesa, orienta, redimensiona y comprime una imagen (tipo WhatsApp) reduciendo fotos de 5MB-10MB a ~200KB-400KB con excelente nitidez.
     *
     * @param UploadedFile $archivo
     * @param string $carpetaDestino
     * @param int $maxDimension Dimensión máxima en píxeles (ancho o alto)
     * @param int $calidad Calidad de compresión (0 a 100)
     * @return string Ruta relativa en el disco 'public'
     */
    public static function optimizarYGuardar(UploadedFile $archivo, string $carpetaDestino, int $maxDimension = 1600, int $calidad = 80): string
    {
        $rutaTemporal = $archivo->getRealPath();
        $mime = $archivo->getMimeType();

        // Cargar imagen original según su formato
        $imagenOriginal = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($rutaTemporal),
            'image/png' => @imagecreatefrompng($rutaTemporal),
            'image/webp' => @imagecreatefromwebp($rutaTemporal),
            default => null,
        };

        // Si GD no puede procesarla directamente, guardarla normal como fallback
        if (!$imagenOriginal) {
            return $archivo->store($carpetaDestino, 'public');
        }

        // 1. Corregir orientación de fotos tomadas con celular (EXIF Orientation)
        if (function_exists('exif_read_data') && ($mime === 'image/jpeg' || $mime === 'image/jpg')) {
            $exif = @exif_read_data($rutaTemporal);
            if (!empty($exif['Orientation'])) {
                $imagenOriginal = match ($exif['Orientation']) {
                    3 => imagerotate($imagenOriginal, 180, 0),
                    6 => imagerotate($imagenOriginal, -90, 0),
                    8 => imagerotate($imagenOriginal, 90, 0),
                    default => $imagenOriginal,
                };
            }
        }

        $anchoOriginal = imagesx($imagenOriginal);
        $altoOriginal = imagesy($imagenOriginal);

        // 2. Calcular dimensiones proporcionales (tipo WhatsApp / Full HD)
        $nuevoAncho = $anchoOriginal;
        $nuevoAlto = $altoOriginal;

        if ($anchoOriginal > $maxDimension || $altoOriginal > $maxDimension) {
            if ($anchoOriginal >= $altoOriginal) {
                $nuevoAncho = $maxDimension;
                $nuevoAlto = (int) round(($altoOriginal * $maxDimension) / $anchoOriginal);
            } else {
                $nuevoAlto = $maxDimension;
                $nuevoAncho = (int) round(($anchoOriginal * $maxDimension) / $altoOriginal);
            }
        }

        // 3. Crear lienzo optimizado con remuestreo de alta calidad
        $lienzoOptimizado = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

        // Mantener fondo blanco o transparente limpio
        $blanco = imagecolorallocate($lienzoOptimizado, 255, 255, 255);
        imagefilledrectangle($lienzoOptimizado, 0, 0, $nuevoAncho, $nuevoAlto, $blanco);

        imagecopyresampled(
            $lienzoOptimizado,
            $imagenOriginal,
            0, 0, 0, 0,
            $nuevoAncho,
            $nuevoAlto,
            $anchoOriginal,
            $altoOriginal
        );

        // 4. Guardar comprimida en memoria/archivo
        $nombreUnico = Str::uuid() . '.jpg';
        $rutaCompletaDestino = "{$carpetaDestino}/{$nombreUnico}";

        ob_start();
        imagejpeg($lienzoOptimizado, null, $calidad);
        $contenidoComprimido = ob_get_clean();

        // Liberar memoria RAM
        imagedestroy($imagenOriginal);
        imagedestroy($lienzoOptimizado);

        // 5. Almacenar en disco público
        Storage::disk('public')->put($rutaCompletaDestino, $contenidoComprimido);

        return $rutaCompletaDestino;
    }
}
