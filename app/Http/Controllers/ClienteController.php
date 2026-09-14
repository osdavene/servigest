<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('buscar');

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
     * API de Búsqueda Inteligente en tiempo real (por Nombre, Cédula / Documento, Teléfono, Barrio).
     */
    public function apiBuscar(Request $request)
    {
        $busqueda = trim((string)$request->input('q'));

        $query = Cliente::with('equipos.categoria');

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

    public function create()
    {
        return view('clientes.crear');
    }

    public function store(Request $request)
    {
        $validados = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:150'],
            'identificacion' => ['nullable', 'string', 'max:50'],
            'telefono' => ['required', 'string', 'max:30'],
            'telefono_secundario' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['required', 'string', 'max:255'],
            'barrio' => ['nullable', 'string', 'max:100'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'notas_adicionales' => ['nullable', 'string'],
        ], [
            'nombre_completo.required' => 'El nombre completo del cliente es obligatorio.',
            'telefono.required' => 'El teléfono principal es obligatorio para contacto y WhatsApp.',
            'direccion.required' => 'La dirección es obligatoria.',
        ]);

        $cliente = Cliente::create($validados);

        return redirect()->route('clientes.show', $cliente)
            ->with('exito', "Cliente {$cliente->nombre_completo} registrado exitosamente.");
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['equipos.categoria', 'ordenesTrabajo' => function ($q) {
            $q->with('equipo')->latest('id');
        }]);

        return view('clientes.ver', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.editar', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validados = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:150'],
            'identificacion' => ['nullable', 'string', 'max:50'],
            'telefono' => ['required', 'string', 'max:30'],
            'telefono_secundario' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['required', 'string', 'max:255'],
            'barrio' => ['nullable', 'string', 'max:100'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'notas_adicionales' => ['nullable', 'string'],
        ]);

        $cliente->update($validados);

        return redirect()->route('clientes.show', $cliente)
            ->with('exito', 'Datos del cliente actualizados.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('exito', 'Cliente archivado correctamente.');
    }
}
