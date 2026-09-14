<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ config('app.name', 'ServiGest') }} - @yield('titulo', 'Gestión Técnica')</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Sistema de Diseño CSS Completo y Autónomo (Mobile First & Responsive) -->
    <style>
        :root {
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --primary-gradient: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            --sidebar-bg: #0b1120;
            --sidebar-hover: #162033;
            --bg-page: #f1f5f9;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --emerald: #10b981;
            --emerald-bg: #ecfdf5;
            --emerald-border: #a7f3d0;
            --amber: #f59e0b;
            --amber-bg: #fffbeb;
            --amber-border: #fde68a;
            --rose: #ef4444;
            --rose-bg: #fef2f2;
            --rose-border: #fecaca;
            --sky: #0ea5e9;
            --sky-bg: #f0f9ff;
            --sky-border: #bae6fd;
            --purple: #8b5cf6;
            --purple-bg: #f5f3ff;
            --purple-border: #ddd6fe;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --modal-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        body { background-color: var(--bg-page); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }
        [x-cloak] { display: none !important; }

        /* Barra Lateral */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 100;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            height: 76px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            gap: 12px;
        }

        .sidebar-logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
        }

        .sidebar-logo-text h1 { font-size: 20px; font-weight: 900; color: #fff; letter-spacing: -0.5px; }
        .sidebar-logo-text h1 span { color: #38bdf8; }
        .sidebar-logo-text span.badge { font-size: 9px; text-transform: uppercase; font-weight: 800; padding: 2px 6px; border-radius: 4px; }

        .tenant-box {
            margin: 16px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .tenant-box .lbl { font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--text-muted); }
        .tenant-box .taller-name { font-size: 13px; font-weight: 800; color: #38bdf8; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .nav-list { flex: 1; padding: 8px 14px; overflow-y: auto; display: flex; flex-direction: column; gap: 4px; }
        .nav-category { font-size: 10px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; color: #475569; padding: 14px 12px 6px; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            border-radius: 12px;
            color: #94a3b8;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
        }

        .nav-item i { font-size: 16px; width: 20px; text-align: center; }
        .nav-item:hover { background: var(--sidebar-hover); color: #ffffff; }
        .nav-item.active { background: var(--primary); color: #ffffff; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35); }

        .user-footer {
            padding: 16px 20px;
            background: rgba(0, 0, 0, 0.25);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-info { display: flex; align-items: center; gap: 10px; overflow: hidden; }
        .user-avatar { width: 36px; height: 36px; border-radius: 10px; background: #0369a1; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; shrink-0: 0; }
        .user-name { font-size: 13px; font-weight: 800; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 10.5px; color: var(--text-muted); text-transform: capitalize; }

        /* Contenedor Principal */
        .main-wrapper {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: calc(100% - 260px);
            transition: margin-left 0.3s ease;
        }

        /* Barra Superior */
        .topbar {
            height: 76px;
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .topbar-title { font-size: 18px; font-weight: 900; color: var(--text-main); letter-spacing: -0.3px; }

        .content-area {
            flex: 1;
            padding: 32px;
            max-width: 1440px;
            width: 100%;
            margin: 0 auto;
        }

        /* Backdrop para Móviles */
        .sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(11, 17, 32, 0.7);
            backdrop-filter: blur(4px);
            z-index: 90;
        }

        .mobile-menu-btn {
            display: none;
            background: #f8fafc;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: var(--text-main);
            cursor: pointer;
        }

        /* Tarjetas y Contenedores */
        .card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid var(--border);
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--card-shadow);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 12px;
        }

        .card-header h3 { font-size: 17px; font-weight: 900; color: var(--text-main); }
        .card-header p { font-size: 12.5px; color: var(--text-muted); margin-top: 2px; }

        /* KPI Dashboard */
        .grid-kpi { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 28px; }
        .kpi-card { background: #ffffff; border-radius: 18px; border: 1px solid var(--border); padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: var(--card-shadow); }
        .kpi-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; shrink-0: 0; }
        .kpi-title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); }
        .kpi-num { font-size: 24px; font-weight: 900; color: var(--text-main); margin-top: 2px; }

        /* Botones */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-primary { background: var(--primary); color: #ffffff; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25); }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .btn-emerald { background: var(--emerald); color: #ffffff; }
        .btn-emerald:hover { background: #059669; }
        .btn-amber { background: var(--amber); color: #ffffff; }
        .btn-amber:hover { background: #d97706; }
        .btn-rose { background: var(--rose); color: #ffffff; }
        .btn-rose:hover { background: #dc2626; }
        .btn-dark { background: #0f172a; color: #ffffff; }
        .btn-outline { background: #ffffff; border: 1.5px solid var(--border); color: #334155; }
        .btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }
        .btn-icon { width: 34px; height: 34px; padding: 0; border-radius: 10px; font-size: 14px; }

        .filter-bar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 22px; }
        .search-group { display: flex; align-items: center; gap: 10px; flex: 1; max-width: 500px; }
        .search-input-wrapper { position: relative; flex: 1; display: flex; align-items: center; }
        .search-input-wrapper i { position: absolute; left: 14px; color: var(--text-muted); font-size: 14px; }
        .input-control, .select-control { width: 100%; padding: 10px 14px 10px 38px; background: #ffffff; border: 1.5px solid var(--border); border-radius: 12px; font-size: 13px; color: var(--text-main); outline: none; }
        .select-control { padding-left: 14px; width: auto; min-width: 160px; cursor: pointer; }
        .input-control:focus, .select-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }

        .table-card { background: #ffffff; border-radius: 20px; border: 1px solid var(--border); overflow: hidden; box-shadow: var(--card-shadow); margin-bottom: 24px; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
        .data-table th { padding: 14px 20px; background: #f8fafc; color: var(--text-muted); font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); }
        .data-table td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: #f8fafc; }

        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid transparent; }
        .badge-active, .badge-finished { background: var(--emerald-bg); color: var(--emerald); border-color: var(--emerald-border); }
        .badge-pending, .badge-trial { background: var(--amber-bg); color: var(--amber); border-color: var(--amber-border); }
        .badge-process { background: var(--sky-bg); color: var(--sky); border-color: var(--sky-border); }
        .badge-danger, .badge-expired { background: var(--rose-bg); color: var(--rose); border-color: var(--rose-border); }
        .badge-purple { background: var(--purple-bg); color: var(--purple); border-color: var(--purple-border); }
        .badge-slate { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }

        .alert-maintenance-box { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid var(--amber-border); border-radius: 22px; padding: 24px; margin-bottom: 28px; box-shadow: var(--card-shadow); }
        .alert-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
        .alert-header-info { display: flex; align-items: center; gap: 14px; }
        .alert-icon-bell { width: 44px; height: 44px; background: var(--amber); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; box-shadow: 0 8px 16px rgba(245, 158, 11, 0.3); }
        .alert-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; }
        .alert-card-item { background: #ffffff; border: 1px solid var(--amber-border); border-radius: 16px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; }

        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .form-group-full { grid-column: 1 / -1; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; margin-bottom: 6px; }
        .form-input-text, .form-textarea { width: 100%; padding: 12px 14px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 12px; font-size: 13.5px; color: var(--text-main); outline: none; }
        .form-input-text:focus, .form-textarea:focus { background: #ffffff; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }

        .detail-grid-layout { display: grid; grid-template-columns: 340px 1fr; gap: 24px; align-items: start; }
        .grid-2-col { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }

        /* Media Queries Adaptativas para Celulares y Tablets */
        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; width: 100%; }
            .topbar { padding: 0 16px; }
            .content-area { padding: 16px; }
            .form-grid-2 { grid-template-columns: 1fr; }
            .detail-grid-layout { grid-template-columns: 1fr; }
            .grid-2-col { grid-template-columns: 1fr; }
            .mobile-menu-btn { display: inline-flex; }
            .grid-kpi { grid-template-columns: 1fr 1fr; gap: 12px; }
            .kpi-card { padding: 14px; }
            .kpi-icon { width: 42px; height: 42px; font-size: 18px; }
            .kpi-num { font-size: 20px; }
            .card { padding: 16px; }
        }

        @media (max-width: 600px) {
            .grid-kpi { grid-template-columns: 1fr; }
            .topbar-title { font-size: 15px; }
            .topbar { height: 64px; }
            .btn { font-size: 12px; padding: 8px 14px; }
        }
    </style>
</head>
<body x-data="{ menuAbierto: false }">

    <!-- Backdrop oscurecido al abrir menú en celular -->
    <div x-show="menuAbierto" @click="menuAbierto = false" x-cloak class="sidebar-backdrop"></div>

    <!-- Barra lateral (Sidebar con Drawer Móvil) -->
    <aside class="sidebar" :class="menuAbierto ? 'open' : ''">
        
        <div class="sidebar-header" style="justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                @if(auth()->check() && auth()->user()->esSuperAdmin())
                    <div class="sidebar-logo-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 8px 16px rgba(245, 158, 11, 0.3);">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div class="sidebar-logo-text">
                        <h1>Servi<span>Gest</span></h1>
                        <span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24;">Master SaaS</span>
                    </div>
                @else
                    <div class="sidebar-logo-icon" style="background: var(--primary-gradient); box-shadow: 0 8px 16px rgba(2, 132, 199, 0.3);">
                        @if(auth()->check() && auth()->user()->taller && auth()->user()->taller->url_logo)
                            <img src="{{ auth()->user()->taller->url_logo }}" alt="Logo" style="max-width: 28px; max-height: 28px; object-fit: contain;">
                        @else
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        @endif
                    </div>
                    <div class="sidebar-logo-text">
                        <h1>Servi<span>Gest</span></h1>
                        <span class="badge" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8;">SaaS Pro</span>
                    </div>
                @endif
            </div>

            <!-- Botón cerrar menú en celular -->
            <button @click="menuAbierto = false" class="mobile-menu-btn" style="border: none; background: transparent; color: #94a3b8; font-size: 20px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        @if(auth()->check() && auth()->user()->taller)
            <div class="tenant-box">
                <div class="lbl">Taller Activo</div>
                <div class="taller-name">{{ auth()->user()->taller->nombre_comercial }}</div>
            </div>
        @endif

        <nav class="nav-list">
            
            {{-- MENÚ EXCLUSIVO DE SUPERADMIN (Dueño de la Plataforma) --}}
            @if(auth()->check() && auth()->user()->esSuperAdmin())
                <div class="nav-category" style="color: #fbbf24;">Consola Central SaaS</div>

                <a href="{{ route('superadmin.talleres.index') }}" class="nav-item {{ request()->routeIs('superadmin.talleres.*') ? 'active' : '' }}" style="{{ request()->routeIs('superadmin.talleres.*') ? 'background: linear-gradient(135deg, #d97706, #b45309);' : '' }}">
                    <i class="fa-solid fa-building-shield" style="color: #fbbf24;"></i>
                    <span>Talleres Registrados</span>
                </a>

                <a href="{{ route('superadmin.talleres.create') }}" class="nav-item" style="color: #fde68a;">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>Alta de Empresa</span>
                </a>

                <div class="nav-category" style="color: #fbbf24;">Planes Comerciales</div>

                <a href="{{ route('superadmin.licencias.index') }}" class="nav-item {{ request()->routeIs('superadmin.licencias.*') ? 'active' : '' }}" style="{{ request()->routeIs('superadmin.licencias.*') ? 'background: linear-gradient(135deg, #d97706, #b45309);' : '' }}">
                    <i class="fa-solid fa-award" style="color: #fbbf24;"></i>
                    <span>Planes & Licencias</span>
                </a>

                <div class="nav-category" style="color: #fbbf24;">Seguridad & Datos</div>

                <a href="{{ route('superadmin.respaldos.index') }}" class="nav-item {{ request()->routeIs('superadmin.respaldos.*') ? 'active' : '' }}" style="{{ request()->routeIs('superadmin.respaldos.*') ? 'background: linear-gradient(135deg, #d97706, #b45309);' : '' }}">
                    <i class="fa-solid fa-database" style="color: #fbbf24;"></i>
                    <span>Copias de Seguridad</span>
                </a>
            
            {{-- MENÚ OPERATIVO PARA TALLERES (Administradores y Técnicos) --}}
            @else
                <a href="{{ route('panel.index') }}" class="nav-item {{ request()->routeIs('panel.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Panel Principal</span>
                </a>

                <div class="nav-category">Operaciones</div>

                <a href="{{ route('ordenes.index') }}" class="nav-item {{ request()->routeIs('ordenes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Órdenes de Trabajo</span>
                </a>

                <a href="{{ route('clientes.index') }}" class="nav-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Clientes</span>
                </a>

                <a href="{{ route('equipos.index') }}" class="nav-item {{ request()->routeIs('equipos.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-laptop-medical"></i>
                    <span>Equipos & Dispositivos</span>
                </a>

                <div class="nav-category">Configuración & Equipo</div>

                <a href="{{ route('categorias.index') }}" class="nav-item {{ request()->routeIs('categorias.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-tags"></i>
                    <span>Categorías</span>
                </a>

                @if(auth()->check() && auth()->user()->esAdminTaller())
                    <div class="nav-category">Gerencia & Reportes</div>

                    <a href="{{ route('informes.index') }}" class="nav-item {{ request()->routeIs('informes.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line" style="color: #38bdf8;"></i>
                        <span>Informes & Estadísticas</span>
                    </a>

                    <a href="{{ route('personal.index') }}" class="nav-item {{ request()->routeIs('personal.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-gear"></i>
                        <span>Equipo & Técnicos</span>
                    </a>

                    <a href="{{ route('configuracion.index') }}" class="nav-item {{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-store"></i>
                        <span>Mi Empresa / Taller</span>
                    </a>

                    <a href="{{ route('respaldo.taller.index') }}" class="nav-item {{ request()->routeIs('respaldo.taller.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-hard-drive" style="color: #38bdf8;"></i>
                        <span>Copia de Seguridad</span>
                    </a>
                @endif
            @endif

        </nav>

        <div class="user-footer">
            <div class="user-info">
                <div class="user-avatar" style="{{ auth()->user()->esSuperAdmin() ? 'background: #78350f; color: #fbbf24;' : '' }}">
                    {{ strtoupper(substr(auth()->user()->nombre ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <div class="user-name">{{ auth()->user()->nombre_completo ?? 'Usuario' }}</div>
                    <div class="user-role">{{ str_replace('_', ' ', auth()->user()->rol ?? '') }}</div>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Cerrar sesión" style="background: none; border: none; color: #94a3b8; cursor: pointer; padding: 6px; font-size: 16px;">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Contenido Principal -->
    <div class="main-wrapper">
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 14px;">
                <!-- Botón de Menú Hamburguesa en Celular -->
                <button @click="menuAbierto = !menuAbierto" class="mobile-menu-btn" title="Menú de Navegación">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h2 class="topbar-title">@yield('titulo', 'Panel de Control')</h2>
            </div>
            
            @if(auth()->check() && auth()->user()->esSuperAdmin())
                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('superadmin.talleres.create') }}" class="btn btn-amber">
                        <i class="fa-solid fa-plus"></i>
                        <span>Alta de Taller</span>
                    </a>
                </div>
            @else
                <a href="{{ route('ordenes.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    <span>Nueva Orden</span>
                </a>
            @endif
        </header>

        <!-- Mensajes Flash de Éxito / Error -->
        <main class="content-area">
            @if(session('exito'))
                <div style="margin-bottom: 20px; padding: 14px 18px; background: var(--emerald-bg); border: 1.5px solid var(--emerald-border); border-radius: 14px; color: var(--emerald); font-weight: 800; font-size: 13.5px; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15);">
                    <i class="fa-solid fa-circle-check" style="font-size: 16px;"></i>
                    <span>{{ session('exito') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div style="margin-bottom: 20px; padding: 14px 18px; background: var(--rose-bg); border: 1.5px solid var(--rose-border); border-radius: 14px; color: var(--rose); font-weight: 800; font-size: 13.5px; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.15);">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 16px;"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div style="margin-bottom: 20px; padding: 14px 18px; background: var(--rose-bg); border: 1.5px solid var(--rose-border); border-radius: 14px; color: var(--rose); font-weight: 700; font-size: 13px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.15);">
                    <div style="font-weight: 900; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-xmark"></i> Por favor verifique los siguientes errores:
                    </div>
                    <ul style="padding-left: 20px; font-size: 12.5px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('contenido')
        </main>
    </div>

</body>
</html>
