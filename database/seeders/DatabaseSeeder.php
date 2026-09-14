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
        $directorio = storage_path("app/public/logos/{$tallerId}");
        if (!File::exists($directorio)) {
            File::makeDirectory($directorio, 0755, true);
        }

        $rutaCompleta = "{$directorio}/logo_{$tallerId}.png";
        $rutaRelativa = "logos/{$tallerId}/logo_{$tallerId}.png";

        $ancho = 560;
        $alto = 160;
        $img = imagecreatetruecolor($ancho, $alto);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        // Fondo transparente
        $transparente = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefilledrectangle($img, 0, 0, $ancho, $alto, $transparente);
        imagealphablending($img, true);

        // Colores de la marca
        $colorBgBadge = imagecolorallocate($img, $colores['badge_r'], $colores['badge_g'], $colores['badge_b']);
        $colorBadgeBorde = imagecolorallocate($img, $colores['accent_r'], $colores['accent_g'], $colores['accent_b']);
        $colorTexto1 = imagecolorallocate($img, 15, 23, 42); // Slate oscuro
        $colorTexto2 = imagecolorallocate($img, $colores['accent_r'], $colores['accent_g'], $colores['accent_b']);
        $colorSubtitulo = imagecolorallocate($img, 100, 116, 139); // Gris slate
        $blanco = imagecolorallocate($img, 255, 255, 255);

        // 1. Icono / Insignia Izquierda (Caja redondeada 100x100)
        imagefilledrectangle($img, 20, 30, 120, 130, $colorBgBadge);
        imagerectangle($img, 20, 30, 120, 130, $colorBadgeBorde);
        imagerectangle($img, 22, 32, 118, 128, $colorBadgeBorde);

        // Detalle en el icono (Iniciales o Símbolo)
        $inicial = substr($nombrePrincipal, 0, 2);
        imagestring($img, 5, 55, 68, $inicial, $blanco);
        imagestring($img, 3, 50, 92, "TECH", $colorBadgeBorde);

        // 2. Textos del Logo
        imagestring($img, 5, 140, 42, strtoupper($nombrePrincipal), $colorTexto1);
        imagestring($img, 4, 140, 72, "SERVICIO TECNICO ESPECIALIZADO", $colorTexto2);
        imagestring($img, 3, 140, 98, $subtitulo, $colorSubtitulo);

        // Barra decorativa inferior
        imagefilledrectangle($img, 140, 124, 480, 127, $colorBadgeBorde);

        imagepng($img, $rutaCompleta);
        imagedestroy($img);

        return $rutaRelativa;
    }

    /**
     * Descarga una foto técnica real desde Unsplash CDN o genera una gráfica Full HD con GD si no hay internet.
     */
    private function generarImagenEvidencia(int $tallerId, int $ordenId, string $etiqueta, string $titulo, ?string $urlFotoReal = null): string
    {
        $directorio = storage_path("app/public/evidencias/{$tallerId}/{$ordenId}");
        if (!File::exists($directorio)) {
            File::makeDirectory($directorio, 0755, true);
        }

        $nombreArchivo = Str::uuid()->toString() . '.jpg';
        $rutaCompleta = "{$directorio}/{$nombreArchivo}";
        $rutaRelativa = "evidencias/{$tallerId}/{$ordenId}/{$nombreArchivo}";

        $fotoDescargada = false;

        // Intentar descargar foto real si se proporcionó URL
        if ($urlFotoReal) {
            try {
                $response = Http::timeout(4)->get($urlFotoReal);
                if ($response->successful() && strlen($response->body()) > 5000) {
                    File::put($rutaCompleta, $response->body());
                    $fotoDescargada = true;
                }
            } catch (\Throwable $e) {
                // Si falla o no hay conexión, se genera localmente con GD
                $fotoDescargada = false;
            }
        }

        // Generar mediante GD si no se descargó
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
            imagestring($img, 4, 40, 80, "Taller ID: {$tallerId} | Orden: #OT-000{$ordenId}", $gris);
            imagestring($img, 5, 40, 140, "ETAPA: " . strtoupper(str_replace('_', ' ', $etiqueta)), $blanco);
            imagestring($img, 4, 40, 180, "Hallazgo: {$titulo}", $blanco);
            imagestring($img, 3, 40, 530, "Fecha: " . date('d/m/Y H:i:s') . " | Foto Full HD", $gris);

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
     * Ejecuta el seeder maestro.
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
        // EMPRESA 1: ElectroTech Soluciones Integrales (Bogotá)
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
        ]);

        // Logo Empresa 1
        $logoT1 = $this->generarLogoTaller($taller1->id, 'ElectroTech', 'Laptops • Línea Blanca • Smart TVs', [
            'badge_r' => 2, 'badge_g' => 132, 'badge_b' => 199, // Azul Sky
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

        $tec1T1 = Usuario::create([
            'taller_id' => $taller1->id,
            'nombre' => 'Diego',
            'apellido' => 'Ramírez',
            'email' => 'diego@electrotech.com',
            'telefono' => '573117778899',
            'password' => Hash::make('password123'),
            'rol' => 'tecnico',
            'esta_activo' => true,
        ]);

        $tec2T1 = Usuario::create([
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
            'nombre' => 'Laptops & Computadores',
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
            'nombre' => 'Smart TVs & Pantallas 4K',
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

        // Clientes Taller 1
        $cli1_1 = Cliente::create([
            'taller_id' => $taller1->id,
            'nombre_completo' => 'María Camila Gómez',
            'identificacion' => 'CC 1020304050',
            'telefono' => '573001234567',
            'email' => 'camila.gomez@gmail.com',
            'direccion' => 'Calle 127 # 45-20',
            'barrio' => 'Unicentro',
            'ciudad' => 'Bogotá',
            'latitud' => 4.707264,
            'longitud' => -74.041695,
            'notas_adicionales' => 'Cliente VIP. Llamar antes de visita en domicilio.',
        ]);

        $cli1_2 = Cliente::create([
            'taller_id' => $taller1->id,
            'nombre_completo' => 'Andrés Felipe Castro',
            'identificacion' => 'CC 79854120',
            'telefono' => '573159876543',
            'email' => 'andres.castro@empresa.com',
            'direccion' => 'Avenida Boyacá # 68-15',
            'barrio' => 'Normandía',
            'ciudad' => 'Bogotá',
            'latitud' => 4.673820,
            'longitud' => -74.108420,
            'notas_adicionales' => 'Conjunto cerrado, ingresar por portería principal.',
        ]);

        $cli1_3 = Cliente::create([
            'taller_id' => $taller1->id,
            'nombre_completo' => 'Restaurante El Fogón Boyacense',
            'identificacion' => 'NIT 901.888.777-2',
            'telefono' => '573183332211',
            'email' => 'compras@fogonboyacense.com',
            'direccion' => 'Carrera 7 # 45-12',
            'barrio' => 'Chapinero',
            'ciudad' => 'Bogotá',
            'notas_adicionales' => 'Atención de equipos comerciales de refrigeración.',
        ]);

        $cli1_4 = Cliente::create([
            'taller_id' => $taller1->id,
            'nombre_completo' => 'Valentina Restrepo',
            'identificacion' => 'CC 52987412',
            'telefono' => '573164448899',
            'email' => 'valentina.restrepo@hotmail.com',
            'direccion' => 'Calle 80 # 70-15',
            'barrio' => 'Pontevedra',
            'ciudad' => 'Bogotá',
        ]);

        // Equipos Taller 1
        $eq1_1 = Equipo::create([
            'taller_id' => $taller1->id,
            'cliente_id' => $cli1_1->id,
            'categoria_id' => $catLaptops->id,
            'marca' => 'Dell',
            'modelo' => 'Inspiron 15 3520 (Intel i7)',
            'numero_serie' => 'DELL-98745632-X',
            'observaciones_fisicas' => 'Tapa con rayones leves. Incluye cargador original 65W.',
            'fecha_ultimo_servicio' => now()->subMonths(7)->toDateString(),
            'fecha_proximo_mantenimiento' => now()->subDays(10)->toDateString(), // Vencido
        ]);

        $eq1_2 = Equipo::create([
            'taller_id' => $taller1->id,
            'cliente_id' => $cli1_1->id,
            'categoria_id' => $catTVs->id,
            'marca' => 'Samsung',
            'modelo' => 'Crystal UHD 55" (UN55AU7000)',
            'numero_serie' => 'SAM-TV-55-AU7000',
            'observaciones_fisicas' => 'Pantalla sin rayones. Con control remoto Smart.',
            'fecha_ultimo_servicio' => now()->subMonths(2)->toDateString(),
        ]);

        $eq1_3 = Equipo::create([
            'taller_id' => $taller1->id,
            'cliente_id' => $cli1_2->id,
            'categoria_id' => $catNeveras->id,
            'marca' => 'Whirlpool',
            'modelo' => 'No-Frost 420L Inverter Xpert Flow',
            'numero_serie' => 'WP-NF-2023-99',
            'observaciones_fisicas' => 'Puerta derecha con pequeño abollón.',
            'fecha_ultimo_servicio' => now()->subMonths(11)->toDateString(),
            'fecha_proximo_mantenimiento' => now()->addDays(5)->toDateString(), // Próximo
        ]);

        $eq1_4 = Equipo::create([
            'taller_id' => $taller1->id,
            'cliente_id' => $cli1_3->id,
            'categoria_id' => $catNeveras->id,
            'marca' => 'Mabe',
            'modelo' => 'Congelador Horizontal 500L Comercial',
            'numero_serie' => 'MB-CONG-500L-2022',
            'observaciones_fisicas' => 'En uso continuo en cocina del restaurante.',
            'fecha_ultimo_servicio' => now()->subMonths(5)->toDateString(),
            'fecha_proximo_mantenimiento' => now()->addDays(30)->toDateString(),
        ]);

        $eq1_5 = Equipo::create([
            'taller_id' => $taller1->id,
            'cliente_id' => $cli1_4->id,
            'categoria_id' => $catLavadoras->id,
            'marca' => 'LG',
            'modelo' => 'Smart Inverter TurboDrum 19Kg',
            'numero_serie' => 'LG-LAV-19KG-WT19',
            'observaciones_fisicas' => 'Display intacto, mangueras originales.',
            'fecha_ultimo_servicio' => now()->subMonths(8)->toDateString(),
            'fecha_proximo_mantenimiento' => now()->subDays(5)->toDateString(),
        ]);

        // Órdenes de Trabajo Taller 1 con Fotos Reales
        
        // OT 1: Finalizada y Entregada (Laptop Dell)
        $ot1_1 = OrdenTrabajo::create([
            'taller_id' => $taller1->id,
            'codigo_orden' => 'OT-00001',
            'cliente_id' => $cli1_1->id,
            'equipo_id' => $eq1_1->id,
            'tecnico_asignado_id' => $tec1T1->id,
            'tipo_ubicacion' => 'ingresado_al_taller',
            'estado' => 'entregado',
            'problema_reportado' => 'El computador recalienta excesivamente y se apaga a los 20 minutos de uso.',
            'diagnostico' => 'Ventilador cooler obstruido por pelusa y pasta térmica totalmente seca y cristalizada.',
            'procedimiento_realizado' => 'Mantenimiento preventivo general, limpieza de disipador con alcohol isopropílico, lubricación de cooler y aplicación de pasta térmica Noctua NT-H1.',
            'repuestos_usados' => 'Pasta térmica Noctua NT-H1 (1.5g)',
            'costo_mano_obra' => 85000,
            'costo_repuestos' => 35000,
            'costo_total' => 120000,
            'nombre_firmante' => 'María Camila Gómez',
            'fecha_firma' => now()->subMonths(2),
            'token_publico_pdf' => (string) Str::uuid(),
            'fecha_ingreso' => now()->subMonths(2)->subDays(2),
            'fecha_promesa' => now()->subMonths(2)->subDays(1),
            'fecha_finalizacion' => now()->subMonths(2),
        ]);

        $this->generarImagenEvidencia($taller1->id, $ot1_1->id, 'como_se_recibe', 'Equipo con polvo interno y disipador obstruido.', 'https://images.unsplash.com/photo-1597740985671-2a8a3b80502e?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller1->id, $ot1_1->id, 'durante_reparacion', 'Limpieza profunda y pasta termica Noctua aplicada.', 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller1->id, $ot1_1->id, 'como_se_devuelve', 'Equipo encendido testeado a 45C en test de estres.', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80');

        // OT 2: Mismo Equipo (Laptop Dell) para Upgrade SSD
        $ot1_2 = OrdenTrabajo::create([
            'taller_id' => $taller1->id,
            'codigo_orden' => 'OT-00002',
            'cliente_id' => $cli1_1->id,
            'equipo_id' => $eq1_1->id,
            'tecnico_asignado_id' => $tec1T1->id,
            'tipo_ubicacion' => 'ingresado_al_taller',
            'estado' => 'finalizado',
            'problema_reportado' => 'Cliente solicita aumento de almacenamiento y clonación de sistema porque el disco mecánico está muy lento.',
            'diagnostico' => 'Disco mecánico de 1TB con sectores lentos. Se actualiza a unidad SSD NVMe M.2 de 1TB.',
            'procedimiento_realizado' => 'Instalación de SSD Kingston NV2 NVMe 1TB, clonación de partición de Windows 11, verificación de arranque veloz.',
            'repuestos_usados' => 'SSD Kingston NV2 1TB NVMe PCIe 4.0',
            'costo_mano_obra' => 60000,
            'costo_repuestos' => 240000,
            'costo_total' => 300000,
            'nombre_firmante' => 'María Camila Gómez',
            'fecha_firma' => now()->subDays(3),
            'token_publico_pdf' => (string) Str::uuid(),
            'fecha_ingreso' => now()->subDays(4),
            'fecha_promesa' => now()->subDays(3),
            'fecha_finalizacion' => now()->subDays(3),
        ]);

        $this->generarImagenEvidencia($taller1->id, $ot1_2->id, 'falla_detectada', 'Prueba CrystalDiskInfo mostrando sectores lentos.', 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller1->id, $ot1_2->id, 'como_se_devuelve', 'SSD NVMe montado y Windows 11 arrancando veloz.', 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?auto=format&fit=crop&w=800&q=80');

        // OT 3: Nevera Domicilio en proceso (Andrés Castro)
        $ot1_3 = OrdenTrabajo::create([
            'taller_id' => $taller1->id,
            'codigo_orden' => 'OT-00003',
            'cliente_id' => $cli1_2->id,
            'equipo_id' => $eq1_3->id,
            'tecnico_asignado_id' => $tec2T1->id,
            'tipo_ubicacion' => 'servicio_en_domicilio',
            'estado' => 'en_proceso',
            'problema_reportado' => 'La nevera enfría abajo pero el congelador acumula escarcha y bloquea el ducto de aire.',
            'diagnostico' => 'Resistencia de descongelamiento abierta y sensor bimetálico averiado.',
            'procedimiento_realizado' => 'Diagnóstico en sitio, medición de resistencia con multímetro. Instalación de kit de descongelamiento.',
            'repuestos_usados' => 'Sensor bimetálico L55 y resistencia tubular 110V',
            'costo_mano_obra' => 120000,
            'costo_repuestos' => 95000,
            'costo_total' => 215000,
            'token_publico_pdf' => (string) Str::uuid(),
            'fecha_ingreso' => now()->subHours(6),
            'fecha_promesa' => now()->addDay(),
        ]);

        $this->generarImagenEvidencia($taller1->id, $ot1_3->id, 'como_se_recibe', 'Evaporador congelado con bloque de hielo.', 'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller1->id, $ot1_3->id, 'falla_detectada', 'Medicion de resistencia en 0 Ohmios.', 'https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=800&q=80');

        // OT 4: Smart TV Samsung Pendiente (María Camila)
        $ot1_4 = OrdenTrabajo::create([
            'taller_id' => $taller1->id,
            'codigo_orden' => 'OT-00004',
            'cliente_id' => $cli1_1->id,
            'equipo_id' => $eq1_2->id,
            'tecnico_asignado_id' => $tec2T1->id,
            'tipo_ubicacion' => 'ingresado_al_taller',
            'estado' => 'pendiente',
            'problema_reportado' => 'El televisor tiene audio normal pero la pantalla se queda totalmente negra.',
            'diagnostico' => null,
            'costo_mano_obra' => 0,
            'costo_repuestos' => 0,
            'costo_total' => 0,
            'token_publico_pdf' => (string) Str::uuid(),
            'fecha_ingreso' => now()->subHours(2),
            'fecha_promesa' => now()->addDays(2),
        ]);

        $this->generarImagenEvidencia($taller1->id, $ot1_4->id, 'como_se_recibe', 'Recepcion de Smart TV 55 pulgadas en mesa de trabajo.', 'https://images.unsplash.com/photo-1593784991095-a205069470b6?auto=format&fit=crop&w=800&q=80');

        // OT 5: Lavadora LG en Domicilio Finalizada (Valentina Restrepo)
        $ot1_5 = OrdenTrabajo::create([
            'taller_id' => $taller1->id,
            'codigo_orden' => 'OT-00005',
            'cliente_id' => $cli1_4->id,
            'equipo_id' => $eq1_5->id,
            'tecnico_asignado_id' => $tec1T1->id,
            'tipo_ubicacion' => 'servicio_en_domicilio',
            'estado' => 'finalizado',
            'problema_reportado' => 'Al centrifugar produce un ruido metálico fuerte y bota agua por debajo.',
            'diagnostico' => 'Retén de tina roto y rodamiento desgastado con filtración.',
            'procedimiento_realizado' => 'Desarme de tina, cambio de rodamientos SKF y retén de alta presión, limpieza de tina.',
            'repuestos_usados' => 'Kit rodamientos sellados SKF + Retén original LG',
            'costo_mano_obra' => 150000,
            'costo_repuestos' => 85000,
            'costo_total' => 235000,
            'nombre_firmante' => 'Valentina Restrepo',
            'fecha_firma' => now()->subHours(1),
            'token_publico_pdf' => (string) Str::uuid(),
            'fecha_ingreso' => now()->subDays(1),
            'fecha_promesa' => now(),
            'fecha_finalizacion' => now()->subHours(1),
        ]);

        $this->generarImagenEvidencia($taller1->id, $ot1_5->id, 'falla_detectada', 'Reten roto con corrosion en rodamiento.', 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller1->id, $ot1_5->id, 'como_se_devuelve', 'Lavadora armada y centrifugando sin ruido.', 'https://images.unsplash.com/photo-1610557892470-55d9e80c0bce?auto=format&fit=crop&w=800&q=80');

        // =========================================================================
        // EMPRESA 2: Climatización & Frío del Norte (Barranquilla)
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
        ]);

        // Logo Empresa 2
        $logoT2 = $this->generarLogoTaller($taller2->id, 'Climatizacion', 'Aires Inverter • Cuartos Frios • Refrigeracion', [
            'badge_r' => 6, 'badge_g' => 78, 'badge_b' => 59, // Verde azulado / Frío
            'accent_r' => 16, 'accent_g' => 185, 'accent_b' => 129,
        ]);
        $taller2->update(['logo_ruta' => $logoT2]);

        $adminT2 = Usuario::create([
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

        $this->generarImagenEvidencia($taller2->id, $ot2_1->id, 'como_se_recibe', 'Turbina con polvo antes del hidrolavado.', 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller2->id, $ot2_1->id, 'durante_reparacion', 'Hidrolavado a presion con funda impermeable.', 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller2->id, $ot2_1->id, 'como_se_devuelve', 'Unidad limpia rindiendo a 16C en habitacion.', 'https://images.unsplash.com/photo-1590756254933-2873d72a83b6?auto=format&fit=crop&w=800&q=80');

        // =========================================================================
        // EMPRESA 3: MacroFix Móviles & Gaming Pro (Medellín)
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
        ]);

        // Logo Empresa 3
        $logoT3 = $this->generarLogoTaller($taller3->id, 'MacroFix', 'iPhones • Samsung • PS5 • Nintendo Switch', [
            'badge_r' => 109, 'badge_g' => 40, 'badge_b' => 217, // Púrpura Gaming
            'accent_r' => 168, 'accent_g' => 85, 'accent_b' => 247,
        ]);
        $taller3->update(['logo_ruta' => $logoT3]);

        $adminT3 = Usuario::create([
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

        $catSmartphones = Categoria::create([
            'taller_id' => $taller3->id,
            'nombre' => 'Smartphones & Tablets (iOS / Android)',
            'descripcion' => 'Displays OLED, baterías, puertos de carga Type-C/Lightning y microsoldadura de placas.',
            'requiere_mantenimiento_preventivo' => false,
        ]);

        $catConsolas = Categoria::create([
            'taller_id' => $taller3->id,
            'nombre' => 'Consolas de Videojuegos (PS5, Xbox, Switch)',
            'descripcion' => 'Reballing APU, cambio de metal líquido, puertos HDMI 2.1 y fuentes internas.',
            'requiere_mantenimiento_preventivo' => true,
            'intervalo_mantenimiento_dias' => 180,
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
        ]);

        $eq3_1 = Equipo::create([
            'taller_id' => $taller3->id,
            'cliente_id' => $cli3_1->id,
            'categoria_id' => $catSmartphones->id,
            'marca' => 'Apple',
            'modelo' => 'iPhone 14 Pro Max (256GB)',
            'numero_serie' => 'F2LZX890N72M',
            'observaciones_fisicas' => 'Vidrio frontal estrellado en esquina superior derecha. FaceID operativo.',
        ]);

        $eq3_2 = Equipo::create([
            'taller_id' => $taller3->id,
            'cliente_id' => $cli3_1->id,
            'categoria_id' => $catConsolas->id,
            'marca' => 'Sony',
            'modelo' => 'PlayStation 5 Digital Edition',
            'numero_serie' => 'PS5-CFI-1215B-9988',
            'observaciones_fisicas' => 'Consola sin rayones. Con cable HDMI y de poder.',
            'fecha_ultimo_servicio' => now()->subMonths(7)->toDateString(),
            'fecha_proximo_mantenimiento' => now()->subDays(15)->toDateString(),
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
            'procedimiento_realizado' => 'Reemplazo de pantalla completa OLED Original Service Pack, traspaso de sensor TrueTone y sellado contra polvo.',
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

        $this->generarImagenEvidencia($taller3->id, $ot3_1->id, 'como_se_recibe', 'Pantalla iPhone estrellada antes del cambio.', 'https://images.unsplash.com/photo-1563770660941-20978e870e26?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller3->id, $ot3_1->id, 'falla_detectada', 'Prueba de tactil fallando en zona superior.', 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller3->id, $ot3_1->id, 'como_se_devuelve', 'Pantalla nueva instalada con TrueTone 100% funcional.', 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=800&q=80');

        $ot3_2 = OrdenTrabajo::create([
            'taller_id' => $taller3->id,
            'codigo_orden' => 'MF-00002',
            'cliente_id' => $cli3_1->id,
            'equipo_id' => $eq3_2->id,
            'tecnico_asignado_id' => $tecT3->id,
            'tipo_ubicacion' => 'ingresado_al_taller',
            'estado' => 'en_proceso',
            'problema_reportado' => 'La PS5 suena muy fuerte el ventilador al jugar títulos pesados y a la hora se apaga con aviso de temperatura.',
            'diagnostico' => 'Oxidación y desplazamiento del metal líquido en el APU, disipador con pelusa en toberas.',
            'procedimiento_realizado' => 'Desensamble completo, pulido de cobre del APU y reaplicación de Thermal Grizzly Conductonaut.',
            'repuestos_usados' => 'Metal líquido Thermal Grizzly Conductonaut + Pads térmicos Gelid 1.5mm',
            'costo_mano_obra' => 110000,
            'costo_repuestos' => 65000,
            'costo_total' => 175000,
            'token_publico_pdf' => (string) Str::uuid(),
            'fecha_ingreso' => now()->subHours(3),
            'fecha_promesa' => now()->addDay(),
        ]);

        $this->generarImagenEvidencia($taller3->id, $ot3_2->id, 'como_se_recibe', 'PS5 abierta con oxidacion de metal liquido.', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=800&q=80');
        $this->generarImagenEvidencia($taller3->id, $ot3_2->id, 'durante_reparacion', 'Reaplicacion uniforme de metal liquido.', 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=800&q=80');

        // =========================================================================
        // EMPRESA 4: ElectroHogar del Valle (Cali)
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

        // Logo Empresa 4
        $logoT4 = $this->generarLogoTaller($taller4->id, 'ElectroHogar', 'Mantenimiento de Electrodomésticos del Hogar', [
            'badge_r' => 217, 'badge_g' => 119, 'badge_b' => 6, // Ámbar Cálido
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
