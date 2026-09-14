@extends('layouts.invitado')

@section('titulo', 'Suscripción Vencida')

@section('contenido')
<div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-rose-200 p-8 text-center">
    
    <div class="inline-flex w-16 h-16 rounded-2xl bg-rose-100 items-center justify-center text-rose-600 mb-4">
        <i class="fa-solid fa-lock text-3xl"></i>
    </div>

    <h2 class="text-xl font-black text-slate-900">Acceso Restringido</h2>
    <p class="text-sm font-semibold text-rose-600 mt-1">Suscripción de Taller no activa</p>

    <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 text-left">
        <p><strong>Taller:</strong> {{ $taller->nombre_comercial }}</p>
        <p><strong>Estado:</strong> {{ ucfirst($taller->estado_suscripcion) }}</p>
        <p><strong>Fecha de corte:</strong> {{ $taller->fecha_vencimiento_suscripcion?->format('d/m/Y') ?? 'Vencida' }}</p>
    </div>

    <p class="text-xs text-slate-500 mt-4">
        Comuníquese con el administrador central de <strong>ServiGest</strong> para renovar su plan y reactivar el acceso inmediatamente.
    </p>

    <div class="mt-6">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
                Cerrar Sesión
            </button>
        </form>
    </div>

</div>
@endsection
