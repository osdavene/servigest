@extends('layouts.app')

@section('titulo', 'Copia de Seguridad de la Empresa')

@section('contenido')
<div style="max-width: 960px; margin: 0 auto;">

    <!-- Encabezado -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Centro de Copias de Seguridad (Backup & Fotos)</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">
                Descarga el 100% de tu empresa: base de datos, clientes, órdenes, cuentas de acceso y todas las fotos de evidencias.
            </p>
        </div>

        <a href="{{ route('panel.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver al Panel</span>
        </a>
    </div>

    <!-- 1. TARJETA DE SOBERANÍA Y DESCARGA INMEDIATA EN .ZIP -->
    <div class="card" style="background: linear-gradient(135deg, #0b1120 0%, #1e293b 100%); color: #ffffff; border: none; box-shadow: 0 10px 25px rgba(11, 17, 32, 0.35); margin-bottom: 24px;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div style="max-width: 580px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(56, 189, 248, 0.2); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="fa-solid fa-file-zipper"></i>
                    </div>
                    <h3 style="font-size: 18px; font-weight: 900; color: #ffffff;">Respaldo Total: Base de Datos + Fotos de Evidencias</h3>
                </div>
                <p style="font-size: 13px; color: #94a3b8; line-height: 1.6;">
                    Tu copia de seguridad empaqueta un archivo comprimido <strong>.ZIP</strong> que contiene tanto la base de datos completa con todos tus registros históricos como los <strong>archivos físicos de todas las fotografías de evidencias y logo</strong> capturados por tu equipo.
                </p>

                <div style="display: flex; align-items: center; gap: 16px; margin-top: 18px; font-size: 12px; color: #38bdf8; flex-wrap: wrap;">
                    <span><i class="fa-solid fa-circle-check"></i> Base de Datos JSON</span>
                    <span><i class="fa-solid fa-circle-check"></i> Fotos Físicas Incluidas</span>
                    <span><i class="fa-solid fa-circle-check"></i> Claves Protegidas</span>
                    <span><i class="fa-solid fa-circle-check"></i> 100% Restaurable</span>
                </div>
            </div>

            <!-- Botón Principal de Descarga Inmediata -->
            <div>
                <a href="{{ route('respaldo.taller.descargar') }}" class="btn btn-primary" style="padding: 14px 28px; font-size: 14px; box-shadow: 0 6px 18px rgba(2, 132, 199, 0.45); display: flex; flex-direction: column; align-items: center; text-align: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-cloud-arrow-down" style="font-size: 18px;"></i>
                        <span style="font-weight: 900;">Generar y Descargar Backup (.ZIP)</span>
                    </div>
                    <span style="font-size: 10.5px; opacity: 0.85; margin-top: 4px; font-weight: 400;">(Base de Datos + Archivos Multimedia)</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. AUDITORÍA DE CONTENIDO INCLUIDO EN EL BACKUP -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div>
                <h3 style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-cubes-stacked" style="color: #0284c7;"></i>
                    <span>Ecosistema de Datos Incluidos en tu Copia de Seguridad</span>
                </h3>
                <p>Auditoría en tiempo real de los registros que componen tu empresa.</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px; text-align: center;">
            
            <div style="padding: 16px 12px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 14px;">
                <i class="fa-solid fa-users" style="color: #2563eb; font-size: 22px; margin-bottom: 6px; display: block;"></i>
                <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; color: #64748b;">Clientes</span>
                <strong style="font-size: 20px; color: #0f172a; display: block; margin-top: 2px;">{{ $totalClientes }}</strong>
            </div>

            <div style="padding: 16px 12px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 14px;">
                <i class="fa-solid fa-laptop-medical" style="color: #7c3aed; font-size: 22px; margin-bottom: 6px; display: block;"></i>
                <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; color: #64748b;">Equipos</span>
                <strong style="font-size: 20px; color: #0f172a; display: block; margin-top: 2px;">{{ $totalEquipos }}</strong>
            </div>

            <div style="padding: 16px 12px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 14px;">
                <i class="fa-solid fa-clipboard-list" style="color: #0284c7; font-size: 22px; margin-bottom: 6px; display: block;"></i>
                <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; color: #64748b;">Órdenes de Trabajo</span>
                <strong style="font-size: 20px; color: #0f172a; display: block; margin-top: 2px;">{{ $totalOrdenes }}</strong>
            </div>

            <div style="padding: 16px 12px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 14px;">
                <i class="fa-solid fa-tags" style="color: #059669; font-size: 22px; margin-bottom: 6px; display: block;"></i>
                <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; color: #64748b;">Categorías</span>
                <strong style="font-size: 20px; color: #0f172a; display: block; margin-top: 2px;">{{ $totalCategorias }}</strong>
            </div>

            <div style="padding: 16px 12px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 14px;">
                <i class="fa-solid fa-user-gear" style="color: #d97706; font-size: 22px; margin-bottom: 6px; display: block;"></i>
                <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; color: #64748b;">Cuentas de Acceso</span>
                <strong style="font-size: 20px; color: #0f172a; display: block; margin-top: 2px;">{{ $totalUsuarios }}</strong>
            </div>

            <div style="padding: 16px 12px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 14px;">
                <i class="fa-solid fa-camera" style="color: #ec4899; font-size: 22px; margin-bottom: 6px; display: block;"></i>
                <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; color: #64748b;">Fotos Respaldadas</span>
                <strong style="font-size: 20px; color: #0f172a; display: block; margin-top: 2px;">{{ $totalEvidencias }}</strong>
            </div>

        </div>

        <!-- Nota de Seguridad sobre la Restauración y Retención -->
        <div style="margin-top: 18px; padding: 14px 16px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 12px; display: flex; align-items: center; gap: 12px; font-size: 12.5px; color: #166534;">
            <i class="fa-solid fa-shield-check" style="font-size: 18px; color: #16a34a; shrink-0: 0;"></i>
            <div>
                <strong>Política de Optimización de Espacio:</strong>
                <span style="color: #374151; display: block; margin-top: 2px;">
                    El servidor mantiene de forma rotativa únicamente tus <strong>3 copias de seguridad más recientes</strong> para optimizar el almacenamiento en disco. Al generar una nueva copia, la más antigua se descarta automáticamente en el servidor.
                </span>
            </div>
        </div>
    </div>

    <!-- 3. HISTORIAL DE RESPALDOS GUARDADOS (MÁXIMO 3 MÁS RECIENTES) -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div>
                <h3 style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-clock-rotate-left" style="color: #0284c7;"></i>
                    <span>Historial de Respaldos en el Servidor (Últimas 3 Copias)</span>
                </h3>
                <p>Archivos de respaldo listos para volver a descargar.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Archivo de Respaldo</th>
                        <th>Fecha de Creación</th>
                        <th>Tamaño</th>
                        <th style="text-align: right;">Descarga</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archivos as $item)
                        <tr>
                            <td>
                                <strong style="color: #0284c7; font-family: monospace; font-size: 13px;">
                                    <i class="fa-solid fa-file-zipper" style="color: #f59e0b; margin-right: 6px;"></i>
                                    {{ $item['nombre'] }}
                                </strong>
                            </td>
                            <td>
                                <span style="font-size: 12.5px; color: #334155;">{{ $item['fecha'] }}</span>
                            </td>
                            <td>
                                <span class="badge badge-slate" style="font-size: 11px;">
                                    {{ $item['tamano_kb'] }} KB
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('respaldo.taller.descargar_archivo', $item['nombre']) }}" class="btn btn-outline" style="font-size: 11.5px; padding: 6px 12px; color: #0284c7;">
                                    <i class="fa-solid fa-download"></i> Descargar .ZIP
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 36px; color: #94a3b8;">
                                <i class="fa-solid fa-hard-drive" style="font-size: 32px; margin-bottom: 8px; display: block;"></i>
                                Aún no has generado tu primera copia de seguridad. Haz clic en el botón superior para crearla.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
