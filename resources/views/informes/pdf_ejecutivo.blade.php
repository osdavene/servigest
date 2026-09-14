<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Gerencial - {{ $taller->nombre_comercial }}</title>
    <style>
        @page {
            margin: 25px 30px;
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            font-size: 11px;
        }

        body {
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        /* Encabezado Membretado */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .header-logo {
            max-height: 55px;
            max-width: 180px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #0284c7;
            text-transform: uppercase;
            text-align: right;
        }

        /* Cuadros de KPIs Financieros */
        .kpi-table {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 6px;
        }

        .kpi-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }

        .kpi-box-highlight {
            background-color: #ecfdf5;
            border: 1.5px solid #10b981;
        }

        .kpi-label {
            font-size: 8.5px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }

        .kpi-value {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
        }

        .kpi-value-green {
            color: #047857;
            font-size: 16px;
        }

        /* Títulos de Sección */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            background-color: #f1f5f9;
            padding: 5px 8px;
            border-left: 3px solid #0284c7;
            margin-top: 14px;
            margin-bottom: 8px;
        }

        /* Tablas de Datos */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5px;
        }

        .data-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            text-align: left;
        }

        .data-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 6px;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) td {
            background-color: #fafbfc;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .footer {
            margin-top: 25px;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            font-size: 8.5px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Encabezado Membretado Oficial -->
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                @if($taller->url_logo)
                    @php
                        $logoSrc = $taller->url_logo;
                        if (!str_starts_with($logoSrc, 'http') && file_exists(storage_path('app/public/' . $taller->logo_ruta))) {
                            $logoSrc = storage_path('app/public/' . $taller->logo_ruta);
                        }
                    @endphp
                    <img src="{{ $logoSrc }}" class="header-logo" alt="Logo">
                @else
                    <div class="company-name">{{ $taller->nombre_comercial }}</div>
                @endif
                <div style="font-size: 9px; color: #64748b; margin-top: 3px;">
                    NIT: {{ $taller->identificacion_fiscal ?? 'N/A' }} | Tel: {{ $taller->telefono }} | {{ $taller->ciudad }}
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;" class="text-right">
                <div class="report-title">Informe Gerencial y Financiero</div>
                <div style="font-size: 9.5px; color: #475569; margin-top: 4px;">
                    <strong>Periodo:</strong> {{ $filtros['nombre_periodo'] }}
                </div>
                <div style="font-size: 8.5px; color: #94a3b8; margin-top: 2px;">
                    Generado el: {{ $fechaGeneracion->format('d/m/Y h:i A') }} por {{ $generadoPor }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Resumen de KPIs Financieros -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-box kpi-box-highlight" style="width: 25%;">
                <div class="kpi-label">Facturación Total</div>
                <div class="kpi-value kpi-value-green">${{ number_format($totalIngresos, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-box" style="width: 25%;">
                <div class="kpi-label">Mano de Obra</div>
                <div class="kpi-value">${{ number_format($totalManoObra, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-box" style="width: 25%;">
                <div class="kpi-label">Repuestos</div>
                <div class="kpi-value">${{ number_format($totalRepuestos, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-box" style="width: 25%;">
                <div class="kpi-label">Ticket Promedio</div>
                <div class="kpi-value">${{ number_format($ticketPromedio, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Resumen Operativo -->
    <table class="kpi-table" style="margin-top: -8px;">
        <tr>
            <td class="kpi-box" style="width: 20%;">
                <div class="kpi-label">Total Órdenes</div>
                <div class="kpi-value">{{ $totalOrdenes }}</div>
            </td>
            <td class="kpi-box" style="width: 20%;">
                <div class="kpi-label">Finalizadas / Cobradas</div>
                <div class="kpi-value" style="color: #047857;">{{ $totalCerradas }}</div>
            </td>
            <td class="kpi-box" style="width: 20%;">
                <div class="kpi-label">En Proceso</div>
                <div class="kpi-value" style="color: #0284c7;">{{ $conteoEnProceso }}</div>
            </td>
            <td class="kpi-box" style="width: 20%;">
                <div class="kpi-label">Tiempo Promedio</div>
                <div class="kpi-value">{{ $tiempoPromedioDias }} Días</div>
            </td>
            <td class="kpi-box" style="width: 20%;">
                <div class="kpi-label">Tasa Efectividad</div>
                <div class="kpi-value" style="color: #10b981;">{{ $tasaEfectividad }}%</div>
            </td>
        </tr>
    </table>

    <!-- Desglose por Técnico -->
    <div class="section-title">Productividad y Rendimiento por Técnico Especialista</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Técnico</th>
                <th class="text-center">Asignadas</th>
                <th class="text-center">Cerradas</th>
                <th class="text-center">En Proceso</th>
                <th class="text-right">Mano de Obra ($)</th>
                <th class="text-right">Facturado ($)</th>
                <th class="text-center">Efectividad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($porTecnico as $itemTec)
                @php
                    $efectividad = $itemTec['total_asignadas'] > 0 ? round(($itemTec['cerradas'] / $itemTec['total_asignadas']) * 100, 1) : 0;
                @endphp
                <tr>
                    <td class="font-bold">{{ $itemTec['tecnico']->nombre_completo }}</td>
                    <td class="text-center">{{ $itemTec['total_asignadas'] }}</td>
                    <td class="text-center font-bold" style="color: #047857;">{{ $itemTec['cerradas'] }}</td>
                    <td class="text-center" style="color: #0284c7;">{{ $itemTec['en_proceso'] }}</td>
                    <td class="text-right">${{ number_format($itemTec['mano_obra'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #047857;">${{ number_format($itemTec['ingresos_generados'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $efectividad }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Desglose por Categoría de Dispositivo -->
    <div class="section-title">Facturación por Tipo de Dispositivo / Categoría</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Categoría de Equipo</th>
                <th class="text-center">Servicios Atendidos</th>
                <th class="text-right">Total Facturado ($)</th>
                <th class="text-right">Participación (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($porCategoria as $catData)
                <tr>
                    <td class="font-bold">{{ $catData['categoria'] }}</td>
                    <td class="text-center">{{ $catData['cantidad'] }}</td>
                    <td class="text-right font-bold" style="color: #047857;">${{ number_format($catData['facturado'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ $catData['porcentaje'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Detalle de Órdenes Atendidas -->
    <div class="section-title">Detalle de Órdenes Atendidas en el Periodo</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Código OT</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Equipo</th>
                <th>Técnico</th>
                <th class="text-right">M. Obra</th>
                <th class="text-right">Repuestos</th>
                <th class="text-right">Total</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ordenesRecientes as $ot)
                <tr>
                    <td class="font-bold" style="color: #0284c7;">{{ $ot->codigo_orden }}</td>
                    <td>{{ $ot->fecha_ingreso?->format('d/m/Y') }}</td>
                    <td>{{ $ot->cliente?->nombre_completo }}</td>
                    <td>{{ $ot->equipo?->marca }} {{ $ot->equipo?->modelo }}</td>
                    <td>{{ $ot->tecnico?->nombre ?? 'N/A' }}</td>
                    <td class="text-right">${{ number_format($ot->costo_mano_obra, 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format($ot->costo_repuestos, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #047857;">${{ number_format($ot->costo_total, 0, ',', '.') }}</td>
                    <td class="text-center">{{ strtoupper(str_replace('_', ' ', $ot->estado)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pie de Página -->
    <div class="footer">
        Documento oficial generado automáticamente por <strong>ServiGest SaaS</strong> para {{ $taller->nombre_comercial }}. Todos los derechos reservados.
    </div>

</body>
</html>
