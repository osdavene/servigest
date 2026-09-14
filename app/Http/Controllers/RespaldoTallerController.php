<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\EvidenciaFotografica;
use App\Models\OrdenTrabajo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class RespaldoTallerController extends Controller
{
    /**
     * Muestra la consola de Copias de Seguridad de la empresa para el Administrador del Taller.
     */
    public function index()
    {
        $taller = auth()->user()->taller;

        // Recuento en tiempo real de todos los datos que componen el 100% de la empresa
        $totalClientes = Cliente::where('taller_id', $taller->id)->count();
        $totalEquipos = Equipo::where('taller_id', $taller->id)->count();
        $totalOrdenes = OrdenTrabajo::where('taller_id', $taller->id)->count();
        $totalCategorias = Categoria::where('taller_id', $taller->id)->count();
        $totalUsuarios = Usuario::where('taller_id', $taller->id)->count();

        $ordenesIds = OrdenTrabajo::where('taller_id', $taller->id)->pluck('id');
        $totalEvidencias = EvidenciaFotografica::whereIn('orden_trabajo_id', $ordenesIds)->count();

        // Listar respaldos históricos guardados en disco para este taller (Máximo 3)
        $this->rotarRespaldos($taller->id, 3);
        $carpetaRespaldos = "respaldos_talleres/{$taller->id}";
        $archivos = [];

        if (Storage::disk('local')->exists($carpetaRespaldos)) {
            $rutas = Storage::disk('local')->files($carpetaRespaldos);
            foreach ($rutas as $ruta) {
                $nombre = basename($ruta);
                $archivos[] = [
                    'nombre' => $nombre,
                    'tamano_kb' => round(Storage::disk('local')->size($ruta) / 1024, 2),
                    'fecha' => date('d/m/Y h:i A', Storage::disk('local')->lastModified($ruta)),
                ];
            }
            // Ordenar los más recientes primero
            usort($archivos, fn($a, $b) => strcmp($b['nombre'], $a['nombre']));
        }

        return view('respaldo_taller.index', compact(
            'taller',
            'totalClientes',
            'totalEquipos',
            'totalOrdenes',
            'totalCategorias',
            'totalUsuarios',
            'totalEvidencias',
            'archivos'
        ));
    }

    /**
     * Genera y descarga el paquete de Respaldo Completo en formato .ZIP (100% Base de Datos + Fotos de Evidencias).
     */
    public function descargar()
    {
        $taller = auth()->user()->taller;

        // 1. Extraemos con exactitud todos los registros pertenecientes a este taller
        $tallerData = DB::table('talleres')->where('id', $taller->id)->first();
        $usuarios = DB::table('usuarios')->where('taller_id', $taller->id)->get();
        $categorias = DB::table('categorias')->where('taller_id', $taller->id)->get();
        $clientes = DB::table('clientes')->where('taller_id', $taller->id)->get();
        $equipos = DB::table('equipos')->where('taller_id', $taller->id)->get();
        $ordenes = DB::table('ordenes_trabajo')->where('taller_id', $taller->id)->get();

        $ordenesIds = $ordenes->pluck('id')->toArray();
        $evidencias = DB::table('evidencias_fotograficas')->whereIn('orden_trabajo_id', $ordenesIds)->get();

        // 2. Estructura integral con metadatos
        $paqueteRespaldo = [
            'sistema' => 'ServiGest SaaS',
            'version_esquema' => '2.0',
            'tipo_respaldo' => 'BACKUP_EMPRESA_100_PORCIENTO_CON_FOTOS',
            'fecha_generacion' => now()->toIso8601String(),
            'generado_por' => [
                'usuario_id' => auth()->id(),
                'nombre' => auth()->user()->nombre_completo,
                'email' => auth()->user()->email,
            ],
            'empresa' => $tallerData,
            'metricas' => [
                'total_usuarios' => $usuarios->count(),
                'total_categorias' => $categorias->count(),
                'total_clientes' => $clientes->count(),
                'total_equipos' => $equipos->count(),
                'total_ordenes_trabajo' => $ordenes->count(),
                'total_evidencias_fotograficas' => $evidencias->count(),
            ],
            'datos' => [
                'usuarios' => $usuarios,
                'categorias' => $categorias,
                'clientes' => $clientes,
                'equipos' => $equipos,
                'ordenes_trabajo' => $ordenes,
                'evidencias_fotograficas' => $evidencias,
            ]
        ];

        $jsonContenido = json_encode($paqueteRespaldo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // 3. Crear archivo .ZIP comprimido que contiene la Base de Datos + Fotos reales
        $nombreLimpio = preg_replace('/[^A-Za-z0-9_\-]/', '_', $taller->nombre_comercial);
        $fecha = now()->format('Y-m-d_H-i-s');
        $nombreZip = "Backup_{$nombreLimpio}_{$fecha}.zip";

        $carpetaDestinoRelativa = "respaldos_talleres/{$taller->id}";
        $rutaCompletaLocal = Storage::disk('local')->path("{$carpetaDestinoRelativa}/{$nombreZip}");

        // Asegurar que el directorio exista
        if (!Storage::disk('local')->exists($carpetaDestinoRelativa)) {
            Storage::disk('local')->makeDirectory($carpetaDestinoRelativa);
        }

        $zip = new ZipArchive();
        if ($zip->open($rutaCompletaLocal, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // A. Añadir la Base de Datos JSON
            $zip->addFromString('datos_empresa.json', $jsonContenido);

            // B. Añadir las fotos de evidencias físicas de este taller
            foreach ($evidencias as $evidencia) {
                if ($evidencia->ruta_imagen && Storage::disk('public')->exists($evidencia->ruta_imagen)) {
                    $rutaFisica = Storage::disk('public')->path($evidencia->ruta_imagen);
                    $zip->addFile($rutaFisica, "archivos/{$evidencia->ruta_imagen}");
                }
            }

            // C. Añadir el logo de la empresa si existe
            if ($taller->logo_ruta && Storage::disk('public')->exists($taller->logo_ruta)) {
                $rutaFisicaLogo = Storage::disk('public')->path($taller->logo_ruta);
                $zip->addFile($rutaFisicaLogo, "archivos/{$taller->logo_ruta}");
            }

            $zip->close();
        }

        // 4. Aplicar política de retención: conservar solo los últimos 3 respaldos
        $this->rotarRespaldos($taller->id, 3);

        // 5. Descargar el archivo .ZIP directamente al usuario
        return response()->download($rutaCompletaLocal, $nombreZip, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Descarga una copia previamente generada del historial.
     */
    public function descargarArchivo(string $archivo)
    {
        $taller = auth()->user()->taller;
        $ruta = "respaldos_talleres/{$taller->id}/{$archivo}";

        if (!Storage::disk('local')->exists($ruta)) {
            return back()->with('error', 'El archivo de respaldo no existe o fue eliminado por la rotación automática.');
        }

        $rutaAbsoluta = Storage::disk('local')->path($ruta);
        return response()->download($rutaAbsoluta, $archivo);
    }

    /**
     * Política de Retención: Conserva únicamente los últimos $maxGuardados respaldos y elimina los anteriores.
     */
    private function rotarRespaldos(int $tallerId, int $maxGuardados = 3): void
    {
        $carpeta = "respaldos_talleres/{$tallerId}";

        if (!Storage::disk('local')->exists($carpeta)) {
            return;
        }

        $archivos = Storage::disk('local')->files($carpeta);

        if (count($archivos) <= $maxGuardados) {
            return;
        }

        // Ordenar archivos por nombre descendente (los más recientes primero)
        usort($archivos, fn($a, $b) => strcmp(basename($b), basename($a)));

        // Eliminar los archivos que excedan el límite de 3
        $archivosParaEliminar = array_slice($archivos, $maxGuardados);
        foreach ($archivosParaEliminar as $archivoAEliminar) {
            Storage::disk('local')->delete($archivoAEliminar);
        }
    }
}
