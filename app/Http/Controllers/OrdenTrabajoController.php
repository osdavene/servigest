<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Ordenes\ActualizarOrdenTrabajoAction;
use App\Actions\Ordenes\CrearOrdenTrabajoAction;
use App\Http\Requests\Ordenes\ActualizarOrdenTrabajoRequest;
use App\Http\Requests\Ordenes\GuardarOrdenTrabajoRequest;
use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\OrdenTrabajo;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrdenTrabajoController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', OrdenTrabajo::class);

        $estado = $request->input('estado');
        $tecnicoId = $request->input('tecnico_id');
        $tipoUbicacion = $request->input('tipo_ubicacion');
        $busqueda = trim((string)$request->input('buscar'));

        $ordenes = OrdenTrabajo::with(['cliente:id,nombre_completo,identificacion,telefono', 'equipo:id,marca,modelo,numero_serie', 'tecnico:id,nombre,apellido'])
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

        $tecnicos = Usuario::whereIn('rol', ['tecnico', 'administrador'])
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido']);

        return view('ordenes.index', compact('ordenes', 'tecnicos', 'estado', 'tecnicoId', 'busqueda', 'tipoUbicacion'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', OrdenTrabajo::class);

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
            : Equipo::with('cliente:id,nombre_completo')->get();
        $tecnicos = Usuario::whereIn('rol', ['tecnico', 'administrador'])->orderBy('nombre')->get(['id', 'nombre', 'apellido', 'rol']);

        return view('ordenes.crear', compact('clientes', 'equipos', 'tecnicos', 'clienteId', 'equipoId'));
    }

    public function store(GuardarOrdenTrabajoRequest $request, CrearOrdenTrabajoAction $action): RedirectResponse
    {
        $this->authorize('create', OrdenTrabajo::class);

        $orden = $action->execute(
            datos: $request->validated(),
            fotoInicial: $request->file('foto_inicial')
        );

        return redirect()->route('ordenes.show', $orden)
            ->with('exito', "Orden de trabajo {$orden->codigo_orden} creada exitosamente.");
    }

    public function show(OrdenTrabajo $orden): View
    {
        $this->authorize('view', $orden);

        $orden->load(['cliente', 'equipo.categoria', 'tecnico', 'evidencias']);

        return view('ordenes.ver', compact('orden'));
    }

    public function edit(OrdenTrabajo $orden): View
    {
        $this->authorize('update', $orden);

        $tecnicos = Usuario::whereIn('rol', ['tecnico', 'administrador'])->orderBy('nombre')->get(['id', 'nombre', 'apellido', 'email']);

        return view('ordenes.editar', compact('orden', 'tecnicos'));
    }

    public function update(
        ActualizarOrdenTrabajoRequest $request,
        OrdenTrabajo $orden,
        ActualizarOrdenTrabajoAction $action
    ): RedirectResponse {
        $this->authorize('update', $orden);

        $action->execute(
            orden: $orden,
            datos: $request->validated(),
            firmaBase64: $request->input('firma_canvas'),
            fotoCierre: $request->file('foto_cierre'),
            descripcionFoto: $request->input('descripcion_foto_cierre')
        );

        return redirect()->route('ordenes.show', $orden)
            ->with('exito', 'Orden de trabajo actualizada correctamente.');
    }

    public function destroy(OrdenTrabajo $orden): RedirectResponse
    {
        $this->authorize('delete', $orden);

        $orden->delete();

        return redirect()->route('ordenes.index')
            ->with('exito', 'Orden de trabajo archivada en el histórico.');
    }
}
