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

    <!-- Script para prevenir parpadeo de tema (Anti-FOUC) -->
    <script>
        (function() {
            var savedTheme = localStorage.getItem('servigest_theme');
            if (!savedTheme) {
                savedTheme = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <!-- Sistema de Diseño CSS Completo y Autónomo (Mobile First & Responsive) -->
    <style>
        :root {
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --primary-gradient: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            --sidebar-bg: #0b1120;
            --sidebar-border: rgba(255, 255, 255, 0.06);
            --sidebar-text: #94a3b8;
            --sidebar-hover: #162033;
            --sidebar-hover-text: #ffffff;
            --sidebar-logo-text: #ffffff;
            --sidebar-category: #475569;
            --sidebar-tenant-bg: rgba(255, 255, 255, 0.04);
            --sidebar-tenant-border: rgba(255, 255, 255, 0.06);
            --sidebar-tenant-lbl: #64748b;
            --sidebar-footer-bg: rgba(0, 0, 0, 0.25);
            --sidebar-footer-border: rgba(255, 255, 255, 0.06);
            --sidebar-user-name: #ffffff;
            --sidebar-logout-btn: #94a3b8;
            --bg-page: #f1f5f9;
            --card-bg: #ffffff;
            --topbar-bg: #ffffff;
            --border: #e2e8f0;
            --border-subtle: #f1f5f9;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --table-header-bg: #f8fafc;
            --table-hover-bg: #f8fafc;
            --input-bg: #ffffff;
            --input-bg-alt: #f8fafc;
            --input-border: #e2e8f0;
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
            --theme-toggle-bg: #f1f5f9;
            --theme-toggle-border: #e2e8f0;
            --theme-toggle-color: #64748b;
        }

        /* Variables para TEMA OSCURO (Paleta UI Kit: Slate Petrol & Cyan #2BA8E2 / #374F5B / #26353D / #2F3F49) */
        [data-theme="dark"] {
            --primary: #2BA8E2;
            --primary-hover: #0092D0;
            --primary-gradient: linear-gradient(135deg, #2BA8E2 0%, #0092D0 100%);
            --sidebar-bg: #161E24;
            --sidebar-border: #2F3F49;
            --sidebar-text: #8BA2B2;
            --sidebar-hover: #2F3F49;
            --sidebar-hover-text: #ffffff;
            --sidebar-logo-text: #ffffff;
            --sidebar-category: #617D8F;
            --sidebar-tenant-bg: #1D262C;
            --sidebar-tenant-border: #2F3F49;
            --sidebar-tenant-lbl: #8BA2B2;
            --sidebar-footer-bg: #11171C;
            --sidebar-footer-border: #2F3F49;
            --sidebar-user-name: #ffffff;
            --sidebar-logout-btn: #8BA2B2;
            --bg-page: #1D262C;
            --card-bg: #26353D;
            --topbar-bg: #222D35;
            --border: #374F5B;
            --border-subtle: #2F3F49;
            --text-main: #FFFFFF;
            --text-muted: #8BA2B2;
            --table-header-bg: #1E2830;
            --table-hover-bg: #2F3F49;
            --input-bg: #1D262C;
            --input-bg-alt: #222D35;
            --input-border: #374F5B;
            --emerald: #10b981;
            --emerald-bg: rgba(16, 185, 129, 0.18);
            --emerald-border: rgba(16, 185, 129, 0.35);
            --amber: #F79B1E;
            --amber-bg: rgba(247, 155, 30, 0.18);
            --amber-border: rgba(247, 155, 30, 0.4);
            --rose: #ef4444;
            --rose-bg: rgba(239, 68, 68, 0.18);
            --rose-border: rgba(239, 68, 68, 0.35);
            --sky: #36AFED;
            --sky-bg: rgba(43, 168, 226, 0.18);
            --sky-border: rgba(43, 168, 226, 0.4);
            --purple: #a855f7;
            --purple-bg: rgba(168, 85, 247, 0.18);
            --purple-border: rgba(168, 85, 247, 0.35);
            --card-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.35);
            --modal-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            --theme-toggle-bg: #222D35;
            --theme-toggle-border: #374F5B;
            --theme-toggle-color: #36AFED;
        }

        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.25) transparent;
        }

        /* Scrollbars Globales Ultrafinos y Modernos */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.25);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.45);
        }

        ::-webkit-scrollbar-button {
            display: none;
            width: 0;
            height: 0;
        }

        body { background-color: var(--bg-page); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }
        [x-cloak] { display: none !important; }

        /* Barra Lateral */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 100;
            border-right: 1px solid var(--sidebar-border);
            transition: transform 0.3s ease, background-color 0.25s ease, border-color 0.25s ease;
        }

        .sidebar-header {
            height: 76px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--sidebar-border);
            gap: 12px;
            transition: border-color 0.25s ease;
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

        .sidebar-logo-text h1 { font-size: 20px; font-weight: 900; color: var(--sidebar-logo-text); letter-spacing: -0.5px; transition: color 0.25s ease; }
        .sidebar-logo-text h1 span { color: #0284c7; }
        [data-theme="dark"] .sidebar-logo-text h1 span { color: #38bdf8; }
        .sidebar-logo-text span.badge { font-size: 9px; text-transform: uppercase; font-weight: 800; padding: 2px 6px; border-radius: 4px; }

        .tenant-box {
            margin: 16px;
            padding: 14px;
            background: var(--sidebar-tenant-bg);
            border-radius: 14px;
            border: 1px solid var(--sidebar-tenant-border);
            transition: all 0.25s ease;
        }

        .tenant-box .lbl { font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--sidebar-tenant-lbl); }
        .tenant-box .taller-name { font-size: 13px; font-weight: 800; color: #0284c7; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        [data-theme="dark"] .tenant-box .taller-name { color: #38bdf8; }

        .nav-list { 
            flex: 1; 
            padding: 8px 14px; 
            overflow-y: auto; 
            overflow-x: hidden;
            display: flex; 
            flex-direction: column; 
            gap: 4px; 
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.12) transparent;
        }

        .nav-list::-webkit-scrollbar {
            width: 4px;
        }

        .nav-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .nav-list::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 8px;
        }

        .nav-list::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .nav-category { font-size: 10px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; color: var(--sidebar-category); padding: 14px 12px 6px; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            border-radius: 12px;
            color: var(--sidebar-text);
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
        }

        .nav-item i { font-size: 16px; width: 20px; text-align: center; }
        .nav-item:hover { background: var(--sidebar-hover); color: var(--sidebar-hover-text); }
        .nav-item.active { background: var(--primary); color: #ffffff !important; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35); }
        .nav-item.active i { color: #ffffff !important; }

        .user-footer {
            padding: 16px 20px;
            background: var(--sidebar-footer-bg);
            border-top: 1px solid var(--sidebar-footer-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.25s ease;
        }

        .user-info { display: flex; align-items: center; gap: 10px; overflow: hidden; }
        .user-avatar { width: 36px; height: 36px; border-radius: 10px; background: #0369a1; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; shrink-0: 0; }
        .user-name { font-size: 13px; font-weight: 800; color: var(--sidebar-user-name); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 10.5px; color: var(--text-muted); text-transform: capitalize; }
        .logout-btn { background: none; border: none; color: var(--sidebar-logout-btn); cursor: pointer; padding: 6px; font-size: 16px; transition: color 0.2s; }
        .logout-btn:hover { color: var(--rose); }

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
            background: var(--card-bg);
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
        .kpi-card { 
            background: var(--card-bg); 
            border-radius: 18px; 
            border: 1px solid var(--border); 
            padding: 20px; 
            display: flex; 
            align-items: center; 
            gap: 16px; 
            box-shadow: var(--card-shadow); 
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.2s ease;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            border-color: var(--primary);
            box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.12), 0 4px 8px -4px rgba(0, 0, 0, 0.06);
        }
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
        .btn-outline { background: var(--card-bg); border: 1.5px solid var(--border); color: var(--text-main); }
        .btn-outline:hover { background: var(--table-hover-bg); border-color: var(--primary); color: var(--primary); }
        .btn-icon { width: 34px; height: 34px; padding: 0; border-radius: 10px; font-size: 14px; }

        /* Botón Conmutador de Tema */
        .btn-theme-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: var(--theme-toggle-bg);
            border: 1.5px solid var(--theme-toggle-border);
            color: var(--theme-toggle-color);
            cursor: pointer;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        .btn-theme-toggle:hover {
            transform: translateY(-1px);
            border-color: var(--primary);
            color: var(--primary);
        }
        [data-theme="dark"] .btn-theme-toggle:hover {
            color: #fbbf24;
            border-color: #fbbf24;
        }

        /* Clases de Paginación Compartidas */
        .btn-pagination {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            background: var(--card-bg);
            color: var(--text-main);
            border: 1px solid var(--border);
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .btn-pagination:hover {
            background: var(--table-hover-bg);
            border-color: var(--primary);
            color: var(--primary);
        }
        .btn-pagination-active {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            background: var(--primary);
            color: #ffffff;
            border: 1px solid var(--primary);
            border-radius: 10px;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35);
        }
        .btn-pagination-disabled {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 34px;
            padding: 0 12px;
            background: var(--bg-page);
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: 10px;
            cursor: not-allowed;
            font-weight: 600;
            gap: 6px;
            opacity: 0.7;
        }

        .filter-bar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 22px; }
        .search-group { display: flex; align-items: center; gap: 10px; flex: 1; max-width: 500px; }
        .search-input-wrapper { position: relative; flex: 1; display: flex; align-items: center; }
        .search-input-wrapper i { position: absolute; left: 14px; color: var(--text-muted); font-size: 14px; }
        .input-control, .select-control { width: 100%; padding: 10px 14px 10px 38px; background: var(--input-bg); border: 1.5px solid var(--border); border-radius: 12px; font-size: 13px; color: var(--text-main); outline: none; }
        .select-control { padding-left: 14px; width: auto; min-width: 160px; cursor: pointer; }
        .input-control:focus, .select-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }

        .table-card { background: var(--card-bg); border-radius: 20px; border: 1px solid var(--border); overflow: hidden; box-shadow: var(--card-shadow); margin-bottom: 24px; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
        .data-table th { padding: 14px 20px; background: var(--table-header-bg); color: var(--text-muted); font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); }
        .data-table td { padding: 16px 20px; border-bottom: 1px solid var(--border-subtle); vertical-align: middle; color: var(--text-main); }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: var(--table-hover-bg); }

        /* Filas clickables en tablas */
        .data-table tbody tr.clickable-row,
        .data-table tbody tr[data-href] {
            cursor: pointer;
            transition: background-color 0.15s ease;
        }

        .data-table tbody tr.clickable-row:hover,
        .data-table tbody tr[data-href]:hover {
            background-color: var(--table-hover-bg) !important;
        }

        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid transparent; }
        .badge-active, .badge-finished { background: var(--emerald-bg); color: var(--emerald); border-color: var(--emerald-border); }
        .badge-pending, .badge-trial { background: var(--amber-bg); color: var(--amber); border-color: var(--amber-border); }
        .badge-process { background: var(--sky-bg); color: var(--sky); border-color: var(--sky-border); }
        .badge-danger, .badge-expired { background: var(--rose-bg); color: var(--rose); border-color: var(--rose-border); }
        .badge-purple { background: var(--purple-bg); color: var(--purple); border-color: var(--purple-border); }
        .badge-slate { background: var(--bg-page); color: var(--text-muted); border-color: var(--border); }

        .alert-maintenance-box { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid var(--amber-border); border-radius: 22px; padding: 24px; margin-bottom: 28px; box-shadow: var(--card-shadow); }
        .alert-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
        .alert-header-info { display: flex; align-items: center; gap: 14px; }
        .alert-icon-bell { width: 44px; height: 44px; background: var(--amber); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; box-shadow: 0 8px 16px rgba(245, 158, 11, 0.3); }
        .alert-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; }
        .alert-card-item { background: var(--card-bg); border: 1px solid var(--amber-border); border-radius: 16px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; }

        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .form-group-full { grid-column: 1 / -1; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 6px; }
        .form-input-text, .form-textarea { width: 100%; padding: 12px 14px; background: var(--input-bg-alt); border: 1.5px solid var(--border); border-radius: 12px; font-size: 13.5px; color: var(--text-main); outline: none; }
        .form-input-text:focus, .form-textarea:focus { background: var(--input-bg); border-color: var(--primary); box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }

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

        /* Reglas Específicas de Tema Oscuro (Paleta UI Kit) */
        [data-theme="dark"] .mobile-menu-btn {
            background: #26353D;
            border-color: #374F5B;
            color: #E2EDF3;
        }
        [data-theme="dark"] .alert-maintenance-box {
            background: linear-gradient(135deg, rgba(247, 155, 30, 0.12) 0%, rgba(224, 106, 17, 0.18) 100%);
            border-color: rgba(247, 155, 30, 0.4);
        }
        [data-theme="dark"] .alert-card-item {
            background: #222D35;
            border-color: rgba(247, 155, 30, 0.25);
        }
        [data-theme="dark"] .alert-card-item strong {
            color: #FFFFFF;
        }
        [data-theme="dark"] select option {
            background: #26353D;
            color: #FFFFFF;
        }
        [data-theme="dark"] .btn-dark {
            background: #161E24;
            border: 1px solid #374F5B;
            color: #FFFFFF;
        }
        [data-theme="dark"] .btn-dark:hover {
            background: #26353D;
        }
        [data-theme="dark"] .btn-outline {
            background: #26353D;
            border-color: #374F5B;
            color: #E2EDF3;
        }
        [data-theme="dark"] .btn-outline:hover {
            background: #2F3F49;
            border-color: #2BA8E2;
            color: #36AFED;
        }
        [data-theme="dark"] [style*="color: #0f172a"],
        [data-theme="dark"] [style*="color: #334155"] {
            color: #FFFFFF !important;
        }
        [data-theme="dark"] [style*="color: #64748b"],
        [data-theme="dark"] [style*="color: #475569"] {
            color: #8BA2B2 !important;
        }
        [data-theme="dark"] [style*="background: #ffffff"],
        [data-theme="dark"] [style*="background: #fff"],
        [data-theme="dark"] [style*="background-color: #ffffff"],
        [data-theme="dark"] [style*="background: #f8fafc"],
        [data-theme="dark"] [style*="background: #f1f5f9"] {
            background-color: var(--card-bg) !important;
        }
        [data-theme="dark"] [style*="border: 1px solid #e2e8f0"],
        [data-theme="dark"] [style*="border-bottom: 1px solid #f1f5f9"],
        [data-theme="dark"] [style*="border-top: 1px solid #f1f5f9"],
        [data-theme="dark"] [style*="border-bottom: 1px solid #e2e8f0"],
        [data-theme="dark"] [style*="border-top: 1px solid #e2e8f0"] {
            border-color: var(--border) !important;
        }
    </style>
</head>
<body x-data="{ 
    menuAbierto: false,
    temaOscuro: document.documentElement.getAttribute('data-theme') === 'dark',
    toggleTheme() {
        this.temaOscuro = !this.temaOscuro;
        const nuevoTema = this.temaOscuro ? 'dark' : 'light';
        localStorage.setItem('servigest_theme', nuevoTema);
        document.documentElement.setAttribute('data-theme', nuevoTema);
    }
}">

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
            <button @click="menuAbierto = false" class="mobile-menu-btn" style="border: none; background: transparent; color: var(--sidebar-text); font-size: 20px;">
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

                <div class="nav-category" style="color: #fbbf24;">Seguridad & Accesos</div>

                <a href="{{ route('superadmin.seguridad.index') }}" class="nav-item {{ request()->routeIs('superadmin.seguridad.*') ? 'active' : '' }}" style="{{ request()->routeIs('superadmin.seguridad.*') ? 'background: linear-gradient(135deg, #d97706, #b45309); color: #ffffff;' : '' }}">
                    <i class="fa-solid fa-shield-halved" style="color: #fbbf24;"></i>
                    <span>Auditoría & Accesos</span>
                </a>

                <a href="{{ route('superadmin.respaldos.index') }}" class="nav-item {{ request()->routeIs('superadmin.respaldos.*') ? 'active' : '' }}" style="{{ request()->routeIs('superadmin.respaldos.*') ? 'background: linear-gradient(135deg, #d97706, #b45309); color: #ffffff;' : '' }}">
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
                <button type="submit" title="Cerrar sesión" class="logout-btn">
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
            
            <div style="display: flex; align-items: center; gap: 10px;">
                <!-- Conmutador de Modo Claro / Oscuro -->
                <button type="button" @click="toggleTheme()" class="btn-theme-toggle" :title="temaOscuro ? 'Cambiar a Modo Claro' : 'Cambiar a Modo Oscuro'" aria-label="Cambiar tema">
                    <i class="fa-solid" :class="temaOscuro ? 'fa-sun' : 'fa-moon'"></i>
                </button>

                @if(auth()->check() && auth()->user()->esSuperAdmin())
                    <a href="{{ route('superadmin.talleres.create') }}" class="btn btn-amber">
                        <i class="fa-solid fa-plus"></i>
                        <span>Alta de Taller</span>
                    </a>
                @else
                    <a href="{{ route('ordenes.create') }}" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        <span>Nueva Orden</span>
                    </a>
                @endif
            </div>
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

    <!-- Script Global para Filas Clickables en Listados -->
    <script>
        document.addEventListener('click', function(e) {
            const interactive = e.target.closest('a, button, input, select, textarea, form, label, [data-no-click]');
            if (interactive) return;

            const row = e.target.closest('tr[data-href], tr.clickable-row');
            if (row) {
                const href = row.getAttribute('data-href');
                if (href) {
                    window.location.href = href;
                }
            }
        });
    </script>

    <!-- Monitor de Seguridad: Cierre Automático tras 15 Minutos de Inactividad -->
    @auth
    <script>
        (function() {
            const TIEMPO_INACTIVIDAD_MS = 15 * 60 * 1000; // 15 minutos de inactividad
            const STORAGE_KEY = 'servigest_ultima_actividad';
            let temporizadorInactividad;

            function registrarActividad() {
                const ahora = Date.now();
                localStorage.setItem(STORAGE_KEY, ahora.toString());
                reiniciarTemporizador();
            }

            function verificarInactividad() {
                const ultimaActividad = parseInt(localStorage.getItem(STORAGE_KEY) || Date.now(), 10);
                const transcurrido = Date.now() - ultimaActividad;

                if (transcurrido >= TIEMPO_INACTIVIDAD_MS) {
                    cerrarSesionPorInactividad();
                } else {
                    const restante = TIEMPO_INACTIVIDAD_MS - transcurrido;
                    clearTimeout(temporizadorInactividad);
                    temporizadorInactividad = setTimeout(verificarInactividad, restante);
                }
            }

            function reiniciarTemporizador() {
                clearTimeout(temporizadorInactividad);
                temporizadorInactividad = setTimeout(verificarInactividad, TIEMPO_INACTIVIDAD_MS);
            }

            function cerrarSesionPorInactividad() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('logout') }}';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                const motivoInput = document.createElement('input');
                motivoInput.type = 'hidden';
                motivoInput.name = 'motivo';
                motivoInput.value = 'inactividad';
                form.appendChild(motivoInput);

                document.body.appendChild(form);
                form.submit();
            }

            const eventos = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'click'];
            let throttleTimeout;
            function manejadorActividad() {
                if (!throttleTimeout) {
                    throttleTimeout = setTimeout(function() {
                        registrarActividad();
                        throttleTimeout = null;
                    }, 1000);
                }
            }

            eventos.forEach(evento => {
                window.addEventListener(evento, manejadorActividad, { passive: true });
            });

            window.addEventListener('storage', function(e) {
                if (e.key === STORAGE_KEY) {
                    verificarInactividad();
                }
            });

            registrarActividad();
        })();
    </script>
    @endauth
</body>
</html>
