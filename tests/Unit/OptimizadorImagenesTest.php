<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\OptimizadorImagenes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OptimizadorImagenesTest extends TestCase
{
    public function test_comprime_y_guarda_imagen_en_disco_publico(): void
    {
        Storage::fake('public');

        $archivo = UploadedFile::fake()->image('test_grande.jpg', 2400, 1800);
        $carpeta = 'evidencias/test/1';

        $ruta = OptimizadorImagenes::optimizarYGuardar($archivo, $carpeta, 1600, 80);

        $this->assertNotEmpty($ruta);
        $this->assertStringStartsWith('evidencias/test/1/', $ruta);
        $this->assertStringEndsWith('.jpg', $ruta);
        Storage::disk('public')->assertExists($ruta);
    }
}
