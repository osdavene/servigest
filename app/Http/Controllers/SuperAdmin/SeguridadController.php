<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\RegistroAcceso;
use App\Models\Taller;
use App\Models\Usuario;
use App\Services\AuditoriaAccesoService;
use Illuminate\Http\Request;

class SeguridadController extends Controller
{
    public function __construct(
        protected AuditoriaAccesoService $auditoriaService
    ) {}

    /**
     * Muestra la bitácora global de accesos y seguridad de todo el SaaS.
     */
    public function index(Request $request)
    {
        $busqueda = trim((string)$request->input('buscar'));
        $tallerId = $request->input('taller_id');
        $estado = $request->input('estado');

        // Métricas globales en tiempo real
        $totalLoginsHoy = RegistroAcceso::whereDate('fecha_ingreso', today())
            ->where('estado', 'exitoso')
            ->count();

        $sesionesActivas = Usuario::whereNotNull('current_session_id')
            ->where('esta_activo', true)
            ->count();

        $intentosFallidosHoy = RegistroAcceso::whereDate('fecha_ingreso', today())
            ->where('estado', 'fallido')
            ->count();

        $talleresConectadosHoy = RegistroAcceso::whereDate('fecha_ingreso', today())
            ->whereNotNull('taller_id')
            ->distinct('taller_id')
            ->count('taller_id');

        // Consulta de registros con filtros
        $registrosAcceso = RegistroAcceso::with(['usuario', 'taller'])
            ->when($tallerId, function ($query, $id) {
                if ($id === 'superadmin') {
                    $query->whereNull('taller_id');
                } else {
                    $query->where('taller_id', $id);
                }
            })
            ->when($estado, fn($query, $est) => $query->where('estado', $est))
            ->when($busqueda, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('email_ingresado', 'like', "%{$buscar}%")
                      ->orWhere('ip_address', 'like', "%{$buscar}%")
                      ->orWhere('ciudad', 'like', "%{$buscar}%")
                      ->orWhere('pais', 'like', "%{$buscar}%")
                      ->orWhere('dispositivo', 'like', "%{$buscar}%")
                      ->orWhere('navegador', 'like', "%{$buscar}%")
                      ->orWhere('sistema_operativo', 'like', "%{$buscar}%")
                      ->orWhereHas('usuario', fn($qu) => $qu->where('nombre', 'like', "%{$buscar}%")->orWhere('apellido', 'like', "%{$buscar}%"))
                      ->orWhereHas('taller', fn($qt) => $qt->where('nombre_comercial', 'like', "%{$buscar}%"));
                });
            })
            ->latest('fecha_ingreso')
            ->paginate(25)
            ->withQueryString();

        $talleres = Taller::orderBy('nombre_comercial')->get(['id', 'nombre_comercial']);

        return view('superadmin.seguridad.index', compact(
            'registrosAcceso',
            'talleres',
            'totalLoginsHoy',
            'sesionesActivas',
            'intentosFallidosHoy',
            'talleresConectadosHoy',
            'busqueda',
            'tallerId',
            'estado'
        ));
    }

    /**
     * Cierra de forma forzosa la sesión remota de cualquier usuario del SaaS.
     */
    public function desconectar(Usuario $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desconectar tu propia sesión maestra de SuperAdmin.');
        }

        $this->auditoriaService->forzarCierreSesion($usuario);

        return back()->with('exito', "Se ha cerrado la sesión activa del usuario {$usuario->nombre_completo} ({$usuario->email}) correctamente.");
    }
}
