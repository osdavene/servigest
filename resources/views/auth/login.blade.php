@extends('layouts.invitado')

@section('titulo', 'Acceso a Talleres')

@section('contenido')
<div class="auth-container">
    
    <!-- Lado Izquierdo: Showcase de Marca y Características -->
    <div class="hero-panel">
        <div>
            <div class="hero-brand">
                <div class="brand-icon">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div>
                    <h1 class="brand-name">Servi<span>Gest</span></h1>
                    <span class="badge-pill">Portal de Talleres</span>
                </div>
            </div>

            <h2 class="hero-title">Gestión técnica profesional para tu taller.</h2>
            <p class="hero-desc">Controla órdenes de servicio, clientes, inventarios de equipos, evidencias fotográficas, firmas digitales táctiles y reportes por WhatsApp en un solo lugar.</p>

            <div class="feature-list">
                <div class="feature-item">
                    <i class="fa-solid fa-shield-halved" style="color: #38bdf8;"></i>
                    <span>Aislamiento multi-inquilino de base de datos</span>
                </div>
                <div class="feature-item">
                    <i class="fa-brands fa-whatsapp" style="color: #34d399;"></i>
                    <span>Notificación y envío de reportes PDF en 1-clic</span>
                </div>
                <div class="feature-item">
                    <i class="fa-solid fa-signature" style="color: #fbbf24;"></i>
                    <span>Captura de firma digital HTML5 en terreno</span>
                </div>
                <div class="feature-item">
                    <i class="fa-solid fa-bell" style="color: #f472b6;"></i>
                    <span>Semáforo de mantenimientos preventivos</span>
                </div>
            </div>
        </div>

        <div class="hero-footer">
            <span>Versión 2.0 Pro</span>
            <div>
                <span class="status-dot"></span>
                <span style="color: #34d399; font-weight: 600;">Servidor Local Activo</span>
            </div>
        </div>
    </div>

    <!-- Lado Derecho: Formulario de Acceso para Talleres -->
    <div class="form-panel">
        
        <div class="form-header">
            <h2>Acceso a tu Taller</h2>
            <p>Ingresa tus credenciales para administrar tus órdenes y clientes.</p>
        </div>

        @if(session('sesion_activa_detectada'))
            <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1.5px solid #fde68a; border-radius: 16px; padding: 18px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: #f59e0b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <strong style="font-size: 14.5px; color: #78350f; display: block; margin-bottom: 4px;">Sesión Activa Detectada</strong>
                        <p style="font-size: 12.5px; color: #92400e; line-height: 1.45; margin-bottom: 8px;">
                            Esta cuenta ya está abierta en <strong>{{ session('sesion_activa_detectada.dispositivo') }}</strong> ({{ session('sesion_activa_detectada.ubicacion') }} • IP {{ session('sesion_activa_detectada.ip') }}).
                        </p>
                        <p style="font-size: 12px; font-weight: 800; color: #b45309; margin-bottom: 12px;">
                            ¿Deseas cerrar la sesión en el otro equipo y continuar en este dispositivo?
                        </p>

                        <form method="POST" action="{{ route('login.post') }}" style="display: flex; gap: 8px; flex-wrap: wrap;">
                            @csrf
                            <input type="hidden" name="email" value="{{ session('sesion_activa_detectada.email') }}">
                            <input type="hidden" name="password" value="{{ session('sesion_activa_detectada.password_temp') }}">
                            <input type="hidden" name="forzar_cierre" value="1">
                            <button type="submit" class="btn-submit" style="padding: 9px 16px; font-size: 12.5px; width: auto; background: linear-gradient(135deg, #d97706, #b45309);">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                <span>Sí, cerrar otra sesión e ingresar</span>
                            </button>
                            <a href="{{ route('login') }}" style="display: inline-flex; align-items: center; justify-content: center; padding: 9px 14px; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 12px; color: #475569; text-decoration: none; font-size: 12.5px; font-weight: 700;">
                                Cancelar
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-box alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('exito'))
            <div class="alert-box" style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; margin-bottom: 20px; padding: 14px; border-radius: 12px; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 16px;"></i>
                <span>{{ session('exito') }}</span>
            </div>
        @endif

        @if($errors->any() && !session('sesion_activa_detectada'))
            <div class="alert-box alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="form-input" placeholder="ejemplo@servigest.com">
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" required
                           class="form-input" placeholder="••••••••">
                </div>
            </div>

            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="recordar" checked>
                    <span>Recordar sesión</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <span>Ingresar al Taller</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <!-- Selector Rápido de Cuentas Demo de Taller -->
        <div class="demo-section">
            <p class="demo-title">
                <i class="fa-solid fa-wand-magic-sparkles" style="color: #0284c7; margin-right: 4px;"></i> 
                Cuentas de Demostración
            </p>
            
            <div class="demo-grid" style="grid-template-columns: 1fr 1fr;">
                <div class="demo-btn" onclick="document.getElementById('email').value='admin@electrotech.com'; document.getElementById('password').value='password123';">
                    <i class="fa-solid fa-user-tie" style="color: #0284c7;"></i>
                    <span class="btn-role">Admin Taller</span>
                </div>

                <div class="demo-btn" onclick="document.getElementById('email').value='diego@electrotech.com'; document.getElementById('password').value='password123';">
                    <i class="fa-solid fa-screwdriver-wrench" style="color: #10b981;"></i>
                    <span class="btn-role">Técnico Operativo</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
