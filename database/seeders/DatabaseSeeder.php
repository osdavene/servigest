<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\EvidenciaFotografica;
use App\Models\OrdenTrabajo;
use App\Models\PlanLicencia;
use App\Models\Taller;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Genera un Logo corporativo nítido en PNG para cada empresa.
     */
    private function generarLogoTaller(int $tallerId, string $nombrePrincipal, string $subtitulo, array $colores): string
    {
        $directorio = storage_path("app/public/servigest/talleres/taller_{$tallerId}/logos");
        if (!File::exists($directorio)) {
            File::makeDirectory($directorio, 0755, true);
        }

        $nombreArchivo = "logo_{$tallerId}.png";
        $rutaCompleta = "{$directorio}/{$nombreArchivo}";
        $rutaRelativa = "servigest/talleres/taller_{$tallerId}/logos/{$nombreArchivo}";

        $ancho = 560;
        $alto = 160;
        $img = imagecreatetruecolor($ancho, $alto);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        $transparente = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefilledrectangle($img, 0, 0, $ancho, $alto, $transparente);
        imagealphablending($img, true);

        $colorBgBadge = imagecolorallocate($img, $colores['badge_r'], $colores['badge_g'], $colores['badge_b']);
        $colorBadgeBorde = imagecolorallocate($img, $colores['accent_r'], $colores['accent_g'], $colores['accent_b']);
        $colorTexto1 = imagecolorallocate($img, 15, 23, 42);
        $colorTexto2 = imagecolorallocate($img, $colores['accent_r'], $colores['accent_g'], $colores['accent_b']);
        $colorSubtitulo = imagecolorallocate($img, 100, 116, 139);
        $blanco = imagecolorallocate($img, 255, 255, 255);

        // Insignia Izquierda
        imagefilledrectangle($img, 20, 30, 120, 130, $colorBgBadge);
        imagerectangle($img, 20, 30, 120, 130, $colorBadgeBorde);
        imagerectangle($img, 22, 32, 118, 128, $colorBadgeBorde);

        $inicial = substr($nombrePrincipal, 0, 2);
        imagestring($img, 5, 55, 68, $inicial, $blanco);
        imagestring($img, 3, 50, 92, "TECH", $colorBadgeBorde);

        // Textos del Logo
        imagestring($img, 5, 140, 42, strtoupper($nombrePrincipal), $colorTexto1);
        imagestring($img, 4, 140, 72, "SERVICIO TECNICO ESPECIALIZADO", $colorTexto2);
        imagestring($img, 3, 140, 98, $subtitulo, $colorSubtitulo);

        imagefilledrectangle($img, 140, 124, 480, 127, $colorBadgeBorde);

        imagepng($img, $rutaCompleta);
        imagedestroy($img);

        return $rutaRelativa;
    }

    /**
     * Genera una imagen de firma digital simulada para actas de entrega.
     */
    private function generarFirmaCliente(int $tallerId, int $clienteId, int $ordenId, string $nombreFirmante): string
    {
        $directorio = storage_path("app/public/servigest/talleres/taller_{$tallerId}/clientes/cliente_{$clienteId}/ordenes/orden_{$ordenId}/firmas");
        if (!File::exists($directorio)) {
            File::makeDirectory($directorio, 0755, true);
        }

        $nombreArchivo = "firma_{$ordenId}.png";
        $rutaCompleta = "{$directorio}/{$nombreArchivo}";
        $rutaRelativa = "servigest/talleres/taller_{$tallerId}/clientes/cliente_{$clienteId}/ordenes/orden_{$ordenId}/firmas/{$nombreArchivo}";

        $ancho = 400;
        $alto = 120;
        $img = imagecreatetruecolor($ancho, $alto);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        $transparente = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefilledrectangle($img, 0, 0, $ancho, $alto, $transparente);
        imagealphablending($img, true);

        $colorTinta = imagecolorallocate($img, 15, 23, 42); // Azul noche oscuro
        $colorGris = imagecolorallocate($img, 148, 163, 184);

        // Trazo de firma simulado con curvas y trazos
        imagesetthickness($img, 2);
        imageline($img, 40, 75, 90, 35, $colorTinta);
        imageline($img, 90, 35, 130, 85, $colorTinta);
        imageline($img, 130, 85, 180, 45, $colorTinta);
        imageline($img, 180, 45, 260, 70, $colorTinta);
        imageline($img, 260, 70, 340, 50, $colorTinta);
        imageline($img, 30, 95, 360, 95, $colorGris);

        imagestring($img, 2, 40, 100, $nombreFirmante, $colorGris);

        imagepng($img, $rutaCompleta);
        imagedestroy($img);

        return $rutaRelativa;
    }

    /**
     * Descarga una foto técnica real desde Unsplash o genera una gráfica Full HD con GD.
     */
    private function generarImagenEvidencia(int $tallerId, int $clienteId, int $ordenId, string $etiqueta, string $titulo, ?string $urlFotoReal = null): string
    {
        $directorio = storage_path("app/public/servigest/talleres/taller_{$tallerId}/clientes/cliente_{$clienteId}/ordenes/orden_{$ordenId}/evidencias");
        if (!File::exists($directorio)) {
            File::makeDirectory($directorio, 0755, true);
        }

        $nombreArchivo = Str::uuid()->toString() . '.jpg';
        $rutaCompleta = "{$directorio}/{$nombreArchivo}";
        $rutaRelativa = "servigest/talleres/taller_{$tallerId}/clientes/cliente_{$clienteId}/ordenes/orden_{$ordenId}/evidencias/{$nombreArchivo}";

        $fotoDescargada = false;

        if ($urlFotoReal) {
            try {
                $response = Http::timeout(3)->get($urlFotoReal);
                if ($response->successful() && strlen($response->body()) > 4000) {
                    File::put($rutaCompleta, $response->body());
                    $fotoDescargada = true;
                }
            } catch (\Throwable $e) {
                $fotoDescargada = false;
            }
        }

        if (!$fotoDescargada) {
            $img = imagecreatetruecolor(800, 600);

            if (str_contains($etiqueta, 'recibe') || str_contains($etiqueta, 'antes')) {
                $fondo = imagecolorallocate($img, 30, 41, 59);
                $colorAcento = imagecolorallocate($img, 245, 158, 11);
            } elseif (str_contains($etiqueta, 'devuelve') || str_contains($etiqueta, 'despues')) {
                $fondo = imagecolorallocate($img, 6, 78, 59);
                $colorAcento = imagecolorallocate($img, 52, 211, 153);
            } elseif (str_contains($etiqueta, 'falla')) {
                $fondo = imagecolorallocate($img, 69, 10, 10);
                $colorAcento = imagecolorallocate($img, 248, 113, 113);
            } else {
                $fondo = imagecolorallocate($img, 12, 74, 110);
                $colorAcento = imagecolorallocate($img, 56, 189, 248);
            }

            $blanco = imagecolorallocate($img, 255, 255, 255);
            $gris = imagecolorallocate($img, 148, 163, 184);

            imagefilledrectangle($img, 0, 0, 800, 600, $fondo);
            imagerectangle($img, 20, 20, 780, 580, $colorAcento);

            imagestring($img, 5, 40, 50, "SERVIGEST - REGISTRO TECNICO", $colorAcento);
            imagestring($img, 4, 40, 80, "Taller ID: {$tallerId} | Cliente ID: {$clienteId} | Orden: #OT-000{$ordenId}", $gris);
            imagestring($img, 5, 40, 140, "ETAPA: " . strtoupper(str_replace('_', ' ', $etiqueta)), $blanco);
            imagestring($img, 4, 40, 180, "Evidencia: {$titulo}", $blanco);
            imagestring($img, 3, 40, 530, "Fecha: " . date('d/m/Y H:i:s') . " | Full HD", $gris);

            imagejpeg($img, $rutaCompleta, 85);
            imagedestroy($img);
        }

        EvidenciaFotografica::create([
            'taller_id' => $tallerId,
            'orden_trabajo_id' => $ordenId,
            'ruta_imagen' => $rutaRelativa,
            'etiqueta' => $etiqueta,
            'descripcion' => $titulo,
        ]);

        return $rutaRelativa;
    }

    /**
     * Ejecuta el seeder maestro con 10 clientes y 3+ órdenes por cliente.
     */
    public function run(): void
    {
        // 0. Planes de Licencia
        $this->call(PlanLicenciaSeeder::class);

        $planMensual = PlanLicencia::where('dias_duracion', 30)->where('es_prueba', false)->first();
        $planAnual = PlanLicencia::where('dias_duracion', 365)->where('es_prueba', false)->first();
        $planDemo = PlanLicencia::where('es_prueba', true)->first();

        // 1. SUPER ADMINISTRADOR
        Usuario::firstOrCreate([
            'email' => 'superadmin@servigest.com',
        ], [
            'taller_id' => null,
            'nombre' => 'Super',
            'apellido' => 'Administrador',
            'telefono' => '573009998877',
            'password' => Hash::make('password123'),
            'rol' => 'super_administrador',
            'esta_activo' => true,
        ]);

        // =========================================================================
        // TALLER 1: ElectroTech Soluciones Integrales (Bogotá)
        // =========================================================================
        $taller1 = Taller::create([
            'plan_licencia_id' => $planAnual?->id ?? $planMensual?->id,
            'nombre_comercial' => 'ElectroTech Soluciones Integrales',
            'identificacion_fiscal' => 'NIT 900.123.456-7',
            'telefono' => '573105554433',
            'email' => 'admin@electrotech.com',
            'direccion' => 'Carrera 15 # 85-30',
            'ciudad' => 'Bogotá',
            'texto_garantia' => 'Garantía legal de 90 días calendario sobre mano de obra y repuestos sustituidos. No cubre daños por sobrevoltaje o humedad.',
            'prefijo_orden' => 'OT',
            'estado_suscripcion' => 'activo',
            'fecha_vencimiento_suscripcion' => now()->addMonths(10),
            'whatsapp_auto_notify_enabled' => true,
        ]);

        $logoT1 = $this->generarLogoTaller($taller1->id, 'ElectroTech', 'Laptops • Línea Blanca • Smart TVs • Gaming', [
            'badge_r' => 2, 'badge_g' => 132, 'badge_b' => 199,
            'accent_r' => 14, 'accent_g' => 165, 'accent_b' => 233,
        ]);
        $taller1->update(['logo_ruta' => $logoT1]);

        // Usuarios Taller 1
        $adminT1 = Usuario::create([
            'taller_id' => $taller1->id,
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza',
            'email' => 'admin@electrotech.com',
            'telefono' => '573105554433',
            'password' => Hash::make('password123'),
            'rol' => 'administrador',
            'esta_activo' => true,
        ]);

        $tec1 = Usuario::create([
            'taller_id' => $taller1->id,
            'nombre' => 'Diego',
            'apellido' => 'Ramírez',
            'email' => 'diego@electrotech.com',
            'telefono' => '573117778899',
            'password' => Hash::make('password123'),
            'rol' => 'tecnico',
            'esta_activo' => true,
        ]);

        $tec2 = Usuario::create([
            'taller_id' => $taller1->id,
            'nombre' => 'Javier',
            'apellido' => 'Hernández',
            'email' => 'javier@electrotech.com',
            'telefono' => '573124445566',
            'password' => Hash::make('password123'),
            'rol' => 'tecnico',
            'esta_activo' => true,
        ]);

        // Categorías Taller 1
        $catLaptops = Categoria::create([
            'taller_id' => $taller1->id,
            'nombre' => 'Laptops & Computadores Gamer',
            'descripcion' => 'Mantenimiento preventivo, cambio de pantallas, teclados, chips de video y discos SSD.',
            'requiere_mantenimiento_preventivo' => true,
            'intervalo_mantenimiento_dias' => 180,
        ]);

        $catNeveras = Categoria::create([
            'taller_id' => $taller1->id,
            'nombre' => 'Neveras & Refrigeración Inverter',
            'descripcion' => 'Carga de refrigerante R600/R134a, bimetálicos, motores inverter y tarjetas electrónicas.',
            'requiere_mantenimiento_preventivo' => true,
            'intervalo_mantenimiento_dias' => 365,
        ]);

        $catTVs = Categoria::create([
            'taller_id' => $taller1->id,
            'nombre' => 'Smart TVs & Pantallas 4K/8K',
            'descripcion' => 'Reparación de tiras LED / backlight, fuentes de poder y tarjetas mainboard.',
            'requiere_mantenimiento_preventivo' => false,
        ]);

        $catLavadoras = Categoria::create([
            'taller_id' => $taller1->id,
            'nombre' => 'Lavadoras & Secadoras Automáticas',
            'descripcion' => 'Transmisión, bombas de desagüe, presostatos, motores y mantenimiento de tinas.',
            'requiere_mantenimiento_preventivo' => true,
            'intervalo_mantenimiento_dias' => 180,
        ]);

        $catSmartphones = Categoria::create([
            'taller_id' => $taller1->id,
            'nombre' => 'Smartphones & Tablets Premium',
            'descripcion' => 'Displays OLED, cambio de baterías originales, puertos de carga y microsoldadura.',
            'requiere_mantenimiento_preventivo' => false,
        ]);

        $catConsolas = Categoria::create([
            'taller_id' => $taller1->id,
            'nombre' => 'Consolas de Videojuegos (PS5, Xbox, Switch)',
            'descripcion' => 'Mantenimiento de metal líquido, puertos HDMI 2.1, fuentes de poder y mandos.',
            'requiere_mantenimiento_preventivo' => true,
            'intervalo_mantenimiento_dias' => 180,
        ]);

        // =========================================================================
        // 10 CLIENTES COMPLETOS PARA EL TALLER 1
        // =========================================================================
        $clientesData = [
            [
                'nombre' => 'María Camila Gómez',
                'id' => 'CC 1020304050',
                'tel' => '573001234567',
                'email' => 'camila.gomez@gmail.com',
                'dir' => 'Calle 127 # 45-20',
                'barrio' => 'Unicentro',
                'ciudad' => 'Bogotá',
                'notas' => 'Cliente VIP corporativa. Solicita siempre informes membretados.',
            ],
            [
                'nombre' => 'Andrés Felipe Castro',
                'id' => 'CC 79854120',
                'tel' => '573159876543',
                'email' => 'andres.castro@empresa.com',
                'dir' => 'Avenida Boyacá # 68-15',
                'barrio' => 'Normandía',
                'ciudad' => 'Bogotá',
                'notas' => 'Conjunto cerrado Portal de Normandía Torre 3 Apto 502.',
            ],
            [
                'nombre' => 'Restaurante El Fogón Boyacense',
                'id' => 'NIT 901.888.777-2',
                'tel' => '573183332211',
                'email' => 'compras@fogonboyacense.com',
                'dir' => 'Carrera 7 # 45-12',
                'barrio' => 'Chapinero',
                'ciudad' => 'Bogotá',
                'notas' => 'Equipos de refrigeración comercial. Atención preferencial antes de mediodía.',
            ],
            [
                'nombre' => 'Valentina Restrepo Morales',
                'id' => 'CC 52987412',
                'tel' => '573164448899',
                'email' => 'valentina.restrepo@hotmail.com',
                'dir' => 'Calle 80 # 70-15',
                'barrio' => 'Pontevedra',
                'ciudad' => 'Bogotá',
                'notas' => 'Diseñadora gráfica. Trabaja con equipos Apple y pantallas 4K.',
            ],
            [
                'nombre' => 'Inversiones & Consultoría Alfa S.A.S.',
                'id' => 'NIT 900.555.444-1',
                'tel' => '573204447788',
                'email' => 'sistemas@inversionesalfa.co',
                'dir' => 'Calle 93B # 13-45 Of. 601',
                'barrio' => 'Chicó Norte',
                'ciudad' => 'Bogotá',
                'notas' => 'Parque tecnológico de 15 Laptops Dell y ThinkPads.',
            ],
            [
                'nombre' => 'Dr. Santiago Morales Pinzón',
                'id' => 'CC 19485230',
                'tel' => '573118889900',
                'email' => 'santiago.morales@clinicacolombia.com',
                'dir' => 'Carrera 68D # 22A-40',
                'barrio' => 'Salitre',
                'ciudad' => 'Bogotá',
                'notas' => 'Consultorio odontológico. Equipos de cómputo y climatización.',
            ],
            [
                'nombre' => 'Hotel Boutique Casa Medina Colonial',
                'id' => 'NIT 860.123.987-6',
                'tel' => '573024567890',
                'email' => 'mantenimiento@casamedinacolonial.com',
                'dir' => 'Carrera 7 # 69A-22',
                'barrio' => 'Zona G',
                'ciudad' => 'Bogotá',
                'notas' => 'Smart TVs en suites y lavadoras industriales de lavandería.',
            ],
            [
                'nombre' => 'Laura Ximena Cárdenas',
                'id' => 'CC 1018456789',
                'tel' => '573147776655',
                'email' => 'laura.cardenas@arquitectura.com',
                'dir' => 'Transversal 23 # 95-10',
                'barrio' => 'La Castellana',
                'ciudad' => 'Bogotá',
                'notas' => 'Estudio de renderizado 3D y gaming.',
            ],
            [
                'nombre' => 'Ferretería & Distribuciones El Constructor',
                'id' => 'NIT 830.456.123-9',
                'tel' => '573138882233',
                'email' => 'gerencia@elconstructor.com.co',
                'dir' => 'Calle 13 # 38-50',
                'barrio' => 'Puente Aranda',
                'ciudad' => 'Bogotá',
                'notas' => 'Servidores locales y computadores de facturación POS.',
            ],
            [
                'nombre' => 'Juan Pablo Echeverri',
                'id' => 'CC 80234567',
                'tel' => '573059991122',
                'email' => 'juanpablo.echeverri@outlook.com',
                'dir' => 'Calle 140 # 11-30',
                'barrio' => 'Cedritos',
                'ciudad' => 'Bogotá',
                'notas' => 'Creador de contenido digital y streamer.',
            ],
        ];

        $clientes = [];
        foreach ($clientesData as $c) {
            $cliente = Cliente::create([
                'taller_id' => $taller1->id,
                'nombre_completo' => $c['nombre'],
                'identificacion' => $c['id'],
                'telefono' => $c['tel'],
                'email' => $c['email'],
                'direccion' => $c['dir'],
                'barrio' => $c['barrio'],
                'ciudad' => $c['ciudad'],
                'notas_adicionales' => $c['notas'],
                'token_portal' => bin2hex(random_bytes(24)),
            ]);
            $clientes[] = $cliente;
        }

        // =========================================================================
        // EQUIPOS Y 3+ ÓRDENES DE TRABAJO POR CADA CLIENTE (35+ ÓRDENES TOTAL)
        // =========================================================================
        $contadorOT = 1;

        $ordenesPorClienteConfig = [
            // CLIENTE 0: María Camila Gómez
            0 => [
                'equipos' => [
                    ['cat' => $catLaptops, 'marca' => 'Dell', 'modelo' => 'Inspiron 15 3520 (Intel i7)', 'serie' => 'DELL-98745632-X', 'ult' => 210, 'prox' => -10, 'obs' => 'Tapa con rayones leves. Incluye cargador original.'],
                    ['cat' => $catTVs, 'marca' => 'Samsung', 'modelo' => 'Crystal UHD 55" 4K', 'serie' => 'SAM-TV-55-AU7000', 'ult' => 60, 'prox' => null, 'obs' => 'Pantalla sin rayones. Con control Smart.'],
                    ['cat' => $catSmartphones, 'marca' => 'Apple', 'modelo' => 'iPhone 13 Pro 128GB', 'serie' => 'IPH-13P-BLUE-99', 'ult' => 15, 'prox' => null, 'obs' => 'Vidrio templado instalado.'],
                ],
                'ordenes' => [
                    [
                        'eq_idx' => 0, 'estado' => 'entregado', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'El computador recalienta excesivamente y se apaga a los 20 minutos de uso.',
                        'diagnostico' => 'Ventilador cooler obstruido por pelusa y pasta térmica totalmente seca y cristalizada.',
                        'procedimiento' => 'Mantenimiento preventivo general, limpieza de disipador, lubricación de ventilador y cambio de pasta térmica Noctua NT-H1.',
                        'repuestos' => 'Pasta térmica Noctua NT-H1 (1.5g)', 'mano_obra' => 85000, 'rep_cost' => 35000,
                        'dias_ant' => 60, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'Equipo con polvo interno y disipador obstruido.', 'https://images.unsplash.com/photo-1597740985671-2a8a3b80502e?auto=format&fit=crop&w=800&q=80'],
                            ['durante', 'Limpieza profunda y pasta termica Noctua aplicada.', 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Equipo encendido testeado a 45C en test de estres.', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 0, 'estado' => 'finalizado', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Cliente solicita aumento de almacenamiento y clonación de sistema porque el disco mecánico está muy lento.',
                        'diagnostico' => 'Disco mecánico de 1TB con sectores lentos. Se actualiza a unidad SSD NVMe M.2 de 1TB.',
                        'procedimiento' => 'Instalación de SSD Kingston NV2 NVMe 1TB, clonación de partición de Windows 11, verificación de arranque veloz.',
                        'repuestos' => 'SSD Kingston NV2 1TB NVMe PCIe 4.0', 'mano_obra' => 60000, 'rep_cost' => 240000,
                        'dias_ant' => 5, 'firmado' => true,
                        'fotos' => [
                            ['falla', 'Prueba CrystalDiskInfo mostrando sectores lentos.', 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'SSD NVMe montado y Windows 11 arrancando veloz.', 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 1, 'estado' => 'en_proceso', 'tec' => $tec2, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'El televisor tiene audio normal pero la pantalla se queda totalmente negra.',
                        'diagnostico' => 'Falla en tiras LED de iluminación trasera (Backlight) por sobretensión en regletas.',
                        'procedimiento' => 'Desensamble del panel LCD 4K, retiro de tiras quemadas y reemplazo por kit completo de tiras LED de aluminio.',
                        'repuestos' => 'Kit completo 6 tiras LED Samsung 55 AU7000', 'mano_obra' => 140000, 'rep_cost' => 120000,
                        'dias_ant' => 1, 'firmado' => false,
                        'fotos' => [
                            ['recibe', 'Recepcion de Smart TV 55 pulgadas en mesa de trabajo.', 'https://images.unsplash.com/photo-1593784991095-a205069470b6?auto=format&fit=crop&w=800&q=80'],
                            ['durante', 'Prueba con probador de LEDs identificando 4 diodos abiertos.', 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 2, 'estado' => 'entregado', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Batería en condición de 74%, se descarga en menos de 4 horas.',
                        'diagnostico' => 'Batería de polímero de litio degradada con ciclos agotados.',
                        'procedimiento' => 'Cambio de batería de alta capacidad con calibración BMS y sellado contra polvo.',
                        'repuestos' => 'Batería iPhone 13 Pro 3095mAh Original Quality', 'mano_obra' => 50000, 'rep_cost' => 160000,
                        'dias_ant' => 15, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'iPhone recibido para sustitución de batería.', 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Condición al 100% y prueba de carga rápida superada.', 'https://images.unsplash.com/photo-1563770660941-20978e870e26?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                ]
            ],

            // CLIENTE 1: Andrés Felipe Castro
            1 => [
                'equipos' => [
                    ['cat' => $catNeveras, 'marca' => 'Whirlpool', 'modelo' => 'No-Frost 420L Inverter Xpert Flow', 'serie' => 'WP-NF-2023-99', 'ult' => 330, 'prox' => 5, 'obs' => 'Puerta con pequeño abollón.'],
                    ['cat' => $catLavadoras, 'marca' => 'Whirlpool', 'modelo' => 'Carga Superior 20Kg Xpert System', 'serie' => 'WP-LAV-20K-88', 'ult' => 90, 'prox' => 90, 'obs' => 'En zona de ropas.'],
                    ['cat' => $catTVs, 'marca' => 'LG', 'modelo' => 'OLED 65" C2 Serie 4K 120Hz', 'serie' => 'LG-OLED-65C2-77', 'ult' => null, 'prox' => null, 'obs' => 'Panel ultra delgado.'],
                ],
                'ordenes' => [
                    [
                        'eq_idx' => 0, 'estado' => 'finalizado', 'tec' => $tec2, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'La nevera enfría abajo pero el congelador acumula escarcha y bloquea el ducto de aire.',
                        'diagnostico' => 'Resistencia de descongelamiento abierta y sensor bimetálico averiado.',
                        'procedimiento' => 'Diagnóstico en sitio, medición de resistencia con multímetro. Instalación de kit de descongelamiento y prueba forzada.',
                        'repuestos' => 'Sensor bimetálico L55 y resistencia tubular 110V', 'mano_obra' => 120000, 'rep_cost' => 95000,
                        'dias_ant' => 4, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'Evaporador congelado con bloque de hielo.', 'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?auto=format&fit=crop&w=800&q=80'],
                            ['falla', 'Medicion de resistencia en circuito abierto.', 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Nevera descongelada y circulando aire frio.', 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 1, 'estado' => 'entregado', 'tec' => $tec2, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'Mantenimiento semestral preventivo y lavado de tinas.',
                        'diagnostico' => 'Tinas con acumulación de sarro y residuos de detergente.',
                        'procedimiento' => 'Desarme de agitador y tina, hidrolavado a presión, desinfección y lubricación de transmisión.',
                        'repuestos' => 'Químico desincrustante + Grasa marina de alta temperatura', 'mano_obra' => 110000, 'rep_cost' => 30000,
                        'dias_ant' => 90, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'Tina con sedimentos acumulados.', 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Tina brillante desinfectada y armada.', 'https://images.unsplash.com/photo-1610557892470-55d9e80c0bce?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 2, 'estado' => 'pendiente', 'tec' => $tec1, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'El televisor no enciende tras tormenta eléctrica, parpadea luz roja 3 veces.',
                        'diagnostico' => null,
                        'procedimiento' => null,
                        'repuestos' => null, 'mano_obra' => 0, 'rep_cost' => 0,
                        'dias_ant' => 0, 'firmado' => false,
                        'fotos' => [
                            ['recibe', 'Visita agendada para diagnostico de fuente OLED.', 'https://images.unsplash.com/photo-1593784991095-a205069470b6?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                ]
            ],

            // CLIENTE 2: Restaurante El Fogón Boyacense
            2 => [
                'equipos' => [
                    ['cat' => $catNeveras, 'marca' => 'Mabe', 'modelo' => 'Congelador Horizontal 500L Comercial', 'serie' => 'MB-CONG-500L-2022', 'ult' => 150, 'prox' => 30, 'obs' => 'En cocina principal.'],
                    ['cat' => $catNeveras, 'marca' => 'Inducol', 'modelo' => 'Vitrina Refrigerada Mostrador 3 Puertas', 'serie' => 'IND-VIT-3P-2021', 'ult' => 45, 'prox' => 135, 'obs' => 'Vitrina de carnes y bebidas.'],
                    ['cat' => $catLaptops, 'marca' => 'HP', 'modelo' => 'ProBook 450 G8 (Servidor Caja)', 'serie' => 'HP-PB450-CAJA1', 'ult' => 180, 'prox' => -5, 'obs' => 'Equipo de facturación continua.'],
                ],
                'ordenes' => [
                    [
                        'eq_idx' => 0, 'estado' => 'entregado', 'tec' => $tec2, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'El congelador no alcanza la temperatura de congelación (-18°C), se mantiene en 5°C.',
                        'diagnostico' => 'Fuga de refrigerante en unión de tubería de baja y filtro secador saturado.',
                        'procedimiento' => 'Presurización con nitrógeno, corrección de fuga con soldadura de plata al 15%, vacío a 250 micrones y carga de gas R134a por peso.',
                        'repuestos' => 'Filtro secador 1/4 + Carga de gas R134a 280g + Varilla de plata', 'mano_obra' => 180000, 'rep_cost' => 145000,
                        'dias_ant' => 45, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'Medicion de presion inicial en 0 PSI.', 'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?auto=format&fit=crop&w=800&q=80'],
                            ['durante', 'Soldadura de union con soplete y nitrogeno.', 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Congelador alcanzando -20C en prueba continua.', 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 1, 'estado' => 'finalizado', 'tec' => $tec2, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'Ventilador forzador del condensador suena ruidoso y vibra.',
                        'diagnostico' => 'Motor ventilador de 16W con bujes desgastados y aspas desbalanceadas.',
                        'procedimiento' => 'Cambio de micromotor forzador elco 16W y aspa de aluminio de 230mm.',
                        'repuestos' => 'Micromotor Elco 16W 110V + Aspa aluminio 230mm', 'mano_obra' => 85000, 'rep_cost' => 95000,
                        'dias_ant' => 10, 'firmado' => true,
                        'fotos' => [
                            ['falla', 'Motor anterior con juego axial pronunciado.', 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Motor nuevo instalado girando silenciosamente.', 'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 2, 'estado' => 'en_proceso', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Mantenimiento térmico de computador de facturación y respaldo de base de datos SQL.',
                        'diagnostico' => 'Pasta térmica reseca y disco NVMe al 90% de capacidad.',
                        'procedimiento' => 'Mantenimiento preventivo, limpieza ultrasónica de ventilador y expansión de almacenamiento SSD.',
                        'repuestos' => 'SSD Crucial P3 1TB NVMe + Pasta térmica Arctic MX-4', 'mano_obra' => 90000, 'rep_cost' => 250000,
                        'dias_ant' => 1, 'firmado' => false,
                        'fotos' => [
                            ['recibe', 'Laptop de punto de venta ingresada al taller.', 'https://images.unsplash.com/photo-1597740985671-2a8a3b80502e?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                ]
            ],

            // CLIENTE 3: Valentina Restrepo Morales
            3 => [
                'equipos' => [
                    ['cat' => $catLavadoras, 'marca' => 'LG', 'modelo' => 'Smart Inverter TurboDrum 19Kg', 'serie' => 'LG-LAV-19KG-WT19', 'ult' => 240, 'prox' => -5, 'obs' => 'Display intacto, mangueras originales.'],
                    ['cat' => $catLaptops, 'marca' => 'Apple', 'modelo' => 'MacBook Pro 16" M2 Pro (32GB)', 'serie' => 'MBP16-M2PRO-2023', 'ult' => 120, 'prox' => 60, 'obs' => 'Equipo de trabajo de diseño.'],
                    ['cat' => $catSmartphones, 'marca' => 'Apple', 'modelo' => 'iPad Pro 12.9" M1 con Magic Keyboard', 'serie' => 'IPAD-PRO-129-M1', 'ult' => null, 'prox' => null, 'obs' => 'Incluye Apple Pencil 2.'],
                ],
                'ordenes' => [
                    [
                        'eq_idx' => 0, 'estado' => 'entregado', 'tec' => $tec1, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'Al centrifugar produce un ruido metálico fuerte y bota agua por debajo.',
                        'diagnostico' => 'Retén de tina roto y rodamiento desgastado con filtración.',
                        'procedimiento' => 'Desarme de tina, cambio de rodamientos SKF y retén de alta presión, limpieza de tina y calibración de suspensión.',
                        'repuestos' => 'Kit rodamientos sellados SKF + Retén original LG', 'mano_obra' => 150000, 'rep_cost' => 85000,
                        'dias_ant' => 30, 'firmado' => true,
                        'fotos' => [
                            ['falla', 'Reten roto con corrosion en rodamiento.', 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Lavadora armada y centrifugando sin ruido.', 'https://images.unsplash.com/photo-1610557892470-55d9e80c0bce?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 1, 'estado' => 'finalizado', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Teclas de espacio y enter se quedan pegadas por derrame de bebida endulzada.',
                        'diagnostico' => 'Líquido seco bajo membrana de teclado sin daño en la placa madre.',
                        'procedimiento' => 'Limpieza ultrasónica de topcase, lubricación de estabilizadores de teclas y prueba de sensor biométrico.',
                        'repuestos' => 'Mecanismos de tijera Apple + Alcohol isopropílico de alta pureza', 'mano_obra' => 130000, 'rep_cost' => 45000,
                        'dias_ant' => 8, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'MacBook Pro ingresada para limpieza de teclado.', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Teclado 100% fluido y testeado con software de pulsaciones.', 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 2, 'estado' => 'en_proceso', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'iPad Pro no carga, puerto USB-C Thunderbolt no detecta accesorios ni cargador.',
                        'diagnostico' => 'Pines de carga quemados y flex USB-C flexionado internamente.',
                        'procedimiento' => 'Despegue de pantalla Liquid Retina XDR, reemplazo de módulo flex USB-C.',
                        'repuestos' => 'Flex USB-C iPad Pro 12.9 Original', 'mano_obra' => 160000, 'rep_cost' => 180000,
                        'dias_ant' => 2, 'firmado' => false,
                        'fotos' => [
                            ['recibe', 'iPad Pro 12.9 en banco de trabajo.', 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=800&q=80'],
                            ['falla', 'Puerto tipo C con pines doblados al microscopio.', 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                ]
            ],

            // CLIENTE 4: Inversiones & Consultoría Alfa S.A.S.
            4 => [
                'equipos' => [
                    ['cat' => $catLaptops, 'marca' => 'Lenovo', 'modelo' => 'ThinkPad T14 Gen 3 (Core i7 / 32GB)', 'serie' => 'TP-T14-GEN3-01', 'ult' => 170, 'prox' => 10, 'obs' => 'Laptop de presidencia.'],
                    ['cat' => $catLaptops, 'marca' => 'Dell', 'modelo' => 'Latitude 5430 (Intel i5)', 'serie' => 'DELL-LAT-5430-88', 'ult' => 40, 'prox' => 140, 'obs' => 'Equipo de analista financiero.'],
                    ['cat' => $catTVs, 'marca' => 'Sony', 'modelo' => 'Bravia 75" 4K HDR Sala de Juntas', 'serie' => 'SONY-75-JUNTAS', 'ult' => null, 'prox' => null, 'obs' => 'En sala principal de juntas.'],
                ],
                'ordenes' => [
                    [
                        'eq_idx' => 0, 'estado' => 'entregado', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Pantalla se apaga aleatoriamente al mover la bisagra de apertura.',
                        'diagnostico' => 'Cable flex de video eDP con fisura en el doblez de la bisagra izquierda.',
                        'procedimiento' => 'Cambio de cable flex de video 40 pines FHD IPS y ajuste de tensión de bisagras.',
                        'repuestos' => 'Cable flex eDP Lenovo ThinkPad T14 original', 'mano_obra' => 95000, 'rep_cost' => 85000,
                        'dias_ant' => 40, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'ThinkPad T14 ingresado para revision de flex.', 'https://images.unsplash.com/photo-1597740985671-2a8a3b80502e?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Pantalla testeada a 180 grados sin parpadeos.', 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 1, 'estado' => 'entregado', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Mantenimiento preventivo anual y actualización de BIOS.',
                        'diagnostico' => 'Disipador térmico con suciedad moderada, firmware desactualizado.',
                        'procedimiento' => 'Mantenimiento físico preventivo, aplicación de pasta térmica térmica y actualización a última versión de BIOS.',
                        'repuestos' => 'Pasta térmica Noctua NT-H1', 'mano_obra' => 70000, 'rep_cost' => 20000,
                        'dias_ant' => 38, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'Equipo antes del mantenimiento.', 'https://images.unsplash.com/photo-1597740985671-2a8a3b80502e?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Pruebas de diagnostico Dell ePSA 100% aprobadas.', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 2, 'estado' => 'en_proceso', 'tec' => $tec2, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'Smart TV de sala de juntas pierde señal HDMI con Apple TV y Teams Rooms.',
                        'diagnostico' => 'Revisión de puertos HDMI 2.1 y switch conmutador de video 4K.',
                        'procedimiento' => 'Diagnóstico en sitio de puertos y cables ópticos HDMI.',
                        'repuestos' => null, 'mano_obra' => 110000, 'rep_cost' => 0,
                        'dias_ant' => 1, 'firmado' => false,
                        'fotos' => [
                            ['recibe', 'Revision tecnica en sala de juntas.', 'https://images.unsplash.com/photo-1593784991095-a205069470b6?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                ]
            ],

            // CLIENTE 5: Dr. Santiago Morales Pinzón
            5 => [
                'equipos' => [
                    ['cat' => $catLaptops, 'marca' => 'Asus', 'modelo' => 'ROG Zephyrus G14 (Ryzen 9 / RTX 4060)', 'serie' => 'ASUS-ROG-G14-2023', 'ult' => 180, 'prox' => 0, 'obs' => 'Laptop personal y de análisis radiológico.'],
                    ['cat' => $catNeveras, 'marca' => 'LG', 'modelo' => 'InstaView Door-in-Door 601L', 'serie' => 'LG-INSTA-601L-99', 'ult' => 300, 'prox' => 65, 'obs' => 'Nevera del hogar.'],
                    ['cat' => $catSmartphones, 'marca' => 'Samsung', 'modelo' => 'Galaxy S23 Ultra 512GB', 'serie' => 'S23U-512GB-BLACK', 'ult' => null, 'prox' => null, 'obs' => 'Con S-Pen original.'],
                ],
                'ordenes' => [
                    [
                        'eq_idx' => 0, 'estado' => 'finalizado', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Mantenimiento preventivo especializado para sistema de refrigeración de metal líquido y ventiladores duales.',
                        'diagnostico' => 'Disipadores de vapor con pelusa, metal líquido desplazado en el CPU Ryzen 9.',
                        'procedimiento' => 'Limpieza de cámara de vapor, sellado de barrera de níquel y aplicación de Thermal Grizzly Conductonaut.',
                        'repuestos' => 'Metal líquido Thermal Grizzly + Pads Gelid Ultimate', 'mano_obra' => 130000, 'rep_cost' => 65000,
                        'dias_ant' => 6, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'ROG Zephyrus ingresado al taller.', 'https://images.unsplash.com/photo-1597740985671-2a8a3b80502e?auto=format&fit=crop&w=800&q=80'],
                            ['durante', 'Aplicacion de metal liquido con micrometricidad.', 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Equipo rindiendo a 78C maximo en benchmark 3DMark.', 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 1, 'estado' => 'entregado', 'tec' => $tec2, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'Fabricador de hielo automático (Ice Maker) no expulsa los cubos de hielo.',
                        'diagnostico' => 'Motor reductor del expulsor trabado por desgaste de piñones plásticos.',
                        'procedimiento' => 'Cambio de ensamble completo Ice Maker original LG y purga de línea de agua.',
                        'repuestos' => 'Módulo Ice Maker LG Inverter original', 'mano_obra' => 110000, 'rep_cost' => 195000,
                        'dias_ant' => 60, 'firmado' => true,
                        'fotos' => [
                            ['falla', 'Ice maker anterior con piñon fracturado.', 'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Produccion de hielo en cubos y triturado restablecida.', 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 2, 'estado' => 'pendiente', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Cámara periscópica 10X vibra y hace un zumbido al enfocar de cerca.',
                        'diagnostico' => null,
                        'procedimiento' => null,
                        'repuestos' => null, 'mano_obra' => 0, 'rep_cost' => 0,
                        'dias_ant' => 0, 'firmado' => false,
                        'fotos' => [
                            ['recibe', 'S23 Ultra recibido para evaluacion de modulo OIS.', 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                ]
            ],

            // CLIENTE 6: Hotel Boutique Casa Medina Colonial
            6 => [
                'equipos' => [
                    ['cat' => $catLavadoras, 'marca' => 'Speed Queen', 'modelo' => 'Lavadora Industrial 25Kg Carga Frontal', 'serie' => 'SQ-IND-25KG-01', 'ult' => 180, 'prox' => 0, 'obs' => 'Lavandería del hotel.'],
                    ['cat' => $catTVs, 'marca' => 'Samsung', 'modelo' => 'The Frame 55" QLED 4K', 'serie' => 'SAM-FRAME-55-SUITE1', 'ult' => 90, 'prox' => null, 'obs' => 'Suite Nupcial.'],
                    ['cat' => $catTVs, 'marca' => 'Samsung', 'modelo' => 'The Frame 55" QLED 4K', 'serie' => 'SAM-FRAME-55-SUITE2', 'ult' => 120, 'prox' => null, 'obs' => 'Suite Presidencial.'],
                ],
                'ordenes' => [
                    [
                        'eq_idx' => 0, 'estado' => 'finalizado', 'tec' => $tec2, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'Válvulas solenoides de entrada de agua caliente presentan goteo continuo.',
                        'diagnostico' => 'Membrana de electroválvula cuarteada por temperatura.',
                        'procedimiento' => 'Cambio de cuerpo de electroválvulas duales y ajuste de abrazaderas de alta presión.',
                        'repuestos' => 'Juego electroválvulas duales 110V alta temperatura', 'mano_obra' => 140000, 'rep_cost' => 115000,
                        'dias_ant' => 3, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'Revision tecnica en cuarto de lavanderia del hotel.', 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Valvulas sellando al 100% en ciclo pesado.', 'https://images.unsplash.com/photo-1610557892470-55d9e80c0bce?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 1, 'estado' => 'entregado', 'tec' => $tec1, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'Caja One Connect de Samsung no transmite video al panel mediante cable de fibra invisible.',
                        'diagnostico' => 'Cable One Connect Invisible doblado en el canal de pared.',
                        'procedimiento' => 'Sustitución de cable óptico One Connect original de 5 metros.',
                        'repuestos' => 'Cable One Invisible Connection Samsung 5m', 'mano_obra' => 90000, 'rep_cost' => 220000,
                        'dias_ant' => 45, 'firmado' => true,
                        'fotos' => [
                            ['falla', 'Cable con quiebre optico.', 'https://images.unsplash.com/photo-1593784991095-a205069470b6?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Modo Arte y 4K funcionando perfectamente.', 'https://images.unsplash.com/photo-1593784991095-a205069470b6?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 2, 'estado' => 'entregado', 'tec' => $tec1, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'Configuración de red hotelera y sincronización con canal de bienvenida.',
                        'diagnostico' => 'Ajuste de parámetros IP y actualización de firmware Tizen OS.',
                        'procedimiento' => 'Configuración de modo hospitality y bloqueo de menús de configuración.',
                        'repuestos' => null, 'mano_obra' => 80000, 'rep_cost' => 0,
                        'dias_ant' => 60, 'firmado' => true,
                        'fotos' => [
                            ['devuelve', 'Configuracion completada exitosamente.', 'https://images.unsplash.com/photo-1593784991095-a205069470b6?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                ]
            ],

            // CLIENTE 7: Laura Ximena Cárdenas
            7 => [
                'equipos' => [
                    ['cat' => $catConsolas, 'marca' => 'Sony', 'modelo' => 'PlayStation 5 Disc Edition (1TB)', 'serie' => 'PS5-DISC-CFI1115-09', 'ult' => 190, 'prox' => -10, 'obs' => 'Consola con 2 mandos DualSense.'],
                    ['cat' => $catLaptops, 'marca' => 'MSI', 'modelo' => 'Creator Z16 (Intel i9 / RTX 3080)', 'serie' => 'MSI-CREATOR-Z16-99', 'ult' => 100, 'prox' => 80, 'obs' => 'Equipo de renderizado arquitectónico.'],
                    ['cat' => $catSmartphones, 'marca' => 'Apple', 'modelo' => 'iPhone 15 Pro Max 256GB Titanio', 'serie' => 'IPH15PM-TIT-2023', 'ult' => null, 'prox' => null, 'obs' => 'En funda protectora Spigen.'],
                ],
                'ordenes' => [
                    [
                        'eq_idx' => 0, 'estado' => 'entregado', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Puerto HDMI roto por tirón accidental del cable, no da señal al monitor.',
                        'diagnostico' => 'Conector HDMI 2.1 con pistas desprendidas de la placa base.',
                        'procedimiento' => 'Microsoldadura de nuevo puerto HDMI 2.1 de alta resistencia, reconstrucción de 2 pistas con hilo de cobre esmaltado y sellado UV.',
                        'repuestos' => 'Puerto HDMI 2.1 PS5 OEM + Máscara UV curable', 'mano_obra' => 160000, 'rep_cost' => 60000,
                        'dias_ant' => 25, 'firmado' => true,
                        'fotos' => [
                            ['falla', 'Puerto HDMI desprendido bajo microscopio.', 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80'],
                            ['durante', 'Microsoldadura y refuerzo estructural con resina.', 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'PS5 emitiendo señal 4K a 120Hz con HDR.', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 1, 'estado' => 'finalizado', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Mantenimiento de refrigeración para renderizado continuo y cambio de ventilador secundario.',
                        'diagnostico' => 'Ventilador de GPU con zumbido y lubricante seco.',
                        'procedimiento' => 'Sustitución de ventilador de GPU, limpieza de disipadores y aplicación de pasta térmica de alto rendimiento.',
                        'repuestos' => 'Ventilador MSI Creator Z16 Original + Pasta Thermal Grizzly Kryonaut', 'mano_obra' => 110000, 'rep_cost' => 110000,
                        'dias_ant' => 5, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'MSI Creator ingresada para mantenimiento termico.', 'https://images.unsplash.com/photo-1597740985671-2a8a3b80502e?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Rendimiento maximo en render de V-Ray a temperaturas estables.', 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 2, 'estado' => 'en_proceso', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Tapa trasera de cristal de titanio estrellada tras caída.',
                        'diagnostico' => 'Vidrio posterior roto sin daño en bobina de carga MagSafe.',
                        'procedimiento' => 'Remoción láser de vidrio trasero y montaje de nuevo cristal original con prensado en frío.',
                        'repuestos' => 'Tapa trasera cristal iPhone 15 Pro Max Titanio Natural', 'mano_obra' => 120000, 'rep_cost' => 280000,
                        'dias_ant' => 1, 'firmado' => false,
                        'fotos' => [
                            ['recibe', 'iPhone con cristal posterior roto.', 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                ]
            ],

            // CLIENTE 8: Ferretería & Distribuciones El Constructor
            8 => [
                'equipos' => [
                    ['cat' => $catLaptops, 'marca' => 'Lenovo', 'modelo' => 'ThinkCentre M70q Tiny (Core i5 / 16GB)', 'serie' => 'TC-M70Q-CAJA1', 'ult' => 200, 'prox' => -20, 'obs' => 'Computador miniatura de caja principal.'],
                    ['cat' => $catLaptops, 'marca' => 'Lenovo', 'modelo' => 'ThinkCentre M70q Tiny (Core i5 / 16GB)', 'serie' => 'TC-M70Q-CAJA2', 'ult' => 150, 'prox' => 30, 'obs' => 'Computador miniatura de caja secundaria.'],
                    ['cat' => $catNeveras, 'marca' => 'Electrolux', 'modelo' => 'Dispensador de Agua Fría y Caliente', 'serie' => 'ELX-DISP-AGUA-2022', 'ult' => 90, 'prox' => 90, 'obs' => 'Dispensador para clientes y personal.'],
                ],
                'ordenes' => [
                    [
                        'eq_idx' => 0, 'estado' => 'entregado', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Computador no da video, emite 3 pitidos cortos y 1 largo al encender.',
                        'diagnostico' => 'Módulo de memoria RAM DDR4 sulfatado por polvo ambiental de la ferretería.',
                        'procedimiento' => 'Limpieza con solvente dieléctrico, baño ultrasónico de ranuras SODIMM y cambio de módulo RAM.',
                        'repuestos' => 'Memoria RAM Kingston 16GB DDR4 3200MHz', 'mano_obra' => 70000, 'rep_cost' => 135000,
                        'dias_ant' => 50, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'ThinkCentre Tiny con acumulacion de polvillo.', 'https://images.unsplash.com/photo-1597740985671-2a8a3b80502e?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Equipo limpio con prueba de memoria MemTest86 100% aprobada.', 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 1, 'estado' => 'finalizado', 'tec' => $tec1, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'Mantenimiento preventivo general y soplado con compresor en sitio.',
                        'diagnostico' => 'Polvo y sedimentos en toberas de ventilación.',
                        'procedimiento' => 'Mantenimiento preventivo en sitio y optimización de inicio de Windows.',
                        'repuestos' => null, 'mano_obra' => 60000, 'rep_cost' => 0,
                        'dias_ant' => 12, 'firmado' => true,
                        'fotos' => [
                            ['devuelve', 'Mantenimiento terminado en punto de venta.', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 2, 'estado' => 'entregado', 'tec' => $tec2, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'El dispensador no calienta agua, sale a temperatura ambiente.',
                        'diagnostico' => 'Termostato bimetálico de seguridad de caldera de agua caliente disparado.',
                        'procedimiento' => 'Sustitución de termostato de rearme automático 95°C y descalcificación de caldera con ácido cítrico.',
                        'repuestos' => 'Termostato cerámico 95°C 10A + Ácido descalcificante', 'mano_obra' => 75000, 'rep_cost' => 35000,
                        'dias_ant' => 30, 'firmado' => true,
                        'fotos' => [
                            ['devuelve', 'Dispensador suministrando agua a 92C y 6C.', 'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                ]
            ],

            // CLIENTE 9: Juan Pablo Echeverri
            9 => [
                'equipos' => [
                    ['cat' => $catConsolas, 'marca' => 'Nintendo', 'modelo' => 'Switch OLED Splatoon 3 Special Edition', 'serie' => 'NSW-OLED-SPL3-88', 'ult' => 80, 'prox' => 100, 'obs' => 'Consola en estuche rígido con Joy-Cons.'],
                    ['cat' => $catSmartphones, 'marca' => 'Samsung', 'modelo' => 'Galaxy Z Fold 5 512GB', 'serie' => 'ZFOLD5-512G-ICE', 'ult' => 30, 'prox' => null, 'obs' => 'Teléfono plegable principal.'],
                    ['cat' => $catLaptops, 'marca' => 'Custom PC', 'modelo' => 'Workstation Streaming (Ryzen 9 7950X / RTX 4080)', 'serie' => 'PC-STREAM-JP-2023', 'ult' => 160, 'prox' => 20, 'obs' => 'Torre gabinete Lian Li con refrigeración líquida.'],
                ],
                'ordenes' => [
                    [
                        'eq_idx' => 0, 'estado' => 'entregado', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Joy-Cons presentan drift severo en ambas palancas análogas.',
                        'diagnostico' => 'Potenciómetros análogos con desgaste de grafito por uso intensivo.',
                        'procedimiento' => 'Instalación de palancas magnéticas con tecnología Hall Effect (antidrift permanente) y calibración en sistema.',
                        'repuestos' => 'Par de palancas Hall Effect GuliKit para Nintendo Switch', 'mano_obra' => 60000, 'rep_cost' => 85000,
                        'dias_ant' => 45, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'Joy-Cons recibidos para actualizacion de sensores.', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=800&q=80'],
                            ['durante', 'Montaje de sensores magneticos Hall Effect.', 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Prueba de calibracion en ceros sin desviacion.', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 1, 'estado' => 'finalizado', 'tec' => $tec1, 'ubic' => 'ingresado_al_taller',
                        'problema' => 'Protector de pantalla plegable original de fábrica se despegó en el centro del pliegue.',
                        'diagnostico' => 'Película protectora hidrogel despegada con burbujas de aire.',
                        'procedimiento' => 'Retiro con calor controlado y aplicación de lámina de poliuretano UV termocurada al vacío.',
                        'repuestos' => 'Lámina UV Premium para Galaxy Z Fold 5', 'mano_obra' => 50000, 'rep_cost' => 70000,
                        'dias_ant' => 10, 'firmado' => true,
                        'fotos' => [
                            ['recibe', 'Fold 5 ingresado para instalacion de proteccion.', 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=800&q=80'],
                            ['devuelve', 'Lamina perfectamente alineada sin burbujas.', 'https://images.unsplash.com/photo-1563770660941-20978e870e26?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                    [
                        'eq_idx' => 2, 'estado' => 'en_proceso', 'tec' => $tec1, 'ubic' => 'servicio_en_domicilio',
                        'problema' => 'Líquido refrigerante de la refrigeración líquida custom se tornó turbio y la bomba emite burbujeo.',
                        'diagnostico' => 'Líquido refrigerante con degradación de biocida y acumulación de microburbujas en el radiador superior de 360mm.',
                        'procedimiento' => 'Drenado completo del circuito, lavado químico con EK-CryoFuel Loop Cleaner, recarga de refrigerante transparente UV.',
                        'repuestos' => 'Refrigerante EK-CryoFuel Clear 1000ml + Limpiador Loop Cleaner', 'mano_obra' => 150000, 'rep_cost' => 165000,
                        'dias_ant' => 2, 'firmado' => false,
                        'fotos' => [
                            ['recibe', 'Workstation Lian Li en proceso de drenado.', 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=800&q=80'],
                        ]
                    ],
                ]
            ],
        ];

        // Creación masiva iterativa
        foreach ($ordenesPorClienteConfig as $cIdx => $config) {
            $clienteObj = $clientes[$cIdx];
            $equiposCreados = [];

            foreach ($config['equipos'] as $eqData) {
                $ultimo = $eqData['ult'] !== null ? now()->subDays($eqData['ult'])->toDateString() : null;
                $proximo = $eqData['prox'] !== null ? now()->addDays($eqData['prox'])->toDateString() : null;

                $equipo = Equipo::create([
                    'taller_id' => $taller1->id,
                    'cliente_id' => $clienteObj->id,
                    'categoria_id' => $eqData['cat']->id,
                    'marca' => $eqData['marca'],
                    'modelo' => $eqData['modelo'],
                    'numero_serie' => $eqData['serie'],
                    'observaciones_fisicas' => $eqData['obs'],
                    'fecha_ultimo_servicio' => $ultimo,
                    'fecha_proximo_mantenimiento' => $proximo,
                ]);
                $equiposCreados[] = $equipo;
            }

            foreach ($config['ordenes'] as $oData) {
                $eqObj = $equiposCreados[$oData['eq_idx']];
                $codigo = sprintf('OT-%05d', $contadorOT);
                $dias = $oData['dias_ant'];

                $fechaIngreso = now()->subDays($dias)->subHours(rand(1, 8));
                $fechaPromesa = now()->subDays(max(0, $dias - 2));
                $fechaFinal = in_array($oData['estado'], ['finalizado', 'entregado']) ? now()->subDays(max(0, $dias - 1)) : null;

                $orden = OrdenTrabajo::create([
                    'taller_id' => $taller1->id,
                    'codigo_orden' => $codigo,
                    'cliente_id' => $clienteObj->id,
                    'equipo_id' => $eqObj->id,
                    'tecnico_asignado_id' => $oData['tec']->id,
                    'tipo_ubicacion' => $oData['ubic'],
                    'estado' => $oData['estado'],
                    'problema_reportado' => $oData['problema'],
                    'diagnostico' => $oData['diagnostico'],
                    'procedimiento_realizado' => $oData['procedimiento'],
                    'repuestos_usados' => $oData['repuestos'],
                    'costo_mano_obra' => $oData['mano_obra'],
                    'costo_repuestos' => $oData['rep_cost'],
                    'costo_total' => $oData['mano_obra'] + $oData['rep_cost'],
                    'nombre_firmante' => $oData['firmado'] ? $clienteObj->nombre_completo : null,
                    'fecha_firma' => $oData['firmado'] ? $fechaFinal : null,
                    'token_publico_pdf' => (string) Str::uuid(),
                    'fecha_ingreso' => $fechaIngreso,
                    'fecha_promesa' => $fechaPromesa,
                    'fecha_finalizacion' => $fechaFinal,
                ]);

                if ($oData['firmado']) {
                    $rutaFirma = $this->generarFirmaCliente($taller1->id, $clienteObj->id, $orden->id, $clienteObj->nombre_completo);
                    $orden->update(['ruta_firma_cliente' => $rutaFirma]);
                }

                if (!empty($oData['fotos'])) {
                    foreach ($oData['fotos'] as $f) {
                        $this->generarImagenEvidencia(
                            $taller1->id,
                            $clienteObj->id,
                            $orden->id,
                            $f[0],
                            $f[1],
                            $f[2] ?? null
                        );
                    }
                }

                $contadorOT++;
            }
        }

        // =========================================================================
        // TALLER 2: Climatización & Frío del Norte (Barranquilla)
        // =========================================================================
        $taller2 = Taller::create([
            'plan_licencia_id' => $planMensual?->id,
            'nombre_comercial' => 'Climatización & Frío del Norte',
            'identificacion_fiscal' => 'NIT 800.951.357-1',
            'telefono' => '573017774411',
            'email' => 'admin@climatizacionnorte.com',
            'direccion' => 'Calle 72 # 54-18',
            'ciudad' => 'Barranquilla',
            'texto_garantia' => 'Garantía de 6 meses en recarga de gas y cambio de compresores. Mantenimiento preventivo garantizado por 30 días.',
            'prefijo_orden' => 'CLI',
            'estado_suscripcion' => 'activo',
            'fecha_vencimiento_suscripcion' => now()->addMonths(6),
            'whatsapp_auto_notify_enabled' => true,
        ]);

        $logoT2 = $this->generarLogoTaller($taller2->id, 'Climatizacion', 'Aires Inverter • Cuartos Frios • Refrigeracion', [
            'badge_r' => 6, 'badge_g' => 78, 'badge_b' => 59,
            'accent_r' => 16, 'accent_g' => 185, 'accent_b' => 129,
        ]);
        $taller2->update(['logo_ruta' => $logoT2]);

        Usuario::create([
            'taller_id' => $taller2->id,
            'nombre' => 'Eduardo',
            'apellido' => 'Pacheco',
            'email' => 'admin@climatizacionnorte.com',
            'telefono' => '573017774411',
            'password' => Hash::make('password123'),
            'rol' => 'administrador',
            'esta_activo' => true,
        ]);

        $tecT2 = Usuario::create([
            'taller_id' => $taller2->id,
            'nombre' => 'Guillermo',
            'apellido' => 'Vargas',
            'email' => 'guillermo@climatizacionnorte.com',
            'telefono' => '573008889900',
            'password' => Hash::make('password123'),
            'rol' => 'tecnico',
            'esta_activo' => true,
        ]);

        $catAires = Categoria::create([
            'taller_id' => $taller2->id,
            'nombre' => 'Aires Acondicionados Mini-Split & Inverter',
            'descripcion' => 'Mantenimiento hidrolavado profundo, desinfección de serpentines y recarga de R410A.',
            'requiere_mantenimiento_preventivo' => true,
            'intervalo_mantenimiento_dias' => 90,
        ]);

        $cli2_1 = Cliente::create([
            'taller_id' => $taller2->id,
            'nombre_completo' => 'Hotel Dann Carlton Barranquilla',
            'identificacion' => 'NIT 890.100.200-5',
            'telefono' => '573005557788',
            'email' => 'mantenimiento@dannbarranquilla.com',
            'direccion' => 'Calle 98 # 52B-10',
            'barrio' => 'Altos de Prado',
            'ciudad' => 'Barranquilla',
            'notas_adicionales' => 'Contrato de mantenimiento trimestral.',
            'token_portal' => bin2hex(random_bytes(24)),
        ]);

        $eq2_1 = Equipo::create([
            'taller_id' => $taller2->id,
            'cliente_id' => $cli2_1->id,
            'categoria_id' => $catAires->id,
            'marca' => 'Carrier',
            'modelo' => 'Inverter 24.000 BTU XPower Gold',
            'numero_serie' => 'CAR-24K-INV-2023',
            'fecha_ultimo_servicio' => now()->subMonths(3)->toDateString(),
            'fecha_proximo_mantenimiento' => now()->subDays(2)->toDateString(),
        ]);

        $ot2_1 = OrdenTrabajo::create([
            'taller_id' => $taller2->id,
            'codigo_orden' => 'CLI-00001',
            'cliente_id' => $cli2_1->id,
            'equipo_id' => $eq2_1->id,
            'tecnico_asignado_id' => $tecT2->id,
            'tipo_ubicacion' => 'servicio_en_domicilio',
            'estado' => 'finalizado',
            'problema_reportado' => 'Mantenimiento preventivo trimestral y desinfección de turbina en suite presidencial.',
            'diagnostico' => 'Turbina con acumulación de hongo y polvo. Presión de gas en 115 PSI (adecuada).',
            'procedimiento_realizado' => 'Lavado con hidrolavadora a presión, aplicación de desengrasante dieléctrico y bactericida, limpieza de bandeja de drenaje.',
            'repuestos_usados' => 'Químico limpiador de serpentín + Pastilla bactericida de drenaje',
            'costo_mano_obra' => 90000,
            'costo_repuestos' => 25000,
            'costo_total' => 115000,
            'nombre_firmante' => 'Jefe de Mantenimiento Dann',
            'fecha_firma' => now()->subDays(1),
            'token_publico_pdf' => (string) Str::uuid(),
            'fecha_ingreso' => now()->subDays(2),
            'fecha_promesa' => now()->subDays(1),
            'fecha_finalizacion' => now()->subDays(1),
        ]);

        $rutaFirmaT2 = $this->generarFirmaCliente($taller2->id, $cli2_1->id, $ot2_1->id, 'Jefe Mantenimiento Dann');
        $ot2_1->update(['ruta_firma_cliente' => $rutaFirmaT2]);

        $this->generarImagenEvidencia($taller2->id, $cli2_1->id, $ot2_1->id, 'como_se_recibe', 'Turbina con polvo antes del hidrolavado.', 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller2->id, $cli2_1->id, $ot2_1->id, 'durante_reparacion', 'Hidrolavado a presion con funda impermeable.', 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller2->id, $cli2_1->id, $ot2_1->id, 'como_se_devuelve', 'Unidad limpia rindiendo a 16C en habitacion.', 'https://images.unsplash.com/photo-1590756254933-2873d72a83b6?auto=format&fit=crop&w=800&q=80');

        // =========================================================================
        // TALLER 3: MacroFix Móviles & Gaming Pro (Medellín)
        // =========================================================================
        $taller3 = Taller::create([
            'plan_licencia_id' => $planMensual?->id,
            'nombre_comercial' => 'MacroFix Móviles & Gaming Pro',
            'identificacion_fiscal' => 'NIT 71.852.963-8',
            'telefono' => '573046669988',
            'email' => 'admin@macrofix.com',
            'direccion' => 'Carrera 43A # 1Sur-220 C.C. Santafé Loc 315',
            'ciudad' => 'Medellín',
            'texto_garantia' => 'Garantía de 90 días en cambio de display y batería original. 30 días en microsoldadura.',
            'prefijo_orden' => 'MF',
            'estado_suscripcion' => 'activo',
            'fecha_vencimiento_suscripcion' => now()->addMonths(8),
            'whatsapp_auto_notify_enabled' => true,
        ]);

        $logoT3 = $this->generarLogoTaller($taller3->id, 'MacroFix', 'iPhones • Samsung • PS5 • Nintendo Switch', [
            'badge_r' => 109, 'badge_g' => 40, 'badge_b' => 217,
            'accent_r' => 168, 'accent_g' => 85, 'accent_b' => 247,
        ]);
        $taller3->update(['logo_ruta' => $logoT3]);

        Usuario::create([
            'taller_id' => $taller3->id,
            'nombre' => 'Sebastián',
            'apellido' => 'Zapata',
            'email' => 'admin@macrofix.com',
            'telefono' => '573046669988',
            'password' => Hash::make('password123'),
            'rol' => 'administrador',
            'esta_activo' => true,
        ]);

        $tecT3 = Usuario::create([
            'taller_id' => $taller3->id,
            'nombre' => 'Alejandro',
            'apellido' => 'Montoya',
            'email' => 'alejandro@macrofix.com',
            'telefono' => '573195551122',
            'password' => Hash::make('password123'),
            'rol' => 'tecnico',
            'esta_activo' => true,
        ]);

        $cli3_1 = Cliente::create([
            'taller_id' => $taller3->id,
            'nombre_completo' => 'Juan Esteban Urrego',
            'identificacion' => 'CC 1037589632',
            'telefono' => '573108883344',
            'email' => 'juanes.urrego@gmail.com',
            'direccion' => 'Calle 10 # 36-14',
            'barrio' => 'El Poblado',
            'ciudad' => 'Medellín',
            'token_portal' => bin2hex(random_bytes(24)),
        ]);

        $catGamingMed = Categoria::create([
            'taller_id' => $taller3->id,
            'nombre' => 'Smartphones & Tablets Premium',
            'descripcion' => 'Cambio de pantalla, batería y componentes de microelectrónica.',
            'requiere_mantenimiento_preventivo' => false,
        ]);

        $eq3_1 = Equipo::create([
            'taller_id' => $taller3->id,
            'cliente_id' => $cli3_1->id,
            'categoria_id' => $catGamingMed->id,
            'marca' => 'Apple',
            'modelo' => 'iPhone 14 Pro Max (256GB)',
            'numero_serie' => 'F2LZX890N72M',
            'observaciones_fisicas' => 'Vidrio frontal estrellado. FaceID operativo.',
        ]);

        $ot3_1 = OrdenTrabajo::create([
            'taller_id' => $taller3->id,
            'codigo_orden' => 'MF-00001',
            'cliente_id' => $cli3_1->id,
            'equipo_id' => $eq3_1->id,
            'tecnico_asignado_id' => $tecT3->id,
            'tipo_ubicacion' => 'ingresado_al_taller',
            'estado' => 'finalizado',
            'problema_reportado' => 'Pantalla rota tras caída, táctil funciona a medias.',
            'diagnostico' => 'Display OLED roto, digitalizador con fallas en zona táctil.',
            'procedimiento_realizado' => 'Reemplazo de pantalla completa OLED Original Service Pack y sellado contra polvo.',
            'repuestos_usados' => 'Pantalla iPhone 14 Pro Max OLED OEM + Adhesivo de sellado 3M',
            'costo_mano_obra' => 120000,
            'costo_repuestos' => 780000,
            'costo_total' => 900000,
            'nombre_firmante' => 'Juan Esteban Urrego',
            'fecha_firma' => now()->subHours(4),
            'token_publico_pdf' => (string) Str::uuid(),
            'fecha_ingreso' => now()->subDays(1),
            'fecha_promesa' => now(),
            'fecha_finalizacion' => now()->subHours(4),
        ]);

        $rutaFirmaT3 = $this->generarFirmaCliente($taller3->id, $cli3_1->id, $ot3_1->id, 'Juan Esteban Urrego');
        $ot3_1->update(['ruta_firma_cliente' => $rutaFirmaT3]);

        $this->generarImagenEvidencia($taller3->id, $cli3_1->id, $ot3_1->id, 'como_se_recibe', 'Pantalla iPhone estrellada antes del cambio.', 'https://images.unsplash.com/photo-1563770660941-20978e870e26?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller3->id, $cli3_1->id, $ot3_1->id, 'falla_detectada', 'Prueba de tactil fallando en zona superior.', 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller3->id, $cli3_1->id, $ot3_1->id, 'como_se_devuelve', 'Pantalla nueva instalada con TrueTone 100% funcional.', 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=800&q=80');

        // =========================================================================
        // TALLER 4: ElectroHogar del Valle (Cali)
        // =========================================================================
        $taller4 = Taller::create([
            'plan_licencia_id' => $planDemo?->id,
            'nombre_comercial' => 'ElectroHogar del Valle',
            'identificacion_fiscal' => 'NIT 890.777.888-9',
            'telefono' => '573152223344',
            'email' => 'admin@electrohogarvalle.com',
            'direccion' => 'Calle 5 # 38-20',
            'ciudad' => 'Cali',
            'estado_suscripcion' => 'periodo_prueba',
            'fecha_vencimiento_suscripcion' => now()->addDays(14),
        ]);

        $logoT4 = $this->generarLogoTaller($taller4->id, 'ElectroHogar', 'Mantenimiento de Electrodomésticos del Hogar', [
            'badge_r' => 217, 'badge_g' => 119, 'badge_b' => 6,
            'accent_r' => 245, 'accent_g' => 158, 'accent_b' => 11,
        ]);
        $taller4->update(['logo_ruta' => $logoT4]);

        Usuario::create([
            'taller_id' => $taller4->id,
            'nombre' => 'Mauricio',
            'apellido' => 'Ospina',
            'email' => 'admin@electrohogarvalle.com',
            'telefono' => '573152223344',
            'password' => Hash::make('password123'),
            'rol' => 'administrador',
            'esta_activo' => true,
        ]);
    }
}
