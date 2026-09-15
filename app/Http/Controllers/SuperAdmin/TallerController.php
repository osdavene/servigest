<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlanLicencia;
use App\Models\Taller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TallerController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string)$request->input('buscar'));
        $estadoSuscripcion = $request->input('estado_suscripcion');

        // Métricas de Talleres SaaS
        $totalTalleres = Taller::count();
        $talleresActivos = Taller::where('estado_suscripcion', 'activo')->count();
        $talleresPrueba = Taller::where('estado_suscripcion', 'periodo_prueba')->count();
        $talleresSuspendidos = Taller::where('estado_suscripcion', 'suspendido')->count();
        $usuariosEnLinea = Usuario::whereNotNull('current_session_id')->where('esta_activo', true)->count();

        $talleres = Taller::withCount(['usuarios', 'clientes', 'equipos', 'ordenesTrabajo'])
            ->when($estadoSuscripcion, fn($query, $est) => $query->where('estado_suscripcion', $est))
            ->when($busqueda, function ($query, $buscar) {
                $query->where('nombre_comercial', 'like', "%{$buscar}%")
                      ->orWhere('identificacion_fiscal', 'like', "%{$buscar}%")
                      ->orWhere('telefono', 'like', "%{$buscar}%")
                      ->orWhere('email', 'like', "%{$buscar}%")
                      ->orWhere('ciudad', 'like', "%{$buscar}%")
                      ->orWhere('slug', 'like', "%{$buscar}%");
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('superadmin.talleres.index', compact(
            'talleres',
            'busqueda',
            'estadoSuscripcion',
            'totalTalleres',
            'talleresActivos',
            'talleresPrueba',
            'talleresSuspendidos',
            'usuariosEnLinea'
        ));
    }

    public function create()
    {
        $planes = PlanLicencia::where('esta_activo', true)->orderBy('dias_duracion')->get();

        return view('superadmin.talleres.crear', compact('planes'));
    }

    public function store(Request $request)
    {
        $validados = $request->validate([
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'identificacion_fiscal' => ['nullable', 'string', 'max:50'],
            'telefono' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150', 'unique:talleres,email'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['required', 'string', 'max:100'],
            'plan_licencia_id' => ['required', 'exists:planes_licencia,id'],
            'nombre_admin' => ['required', 'string', 'max:100'],
            'apellido_admin' => ['required', 'string', 'max:100'],
            'email_admin' => ['required', 'email', 'max:150', 'unique:usuarios,email'],
            'password_admin' => ['required', 'string', 'min:6'],
        ]);

        $plan = PlanLicencia::findOrFail($validados['plan_licencia_id']);

        DB::transaction(function () use ($validados, $plan) {
            $fechaFin = now()->addDays($plan->dias_duracion);

            $taller = Taller::create([
                'plan_licencia_id' => $plan->id,
                'nombre_comercial' => $validados['nombre_comercial'],
                'identificacion_fiscal' => $validados['identificacion_fiscal'] ?? null,
                'telefono' => $validados['telefono'],
                'email' => $validados['email'],
                'direccion' => $validados['direccion'] ?? null,
                'ciudad' => $validados['ciudad'],
                'fecha_inicio_suscripcion' => now(),
                'fecha_fin_suscripcion' => $fechaFin,
                'estado_suscripcion' => 'activo',
            ]);

            Usuario::create([
                'taller_id' => $taller->id,
                'nombre' => $validados['nombre_admin'],
                'apellido' => $validados['apellido_admin'],
                'email' => $validados['email_admin'],
                'password' => Hash::make($validados['password_admin']),
                'rol' => 'administrador',
                'esta_activo' => true,
            ]);
        });

        return redirect()->route('superadmin.talleres.index')
            ->with('exito', 'Taller registrado y cuenta de administrador configurada correctamente.');
    }

    public function show(Taller $taller)
    {
        $taller->load(['planLicencia', 'usuarios', 'clientes' => fn($q) => $q->latest()->limit(5), 'ordenesTrabajo' => fn($q) => $q->latest()->limit(5)]);
        return view('superadmin.talleres.ver', compact('taller'));
    }

    public function edit(Taller $taller)
    {
        $planes = PlanLicencia::all();
        return view('superadmin.talleres.editar', compact('taller', 'planes'));
    }

    public function update(Request $request, Taller $taller)
    {
        $validados = $request->validate([
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'identificacion_fiscal' => ['nullable', 'string', 'max:50'],
            'telefono' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150'],
            'ciudad' => ['required', 'string', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado_suscripcion' => ['required', 'in:activo,periodo_prueba,suspendido,cancelado'],
            'fecha_fin_suscripcion' => ['required', 'date'],
        ]);

        $taller->update($validados);

        return redirect()->route('superadmin.talleres.index')
            ->with('exito', 'Taller actualizado correctamente.');
    }

    public function destroy(Taller $taller)
    {
        $taller->delete();

        return redirect()->route('superadmin.talleres.index')
            ->with('exito', 'Taller archivado correctamente.');
    }

    public function toggleEstado(Taller $taller)
    {
        $nuevoEstado = ($taller->estado_suscripcion === 'activo' || $taller->estado_suscripcion === 'periodo_prueba')
            ? 'suspendido'
            : 'activo';

        $taller->update(['estado_suscripcion' => $nuevoEstado]);

        return redirect()->back()
            ->with('exito', "Estado del taller cambiado a {$nuevoEstado}.");
    }

    public function extenderLicencia(Request $request, Taller $taller)
    {
        $dias = (int) $request->input('dias', 30);
        $fechaBase = ($taller->fecha_fin_suscripcion && $taller->fecha_fin_suscripcion->isFuture())
            ? $taller->fecha_fin_suscripcion
            : now();

        $taller->update([
            'fecha_fin_suscripcion' => $fechaBase->addDays($dias),
            'estado_suscripcion' => 'activo',
        ]);

        return redirect()->back()
            ->with('exito', "Licencia extendida por {$dias} días adicionales.");
    }
}
