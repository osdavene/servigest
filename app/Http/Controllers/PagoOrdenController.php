<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OrdenTrabajo;
use App\Models\PagoOrden;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PagoOrdenController extends Controller
{
    /**
     * Registra un nuevo abono o pago en la orden de trabajo.
     */
    public function store(Request $request, OrdenTrabajo $orden): RedirectResponse
    {
        $usuario = auth()->user();

        // Validar que el usuario pertenezca al taller de la orden
        if (!$usuario || (!$usuario->esSuperAdmin() && $orden->taller_id !== $usuario->taller_id)) {
            abort(403, 'No tienes permiso para registrar pagos en esta orden.');
        }

        $validados = $request->validate([
            'monto' => ['required', 'numeric', 'min:100'],
            'metodo_pago' => ['required', 'string', 'in:efectivo,nequi,daviplata,transferencia_bancaria,tarjeta,otro'],
            'referencia' => ['nullable', 'string', 'max:100'],
            'notas' => ['nullable', 'string', 'max:255'],
            'fecha_pago' => ['nullable', 'date'],
        ], [
            'monto.required' => 'El monto del abono o pago es obligatorio.',
            'monto.numeric' => 'El monto debe ser un valor numérico.',
            'monto.min' => 'El monto mínimo a registrar es de $100.',
            'metodo_pago.required' => 'Debe seleccionar el medio de pago.',
            'metodo_pago.in' => 'El medio de pago seleccionado no es válido.',
        ]);

        $pago = PagoOrden::create([
            'taller_id' => $orden->taller_id,
            'orden_trabajo_id' => $orden->id,
            'usuario_id' => $usuario->id,
            'monto' => $validados['monto'],
            'metodo_pago' => $validados['metodo_pago'],
            'referencia' => $validados['referencia'] ?? null,
            'notas' => $validados['notas'] ?? null,
            'fecha_pago' => !empty($validados['fecha_pago']) ? $validados['fecha_pago'] : now(),
        ]);

        $montoFormateado = number_format((float) $pago->monto, 0, ',', '.');
        $metodoTexto = $pago->nombre_metodo;

        return redirect()->route('ordenes.show', $orden)
            ->with('exito', "¡Abono de \${$montoFormateado} ({$metodoTexto}) registrado correctamente!");
    }

    /**
     * Anula un registro de abono / pago.
     */
    public function destroy(PagoOrden $pago): RedirectResponse
    {
        $usuario = auth()->user();

        if (!$usuario || (!$usuario->esSuperAdmin() && $pago->taller_id !== $usuario->taller_id)) {
            abort(403, 'No tienes permiso para anular este pago.');
        }

        $orden = $pago->ordenTrabajo;
        $montoFormateado = number_format((float) $pago->monto, 0, ',', '.');
        $pago->delete();

        return redirect()->route('ordenes.show', $orden)
            ->with('exito', "El registro de abono por \${$montoFormateado} ha sido anulado.");
    }
}
