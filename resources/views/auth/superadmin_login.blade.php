@extends('layouts.invitado')

@section('titulo', 'Control Central SaaS')

@section('contenido')
<div class="auth-container" style="max-width: 900px; grid-template-columns: 1fr 1fr;">
    
    <!-- Lado Izquierdo: Seguridad y Centro de Comando -->
    <div class="hero-panel" style="background: linear-gradient(145deg, #110e05 0%, #1f1604 50%, #332204 100%); border-right: 1px solid rgba(245, 158, 11, 0.2);">
        <div>
            <div class="hero-brand">
                <div class="brand-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);">
                    <i class="fa-solid fa-crown" style="color: #ffffff;"></i>
                </div>
                <div>
                    <h1 class="brand-name">Servi<span>Gest</span></h1>
                    <span class="badge-pill" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3);">
                        Master Console
                    </span>
                </div>
            </div>

            <h2 class="hero-title" style="color: #fef3c7;">Consola Maestra del Propietario SaaS</h2>
            <p class="hero-desc" style="color: #d1d5db;">Acceso exclusivo y restringido para la administración global de la infraestructura, suscripciones de talleres y facturación del software.</p>

            <div class="feature-list">
                <div class="feature-item" style="background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.15);">
                    <i class="fa-solid fa-building-shield" style="color: #fbbf24;"></i>
                    <span>Control y alta de empresas/talleres clientes</span>
                </div>
                <div class="feature-item" style="background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.15);">
                    <i class="fa-solid fa-file-invoice-dollar" style="color: #fbbf24;"></i>
                    <span>Gestión de periodos de prueba y suscripciones</span>
                </div>
                <div class="feature-item" style="background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.15);">
                    <i class="fa-solid fa-server" style="color: #fbbf24;"></i>
                    <span>Monitoreo multi-inquilino de la base de datos</span>
                </div>
            </div>
        </div>

        <div class="hero-footer">
            <span style="color: #fbbf24; font-weight: 700;">Área Restringida</span>
            <div>
                <span class="status-dot" style="background: #fbbf24; box-shadow: 0 0 10px #fbbf24;"></span>
                <span style="color: #fde68a; font-weight: 600;">Canal Encriptado</span>
            </div>
        </div>
    </div>

    <!-- Lado Derecho: Formulario de Acceso SuperAdmin -->
    <div class="form-panel" style="background: #ffffff;">
        
        <div class="form-header">
            <h2 style="color: #78350f;">Acceso Super Administrador</h2>
            <p>Portal exclusivo de dirección general de ServiGest SaaS.</p>
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

        <form method="POST" action="{{ route('superadmin.login.post') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label" style="color: #92400e;">Correo Maestro</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user-shield input-icon" style="color: #d97706;"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="form-input" placeholder="admin@servigest.com"
                           style="border-color: #fed7aa;">
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label" style="color: #92400e;">Contraseña Maestra</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-key input-icon" style="color: #d97706;"></i>
                    <input type="password" id="password" name="password" required
                           class="form-input" placeholder="••••••••"
                           style="border-color: #fed7aa;">
                </div>
            </div>

            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="recordar" checked style="accent-color: #d97706;">
                    <span style="color: #78350f; font-weight: 500;">Sesión Segura</span>
                </label>
            </div>

            <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); box-shadow: 0 10px 20px -5px rgba(217, 119, 6, 0.4);">
                <span>Ingresar a Consola Maestra</span>
                <i class="fa-solid fa-shield-halved"></i>
            </button>
        </form>

        <!-- Selector Rápido SuperAdmin -->
        <div class="demo-section">
            <p class="demo-title" style="color: #b45309;">
                <i class="fa-solid fa-crown" style="color: #f59e0b; margin-right: 4px;"></i> 
                Relleno Rápido de Demostración
            </p>
            
            <div class="demo-grid" style="grid-template-columns: 1fr;">
                <div class="demo-btn" onclick="document.getElementById('email').value='superadmin@servigest.com'; document.getElementById('password').value='password123';"
                     style="background: #fffbeb; border-color: #fde68a;">
                    <i class="fa-solid fa-crown" style="color: #d97706;"></i>
                    <span class="btn-role" style="color: #78350f;">Super Administrador General</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
