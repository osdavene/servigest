<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket POS #{{ $orden->codigo_orden }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace, sans-serif;
        }
        body {
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .ticket-wrapper {
            background: #fff;
            width: {{ $anchoPapel == '58' ? '58mm' : '80mm' }};
            padding: 12px 10px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            color: #000;
            font-size: {{ $anchoPapel == '58' ? '11px' : '13px' }};
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .double-divider {
            border-top: 2px dashed #000;
            margin: 8px 0;
        }
        .logo-img {
            max-width: 140px;
            max-height: 50px;
            object-fit: contain;
            margin-bottom: 5px;
            filter: grayscale(100%) contrast(150%);
        }
        .qr-img {
            width: {{ $anchoPapel == '58' ? '110px' : '130px' }};
            height: {{ $anchoPapel == '58' ? '110px' : '130px' }};
            margin: 6px auto;
            display: block;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .barcode-box {
            background: #000;
            color: #fff;
            padding: 4px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 2px;
            margin: 6px 0;
        }
        .no-print {
            position: fixed;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
            background: #1e293b;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            z-index: 1000;
        }
        .btn-print {
            background: #0284c7;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-family: sans-serif;
            font-size: 13px;
        }
        .btn-toggle {
            background: #334155;
            color: #f8fafc;
            border: 1px solid #64748b;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-family: sans-serif;
            font-size: 12px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .ticket-wrapper {
                width: 100%;
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            @page {
                margin: 0;
                size: {{ $anchoPapel == '58' ? '58mm' : '80mm' }} auto;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <a href="?ancho=80" class="btn-toggle {{ $anchoPapel == '80' ? 'style=border-color:#38bdf8;font-weight:bold;' : '' }}">Format 80mm</a>
        <a href="?ancho=58" class="btn-toggle {{ $anchoPapel == '58' ? 'style=border-color:#38bdf8;font-weight:bold;' : '' }}">Format 58mm</a>
        <button onclick="window.print()" class="btn-print">🖨️ IMPRIMIR TICKET</button>
        <button onclick="window.close()" class="btn-toggle" style="background:#dc2626;border:none;">Cerrar</button>
    </div>

    <div class="ticket-wrapper">
        <!-- Encabezado Taller -->
        <div class="text-center">
            @if($taller->url_logo)
                <img src="{{ $taller->url_logo }}" alt="Logo" class="logo-img">
            @endif
            <div class="bold" style="font-size: {{ $anchoPapel == '58' ? '13px' : '15px' }};">{{ strtoupper($taller->nombre_comercial) }}</div>
            @if($taller->identificacion_fiscal)
                <div>{{ $taller->identificacion_fiscal }}</div>
            @endif
            <div>Tel: {{ $taller->telefono }}</div>
            @if($taller->direccion)
                <div>{{ $taller->direccion }} {{ $taller->ciudad ? '- ' . $taller->ciudad : '' }}</div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Código de Orden -->
        <div class="barcode-box">
            * {{ $orden->codigo_orden }} *
        </div>

        <div class="row">
            <span>Fecha Ingreso:</span>
            <span class="bold">{{ $orden->fecha_ingreso ? $orden->fecha_ingreso->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span>Estado:</span>
            <span class="bold uppercase">{{ str_replace('_', ' ', $orden->estado) }}</span>
        </div>
        <div class="row">
            <span>Modalidad:</span>
            <span>{{ $orden->tipo_ubicacion === 'servicio_en_domicilio' ? 'En Domicilio' : 'En Taller' }}</span>
        </div>

        <div class="divider"></div>

        <!-- Datos del Cliente -->
        <div class="bold uppercase" style="margin-bottom: 4px;">CLIENTE:</div>
        <div>{{ $orden->cliente?->nombre_completo ?? 'General' }}</div>
        @if($orden->cliente?->identificacion)
            <div>Doc: {{ $orden->cliente?->identificacion }}</div>
        @endif
        <div>Tel: {{ $orden->cliente?->telefono }}</div>

        <div class="divider"></div>

        <!-- Datos del Equipo -->
        <div class="bold uppercase" style="margin-bottom: 4px;">EQUIPO / DISPOSITIVO:</div>
        <div class="bold">{{ $orden->equipo?->marca }} {{ $orden->equipo?->modelo }}</div>
        @if($orden->equipo?->numero_serie)
            <div>S/N: {{ $orden->equipo?->numero_serie }}</div>
        @endif
        @if($orden->equipo?->categoria)
            <div>Cat: {{ $orden->equipo?->categoria?->nombre }}</div>
        @endif

        <div class="divider"></div>

        <!-- Problema Reportado -->
        <div class="bold uppercase">FALLA REPORTADA:</div>
        <div style="font-style: italic; margin-top: 2px;">{{ $orden->problema_reportado }}</div>

        @if($orden->diagnostico)
            <div class="bold uppercase" style="margin-top: 6px;">DIAGNÓSTICO:</div>
            <div>{{ $orden->diagnostico }}</div>
        @endif

        @if($orden->procedimiento_realizado)
            <div class="bold uppercase" style="margin-top: 6px;">PROCEDIMIENTO:</div>
            <div>{{ $orden->procedimiento_realizado }}</div>
        @endif

        <div class="double-divider"></div>

        <!-- Liquidación de Costos -->
        <div class="row">
            <span>Mano de Obra:</span>
            <span>${{ number_format($orden->costo_mano_obra, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Repuestos:</span>
            <span>${{ number_format($orden->costo_repuestos, 0, ',', '.') }}</span>
        </div>
        <div class="row bold" style="font-size: {{ $anchoPapel == '58' ? '13px' : '15px' }}; margin-top: 4px;">
            <span>TOTAL:</span>
            <span>${{ number_format($orden->costo_total, 0, ',', '.') }}</span>
        </div>

        <div class="divider"></div>

        <!-- Código QR de Seguimiento -->
        <div class="text-center" style="margin: 8px 0;">
            <div class="bold" style="font-size: 10px;">ESCANEÉ PARA SEGUIMIENTO ONLINE:</div>
            <img src="{{ $qrCodeUrl }}" alt="QR Seguimiento" class="qr-img">
            <div style="font-size: 9px; word-break: break-all;">{{ $urlSeguimiento }}</div>
        </div>

        <div class="divider"></div>

        <!-- Garantía y Políticas -->
        <div class="text-center" style="font-size: 9px; line-height: 1.2;">
            <div class="bold">TÉRMINOS Y GARANTÍA</div>
            <div>{{ $taller->texto_garantia ?: 'Garantía legal de 90 días sobre mano de obra y repuestos instalados.' }}</div>
            <div style="margin-top: 6px;">¡Gracias por su confianza!</div>
        </div>
    </div>

</body>
</html>
