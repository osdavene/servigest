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

        @if(session('error'))
            <div class="alert-box alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
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
