<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta QR #{{ $orden->codigo_orden }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        body {
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .etiqueta-wrapper {
            background: #fff;
            width: 60mm;
            height: 38mm;
            padding: 4mm;
            border: 1px dashed #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            overflow: hidden;
        }
        .info-box {
            width: 58%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }
        .taller-nombre {
            font-size: 8px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .codigo-ot {
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin: 1px 0;
        }
        .equipo-txt {
            font-size: 9px;
            font-weight: bold;
            color: #1e293b;
            line-height: 1.1;
        }
        .cliente-txt {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .fecha-txt {
            font-size: 7.5px;
            color: #94a3b8;
        }
        .qr-box {
            width: 38%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .qr-img {
            width: 25mm;
            height: 25mm;
            display: block;
        }
        .qr-label {
            font-size: 6.5px;
            font-weight: bold;
            text-align: center;
            margin-top: 1px;
            color: #0f172a;
            text-transform: uppercase;
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
            font-size: 13px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
                min-height: auto;
            }
            .etiqueta-wrapper {
                border: none;
                box-shadow: none;
                width: 100%;
                height: 100%;
                padding: 2mm;
            }
            .no-print {
                display: none !important;
            }
            @page {
                margin: 0;
                size: 60mm 38mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🏷️ IMPRIMIR ETIQUETA QR</button>
        <button onclick="window.close()" style="background:#dc2626;color:#fff;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;">Cerrar</button>
    </div>

    <div class="etiqueta-wrapper">
        <div class="info-box">
            <div class="taller-nombre">{{ $taller->nombre_comercial }}</div>
            <div class="codigo-ot">{{ $orden->codigo_orden }}</div>
            <div class="equipo-txt">{{ $orden->equipo?->marca }} {{ $orden->equipo?->modelo }}</div>
            <div class="cliente-txt">👤 {{ $orden->cliente?->nombre_completo ?? 'Cliente' }}</div>
            <div class="fecha-txt">📅 {{ $orden->fecha_ingreso ? $orden->fecha_ingreso->format('d/m/Y') : date('d/m/Y') }}</div>
        </div>

        <div class="qr-box">
            <img src="{{ $qrCodeUrl }}" alt="QR" class="qr-img">
            <div class="qr-label">Escanear OT</div>
        </div>
    </div>

</body>
</html>
