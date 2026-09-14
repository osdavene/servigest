<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlanLicencia;
use App\Models\Taller;
use Illuminate\Http\Request;

class PlanLicenciaController extends Controller
{
    public function index()
    {
        $planes = PlanLicencia::withCount(['talleres' => function ($query) {
            $query->where('estado_suscripcion', '!=', 'suspendido')
                  ->where(function ($sub) {
                      $sub->whereNull('fecha_vencimiento_suscripcion')
                          ->orWhere('fecha_vencimiento_suscripcion', '>=', now()->toDateString());
                  });
        }])->orderBy('dias_duracion')->get();

        return view('superadmin.licencias.index', compact('planes'));
    }

    public function create()
    {
        return view('superadmin.licencias.crear');
    }

    public function store(Request $request)
    {
        $validados = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'dias_duracion' => ['required', 'integer', 'min:1'],
            'precio' => ['required', 'numeric', 'min:0'],
            'limite_usuarios' => ['nullable', 'integer', 'min:1'],
            'descripcion' => ['nullable', 'string'],
            'es_prueba' => ['nullable', 'boolean'],
            'esta_activo' => ['nullable', 'boolean'],
        ]);

        $validados['es_prueba'] = $request->boolean('es_prueba');
        $validados['esta_activo'] = $request->boolean('esta_activo', true);

        PlanLicencia::create($validados);

        return redirect()->route('superadmin.licencias.index')
            ->with('exito', 'Nuevo plan de licencia creado exitosamente.');
    }

    public function edit(PlanLicencia $licencia)
    {
        // Contar talleres que tienen activa esta licencia actualmente
        $talleresActivosCount = $licencia->talleres()
            ->where('estado_suscripcion', '!=', 'suspendido')
            ->where(function ($sub) {
                $sub->whereNull('fecha_vencimiento_suscripcion')
                    ->orWhere('fecha_vencimiento_suscripcion', '>=', now()->toDateString());
            })->count();

        $estaBloqueada = $talleresActivosCount > 0;

        return view('superadmin.licencias.editar', compact('licencia', 'talleresActivosCount', 'estaBloqueada'));
    }

    public function update(Request $request, PlanLicencia $licencia)
    {
        // Verificar si la licencia está activa en algún taller cliente
        $talleresActivosCount = $licencia->talleres()
            ->where('estado_suscripcion', '!=', 'suspendido')
            ->where(function ($sub) {
                $sub->whereNull('fecha_vencimiento_suscripcion')
                    ->orWhere('fecha_vencimiento_suscripcion', '>=', now()->toDateString());
            })->count();

        if ($talleresActivosCount > 0) {
            return back()->with('error', "ACCESO DENEGADO: La licencia '{$licencia->nombre}' está activa en {$talleresActivosCount} empresa(s). Por protección e integridad contractual, no se puede modificar ni editar mientras esté en uso.");
        }

        $validados = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'dias_duracion' => ['required', 'integer', 'min:1'],
            'precio' => ['required', 'numeric', 'min:0'],
            'limite_usuarios' => ['nullable', 'integer', 'min:1'],
            'descripcion' => ['nullable', 'string'],
            'es_prueba' => ['nullable', 'boolean'],
            'esta_activo' => ['nullable', 'boolean'],
        ]);

        $validados['es_prueba'] = $request->boolean('es_prueba');
        $validados['esta_activo'] = $request->boolean('esta_activo', true);

        $licencia->update($validados);

        return redirect()->route('superadmin.licencias.index')
            ->with('exito', 'Plan de licencia actualizado correctamente.');
    }

    public function destroy(PlanLicencia $licencia)
    {
        // Verificar si la licencia está en uso activo en algún taller
        $talleresActivosCount = $licencia->talleres()
            ->where('estado_suscripcion', '!=', 'suspendido')
            ->where(function ($sub) {
                $sub->whereNull('fecha_vencimiento_suscripcion')
                    ->orWhere('fecha_vencimiento_suscripcion', '>=', now()->toDateString());
            })->count();

        if ($talleresActivosCount > 0) {
            return back()->with('error', "BLOQUEADO: No se puede eliminar la licencia '{$licencia->nombre}' porque está actualmente activa en {$talleresActivosCount} empresa(s).");
        }

        // Ejecutar borrado lógico seguro (SoftDelete)
        $licencia->delete();

        return redirect()->route('superadmin.licencias.index')
            ->with('exito', 'Plan de licencia retirado de forma segura (borrado lógico).');
    }
}
