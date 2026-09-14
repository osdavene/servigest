<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Equipo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string)$request->input('buscar'));
        $categoriaId = $request->input('categoria_id');
        $soloMantenimiento = $request->boolean('alerta_mantenimiento');

        $equipos = Equipo::with(['cliente', 'categoria'])
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
            ->when($categoriaId, function ($query, $catId) {
                $query->where('categoria_id', $catId);
            })
            ->when($soloMantenimiento, function ($query) {
                $query->whereNotNull('fecha_proximo_mantenimiento')
                      ->where('fecha_proximo_mantenimiento', '<=', now()->addDays(7));
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $categorias = Categoria::orderBy('nombre')->get();

        return view('equipos.index', compact('equipos', 'categorias', 'busqueda', 'categoriaId', 'soloMantenimiento'));
    }

    public function show(Equipo $equipo)
    {
        $equipo->load([
            'cliente',
            'categoria',
            'ordenesTrabajo' => function ($query) {
                $query->with(['tecnico', 'evidencias'])->latest('id');
            }
        ]);

        return view('equipos.ver', compact('equipo'));
    }

    public function create(Request $request)
    {
        $clienteSeleccionadoId = $request->input('cliente_id');
        $clientes = Cliente::orderBy('nombre_completo')->get();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('equipos.crear', compact('clientes', 'categorias', 'clienteSeleccionadoId'));
    }

    public function store(Request $request)
    {
        $validados = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'marca' => ['required', 'string', 'max:80'],
            'modelo' => ['required', 'string', 'max:100'],
            'numero_serie' => ['nullable', 'string', 'max:100'],
            'observaciones_fisicas' => ['nullable', 'string'],
            'fecha_ultimo_servicio' => ['nullable', 'date'],
            'fecha_proximo_mantenimiento' => ['nullable', 'date'],
        ], [
            'cliente_id.required' => 'Debe asociar el equipo a un cliente.',
            'categoria_id.required' => 'Debe seleccionar una categoría.',
            'marca.required' => 'La marca es obligatoria.',
            'modelo.required' => 'El modelo es obligatorio.',
        ]);

        // Si no especificó fecha de próximo mantenimiento pero la categoría tiene intervalo, se calcula
        if (empty($validados['fecha_proximo_mantenimiento'])) {
            $categoria = Categoria::find($validados['categoria_id']);
            if ($categoria && $categoria->requiere_mantenimiento_preventivo && $categoria->intervalo_mantenimiento_dias) {
                $fechaBase = !empty($validados['fecha_ultimo_servicio']) ? Carbon::parse($validados['fecha_ultimo_servicio']) : now();
                $validados['fecha_proximo_mantenimiento'] = $fechaBase->copy()->addDays($categoria->intervalo_mantenimiento_dias)->toDateString();
            }
        }

        $equipo = Equipo::create($validados);

        return redirect()->route('equipos.show', $equipo)
            ->with('exito', "Equipo {$equipo->marca} {$equipo->modelo} registrado exitosamente.");
    }

    public function edit(Equipo $equipo)
    {
        $clientes = Cliente::orderBy('nombre_completo')->get();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('equipos.editar', compact('equipo', 'clientes', 'categorias'));
    }

    public function update(Request $request, Equipo $equipo)
    {
        $validados = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'marca' => ['required', 'string', 'max:80'],
            'modelo' => ['required', 'string', 'max:100'],
            'numero_serie' => ['nullable', 'string', 'max:100'],
            'observaciones_fisicas' => ['nullable', 'string'],
            'fecha_ultimo_servicio' => ['nullable', 'date'],
            'fecha_proximo_mantenimiento' => ['nullable', 'date'],
        ]);

        $equipo->update($validados);

        return redirect()->route('equipos.show', $equipo)
            ->with('exito', 'Equipo actualizado correctamente.');
    }

    public function destroy(Equipo $equipo)
    {
        $equipo->delete();

        return redirect()->route('equipos.index')
            ->with('exito', 'Equipo archivado en el histórico correctamente.');
    }
}
