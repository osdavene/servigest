<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OptimizadorImagenes
{
    /**
     * Procesa, orienta, redimensiona y comprime una imagen (tipo WhatsApp Full HD) y la sube a Cloudinary en su carpeta jerárquica.
     * Si Cloudinary no está disponible, la almacena de forma segura en el almacenamiento local como respaldo.
     *
     * @param UploadedFile|string $archivo Archivo subido, ruta absoluta o string base64
     * @param string $carpetaDestino Carpeta jerárquica (ej: 'servigest/talleres/taller_1/clientes/cliente_4/ordenes/orden_1/evidencias')
     * @param int $maxDimension Dimensión máxima en píxeles (ancho o alto)
     * @param int $calidad Calidad de compresión (0 a 100)
     * @return string URL segura HTTPS de Cloudinary o ruta relativa en el disco 'public'
     */
    public static function optimizarYGuardar(
        UploadedFile|string $archivo,
        string $carpetaDestino,
        int $maxDimension = 1600,
        int $calidad = 80
    ): string {
        $rutaTemporal = null;
        $esTemporalCreado = false;
        $mime = null;

        if ($archivo instanceof UploadedFile) {
            $rutaTemporal = $archivo->getRealPath();
            $mime = $archivo->getMimeType();
        } elseif (is_string($archivo)) {
            // Data URI Base64 (ej: firmas de canvas)
            if (str_starts_with($archivo, 'data:image/') || preg_match('/^[a-zA-Z0-9\/\r\n+={}]++$/', substr($archivo, 0, 100))) {
                $raw = str_contains($archivo, ',') ? substr($archivo, strpos($archivo, ',') + 1) : $archivo;
                $decoded = base64_decode($raw);
                if ($decoded !== false) {
                    $tmp = tempnam(sys_get_temp_dir(), 'opt_b64_');
                    file_put_contents($tmp, $decoded);
                    $rutaTemporal = $tmp;
                    $esTemporalCreado = true;
                    $mime = 'image/png';
                }
            } elseif (file_exists($archivo)) {
                $rutaTemporal = $archivo;
                $mime = mime_content_type($archivo) ?: 'image/jpeg';
            }
        }

        if (!$rutaTemporal || !file_exists($rutaTemporal)) {
            Log::warning('OptimizadorImagenes: Archivo no válido para optimización.');
            return '';
        }

        // Cargar imagen original según su formato con GD
        $imagenOriginal = null;
        if (extension_loaded('gd')) {
            $imagenOriginal = match ($mime) {
                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($rutaTemporal),
                'image/png' => @imagecreatefrompng($rutaTemporal),
                'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($rutaTemporal) : null,
                default => null,
            };
        }

        $contenidoComprimido = null;

        // Si GD pudo procesarla, aplicar orientación EXIF, redimensionado proporcional y compresión
        if ($imagenOriginal) {
            // 1. Corregir orientación de fotos tomadas con celular
            if (function_exists('exif_read_data') && ($mime === 'image/jpeg' || $mime === 'image/jpg')) {
                try {
                    $exif = @exif_read_data($rutaTemporal);
                    if (!empty($exif['Orientation'])) {
                        $imagenOriginal = match ($exif['Orientation']) {
                            3 => imagerotate($imagenOriginal, 180, 0),
                            6 => imagerotate($imagenOriginal, -90, 0),
                            8 => imagerotate($imagenOriginal, 90, 0),
                            default => $imagenOriginal,
                        };
                    }
                } catch (\Throwable) {
                    // Ignorar errores en lectura de EXIF
                }
            }

            $anchoOriginal = imagesx($imagenOriginal);
            $altoOriginal = imagesy($imagenOriginal);

            // 2. Calcular dimensiones proporcionales
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

            // 3. Crear lienzo optimizado
            $lienzoOptimizado = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
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

            // 4. Guardar comprimida en buffer
            ob_start();
            imagejpeg($lienzoOptimizado, null, $calidad);
            $contenidoComprimido = ob_get_clean();

            // Liberar memoria RAM
            imagedestroy($imagenOriginal);
            imagedestroy($lienzoOptimizado);
        } else {
            $contenidoComprimido = file_get_contents($rutaTemporal);
        }

        if ($esTemporalCreado && file_exists($rutaTemporal)) {
            @unlink($rutaTemporal);
        }

        // Crear un archivo temporal para subir a Cloudinary
        $tmpUpload = tempnam(sys_get_temp_dir(), 'opt_upl_');
        file_put_contents($tmpUpload, $contenidoComprimido);

        // 5. Intentar subir a Cloudinary en la carpeta especificada
        $cloudinary = new CloudinaryService();
        $urlCloudinary = $cloudinary->subirImagen($tmpUpload, $carpetaDestino);

        if (file_exists($tmpUpload)) {
            @unlink($tmpUpload);
        }

        if ($urlCloudinary) {
            return $urlCloudinary;
        }

        // 6. Fallback en almacenamiento local público si Cloudinary falla
        $nombreLocal = Str::uuid() . '.jpg';
        $rutaLocal = trim($carpetaDestino, '/') . '/' . $nombreLocal;
        Storage::disk('public')->put($rutaLocal, $contenidoComprimido);

        return $rutaLocal;
    }

    /**
     * Elimina una imagen ya sea de Cloudinary o del almacenamiento local.
     */
    public static function eliminar(string $rutaOUrl): bool
    {
        if (empty($rutaOUrl)) {
            return false;
        }

        if (str_contains($rutaOUrl, 'cloudinary.com/')) {
            $cloudinary = new CloudinaryService();
            return $cloudinary->eliminarImagen($rutaOUrl);
        }

        if (Storage::disk('public')->exists($rutaOUrl)) {
            return Storage::disk('public')->delete($rutaOUrl);
        }

        return false;
    }
}
