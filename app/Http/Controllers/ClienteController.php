<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Clientes\ActualizarClienteRequest;
use App\Http\Requests\Clientes\GuardarClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Cliente::class);

        $busqueda = trim((string)$request->input('buscar'));

        $clientes = Cliente::query()
            ->when($busqueda, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre_completo', 'like', "%{$buscar}%")
                      ->orWhere('identificacion', 'like', "%{$buscar}%")
                      ->orWhere('telefono', 'like', "%{$buscar}%")
                      ->orWhere('telefono_secundario', 'like', "%{$buscar}%")
                      ->orWhere('email', 'like', "%{$buscar}%")
                      ->orWhere('barrio', 'like', "%{$buscar}%")
                      ->orWhere('ciudad', 'like', "%{$buscar}%");
                });
            })
            ->withCount('equipos', 'ordenesTrabajo')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('clientes.index', compact('clientes', 'busqueda'));
    }

    /**
     * API de Búsqueda en tiempo real para autocompletados.
     */
    public function apiBuscar(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Cliente::class);

        $busqueda = trim((string)$request->input('q'));

        $query = Cliente::with('equipos.categoria:id,nombre');

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre_completo', 'like', "%{$busqueda}%")
                  ->orWhere('identificacion', 'like', "%{$busqueda}%")
                  ->orWhere('telefono', 'like', "%{$busqueda}%")
                  ->orWhere('telefono_secundario', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%")
                  ->orWhere('direccion', 'like', "%{$busqueda}%")
                  ->orWhere('barrio', 'like', "%{$busqueda}%");
            });
        }

        $clientes = $query->latest('id')->limit(20)->get();

        return response()->json($clientes);
    }

    public function create(): View
    {
        $this->authorize('create', Cliente::class);

        return view('clientes.crear');
    }

    public function store(GuardarClienteRequest $request): RedirectResponse
    {
        $this->authorize('create', Cliente::class);

        $cliente = Cliente::create($request->validated());

        return redirect()->route('clientes.show', $cliente)
            ->with('exito', "Cliente {$cliente->nombre_completo} registrado exitosamente.");
    }

    public function show(Cliente $cliente): View
    {
        $this->authorize('view', $cliente);

        $cliente->load(['equipos.categoria', 'ordenesTrabajo' => function ($q) {
            $q->with('equipo')->latest('id');
        }]);

        return view('clientes.ver', compact('cliente'));
    }

    public function edit(Cliente $cliente): View
    {
        $this->authorize('update', $cliente);

        return view('clientes.editar', compact('cliente'));
    }

    public function update(ActualizarClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $this->authorize('update', $cliente);

        $cliente->update($request->validated());

        return redirect()->route('clientes.show', $cliente)
            ->with('exito', 'Datos del cliente actualizados.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $this->authorize('delete', $cliente);

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('exito', 'Cliente archivado correctamente.');
    }
}
