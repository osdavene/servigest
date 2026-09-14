<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioTallerController extends Controller
{
    /**
     * Lista todos los técnicos y administradores pertenecientes al taller actual con búsqueda reactiva.
     */
    public function index(Request $request)
    {
        $taller = auth()->user()->taller;
        $busqueda = trim((string)$request->input('buscar'));

        $usuarios = Usuario::where('taller_id', $taller->id)
            ->when($busqueda, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                      ->orWhere('apellido', 'like', "%{$buscar}%")
                      ->orWhere('email', 'like', "%{$buscar}%")
                      ->orWhere('telefono', 'like', "%{$buscar}%")
                      ->orWhere('rol', 'like', "%{$buscar}%");
                });
            })
            ->withCount('ordenesAsignadas')
            ->orderBy('nombre')
            ->get();

        $limiteUsuarios = $taller->planLicencia?->limite_usuarios;
        $totalUsuarios = Usuario::where('taller_id', $taller->id)->where('esta_activo', true)->count();
        $limiteAlcanzado = $limiteUsuarios && $totalUsuarios >= $limiteUsuarios;

        return view('usuarios_taller.index', compact('usuarios', 'taller', 'limiteUsuarios', 'totalUsuarios', 'limiteAlcanzado', 'busqueda'));
    }

    /**
     * Muestra el formulario para crear un nuevo técnico o administrador.
     */
    public function create()
    {
        $taller = auth()->user()->taller;
        $limiteUsuarios = $taller->planLicencia?->limite_usuarios;
        $totalUsuarios = Usuario::where('taller_id', $taller->id)->where('esta_activo', true)->count();

        if ($limiteUsuarios && $totalUsuarios >= $limiteUsuarios) {
            return redirect()->route('personal.index')
                ->with('error', "Has alcanzado el límite máximo de {$limiteUsuarios} usuarios permitidos en tu plan actual. Contacta al soporte para ampliar tu licencia.");
        }

        return view('usuarios_taller.crear', compact('taller'));
    }

    /**
     * Guarda el nuevo usuario asignado estrictamente al taller del administrador autenticado.
     */
    public function store(Request $request)
    {
        $taller = auth()->user()->taller;
        $limiteUsuarios = $taller->planLicencia?->limite_usuarios;
        $totalUsuarios = Usuario::where('taller_id', $taller->id)->where('esta_activo', true)->count();

        if ($limiteUsuarios && $totalUsuarios >= $limiteUsuarios) {
            return redirect()->route('personal.index')
                ->with('error', 'Límite de usuarios alcanzado para tu plan.');
        }

        $validados = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:usuarios,email'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
            'rol' => ['required', 'in:tecnico,administrador'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya está registrado en el sistema.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'rol.required' => 'Debe seleccionar un rol.',
        ]);

        Usuario::create([
            'taller_id' => $taller->id,
            'nombre' => $validados['nombre'],
            'apellido' => $validados['apellido'],
            'email' => $validados['email'],
            'telefono' => $validados['telefono'] ?? null,
            'password' => Hash::make($validados['password']),
            'rol' => $validados['rol'],
            'esta_activo' => true,
        ]);

        return redirect()->route('personal.index')
            ->with('exito', 'Nuevo colaborador registrado exitosamente en tu taller.');
    }

    /**
     * Formulario de edición de un colaborador del taller.
     */
    public function edit(Usuario $usuario)
    {
        $personal = $usuario;

        // Asegurar que solo pueda editar usuarios de su propio taller
        if ((int)$personal->taller_id !== (int)auth()->user()->taller_id) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        return view('usuarios_taller.editar', compact('personal', 'usuario'));
    }

    /**
     * Actualiza los datos o contraseña del colaborador.
     */
    public function update(Request $request, Usuario $usuario)
    {
        $personal = $usuario;

        if ((int)$personal->taller_id !== (int)auth()->user()->taller_id) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        $validados = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($personal->id)],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:6'],
            'rol' => ['required', 'in:tecnico,administrador'],
            'esta_activo' => ['nullable', 'boolean'],
        ]);

        $datosActualizar = [
            'nombre' => $validados['nombre'],
            'apellido' => $validados['apellido'],
            'email' => $validados['email'],
            'telefono' => $validados['telefono'] ?? null,
            'rol' => $validados['rol'],
            'esta_activo' => $request->boolean('esta_activo', true),
        ];

        if (!empty($validados['password'])) {
            $datosActualizar['password'] = Hash::make($validados['password']);
        }

        $personal->update($datosActualizar);

        return redirect()->route('personal.index')
            ->with('exito', 'Datos del colaborador actualizados correctamente.');
    }

    /**
     * Desactiva / Borrado lógico de un colaborador del taller.
     */
    public function destroy(Usuario $usuario)
    {
        $personal = $usuario;

        if ((int)$personal->taller_id !== (int)auth()->user()->taller_id) {
            abort(403, 'No tienes permiso.');
        }

        if ($personal->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de usuario activa.');
        }

        $personal->delete();

        return redirect()->route('personal.index')
            ->with('exito', 'Usuario desactivado correctamente.');
    }
}
