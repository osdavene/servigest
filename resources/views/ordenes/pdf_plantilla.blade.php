<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Técnico - {{ $orden->codigo_orden }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        .header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .empresa-nombre {
            font-size: 17px;
            font-weight: bold;
            color: #0369a1;
            text-transform: uppercase;
        }
        .empresa-datos {
            font-size: 9px;
            color: #64748b;
        }
        .titulo-doc {
            text-align: right;
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
        }
        .codigo-ot {
            font-size: 14px;
            color: #0284c7;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .seccion-titulo {
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 8px;
            border-left: 3px solid #0284c7;
            margin-top: 10px;
            margin-bottom: 6px;
        }
        .tabla-info td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #475569;
            width: 25%;
        }
        .valor {
            color: #0f172a;
        }
        .caja-texto {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 10px;
        }
        .tabla-costos {
            width: 60%;
            margin-left: auto;
            border: 1px solid #cbd5e1;
        }
        .tabla-costos td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .tabla-costos .total {
            background-color: #f0fdf4;
            font-weight: bold;
            font-size: 12px;
            color: #166534;
        }
        .firma-box {
            margin-top: 25px;
            width: 45%;
            text-align: center;
            border-top: 1px solid #64748b;
            padding-top: 5px;
            float: right;
        }
        .firma-img {
            max-height: 55px;
            margin-bottom: 5px;
        }
        .evidencias-grid {
            margin-top: 10px;
        }
        .evidencia-item {
            display: inline-block;
            width: 48%;
            margin-right: 2%;
            margin-bottom: 10px;
            vertical-align: top;
            border: 1px solid #e2e8f0;
            padding: 4px;
            background: #fff;
        }
        .evidencia-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        .evidencia-desc {
            font-size: 8px;
            color: #475569;
            margin-top: 3px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            clear: both;
        }
    </style>
</head>
<body>

    <!-- Encabezado con datos del Taller y Logo -->
    <table class="header">
        <tr>
            <td style="width: 60%;">
                @if($orden->taller?->url_logo)
                    @php
                        $logoSrc = $orden->taller->url_logo;
                        if (!str_starts_with($logoSrc, 'http') && file_exists(storage_path('app/public/' . $orden->taller->logo_ruta))) {
                            $logoSrc = storage_path('app/public/' . $orden->taller->logo_ruta);
                        }
                    @endphp
                    <img src="{{ $logoSrc }}" style="max-height: 44px; max-width: 160px; margin-bottom: 6px; display: block;">
                @endif
                <div class="empresa-nombre">{{ $orden->taller?->nombre_comercial ?? 'ServiGest Taller' }}</div>
                <div class="empresa-datos">
                    {{ $orden->taller?->identificacion_fiscal ?? '' }}<br>
                    Teléfono: {{ $orden->taller?->telefono ?? 'N/A' }} | Email: {{ $orden->taller?->email ?? 'N/A' }}<br>
                    {{ $orden->taller?->direccion ?? '' }} - {{ $orden->taller?->ciudad ?? '' }}
                </div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div class="titulo-doc">INFORME TÉCNICO DE SERVICIO</div>
                <div class="codigo-ot">{{ $orden->codigo_orden }}</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 3px;">
                    Fecha: {{ $orden->fecha_ingreso?->format('d/m/Y') }}<br>
                    Estado: <strong>{{ strtoupper(str_replace('_', ' ', $orden->estado)) }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Datos del Cliente y Equipo -->
    <table>
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                <div class="seccion-titulo">Datos del Cliente</div>
                <table class="tabla-info">
                    <tr>
                        <td class="label">Nombre:</td>
                        <td class="valor">{{ $orden->cliente?->nombre_completo }}</td>
                    </tr>
                    <tr>
                        <td class="label">Teléfono:</td>
                        <td class="valor">{{ $orden->cliente?->telefono }}</td>
                    </tr>
                    <tr>
                        <td class="label">Dirección:</td>
                        <td class="valor">{{ $orden->cliente?->direccion }} ({{ $orden->cliente?->ciudad }})</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                <div class="seccion-titulo">Datos del Dispositivo</div>
                <table class="tabla-info">
                    <tr>
                        <td class="label">Categoría:</td>
                        <td class="valor">{{ $orden->equipo?->categoria?->nombre ?? 'General' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Marca/Modelo:</td>
                        <td class="valor">{{ $orden->equipo?->marca }} {{ $orden->equipo?->modelo }}</td>
                    </tr>
                    <tr>
                        <td class="label">N° Serie:</td>
                        <td class="valor">{{ $orden->equipo?->numero_serie ?? 'N/A' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Motivo y Diagnóstico -->
    <div class="seccion-titulo">Detalle del Servicio Técnico</div>
    
    <div style="font-size: 9px; font-weight: bold; color: #475569; margin-bottom: 2px;">Problema Reportado / Motivo de Ingreso:</div>
    <div class="caja-texto">{{ $orden->problema_reportado ?? 'Revisión técnica general.' }}</div>

    @if($orden->diagnostico)
        <div style="font-size: 9px; font-weight: bold; color: #475569; margin-bottom: 2px;">Diagnóstico del Especialista:</div>
        <div class="caja-texto">{{ $orden->diagnostico }}</div>
    @endif

    @if($orden->procedimiento_realizado)
        <div style="font-size: 9px; font-weight: bold; color: #475569; margin-bottom: 2px;">Procedimiento y Trabajo Realizado:</div>
        <div class="caja-texto">{{ $orden->procedimiento_realizado }}</div>
    @endif

    <!-- Liquidación Económica -->
    <div class="seccion-titulo">Liquidación de Costos</div>
    <table class="tabla-costos">
        <tr>
            <td>Mano de Obra y Servicios:</td>
            <td style="text-align: right;">${{ number_format($orden->costo_mano_obra, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Repuestos y Materiales:</td>
            <td style="text-align: right;">${{ number_format($orden->costo_repuestos, 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td>TOTAL SERVICIO:</td>
            <td style="text-align: right;">${{ number_format($orden->costo_total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Firma del Cliente de Recibido / Conformidad -->
    <div style="margin-top: 20px; width: 100%;">
        <div style="width: 45%; float: left; font-size: 9px; color: #64748b;">
            <strong>Técnico Responsable:</strong><br>
            {{ $orden->tecnico?->nombre_completo ?? 'Servicio Técnico Autorizado' }}
        </div>

        <div class="firma-box">
            @if($orden->url_firma_cliente)
                @php
                    $firmaSrc = $orden->url_firma_cliente;
                    if (!str_starts_with($firmaSrc, 'http') && file_exists(storage_path('app/public/' . $orden->ruta_firma_cliente))) {
                        $firmaSrc = storage_path('app/public/' . $orden->ruta_firma_cliente);
                    }
                @endphp
                <img src="{{ $firmaSrc }}" class="firma-img" alt="Firma"><br>
            @endif
            <strong>{{ $orden->nombre_firmante ?? $orden->cliente?->nombre_completo }}</strong><br>
            <span style="font-size: 8px; color: #64748b;">Firma de Conformidad / Aceptación</span>
        </div>
    </div>

    <!-- Galería de Evidencias Fotográficas del Servicio -->
    @if($orden->evidencias && $orden->evidencias->count() > 0)
        <div class="seccion-titulo" style="clear: both; margin-top: 18px;">Registro y Evidencias Fotográficas del Servicio</div>
        <table style="width: 100%; margin-top: 6px; border-collapse: collapse;">
            <tr>
                @foreach($orden->evidencias as $index => $evi)
                    @php
                        $rutaFoto = $evi->url_imagen;
                        if (!str_starts_with($rutaFoto, 'http') && file_exists(storage_path('app/public/' . $evi->ruta_imagen))) {
                            $rutaFoto = storage_path('app/public/' . $evi->ruta_imagen);
                        }
                    @endphp
                    @if($index > 0 && $index % 3 == 0)
                        </tr><tr>
                    @endif
                    <td style="width: 33.33%; padding: 4px; vertical-align: top;">
                        <div style="border: 1px solid #cbd5e1; padding: 4px; text-align: center; border-radius: 4px; background: #ffffff;">
                            @if($rutaFoto)
                                <img src="{{ $rutaFoto }}" style="width: 100%; height: 95px; object-fit: cover; border-radius: 2px;">
                            @endif
                            <div style="font-size: 7.5px; font-weight: bold; color: #0284c7; margin-top: 3px;">
                                {{ $evi->nombre_etiqueta }}
                            </div>
                            @if($evi->descripcion)
                                <div style="font-size: 7px; color: #64748b; margin-top: 1px;">
                                    {{ $evi->descripcion }}
                                </div>
                            @endif
                        </div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    <div class="footer">
        Documento generado automáticamente por <strong>ServiGest SaaS</strong> | Código de verificación: {{ $orden->token_publico_pdf }}
    </div>

</body>
</html>
