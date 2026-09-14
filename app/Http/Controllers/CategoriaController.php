<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string)$request->input('buscar'));

        $categorias = Categoria::withCount('equipos')
            ->when($busqueda, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                      ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('nombre')
            ->get();

        return view('categorias.index', compact('categorias', 'busqueda'));
    }

    public function store(Request $request)
    {
        $validados = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'requiere_mantenimiento_preventivo' => ['nullable', 'boolean'],
            'intervalo_mantenimiento_dias' => ['nullable', 'integer', 'min:1'],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
        ]);

        $validados['requiere_mantenimiento_preventivo'] = $request->boolean('requiere_mantenimiento_preventivo');

        Categoria::create($validados);

        return redirect()->route('categorias.index')
            ->with('exito', 'Categoría creada exitosamente.');
    }

    public function update(Request $request, Categoria $categoria)
    {
        $validados = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'requiere_mantenimiento_preventivo' => ['nullable', 'boolean'],
            'intervalo_mantenimiento_dias' => ['nullable', 'integer', 'min:1'],
        ]);

        $validados['requiere_mantenimiento_preventivo'] = $request->boolean('requiere_mantenimiento_preventivo');

        $categoria->update($validados);

        return redirect()->route('categorias.index')
            ->with('exito', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->equipos()->exists()) {
            return back()->with('error', 'No se puede eliminar la categoría porque tiene equipos asociados.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('exito', 'Categoría eliminada correctamente.');
    }
}
