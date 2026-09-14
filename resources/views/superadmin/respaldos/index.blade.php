@extends('layouts.app')

@section('titulo', 'Centro de Copias de Seguridad & Restauración')

@section('contenido')
<div>

    <div style="margin-bottom: 24px;">
        <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Centro de Copias de Seguridad & Restauración (SuperAdmin)</h3>
        <p style="font-size: 13px; color: #64748b; margin-top: 4px;">Descarga copias de seguridad de la infraestructura completa o restaura empresas a partir de sus respaldos.</p>
    </div>

    <!-- Grid de Respaldo Global y Restauración Exclusiva -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px; margin-bottom: 32px;">
        
        <!-- Tarjeta de Respaldo Global -->
        <div class="card" style="background: linear-gradient(135deg, #1e1b12 0%, #29210c 100%); border-color: rgba(245, 158, 11, 0.3); color: #ffffff; padding: 24px; margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 12px;">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #ffffff; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <div>
                        <span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border-color: rgba(245, 158, 11, 0.4); font-size: 9.5px;">
                            Infraestructura Completa
                        </span>
                        <h4 style="font-size: 16px; font-weight: 900; color: #ffffff; margin-top: 2px;">Respaldo Global (.sqlite)</h4>
                    </div>
                </div>
                <p style="font-size: 12.5px; color: #d1d5db; line-height: 1.5;">
                    Copia en caliente de toda la base de datos con todos los talleres, planes, usuarios y configuraciones maestras. Tamaño actual: <strong>{{ $tamanoBd }} KB</strong>.
                </p>
            </div>

            <a href="{{ route('superadmin.respaldos.global') }}" class="btn btn-amber" style="margin-top: 20px; padding: 12px; justify-content: center;">
                <i class="fa-solid fa-cloud-arrow-down"></i>
                <span>Descargar Base de Datos Global</span>
            </a>
        </div>

        <!-- Tarjeta de Restauración de Empresa desde Backup -->
        <div class="card" style="border: 2px dashed #0284c7; background: #f0f9ff; padding: 24px; margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 12px;">
                    <div style="width: 48px; height: 48px; background: #0284c7; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #ffffff; box-shadow: 0 8px 20px rgba(2, 132, 199, 0.35);">
                        <i class="fa-solid fa-upload"></i>
                    </div>
                    <div>
                        <span class="badge badge-active" style="font-size: 9.5px;">
                            Exclusivo SuperAdmin
                        </span>
                        <h4 style="font-size: 16px; font-weight: 900; color: #0f172a; margin-top: 2px;">Restaurar Empresa (Restore)</h4>
                    </div>
                </div>
                <p style="font-size: 12.5px; color: #475569; line-height: 1.5;">
                    Sube un archivo de backup <strong>.ZIP</strong> o <strong>.JSON</strong> generado por cualquier taller para reconstruir y sincronizar toda su base de datos y restaurar sus fotos físicas de evidencias al 100%.
                </p>
            </div>

            <form method="POST" action="{{ route('superadmin.respaldos.restaurar') }}" enctype="multipart/form-data" style="margin-top: 16px;" onsubmit="return confirm('¿Confirmas la restauración de este paquete de empresa? Los registros y fotos se sincronizarán en el sistema.');">
                @csrf
                <div style="display: flex; gap: 8px;">
                    <input type="file" name="archivo_backup" required accept=".zip,.json" class="form-input-text" style="padding: 6px 10px; font-size: 11.5px; background: #ffffff;">
                    <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                        <i class="fa-solid fa-rotate-right"></i> Restaurar
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- Sección de Respaldo Individual por Empresa -->
    <div class="filter-bar">
        <div>
            <h4 style="font-size: 17px; font-weight: 900; color: #0f172a;">Descarga de Respaldos Aislados por Empresa</h4>
            <p style="font-size: 12px; color: #64748b; margin-top: 2px;">Genera un paquete .ZIP comprimido con la base de datos + todas las fotos de evidencias físicas de cada cliente.</p>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Empresa / Taller</th>
                        <th>Estado Suscripción</th>
                        <th style="text-align: center;">Usuarios</th>
                        <th style="text-align: center;">Clientes</th>
                        <th style="text-align: center;">Equipos</th>
                        <th style="text-align: center;">Órdenes</th>
                        <th style="text-align: right;">Acción de Respaldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($talleres as $taller)
                        <tr>
                            <td>
                                <strong style="color: #0f172a; font-size: 14px; display: block;">{{ $taller->nombre_comercial }}</strong>
                                <span style="font-size: 11px; color: #94a3b8;">{{ $taller->ciudad }} • NIT: {{ $taller->identificacion_fiscal ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($taller->estado_suscripcion === 'activo')
                                    <span class="badge badge-active">Activo</span>
                                @elseif($taller->estado_suscripcion === 'periodo_prueba')
                                    <span class="badge badge-pending">Prueba</span>
                                @else
                                    <span class="badge badge-danger">Suspendido</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-slate">{{ $taller->usuarios_count }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-slate">{{ $taller->clientes_count }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-slate">{{ $taller->equipos_count }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-slate">{{ $taller->ordenes_trabajo_count }}</span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('superadmin.respaldos.taller', $taller) }}" class="btn btn-outline" style="font-size: 11px; padding: 6px 12px; color: #0284c7;">
                                    <i class="fa-solid fa-file-zipper" style="color: #f59e0b; margin-right: 4px;"></i> Descargar .ZIP
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 36px; color: #94a3b8;">
                                No hay talleres registrados en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
