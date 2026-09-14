<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Cliente — {{ $cliente->nombre_completo }} | {{ $taller->nombre_comercial }}</title>
    
    <!-- Alpine.js & FontAwesome -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background: var(--bg); color: var(--text-main); line-height: 1.5; padding-bottom: 60px; }

        .container { max-width: 1100px; margin: 0 auto; padding: 0 16px; }

        /* Header */
        .portal-header {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 16px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .brand-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-logo {
            height: 42px;
            max-width: 140px;
            object-fit: contain;
        }
        .brand-name {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }
        .brand-sub {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Hero */
        .hero-card {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border-radius: var(--radius);
            padding: 24px;
            margin-top: 24px;
            box-shadow: 0 10px 20px -5px rgba(2, 132, 199, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        /* KPIs */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        .kpi-card {
            background: var(--surface);
            padding: 18px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .kpi-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .kpi-val { font-size: 24px; font-weight: 900; color: #0f172a; }
        .kpi-lbl { font-size: 12px; color: var(--text-muted); font-weight: 600; }

        /* Tabs */
        .tab-nav {
            display: flex;
            gap: 8px;
            border-bottom: 2px solid var(--border);
            margin-top: 28px;
            overflow-x: auto;
        }
        .tab-btn {
            background: none;
            border: none;
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        /* Cards & Tables */
        .content-card {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 20px;
            margin-top: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px; }
        th { padding: 12px 14px; font-size: 11.5px; text-transform: uppercase; color: #64748b; font-weight: 700; border-bottom: 1px solid var(--border); }
        td { padding: 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:hover { background: #f8fafc; }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 700;
        }
        .badge-pending { background: #fef3c7; color: #b45309; }
        .badge-process { background: #e0f2fe; color: #0369a1; }
        .badge-done { background: #dcfce7; color: #15803d; }
        .badge-delivered { background: #f1f5f9; color: #475569; }

        /* Buttons & Forms */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline { background: #fff; color: #0f172a; border: 1px solid var(--border); }
        .btn-outline:hover { background: #f8fafc; }
        .btn-emerald { background: #10b981; color: #fff; }

        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            color: #0f172a;
            background: #fff;
        }
        .form-control:focus { outline: 2px solid var(--primary); border-color: transparent; }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body x-data="{ tab: 'ordenes' }">

    <!-- Header Corporativo -->
    <header class="portal-header">
        <div class="container">
            <div class="header-content">
                <div class="brand-box">
                    @if($taller->url_logo)
                        <img src="{{ asset('storage/' . $taller->logo_ruta) }}" alt="Logo" class="brand-logo">
                    @endif
                    <div>
                        <div class="brand-name">{{ $taller->nombre_comercial }}</div>
                        <div class="brand-sub">Portal de Clientes & Servicios Técnicos</div>
                    </div>
                </div>

                <div>
                    <a href="{{ $cliente->enlace_whatsapp }}" target="_blank" class="btn btn-emerald">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>Soporte WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        
        <!-- Mensajes Flash -->
        @if(session('exito'))
            <div class="alert-success">
                <i class="fa-solid fa-circle-check" style="font-size: 18px;"></i>
                <span>{{ session('exito') }}</span>
            </div>
        @endif

        <!-- Hero Card -->
        <div class="hero-card">
            <div>
                <span style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9;">Portal de Autoservicio B2B</span>
                <h1 style="font-size: 24px; font-weight: 900; margin-top: 4px;">Bienvenido, {{ $cliente->nombre_completo }}</h1>
                <p style="font-size: 13px; opacity: 0.85; margin-top: 4px;">
                    @if($cliente->identificacion) Documento: {{ $cliente->identificacion }} • @endif
                    Tel: {{ $cliente->telefono }} • {{ $cliente->ciudad ?? 'Colombia' }}
                </p>
            </div>
            <div>
                <button @click="tab = 'solicitar'" class="btn" style="background: #fff; color: #0369a1; padding: 12px 20px; font-size: 14px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                    <i class="fa-solid fa-plus-circle" style="color: #0284c7;"></i>
                    <span>Solicitar Mantenimiento</span>
                </button>
            </div>
        </div>

        <!-- KPIs Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-laptop"></i>
                </div>
                <div>
                    <div class="kpi-val">{{ $totalEquipos }}</div>
                    <div class="kpi-lbl">Equipos Registrados</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div>
                    <div class="kpi-val">{{ $ordenesActivas }}</div>
                    <div class="kpi-lbl">Órdenes en Proceso</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="kpi-val">{{ $ordenesFinalizadas }}</div>
                    <div class="kpi-lbl">Servicios Entregados</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: {{ $mantenimientosPendientes > 0 ? '#fee2e2' : '#f1f5f9' }}; color: {{ $mantenimientosPendientes > 0 ? '#dc2626' : '#64748b' }};">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div>
                    <div class="kpi-val">{{ $mantenimientosPendientes }}</div>
                    <div class="kpi-lbl">Mantenimientos por Vencer</div>
                </div>
            </div>
        </div>

        <!-- Navegación por Pestañas -->
        <div class="tab-nav">
            <button @click="tab = 'ordenes'" :class="{ 'active': tab === 'ordenes' }" class="tab-btn">
                <i class="fa-solid fa-receipt"></i>
                <span>Historial de Servicios ({{ $ordenes->count() }})</span>
            </button>
            <button @click="tab = 'equipos'" :class="{ 'active': tab === 'equipos' }" class="tab-btn">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>Mis Equipos ({{ $totalEquipos }})</span>
            </button>
            <button @click="tab = 'solicitar'" :class="{ 'active': tab === 'solicitar' }" class="tab-btn">
                <i class="fa-solid fa-plus-circle"></i>
                <span>Solicitar Nuevo Servicio</span>
            </button>
        </div>

        <!-- 1. PESTAÑA: HISTORIAL DE ÓRDENES -->
        <div x-show="tab === 'ordenes'" class="content-card">
            <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 14px;">Historial de Órdenes de Servicio</h3>

            @if($ordenes->isEmpty())
                <div style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fa-solid fa-clipboard-list" style="font-size: 40px; margin-bottom: 10px;"></i>
                    <p>No tienes órdenes de trabajo registradas actualmente.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Orden #</th>
                                <th>Fecha</th>
                                <th>Equipo</th>
                                <th>Estado</th>
                                <th>Técnico</th>
                                <th>Total</th>
                                <th style="text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordenes as $ot)
                                <tr>
                                    <td style="font-weight: 800; color: #0284c7;">{{ $ot->codigo_orden }}</td>
                                    <td>{{ $ot->fecha_ingreso ? $ot->fecha_ingreso->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <div style="font-weight: 700;">{{ $ot->equipo?->marca }} {{ $ot->equipo?->modelo }}</div>
                                        <div style="font-size: 11px; color: #64748b;">{{ $ot->problema_reportado }}</div>
                                    </td>
                                    <td>
                                        @if($ot->estado === 'finalizado')
                                            <span class="badge badge-done"><i class="fa-solid fa-check"></i> Finalizado</span>
                                        @elseif($ot->estado === 'en_proceso')
                                            <span class="badge badge-process"><i class="fa-solid fa-wrench"></i> En Proceso</span>
                                        @elseif($ot->estado === 'entregado')
                                            <span class="badge badge-delivered"><i class="fa-solid fa-box"></i> Entregado</span>
                                        @else
                                            <span class="badge badge-pending"><i class="fa-solid fa-clock"></i> Pendiente</span>
                                        @endif
                                    </td>
                                    <td>{{ $ot->tecnico?->nombre ?? 'Por Asignar' }}</td>
                                    <td style="font-weight: 800;">${{ number_format($ot->costo_total, 0, ',', '.') }}</td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('ordenes.publico', $ot->token_publico_pdf) }}" target="_blank" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px;">
                                            <i class="fa-solid fa-eye"></i>
                                            <span>Ver Estado</span>
                                        </a>
                                        <a href="{{ route('ordenes.pdf.descargar_publico', $ot->token_publico_pdf) }}" target="_blank" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px;">
                                            <i class="fa-solid fa-file-pdf" style="color: #ef4444;"></i>
                                            <span>PDF</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- 2. PESTAÑA: MIS EQUIPOS -->
        <div x-show="tab === 'equipos'" class="content-card" style="display: none;">
            <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 14px;">Inventario de Equipos Registrados</h3>

            @if($equipos->isEmpty())
                <div style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fa-solid fa-laptop" style="font-size: 40px; margin-bottom: 10px;"></i>
                    <p>No tienes equipos asociados a tu cuenta.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Marca / Modelo</th>
                                <th>Categoría</th>
                                <th>Número de Serie</th>
                                <th>Último Servicio</th>
                                <th>Próximo Mantenimiento</th>
                                <th style="text-align: right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equipos as $eq)
                                <tr>
                                    <td style="font-weight: 800;">{{ $eq->marca }} {{ $eq->modelo }}</td>
                                    <td>{{ $eq->categoria?->nombre ?? 'General' }}</td>
                                    <td><code>{{ $eq->numero_serie ?: 'S/N No Registrado' }}</code></td>
                                    <td>{{ $eq->fecha_ultimo_servicio ? \Carbon\Carbon::parse($eq->fecha_ultimo_servicio)->format('d/m/Y') : 'Nunca' }}</td>
                                    <td>
                                        @if($eq->fecha_proximo_mantenimiento)
                                            @php $vencido = \Carbon\Carbon::parse($eq->fecha_proximo_mantenimiento)->isPast(); @endphp
                                            <span style="font-weight: 700; color: {{ $vencido ? '#dc2626' : '#16a34a' }};">
                                                {{ \Carbon\Carbon::parse($eq->fecha_proximo_mantenimiento)->format('d/m/Y') }}
                                                @if($vencido) <i class="fa-solid fa-triangle-exclamation"></i> @endif
                                            </span>
                                        @else
                                            <span style="color: #94a3b8;">No Programado</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <button @click="tab = 'solicitar'; $nextTick(() => document.getElementById('selectEquipo').value = '{{ $eq->id }}')" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">
                                            <i class="fa-solid fa-wrench"></i>
                                            <span>Mantenimiento</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- 3. PESTAÑA: SOLICITAR SERVICIO EXPRESS -->
        <div x-show="tab === 'solicitar'" class="content-card" style="display: none;">
            <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 6px;">Solicitud Express de Servicio Técnico</h3>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
                Reporta una avería o solicita mantenimiento preventivo para tus equipos. Tu orden se creará al instante en el taller.
            </p>

            <form action="{{ route('portal.cliente.solicitar', $cliente->token_portal) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Seleccione el Equipo a Revisar *</label>
                    <select name="equipo_id" id="selectEquipo" class="form-control" required>
                        <option value="">-- Seleccionar Equipo Registrado --</option>
                        @foreach($equipos as $eq)
                            <option value="{{ $eq->id }}">{{ $eq->marca }} {{ $eq->modelo }} (S/N: {{ $eq->numero_serie ?: 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Modalidad de Atención *</label>
                    <select name="tipo_ubicacion" class="form-control" required>
                        <option value="servicio_en_domicilio">🏠 Visita Técnica en Domicilio / Empresa</option>
                        <option value="ingresado_al_taller">🏢 Llevar el Equipo al Taller</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción de la Falla o Trabajo Solicitado *</label>
                    <textarea name="problema_reportado" rows="4" class="form-control" required
                              placeholder="Describa el comportamiento del equipo, ruidos, mensajes de error o si requiere mantenimiento preventivo periódico..."></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 14px;">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Enviar Solicitud al Taller</span>
                    </button>
                </div>
            </form>
        </div>

    </main>

</body>
</html>
