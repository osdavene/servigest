<?php

declare(strict_types=1);

namespace App\Http\Requests\Ordenes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarOrdenTrabajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ($this->user()->taller_id !== null || $this->user()->esSuperAdmin());
    }

    public function rules(): array
    {
        $tallerId = $this->user()->taller_id ?? session('taller_id_activo');

        return [
            'cliente_id' => [
                'required',
                'integer',
                Rule::exists('clientes', 'id')->where('taller_id', $tallerId),
            ],
            'equipo_id' => [
                'required',
                'integer',
                Rule::exists('equipos', 'id')->where('taller_id', $tallerId),
            ],
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
            'problema_reportado' => ['required', 'string', 'max:2000'],
            'accesorios_incluidos' => ['nullable', 'string', 'max:500'],
            'diagnostico' => ['nullable', 'string', 'max:2000'],
            'fecha_promesa' => ['nullable', 'date'],
            'costo_mano_obra' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'costo_repuestos' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'foto_inicial' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'], // 15MB max
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe asociar un cliente válido a la orden.',
            'cliente_id.exists' => 'El cliente seleccionado no pertenece a su taller.',
            'equipo_id.required' => 'Debe seleccionar un equipo a reparar.',
            'equipo_id.exists' => 'El equipo seleccionado no pertenece a su taller.',
            'tecnico_asignado_id.exists' => 'El técnico seleccionado no pertenece a su empresa.',
            'problema_reportado.required' => 'El problema o motivo de ingreso es obligatorio.',
        ];
    }
}
