<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioSuperAdminController extends Controller
{
    /**
     * Lista todos los usuarios con rol de Super Administrador.
     */
    public function index(Request $request)
    {
        $busqueda = trim((string)$request->input('buscar'));

        $superadmins = Usuario::where('rol', 'super_administrador')
            ->when($busqueda, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                      ->orWhere('apellido', 'like', "%{$buscar}%")
                      ->orWhere('email', 'like', "%{$buscar}%")
                      ->orWhere('telefono', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('nombre')
            ->get();

        return view('superadmin.usuarios.index', compact('superadmins', 'busqueda'));
    }

    /**
     * Muestra el formulario para registrar un nuevo Super Administrador.
     */
    public function create()
    {
        return view('superadmin.usuarios.crear');
    }

    /**
     * Guarda el nuevo Super Administrador en la plataforma.
     */
    public function store(Request $request)
    {
        $validados = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:usuarios,email'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya está registrado en el sistema.',
            'password.required' => 'La contraseña maestra es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        Usuario::create([
            'taller_id' => null, // Exclusivo global SaaS
            'nombre' => $validados['nombre'],
            'apellido' => $validados['apellido'],
            'email' => $validados['email'],
            'telefono' => $validados['telefono'] ?? null,
            'password' => Hash::make($validados['password']),
            'rol' => 'super_administrador',
            'esta_activo' => true,
        ]);

        return redirect()->route('superadmin.usuarios.index')
            ->with('exito', 'Nuevo Super Administrador registrado exitosamente.');
    }

    /**
     * Muestra el formulario de edición y cambio de contraseña.
     */
    public function edit(Usuario $usuario)
    {
        if (!$usuario->esSuperAdmin()) {
            abort(404, 'Usuario no encontrado.');
        }

        return view('superadmin.usuarios.editar', compact('usuario'));
    }

    /**
     * Actualiza los datos y/o contraseña del Super Administrador.
     */
    public function update(Request $request, Usuario $usuario)
    {
        if (!$usuario->esSuperAdmin()) {
            abort(404, 'Usuario no encontrado.');
        }

        $validados = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'esta_activo' => ['nullable', 'boolean'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya pertenece a otro usuario.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $datos = [
            'nombre' => $validados['nombre'],
            'apellido' => $validados['apellido'],
            'email' => $validados['email'],
            'telefono' => $validados['telefono'] ?? null,
        ];

        // Solo permitir desactivar si no es su propio usuario
        if ($usuario->id !== auth()->id()) {
            $datos['esta_activo'] = $request->boolean('esta_activo', true);
        }

        if (!empty($validados['password'])) {
            $datos['password'] = Hash::make($validados['password']);
        }

        $usuario->update($datos);

        return redirect()->route('superadmin.usuarios.index')
            ->with('exito', "Datos y credenciales de {$usuario->nombre_completo} actualizados correctamente.");
    }

    /**
     * Desactiva / Elimina un Super Administrador con protecciones.
     */
    public function destroy(Usuario $usuario)
    {
        if (!$usuario->esSuperAdmin()) {
            abort(404);
        }

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de Super Administrador activa.');
        }

        $totalSuperadmins = Usuario::where('rol', 'super_administrador')->where('esta_activo', true)->count();
        if ($totalSuperadmins <= 1) {
            return back()->with('error', 'No puedes eliminar el único Super Administrador activo de la plataforma.');
        }

        $usuario->delete();

        return redirect()->route('superadmin.usuarios.index')
            ->with('exito', "Cuenta de Super Administrador eliminada correctamente.");
    }
}
