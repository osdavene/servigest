<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Taller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class RespaldoController extends Controller
{
    /**
     * Muestra el panel de copias de seguridad para el SuperAdmin.
     */
    public function index()
    {
        $talleres = Taller::withCount(['usuarios', 'clientes', 'equipos', 'ordenesTrabajo'])
            ->orderBy('nombre_comercial')
            ->get();

        $rutaBd = database_path('database.sqlite');
        $tamanoBd = file_exists($rutaBd) ? round(filesize($rutaBd) / 1024, 2) : 0; // en KB

        return view('superadmin.respaldos.index', compact('talleres', 'tamanoBd'));
    }

    /**
     * Genera y descarga una copia completa de toda la Base de Datos del sistema.
     */
    public function descargarGlobal()
    {
        $rutaBd = database_path('database.sqlite');

        if (!file_exists($rutaBd)) {
            return back()->with('error', 'El archivo de base de datos no fue encontrado.');
        }

        $fecha = now()->format('Y-m-d_H-i-s');
        $nombreArchivo = "ServiGest_Backup_GLOBAL_{$fecha}.sqlite";

        return response()->download($rutaBd, $nombreArchivo);
    }

    /**
     * Genera y descarga un respaldo exclusivo e individual para una empresa/taller en formato .ZIP (Base de Datos + Fotos).
     */
    public function descargarPorTaller(Taller $taller)
    {
        $tallerData = DB::table('talleres')->where('id', $taller->id)->first();
        $usuarios = DB::table('usuarios')->where('taller_id', $taller->id)->get();
        $clientes = DB::table('clientes')->where('taller_id', $taller->id)->get();
        $categorias = DB::table('categorias')->where('taller_id', $taller->id)->get();
        $equipos = DB::table('equipos')->where('taller_id', $taller->id)->get();
        $ordenes = DB::table('ordenes_trabajo')->where('taller_id', $taller->id)->get();
        
        $ordenesIds = $ordenes->pluck('id')->toArray();
        $evidencias = DB::table('evidencias_fotograficas')->whereIn('orden_trabajo_id', $ordenesIds)->get();

        $datosRespaldo = [
            'sistema' => 'ServiGest SaaS',
            'version_esquema' => '2.0',
            'tipo_respaldo' => 'BACKUP_EMPRESA_100_PORCIENTO_CON_FOTOS',
            'fecha_generacion' => now()->toIso8601String(),
            'empresa' => $tallerData,
            'metricas' => [
                'total_usuarios' => $usuarios->count(),
                'total_clientes' => $clientes->count(),
                'total_categorias' => $categorias->count(),
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

        $jsonContenido = json_encode($datosRespaldo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $nombreLimpio = preg_replace('/[^A-Za-z0-9_\-]/', '_', $taller->nombre_comercial);
        $fecha = now()->format('Y-m-d_H-i-s');
        $nombreZip = "Backup_{$nombreLimpio}_{$fecha}.zip";

        $rutaTemp = storage_path("app/private/{$nombreZip}");
        $zip = new ZipArchive();
        if ($zip->open($rutaTemp, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFromString('datos_empresa.json', $jsonContenido);

            foreach ($evidencias as $evidencia) {
                if ($evidencia->ruta_imagen && Storage::disk('public')->exists($evidencia->ruta_imagen)) {
                    $rutaFisica = Storage::disk('public')->path($evidencia->ruta_imagen);
                    $zip->addFile($rutaFisica, "archivos/{$evidencia->ruta_imagen}");
                }
            }

            if ($taller->logo_ruta && Storage::disk('public')->exists($taller->logo_ruta)) {
                $rutaFisicaLogo = Storage::disk('public')->path($taller->logo_ruta);
                $zip->addFile($rutaFisicaLogo, "archivos/{$taller->logo_ruta}");
            }

            $zip->close();
        }

        return response()->download($rutaTemp, $nombreZip)->deleteFileAfterSend(true);
    }

    /**
     * Motor de Restauración Exclusivo para el Super Administrador.
     * Restaura una empresa completa al 100% de operatividad a partir de su archivo .ZIP o .JSON.
     */
    public function restaurarEmpresa(Request $request)
    {
        $request->validate([
            'archivo_backup' => ['required', 'file'],
        ], [
            'archivo_backup.required' => 'Debe seleccionar un archivo de copia de seguridad (.zip o .json) para restaurar.',
        ]);

        $archivo = $request->file('archivo_backup');
        $extension = strtolower($archivo->getClientOriginalExtension());
        $paquete = null;

        // 1. Si es archivo .ZIP (Base de Datos + Fotos físicas)
        if ($extension === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($archivo->getRealPath()) === true) {
                // Extraer el JSON de base de datos
                $jsonContenido = $zip->getFromName('datos_empresa.json');
                if ($jsonContenido) {
                    $paquete = json_decode($jsonContenido, true);
                }

                // Extraer y restaurar todas las fotos físicas al disco público
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $nombreEnZip = $zip->getNameIndex($i);
                    if (str_starts_with($nombreEnZip, 'archivos/') && !str_ends_with($nombreEnZip, '/')) {
                        $rutaRelativaPublic = substr($nombreEnZip, strlen('archivos/'));
                        $contenidoArchivo = $zip->getFromIndex($i);
                        Storage::disk('public')->put($rutaRelativaPublic, $contenidoArchivo);
                    }
                }

                $zip->close();
            }
        } 
        // 2. Si es archivo .JSON directo
        else {
            $contenido = file_get_contents($archivo->getRealPath());
            $paquete = json_decode($contenido, true);
        }

        if (!$paquete || !isset($paquete['empresa']) || !isset($paquete['datos'])) {
            return back()->with('error', 'El archivo no tiene el formato válido de copia de seguridad de ServiGest (.zip o .json).');
        }

        $empresaData = (array) $paquete['empresa'];
        $datos = $paquete['datos'];

        // 3. Transacción atómica de Base de Datos
        DB::transaction(function () use ($empresaData, $datos) {
            // A. Sincronizar o crear Taller
            $tallerId = $empresaData['id'] ?? null;
            $taller = Taller::withTrashed()->find($tallerId);

            $datosTaller = [
                'nombre_comercial' => $empresaData['nombre_comercial'],
                'identificacion_fiscal' => $empresaData['identificacion_fiscal'] ?? null,
                'email' => $empresaData['email'],
                'telefono' => $empresaData['telefono'],
                'direccion' => $empresaData['direccion'] ?? null,
                'ciudad' => $empresaData['ciudad'] ?? 'Bogotá',
                'estado_suscripcion' => $empresaData['estado_suscripcion'] ?? 'activo',
                'fecha_fin_suscripcion' => $empresaData['fecha_fin_suscripcion'] ?? now()->addMonth(),
                'prefijo_orden' => $empresaData['prefijo_orden'] ?? 'OT',
                'url_logo' => $empresaData['url_logo'] ?? null,
                'texto_garantia' => $empresaData['texto_garantia'] ?? null,
                'deleted_at' => null,
            ];

            if ($taller) {
                $taller->update($datosTaller);
            } else {
                $datosTaller['id'] = $tallerId;
                $datosTaller['plan_licencia_id'] = $empresaData['plan_licencia_id'] ?? 1;
                $taller = Taller::create($datosTaller);
            }

            // B. Restaurar Categorías
            if (!empty($datos['categorias'])) {
                foreach ($datos['categorias'] as $cat) {
                    $cat = (array) $cat;
                    DB::table('categorias')->updateOrInsert(
                        ['id' => $cat['id']],
                        array_merge($cat, ['taller_id' => $taller->id, 'deleted_at' => $cat['deleted_at'] ?? null])
                    );
                }
            }

            // C. Restaurar Clientes
            if (!empty($datos['clientes'])) {
                foreach ($datos['clientes'] as $cli) {
                    $cli = (array) $cli;
                    DB::table('clientes')->updateOrInsert(
                        ['id' => $cli['id']],
                        array_merge($cli, ['taller_id' => $taller->id, 'deleted_at' => $cli['deleted_at'] ?? null])
                    );
                }
            }

            // D. Restaurar Equipos
            if (!empty($datos['equipos'])) {
                foreach ($datos['equipos'] as $eq) {
                    $eq = (array) $eq;
                    DB::table('equipos')->updateOrInsert(
                        ['id' => $eq['id']],
                        array_merge($eq, ['taller_id' => $taller->id, 'deleted_at' => $eq['deleted_at'] ?? null])
                    );
                }
            }

            // E. Restaurar Usuarios (con credenciales y contraseñas intactas)
            if (!empty($datos['usuarios'])) {
                foreach ($datos['usuarios'] as $u) {
                    $u = (array) $u;
                    DB::table('usuarios')->updateOrInsert(
                        ['id' => $u['id']],
                        array_merge($u, ['taller_id' => $taller->id, 'deleted_at' => $u['deleted_at'] ?? null])
                    );
                }
            }

            // F. Restaurar Órdenes de Trabajo
            if (!empty($datos['ordenes_trabajo'])) {
                foreach ($datos['ordenes_trabajo'] as $ot) {
                    $ot = (array) $ot;
                    DB::table('ordenes_trabajo')->updateOrInsert(
                        ['id' => $ot['id']],
                        array_merge($ot, ['taller_id' => $taller->id, 'deleted_at' => $ot['deleted_at'] ?? null])
                    );
                }
            }

            // G. Restaurar Evidencias Fotográficas
            if (!empty($datos['evidencias_fotograficas'])) {
                foreach ($datos['evidencias_fotograficas'] as $ev) {
                    $ev = (array) $ev;
                    DB::table('evidencias_fotograficas')->updateOrInsert(
                        ['id' => $ev['id']],
                        $ev
                    );
                }
            }
        });

        return redirect()->route('superadmin.respaldos.index')
            ->with('exito', "¡Copia de seguridad restaurada exitosamente para '{$empresaData['nombre_comercial']}'! Se restablecieron todas las tablas y los archivos físicos de fotos.");
    }
}
