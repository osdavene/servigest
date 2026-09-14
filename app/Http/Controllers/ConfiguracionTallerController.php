<?php

namespace App\Http\Controllers;

use App\Models\Taller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracionTallerController extends Controller
{
    /**
     * Muestra la vista de configuración y perfil del taller para el administrador del taller.
     */
    public function index()
    {
        $taller = auth()->user()->taller;

        if (!$taller) {
            return redirect()->route('panel.index')->with('error', 'No tiene un taller asociado.');
        }

        return view('configuracion.index', compact('taller'));
    }

    /**
     * Actualiza los datos comerciales, logo y políticas del taller.
     */
    public function update(Request $request)
    {
        $taller = auth()->user()->taller;

        if (!$taller) {
            return redirect()->route('panel.index')->with('error', 'No tiene un taller asociado.');
        }

        $validados = $request->validate([
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'identificacion_fiscal' => ['nullable', 'string', 'max:50'],
            'telefono' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150', 'unique:talleres,email,' . $taller->id],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'texto_garantia' => ['nullable', 'string', 'max:1000'],
            'prefijo_orden' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:3072'], // Max 3MB
        ], [
            'nombre_comercial.required' => 'El nombre del taller es obligatorio.',
            'telefono.required' => 'El teléfono de contacto es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'logo.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'logo.max' => 'El logo no debe superar los 3 MB de tamaño.',
        ]);

        // Procesar subida del Logo de la Empresa
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($taller->logo_ruta && Storage::disk('public')->exists($taller->logo_ruta)) {
                Storage::disk('public')->delete($taller->logo_ruta);
            }

            $validados['logo_ruta'] = \App\Services\OptimizadorImagenes::optimizarYGuardar($request->file('logo'), "logos/{$taller->id}", 800, 90);
        }

        // Sanitizar prefijo de orden (mayúsculas y sin caracteres especiales)
        if (!empty($validados['prefijo_orden'])) {
            $validados['prefijo_orden'] = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', $validados['prefijo_orden']));
        }

        $taller->update($validados);

        return redirect()->route('configuracion.index')
            ->with('exito', 'Los datos y el logo de tu taller fueron actualizados exitosamente.');
    }
}
