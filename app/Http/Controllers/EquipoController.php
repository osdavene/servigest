<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Equipos\ActualizarEquipoAction;
use App\Actions\Equipos\CrearEquipoAction;
use App\Http\Requests\Equipos\ActualizarEquipoRequest;
use App\Http\Requests\Equipos\GuardarEquipoRequest;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Equipo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipoController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Equipo::class);

        $busqueda = trim((string)$request->input('buscar'));
        $categoriaId = $request->input('categoria_id');
        $soloMantenimiento = $request->boolean('alerta_mantenimiento');

        $equipos = Equipo::with(['cliente:id,nombre_completo,identificacion,telefono', 'categoria:id,nombre'])
            ->withCount('ordenesTrabajo')
            ->when($busqueda, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('marca', 'like', "%{$buscar}%")
                      ->orWhere('modelo', 'like', "%{$buscar}%")
                      ->orWhere('numero_serie', 'like', "%{$buscar}%")
                      ->orWhereHas('cliente', function ($qc) use ($buscar) {
                          $qc->where('nombre_completo', 'like', "%{$buscar}%")
                            ->orWhere('identificacion', 'like', "%{$buscar}%")
                            ->orWhere('telefono', 'like', "%{$buscar}%");
                      });
                });
            })
            ->when($categoriaId, fn($q, $catId) => $q->where('categoria_id', $catId))
            ->when($soloMantenimiento, fn($q) => $q->whereNotNull('fecha_proximo_mantenimiento')->where('fecha_proximo_mantenimiento', '<=', now()->addDays(7)))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);

        return view('equipos.index', compact('equipos', 'categorias', 'busqueda', 'categoriaId', 'soloMantenimiento'));
    }

    public function show(Equipo $equipo): View
    {
        $this->authorize('view', $equipo);

        $equipo->load([
            'cliente',
            'categoria',
            'ordenesTrabajo' => function ($query) {
                $query->with(['tecnico', 'evidencias'])->latest('id');
            }
        ]);

        return view('equipos.ver', compact('equipo'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Equipo::class);

        $clienteSeleccionadoId = $request->input('cliente_id');
        $clientes = Cliente::orderBy('nombre_completo')->get();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('equipos.crear', compact('clientes', 'categorias', 'clienteSeleccionadoId'));
    }

    public function store(GuardarEquipoRequest $request, CrearEquipoAction $action): RedirectResponse
    {
        $this->authorize('create', Equipo::class);

        $equipo = $action->execute($request->validated());

        return redirect()->route('equipos.show', $equipo)
            ->with('exito', "Equipo {$equipo->marca} {$equipo->modelo} registrado exitosamente.");
    }

    public function edit(Equipo $equipo): View
    {
        $this->authorize('update', $equipo);

        $clientes = Cliente::orderBy('nombre_completo')->get();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('equipos.editar', compact('equipo', 'clientes', 'categorias'));
    }

    public function update(ActualizarEquipoRequest $request, Equipo $equipo, ActualizarEquipoAction $action): RedirectResponse
    {
        $this->authorize('update', $equipo);

        $action->execute($equipo, $request->validated());

        return redirect()->route('equipos.show', $equipo)
            ->with('exito', 'Equipo actualizado correctamente.');
    }

    public function destroy(Equipo $equipo): RedirectResponse
    {
        $this->authorize('delete', $equipo);

        $equipo->delete();

        return redirect()->route('equipos.index')
            ->with('exito', 'Equipo archivado en el histórico correctamente.');
    }
}
