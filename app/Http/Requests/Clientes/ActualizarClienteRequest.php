<?php

declare(strict_types=1);

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ($this->user()->taller_id !== null || $this->user()->esSuperAdmin());
    }

    public function rules(): array
    {
        $tallerId = $this->user()->taller_id ?? session('taller_id_activo');
        $clienteId = $this->route('cliente')?->id ?? $this->route('cliente');

        return [
            'nombre_completo' => ['required', 'string', 'max:150'],
            'identificacion' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('clientes', 'identificacion')
                    ->where('taller_id', $tallerId)
                    ->ignore($clienteId),
            ],
            'telefono' => ['required', 'string', 'max:30'],
            'telefono_secundario' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'barrio' => ['nullable', 'string', 'max:100'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'notas_adicionales' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
