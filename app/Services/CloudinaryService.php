<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;

    public function __construct()
    {
        $this->cloudName = config('services.cloudinary.cloud_name', env('CLOUDINARY_CLOUD_NAME', 'vqeutyrl'));
        $this->apiKey    = config('services.cloudinary.api_key', env('CLOUDINARY_API_KEY', '855963679912268'));
        $this->apiSecret = config('services.cloudinary.api_secret', env('CLOUDINARY_API_SECRET', 'Or1gbh5lgNz2ClvVHaQMVoXUhU4'));
    }

    /**
     * Sube una imagen (UploadedFile, ruta de archivo o string binario/base64) a Cloudinary y devuelve la URL HTTPS segura.
     *
     * @param UploadedFile|string $archivo
     * @param string $carpeta Directorio estructurado (ej: 'servigest/talleres/taller_1/clientes/cliente_4/ordenes/orden_1/evidencias')
     * @param string|null $publicId Nombre opcional del recurso
     * @return string|null URL segura HTTPS (ej: https://res.cloudinary.com/.../image/upload/...)
     */
    public function subirImagen(UploadedFile|string $archivo, string $carpeta = 'servigest/general', ?string $publicId = null): ?string
    {
        if (empty($this->cloudName) || empty($this->apiKey) || empty($this->apiSecret)) {
            Log::warning('Cloudinary credentials missing, upload aborted.');
            return null;
        }

        $archivoTemporalCreado = null;
        $filePath = null;

        if ($archivo instanceof UploadedFile) {
            $filePath = $archivo->getRealPath();
        } elseif (is_string($archivo)) {
            // Si es una data URI en Base64 (ej: firmas canvas)
            if (str_starts_with($archivo, 'data:image/') || preg_match('/^[a-zA-Z0-9\/\r\n+={}]++$/', substr($archivo, 0, 100))) {
                $rawContent = str_contains($archivo, ',') ? substr($archivo, strpos($archivo, ',') + 1) : $archivo;
                $decoded = base64_decode($rawContent);
                if ($decoded !== false) {
                    $tmpPath = tempnam(sys_get_temp_dir(), 'cl_img_');
                    file_put_contents($tmpPath, $decoded);
                    $filePath = $tmpPath;
                    $archivoTemporalCreado = $tmpPath;
                }
            } elseif (file_exists($archivo)) {
                $filePath = $archivo;
            } else {
                // String binario en memoria
                $tmpPath = tempnam(sys_get_temp_dir(), 'cl_bin_');
                file_put_contents($tmpPath, $archivo);
                $filePath = $tmpPath;
                $archivoTemporalCreado = $tmpPath;
            }
        }

        if (!$filePath || !file_exists($filePath)) {
            Log::error('Cloudinary: No se pudo resolver la ruta de archivo para subir.');
            return null;
        }

        $timestamp = time();
        $params = [
            'folder'    => trim($carpeta, '/'),
            'timestamp' => $timestamp,
        ];

        if ($publicId) {
            $params['public_id'] = $publicId;
        }

        ksort($params);
        $signStr = '';
        foreach ($params as $k => $v) {
            $signStr .= "{$k}={$v}&";
        }
        $signStr = rtrim($signStr, '&') . $this->apiSecret;
        $signature = sha1($signStr);

        $postData = [
            'file'      => new \CURLFile($filePath),
            'api_key'   => $this->apiKey,
            'timestamp' => $timestamp,
            'folder'    => trim($carpeta, '/'),
            'signature' => $signature,
        ];

        if ($publicId) {
            $postData['public_id'] = $publicId;
        }

        $ch = curl_init("https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($archivoTemporalCreado && file_exists($archivoTemporalCreado)) {
            @unlink($archivoTemporalCreado);
        }

        if ($curlErr) {
            Log::error("Cloudinary cURL error: {$curlErr}");
            return null;
        }

        $json = json_decode($resp, true);

        if ($httpCode === 200 && isset($json['secure_url'])) {
            return $json['secure_url'];
        }

        Log::error('Cloudinary upload error: ' . ($resp ?: 'Unknown error'));
        return null;
    }

    /**
     * Elimina una imagen de Cloudinary a partir de su URL o Public ID.
     */
    public function eliminarImagen(string $publicIdOrUrl): bool
    {
        if (empty($this->cloudName) || empty($this->apiKey) || empty($this->apiSecret)) {
            return false;
        }

        $publicId = $publicIdOrUrl;
        if (str_contains($publicIdOrUrl, 'cloudinary.com/')) {
            $path = parse_url($publicIdOrUrl, PHP_URL_PATH);
            $parts = explode('/upload/', $path);
            if (isset($parts[1])) {
                $sub = preg_replace('/^v\d+\//', '', $parts[1]);
                $publicId = pathinfo($sub, PATHINFO_DIRNAME) . '/' . pathinfo($sub, PATHINFO_FILENAME);
                $publicId = ltrim($publicId, './');
            }
        }

        $timestamp = time();
        $signStr = "public_id={$publicId}&timestamp={$timestamp}{$this->apiSecret}";
        $signature = sha1($signStr);

        $postData = [
            'public_id' => $publicId,
            'api_key'   => $this->apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ];

        $ch = curl_init("https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($resp, true);
        return $httpCode === 200 && ($json['result'] ?? '') === 'ok';
    }
}
