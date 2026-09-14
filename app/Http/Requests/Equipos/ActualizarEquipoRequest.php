<?php

declare(strict_types=1);

namespace App\Http\Requests\Equipos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarEquipoRequest extends FormRequest
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
            'categoria_id' => [
                'required',
                'integer',
                Rule::exists('categorias', 'id')->where('taller_id', $tallerId),
            ],
            'marca' => ['required', 'string', 'max:80'],
            'modelo' => ['required', 'string', 'max:100'],
            'numero_serie' => ['nullable', 'string', 'max:100'],
            'observaciones_fisicas' => ['nullable', 'string', 'max:1000'],
            'fecha_ultimo_servicio' => ['nullable', 'date'],
            'fecha_proximo_mantenimiento' => ['nullable', 'date'],
        ];
    }
}
