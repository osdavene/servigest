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

        $driverBd = config('database.default', 'sqlite');
        $tamanoBd = 0; // en KB

        if ($driverBd === 'sqlite') {
            $rutaBd = config('database.connections.sqlite.database', database_path('database.sqlite'));
            $tamanoBd = file_exists($rutaBd) ? round(filesize($rutaBd) / 1024, 2) : 0;
        } elseif ($driverBd === 'mysql') {
            try {
                $dbName = config('database.connections.mysql.database');
                $res = DB::select("
                    SELECT ROUND(SUM(data_length + index_length) / 1024, 2) AS tamano_kb 
                    FROM information_schema.tables 
                    WHERE table_schema = ?
                ", [$dbName]);
                $tamanoBd = $res[0]->tamano_kb ?? 0;
            } catch (\Throwable $e) {
                $tamanoBd = 0;
            }
        }

        return view('superadmin.respaldos.index', compact('talleres', 'tamanoBd', 'driverBd'));
    }

    /**
     * Genera y descarga una copia completa de toda la Base de Datos del sistema (compatible con SQLite y MySQL).
     */
    public function descargarGlobal()
    {
        $driverBd = config('database.default', 'sqlite');
        $fecha = now()->format('Y-m-d_H-i-s');

        // 1. Manejo para SQLite
        if ($driverBd === 'sqlite') {
            $rutaBd = config('database.connections.sqlite.database', database_path('database.sqlite'));

            if (!file_exists($rutaBd)) {
                return back()->with('error', 'El archivo de base de datos SQLite no fue encontrado en el servidor.');
            }

            $nombreArchivo = "ServiGest_Backup_GLOBAL_{$fecha}.sqlite";
            return response()->download($rutaBd, $nombreArchivo);
        }

        // 2. Manejo para MySQL en Producción (AWS / Docker)
        if ($driverBd === 'mysql') {
            try {
                $dbName = config('database.connections.mysql.database');
                $nombreArchivo = "ServiGest_Backup_GLOBAL_{$fecha}.sql";
                
                $dirPrivado = storage_path('app/private');
                if (!is_dir($dirPrivado)) {
                    mkdir($dirPrivado, 0755, true);
                }
                $rutaSql = "{$dirPrivado}/{$nombreArchivo}";
                $fp = fopen($rutaSql, 'w');

                if (!$fp) {
                    return back()->with('error', 'No se pudo crear el archivo temporal de respaldo en disco.');
                }

                // Cabecera SQL estándar
                $fechaTexto = now()->toDateTimeString();
                fwrite($fp, "-- ============================================================\n");
                fwrite($fp, "-- ServiGest SaaS - Volcado Global de Base de Datos MySQL\n");
                fwrite($fp, "-- Base de datos: `{$dbName}`\n");
                fwrite($fp, "-- Generado: {$fechaTexto}\n");
                fwrite($fp, "-- ============================================================\n\n");
                fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n");
                fwrite($fp, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
                fwrite($fp, "SET time_zone = \"+00:00\";\n\n");

                $tablasRaw = DB::select('SHOW TABLES');
                foreach ($tablasRaw as $filaTabla) {
                    $propiedades = (array) $filaTabla;
                    $nombreTabla = reset($propiedades);

                    if (empty($nombreTabla)) {
                        continue;
                    }

                    // Estructura de la tabla (CREATE TABLE)
                    $createRes = DB::select("SHOW CREATE TABLE `{$nombreTabla}`");
                    $createArray = (array) ($createRes[0] ?? []);
                    $createSql = $createArray['Create Table'] ?? null;

                    if ($createSql) {
                        fwrite($fp, "-- --------------------------------------------------------\n");
                        fwrite($fp, "-- Estructura de tabla para `{$nombreTabla}`\n");
                        fwrite($fp, "-- --------------------------------------------------------\n");
                        fwrite($fp, "DROP TABLE IF EXISTS `{$nombreTabla}`;\n");
                        fwrite($fp, $createSql . ";\n\n");
                    }

                    // Datos de la tabla por fragmentos para optimizar RAM
                    $totalFilas = DB::table($nombreTabla)->count();
                    if ($totalFilas > 0) {
                        fwrite($fp, "-- Volcado de datos para la tabla `{$nombreTabla}` ({$totalFilas} registros)\n");
                        DB::table($nombreTabla)->orderBy(DB::raw('1'))->chunk(250, function ($filas) use ($fp, $nombreTabla) {
                            foreach ($filas as $fila) {
                                $arrFila = (array) $fila;
                                $columnas = array_keys($arrFila);
                                $valores = array_map(function ($val) {
                                    if (is_null($val)) {
                                        return 'NULL';
                                    }
                                    return DB::getPdo()->quote((string) $val);
                                }, array_values($arrFila));

                                $insertSql = "INSERT INTO `{$nombreTabla}` (`" . implode('`, `', $columnas) . "`) VALUES (" . implode(', ', $valores) . ");\n";
                                fwrite($fp, $insertSql);
                            }
                        });
                        fwrite($fp, "\n");
                    }
                }

                // Pie SQL
                fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
                fwrite($fp, "-- Fin del respaldo ServiGest SaaS\n");
                fclose($fp);

                return response()->download($rutaSql, $nombreArchivo, [
                    'Content-Type' => 'application/sql',
                ])->deleteFileAfterSend(true);

            } catch (\Throwable $e) {
                return back()->with('error', 'Error al generar el respaldo de MySQL: ' . $e->getMessage());
            }
        }

        return back()->with('error', "El motor de base de datos ({$driverBd}) no cuenta con exportación directa.");
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
     * Restaura una empresa completa al 100% con aislamiento multi-inquilino estricto,
     * remapeo de llaves para evitar colisiones entre talleres y sanitización de archivos.
     */
    public function restaurarEmpresa(Request $request)
    {
        $request->validate([
            'archivo_backup' => ['required', 'file', 'max:102400'], // Máx 100MB
        ], [
            'archivo_backup.required' => 'Debe seleccionar un archivo de copia de seguridad (.zip o .json) para restaurar.',
            'archivo_backup.max' => 'El archivo no debe superar los 100 MB.',
        ]);

        $archivo = $request->file('archivo_backup');
        $extension = strtolower($archivo->getClientOriginalExtension());
        if (!in_array($extension, ['zip', 'json'])) {
            return back()->with('error', 'Formato no permitido. Solo se aceptan archivos .zip o .json.');
        }

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

                // Extensiones estrictamente permitidas para fotos y logos (Protección RCE)
                $extensionesSeguras = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

                // Extraer y restaurar todas las fotos físicas al disco público de forma segura
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $nombreEnZip = $zip->getNameIndex($i);
                    if (str_starts_with($nombreEnZip, 'archivos/') && !str_ends_with($nombreEnZip, '/')) {
                        $rutaRelativaPublic = substr($nombreEnZip, strlen('archivos/'));

                        // Protección contra Directory Traversal (../../)
                        if (str_contains($rutaRelativaPublic, '..') || str_starts_with($rutaRelativaPublic, '/') || str_starts_with($rutaRelativaPublic, '\\')) {
                            continue;
                        }

                        // Validación de extensión permitida
                        $extArchivo = strtolower(pathinfo($rutaRelativaPublic, PATHINFO_EXTENSION));
                        if (!in_array($extArchivo, $extensionesSeguras)) {
                            continue;
                        }

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

        // 3. Transacción atómica de Base de Datos con Remapeo Seguro de IDs (Aislamiento Multi-Tenant)
        DB::transaction(function () use ($empresaData, $datos) {
            // A. Sincronizar o crear Taller
            $taller = null;
            if (!empty($empresaData['email'])) {
                $taller = Taller::withTrashed()->where('email', $empresaData['email'])->first();
            }
            if (!$taller && !empty($empresaData['id'])) {
                $posibleTaller = Taller::withTrashed()->find($empresaData['id']);
                if ($posibleTaller && $posibleTaller->nombre_comercial === $empresaData['nombre_comercial']) {
                    $taller = $posibleTaller;
                }
            }

            $datosTaller = [
                'nombre_comercial' => $empresaData['nombre_comercial'],
                'identificacion_fiscal' => $empresaData['identificacion_fiscal'] ?? null,
                'email' => $empresaData['email'],
                'telefono' => $empresaData['telefono'],
                'direccion' => $empresaData['direccion'] ?? null,
                'ciudad' => $empresaData['ciudad'] ?? 'Bogotá',
                'estado_suscripcion' => $empresaData['estado_suscripcion'] ?? 'activo',
                'fecha_vencimiento_suscripcion' => $empresaData['fecha_vencimiento_suscripcion'] ?? $empresaData['fecha_fin_suscripcion'] ?? now()->addMonth(),
                'prefijo_orden' => $empresaData['prefijo_orden'] ?? 'OT',
                'logo_ruta' => $empresaData['logo_ruta'] ?? $empresaData['url_logo'] ?? null,
                'texto_garantia' => $empresaData['texto_garantia'] ?? null,
                'deleted_at' => null,
            ];

            if ($taller) {
                $taller->update($datosTaller);
            } else {
                $datosTaller['plan_licencia_id'] = $empresaData['plan_licencia_id'] ?? 1;
                $taller = Taller::create($datosTaller);
            }

            // Mapas para traducir IDs originales a los IDs reales generados en la base de datos
            $mapaCategorias = [];
            $mapaClientes = [];
            $mapaEquipos = [];
            $mapaUsuarios = [];
            $mapaOrdenes = [];

            // B. Restaurar Categorías de forma aislada
            if (!empty($datos['categorias'])) {
                foreach ($datos['categorias'] as $cat) {
                    $cat = (array) $cat;
                    $oldId = $cat['id'];
                    unset($cat['id']);
                    $cat['taller_id'] = $taller->id;
                    $cat['deleted_at'] = $cat['deleted_at'] ?? null;

                    $catExistente = DB::table('categorias')
                        ->where('taller_id', $taller->id)
                        ->where('nombre', $cat['nombre'])
                        ->first();

                    if ($catExistente) {
                        DB::table('categorias')->where('id', $catExistente->id)->update($cat);
                        $mapaCategorias[$oldId] = $catExistente->id;
                    } else {
                        $newId = DB::table('categorias')->insertGetId($cat);
                        $mapaCategorias[$oldId] = $newId;
                    }
                }
            }

            // C. Restaurar Usuarios con protección contra elevación de privilegios
            if (!empty($datos['usuarios'])) {
                foreach ($datos['usuarios'] as $u) {
                    $u = (array) $u;
                    $oldId = $u['id'];
                    unset($u['id']);
                    $u['taller_id'] = $taller->id;
                    $u['deleted_at'] = $u['deleted_at'] ?? null;

                    // Bloquear asignación de rol super_administrador desde respaldos de taller
                    if (($u['rol'] ?? '') === 'super_administrador') {
                        $u['rol'] = 'administrador';
                    }

                    $userExistente = DB::table('usuarios')->where('email', $u['email'])->first();

                    if ($userExistente && $userExistente->taller_id == $taller->id) {
                        DB::table('usuarios')->where('id', $userExistente->id)->update($u);
                        $mapaUsuarios[$oldId] = $userExistente->id;
                    } elseif (!$userExistente) {
                        $newId = DB::table('usuarios')->insertGetId($u);
                        $mapaUsuarios[$oldId] = $newId;
                    } else {
                        // Si el email pertenece a otro taller, diferenciamos el email restaurado
                        $u['email'] = 'restaurado_' . uniqid() . '_' . $u['email'];
                        $newId = DB::table('usuarios')->insertGetId($u);
                        $mapaUsuarios[$oldId] = $newId;
                    }
                }
            }

            // D. Restaurar Clientes
            if (!empty($datos['clientes'])) {
                foreach ($datos['clientes'] as $cli) {
                    $cli = (array) $cli;
                    $oldId = $cli['id'];
                    unset($cli['id']);
                    $cli['taller_id'] = $taller->id;
                    $cli['deleted_at'] = $cli['deleted_at'] ?? null;

                    $cliExistente = null;
                    if (!empty($cli['identificacion'])) {
                        $cliExistente = DB::table('clientes')
                            ->where('taller_id', $taller->id)
                            ->where('identificacion', $cli['identificacion'])
                            ->first();
                    }

                    if ($cliExistente) {
                        DB::table('clientes')->where('id', $cliExistente->id)->update($cli);
                        $mapaClientes[$oldId] = $cliExistente->id;
                    } else {
                        $newId = DB::table('clientes')->insertGetId($cli);
                        $mapaClientes[$oldId] = $newId;
                    }
                }
            }

            // E. Restaurar Equipos con llaves foráneas remapeadas
            if (!empty($datos['equipos'])) {
                foreach ($datos['equipos'] as $eq) {
                    $eq = (array) $eq;
                    $oldId = $eq['id'];
                    unset($eq['id']);
                    $eq['taller_id'] = $taller->id;
                    $eq['deleted_at'] = $eq['deleted_at'] ?? null;

                    if (!empty($eq['cliente_id']) && isset($mapaClientes[$eq['cliente_id']])) {
                        $eq['cliente_id'] = $mapaClientes[$eq['cliente_id']];
                    }
                    if (!empty($eq['categoria_id']) && isset($mapaCategorias[$eq['categoria_id']])) {
                        $eq['categoria_id'] = $mapaCategorias[$eq['categoria_id']];
                    }

                    $eqExistente = null;
                    if (!empty($eq['numero_serie'])) {
                        $eqExistente = DB::table('equipos')
                            ->where('taller_id', $taller->id)
                            ->where('numero_serie', $eq['numero_serie'])
                            ->first();
                    }

                    if ($eqExistente) {
                        DB::table('equipos')->where('id', $eqExistente->id)->update($eq);
                        $mapaEquipos[$oldId] = $eqExistente->id;
                    } else {
                        $newId = DB::table('equipos')->insertGetId($eq);
                        $mapaEquipos[$oldId] = $newId;
                    }
                }
            }

            // F. Restaurar Órdenes de Trabajo con llaves foráneas remapeadas
            if (!empty($datos['ordenes_trabajo'])) {
                foreach ($datos['ordenes_trabajo'] as $ot) {
                    $ot = (array) $ot;
                    $oldId = $ot['id'];
                    unset($ot['id']);
                    $ot['taller_id'] = $taller->id;
                    $ot['deleted_at'] = $ot['deleted_at'] ?? null;

                    if (!empty($ot['cliente_id']) && isset($mapaClientes[$ot['cliente_id']])) {
                        $ot['cliente_id'] = $mapaClientes[$ot['cliente_id']];
                    }
                    if (!empty($ot['equipo_id']) && isset($mapaEquipos[$ot['equipo_id']])) {
                        $ot['equipo_id'] = $mapaEquipos[$ot['equipo_id']];
                    }
                    if (!empty($ot['tecnico_asignado_id']) && isset($mapaUsuarios[$ot['tecnico_asignado_id']])) {
                        $ot['tecnico_asignado_id'] = $mapaUsuarios[$ot['tecnico_asignado_id']];
                    }

                    $otExistente = null;
                    if (!empty($ot['codigo_orden'])) {
                        $otExistente = DB::table('ordenes_trabajo')
                            ->where('taller_id', $taller->id)
                            ->where('codigo_orden', $ot['codigo_orden'])
                            ->first();
                    }

                    if ($otExistente) {
                        DB::table('ordenes_trabajo')->where('id', $otExistente->id)->update($ot);
                        $mapaOrdenes[$oldId] = $otExistente->id;
                    } else {
                        $newId = DB::table('ordenes_trabajo')->insertGetId($ot);
                        $mapaOrdenes[$oldId] = $newId;
                    }
                }
            }

            // G. Restaurar Evidencias Fotográficas vinculadas a las órdenes correctas
            if (!empty($datos['evidencias_fotograficas'])) {
                foreach ($datos['evidencias_fotograficas'] as $ev) {
                    $ev = (array) $ev;
                    unset($ev['id']);
                    $ev['taller_id'] = $taller->id;

                    if (!empty($ev['orden_trabajo_id']) && isset($mapaOrdenes[$ev['orden_trabajo_id']])) {
                        $ev['orden_trabajo_id'] = $mapaOrdenes[$ev['orden_trabajo_id']];
                    }

                    if (!empty($ev['orden_trabajo_id'])) {
                        DB::table('evidencias_fotograficas')->insert($ev);
                    }
                }
            }
        });

        return redirect()->route('superadmin.respaldos.index')
            ->with('exito', "¡Copia de seguridad restaurada exitosamente para '{$empresaData['nombre_comercial']}'! Se preservó el aislamiento de datos y se restablecieron los archivos físicos.");
    }
}
