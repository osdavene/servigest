<?php

declare(strict_types=1);

namespace App\Http\Requests\Ordenes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarOrdenTrabajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ($this->user()->taller_id !== null || $this->user()->esSuperAdmin());
    }

    public function rules(): array
    {
        $tallerId = $this->user()->taller_id ?? session('taller_id_activo');

        return [
            'tecnico_asignado_id' => [
                'nullable',
                'integer',
                Rule::exists('usuarios', 'id')
                    ->where('taller_id', $tallerId)
                    ->whereIn('rol', ['tecnico', 'administrador']),
            ],
            'tipo_ubicacion' => [
                'required',
                'string',
                Rule::in(['servicio_en_domicilio', 'ingresado_al_taller']),
            ],
            'estado' => [
                'required',
                'string',
                Rule::in(['pendiente', 'en_proceso', 'finalizado', 'entregado', 'cancelado']),
            ],
            'problema_reportado' => ['required', 'string', 'max:2000'],
            'diagnostico' => ['nullable', 'string', 'max:2000'],
            'procedimiento_realizado' => ['nullable', 'string', 'max:2000'],
            'repuestos_usados' => ['nullable', 'string', 'max:1000'],
            'costo_mano_obra' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'costo_repuestos' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'fecha_promesa' => ['nullable', 'date'],
            'fecha_finalizacion' => ['nullable', 'date'],
            'firma_canvas' => ['nullable', 'string'],
            'nombre_firmante' => ['nullable', 'string', 'max:150'],
            'foto_cierre' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'descripcion_foto_cierre' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'tecnico_asignado_id.exists' => 'El técnico seleccionado no pertenece a su empresa.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'tipo_ubicacion.in' => 'La modalidad de ubicación debe ser en taller o domicilio.',
        ];
    }
}
