<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\OrdenTrabajo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrdenTrabajoController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->input('estado');
        $tecnicoId = $request->input('tecnico_id');
        $tipoUbicacion = $request->input('tipo_ubicacion');
        $busqueda = trim((string)$request->input('buscar'));

        $ordenes = OrdenTrabajo::with(['cliente', 'equipo', 'tecnico'])
            ->when($estado, fn($q, $e) => $q->where('estado', $e))
            ->when($tecnicoId, fn($q, $t) => $q->where('tecnico_asignado_id', $t))
            ->when($tipoUbicacion, fn($q, $u) => $q->where('tipo_ubicacion', $u))
            ->when($busqueda, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('codigo_orden', 'like', "%{$buscar}%")
                      ->orWhere('problema_reportado', 'like', "%{$buscar}%")
                      ->orWhere('diagnostico', 'like', "%{$buscar}%")
                      ->orWhere('repuestos_usados', 'like', "%{$buscar}%")
                      ->orWhereHas('cliente', fn($qc) => $qc->where('nombre_completo', 'like', "%{$buscar}%")
                                                            ->orWhere('identificacion', 'like', "%{$buscar}%")
                                                            ->orWhere('telefono', 'like', "%{$buscar}%"))
                      ->orWhereHas('equipo', fn($qe) => $qe->where('marca', 'like', "%{$buscar}%")
                                                           ->orWhere('modelo', 'like', "%{$buscar}%")
                                                           ->orWhere('numero_serie', 'like', "%{$buscar}%"))
                      ->orWhereHas('tecnico', fn($qt) => $qt->where('nombre', 'like', "%{$buscar}%")
                                                            ->orWhere('apellido', 'like', "%{$buscar}%"));
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $tecnicos = Usuario::whereIn('rol', ['tecnico', 'administrador'])->orderBy('nombre')->get();

        return view('ordenes.index', compact('ordenes', 'tecnicos', 'estado', 'tecnicoId', 'busqueda', 'tipoUbicacion'));
    }

    public function create(Request $request)
    {
        $clienteId = $request->input('cliente_id');
        $equipoId = $request->input('equipo_id');

        if ($equipoId && !$clienteId) {
            $eq = Equipo::find($equipoId);
            if ($eq) {
                $clienteId = $eq->cliente_id;
            }
        }

        $clientes = Cliente::with(['equipos.categoria'])->orderBy('nombre_completo')->get();
        $equipos = $clienteId 
            ? Equipo::where('cliente_id', $clienteId)->get() 
            : Equipo::with('cliente')->get();
        $tecnicos = Usuario::whereIn('rol', ['tecnico', 'administrador'])->orderBy('nombre')->get();

        return view('ordenes.crear', compact('clientes', 'equipos', 'tecnicos', 'clienteId', 'equipoId'));
    }

    public function store(Request $request)
    {
        $validados = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'equipo_id' => ['required', 'exists:equipos,id'],
            'tecnico_asignado_id' => ['nullable', 'exists:usuarios,id'],
            'tipo_ubicacion' => ['required', 'in:servicio_en_domicilio,ingresado_al_taller'],
            'problema_reportado' => ['required', 'string'],
            'diagnostico' => ['nullable', 'string'],
            'fecha_promesa' => ['nullable', 'date'],
            'costo_mano_obra' => ['nullable', 'numeric', 'min:0'],
            'costo_repuestos' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validados['estado'] = 'pendiente';
        $manoObra = (float)($validados['costo_mano_obra'] ?? 0);
        $repuestos = (float)($validados['costo_repuestos'] ?? 0);
        $validados['costo_total'] = $manoObra + $repuestos;

        $orden = OrdenTrabajo::create($validados);

        return redirect()->route('ordenes.show', $orden)
            ->with('exito', "Orden de trabajo {$orden->codigo_orden} creada exitosamente.");
    }

    public function show(OrdenTrabajo $orden)
    {
        $orden->load(['cliente', 'equipo.categoria', 'tecnico', 'evidencias']);

        return view('ordenes.ver', compact('orden'));
    }

    public function edit(OrdenTrabajo $orden)
    {
        $tecnicos = Usuario::whereIn('rol', ['tecnico', 'administrador'])->orderBy('nombre')->get();

        return view('ordenes.editar', compact('orden', 'tecnicos'));
    }

    public function update(Request $request, OrdenTrabajo $orden)
    {
        $validados = $request->validate([
            'tecnico_asignado_id' => ['nullable', 'exists:usuarios,id'],
            'tipo_ubicacion' => ['required', 'in:servicio_en_domicilio,ingresado_al_taller'],
            'estado' => ['required', 'in:pendiente,en_proceso,finalizado,entregado,cancelado'],
            'problema_reportado' => ['required', 'string'],
            'diagnostico' => ['nullable', 'string'],
            'procedimiento_realizado' => ['nullable', 'string'],
            'repuestos_usados' => ['nullable', 'string'],
            'costo_mano_obra' => ['nullable', 'numeric', 'min:0'],
            'costo_repuestos' => ['nullable', 'numeric', 'min:0'],
            'fecha_promesa' => ['nullable', 'date'],
            'fecha_finalizacion' => ['nullable', 'date'],
            'firma_canvas' => ['nullable', 'string'],
            'nombre_firmante' => ['nullable', 'string', 'max:150'],
        ]);

        $manoObra = (float)($validados['costo_mano_obra'] ?? 0);
        $repuestos = (float)($validados['costo_repuestos'] ?? 0);
        $validados['costo_total'] = $manoObra + $repuestos;

        if ($validados['estado'] === 'finalizado' && empty($orden->fecha_finalizacion) && empty($validados['fecha_finalizacion'])) {
            $validados['fecha_finalizacion'] = now();
        }

        // Si se envió la firma digital en Base64 desde el Canvas HTML5
        if (!empty($request->input('firma_canvas'))) {
            $imagenBase64 = $request->input('firma_canvas');
            if (preg_match('/^data:image\/(\w+);base64,/', $imagenBase64, $tipo)) {
                $datos = substr($imagenBase64, strpos($imagenBase64, ',') + 1);
                $datosDecodificados = base64_decode($datos);
                
                $extension = strtolower($tipo[1]);
                $nombreArchivo = "firmas/{$orden->taller_id}/orden_{$orden->id}_firma_" . time() . ".{$extension}";
                
                Storage::disk('public')->put($nombreArchivo, $datosDecodificados);
                $validados['ruta_firma_cliente'] = $nombreArchivo;
                $validados['fecha_firma'] = now();
            }
        }

        $orden->update($validados);

        // Si la orden se finalizó, actualizar la fecha de último servicio del equipo y recalcular próximo mantenimiento
        if ($orden->estado === 'finalizado' || $orden->estado === 'entregado') {
            $equipo = $orden->equipo;
            if ($equipo) {
                $equipo->fecha_ultimo_servicio = now()->toDateString();
                if ($equipo->categoria && $equipo->categoria->requiere_mantenimiento_preventivo && $equipo->categoria->intervalo_mantenimiento_dias) {
                    $equipo->fecha_proximo_mantenimiento = now()->addDays($equipo->categoria->intervalo_mantenimiento_dias)->toDateString();
                }
                $equipo->save();
            }
        }

        // Si se subió foto de cierre / cómo se devuelve el equipo
        if ($request->hasFile('foto_cierre')) {
            $carpetaDestino = "evidencias/{$orden->taller_id}/{$orden->id}";
            $rutaOptimizada = \App\Services\OptimizadorImagenes::optimizarYGuardar($request->file('foto_cierre'), $carpetaDestino);

            \App\Models\EvidenciaFotografica::create([
                'taller_id' => $orden->taller_id,
                'orden_trabajo_id' => $orden->id,
                'ruta_imagen' => $rutaOptimizada,
                'etiqueta' => 'como_se_devuelve',
                'descripcion' => $request->input('descripcion_foto_cierre') ?: 'Estado final del equipo al momento de entrega y cierre.',
            ]);
        }

        return redirect()->route('ordenes.show', $orden)
            ->with('exito', 'Orden de trabajo actualizada correctamente.');
    }

    public function destroy(OrdenTrabajo $orden)
    {
        $orden->delete();

        return redirect()->route('ordenes.index')
            ->with('exito', 'Orden de trabajo archivada en el histórico.');
    }
}
