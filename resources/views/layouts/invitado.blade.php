<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'ServiGest') }} - @yield('titulo', 'Acceso')</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Framework CSS Autónomo y Robusto (100% independiente de bloqueadores de scripts) -->
    <style>
        :root {
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --primary-gradient: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            --dark-bg: #090d16;
            --dark-card: #0f172a;
            --border-color: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --emerald: #10b981;
            --amber: #f59e0b;
            --rose: #f43f5e;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: 
                radial-gradient(at 10% 10%, rgba(2, 132, 199, 0.2) 0px, transparent 50%),
                radial-gradient(at 90% 90%, rgba(37, 99, 235, 0.18) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.8) 0px, transparent 100%);
            background-attachment: fixed;
        }

        /* Contenedor Principal Split SaaS */
        .auth-container {
            width: 100%;
            max-width: 1020px;
            background: #ffffff;
            border-radius: 28px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1.15fr;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 820px) {
            .auth-container {
                grid-template-columns: 1fr;
                max-width: 480px;
            }
            .hero-panel {
                display: none;
            }
        }

        /* Panel Izquierdo Hero */
        .hero-panel {
            background: linear-gradient(145deg, #090f1f 0%, #0f1c3f 50%, #0c2b55 100%);
            color: #ffffff;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .hero-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 32px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-gradient);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #ffffff;
            box-shadow: 0 10px 25px rgba(2, 132, 199, 0.4);
        }

        .brand-name {
            font-size: 24px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #ffffff;
        }

        .brand-name span {
            color: #38bdf8;
        }

        .badge-pill {
            display: inline-block;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            padding: 3px 10px;
            border-radius: 20px;
            border: 1px solid rgba(56, 189, 248, 0.3);
            margin-top: 4px;
        }

        .hero-title {
            font-size: 26px;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 12px;
            color: #ffffff;
        }

        .hero-desc {
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.04);
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            font-size: 13px;
            color: #e2e8f0;
        }

        .feature-item i {
            font-size: 16px;
        }

        .hero-footer {
            margin-top: 36px;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #64748b;
        }

        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: var(--emerald);
            border-radius: 50%;
            margin-right: 6px;
            box-shadow: 0 0 10px var(--emerald);
        }

        /* Panel Derecho Formulario */
        .form-panel {
            background: #ffffff;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: #1e293b;
        }

        .form-header {
            margin-bottom: 28px;
        }

        .form-header h2 {
            font-size: 26px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .form-header p {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 16px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 13px 14px 13px 44px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 14px;
            color: #0f172a;
            font-weight: 500;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.15);
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 24px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox-label input {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary-gradient);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 20px -5px rgba(2, 132, 199, 0.4);
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 26px -5px rgba(2, 132, 199, 0.5);
        }

        /* Selector de Cuentas Demo */
        .demo-section {
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid #f1f5f9;
        }

        .demo-title {
            text-align: center;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            margin-bottom: 14px;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .demo-btn {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 12px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .demo-btn:hover {
            background: #f0f9ff;
            border-color: #38bdf8;
            transform: translateY(-2px);
        }

        .demo-btn i {
            display: block;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .demo-btn .btn-role {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
        }

        .demo-btn .btn-desc {
            display: block;
            font-size: 10px;
            color: #64748b;
        }

        .alert-box {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
    @yield('contenido')
</body>
</html>
