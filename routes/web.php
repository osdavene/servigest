<?php

use App\Http\Controllers\AutenticacionController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfiguracionTallerController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\EstadisticaReporteController;
use App\Http\Controllers\EvidenciaFotograficaController;
use App\Http\Controllers\OrdenTrabajoController;
use App\Http\Controllers\PanelPrincipalController;
use App\Http\Controllers\ReportePdfController;
use App\Http\Controllers\RespaldoTallerController;
use App\Http\Controllers\SuperAdmin\PlanLicenciaController as SuperAdminPlanLicenciaController;
use App\Http\Controllers\SuperAdmin\RespaldoController as SuperAdminRespaldoController;
use App\Http\Controllers\SuperAdmin\TallerController as SuperAdminTallerController;
use App\Http\Controllers\UsuarioTallerController;
use Illuminate\Support\Facades\Route;

// Redirección inicial
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->esSuperAdmin() 
            ? redirect()->route('superadmin.talleres.index') 
            : redirect()->route('panel.index');
    }
    return redirect()->route('login');
});

// Autenticación de Clientes y Técnicos (Talleres)
Route::get('/login', [AutenticacionController::class, 'mostrarFormularioLogin'])->name('login');
Route::post('/login', [AutenticacionController::class, 'iniciarSesion'])->name('login.post');
Route::post('/logout', [AutenticacionController::class, 'cerrarSesion'])->name('logout');

// Acceso Exclusivo y Privado para el Dueño del SaaS (Super Administrador)
Route::get('/control-central', [AutenticacionController::class, 'mostrarFormularioLoginSuperAdmin'])->name('superadmin.login');
Route::post('/control-central', [AutenticacionController::class, 'iniciarSesionSuperAdmin'])->name('superadmin.login.post');

// Rutas Públicas de Consulta e Informe para Clientes (acceso directo desde enlace de WhatsApp)
Route::get('/servicio/{token}', [ReportePdfController::class, 'verPdfPublico'])->name('ordenes.pdf.publico');
Route::get('/servicio/{token}/ver', [ReportePdfController::class, 'verPdfPublico'])->name('ordenes.publico');
Route::get('/servicio/{token}/descargar-pdf', [ReportePdfController::class, 'descargarPdfPublico'])->name('ordenes.pdf.descargar_publico');

// Portal de Autoservicio para Clientes B2B
Route::get('/portal/{token}', [\App\Http\Controllers\PortalClienteController::class, 'index'])->name('portal.cliente');
Route::post('/portal/{token}/solicitar', [\App\Http\Controllers\PortalClienteController::class, 'solicitarServicio'])->name('portal.cliente.solicitar');

// Rutas Protegidas del Sistema
Route::middleware(['auth'])->group(function () {
    
    // Módulos Operativos Exclusivos de Talleres (Con validación de inquilino activo)
    Route::middleware(['inquilino.activo'])->group(function () {
        // Panel de Control / Dashboard del Taller
        Route::get('/panel', [PanelPrincipalController::class, 'index'])->name('panel.index');

        // Búsqueda Inteligente en Tiempo Real de Clientes (Autocompletado reactivo)
        Route::get('/api/clientes/buscar', [ClienteController::class, 'apiBuscar'])->name('api.clientes.buscar');

        // Gestión de Clientes
        Route::resource('clientes', ClienteController::class)->parameters(['clientes' => 'cliente']);

        // Gestión de Categorías Dinámicas de Equipos
        Route::resource('categorias', CategoriaController::class)->parameters(['categorias' => 'categoria'])->except(['create', 'show', 'edit']);

        // Gestión de Equipos
        Route::resource('equipos', EquipoController::class)->parameters(['equipos' => 'equipo']);

        // Gestión de Órdenes de Trabajo
        Route::resource('ordenes', OrdenTrabajoController::class)->parameters(['ordenes' => 'orden']);
        Route::get('/ordenes/{orden}/descargar-pdf', [ReportePdfController::class, 'descargarPdf'])->name('ordenes.pdf.descargar');
        Route::get('/ordenes/{orden}/ticket-pos', [\App\Http\Controllers\TicketPosController::class, 'ticket'])->name('ordenes.ticket.pos');
        Route::get('/ordenes/{orden}/etiqueta-qr', [\App\Http\Controllers\TicketPosController::class, 'etiqueta'])->name('ordenes.etiqueta.qr');

        // Evidencias Fotográficas
        Route::post('/ordenes/{orden}/evidencias', [EvidenciaFotograficaController::class, 'store'])->name('evidencias.store');
        Route::delete('/evidencias/{evidencia}', [EvidenciaFotograficaController::class, 'destroy'])->name('evidencias.destroy');

        // Módulos Exclusivos para Administradores del Taller
        Route::middleware('rol:administrador')->group(function () {
            // Informes, Estadísticas y Analíticas Gerenciales
            Route::get('/informes', [EstadisticaReporteController::class, 'index'])->name('informes.index');
            Route::get('/informes/exportar-pdf', [EstadisticaReporteController::class, 'exportarPdf'])->name('informes.pdf');
            Route::get('/informes/exportar-csv', [EstadisticaReporteController::class, 'exportarCsv'])->name('informes.csv');

            // Copias de Seguridad de la Empresa (Backup del 100%)
            Route::get('/respaldo', [RespaldoTallerController::class, 'index'])->name('respaldo.taller.index');
            Route::get('/respaldo/descargar', [RespaldoTallerController::class, 'descargar'])->name('respaldo.taller.descargar');
            Route::get('/respaldo/descargar-archivo/{archivo}', [RespaldoTallerController::class, 'descargarArchivo'])->name('respaldo.taller.descargar_archivo');

            // Gestión de Técnicos y Personal del Taller
            Route::resource('personal', UsuarioTallerController::class)->parameters(['personal' => 'usuario']);
            Route::post('personal/{usuario}/desconectar', [UsuarioTallerController::class, 'desconectarSesion'])->name('personal.desconectar');

            // Configuración y Perfil del Taller
            Route::get('/configuracion', [ConfiguracionTallerController::class, 'index'])->name('configuracion.index');
            Route::put('/configuracion', [ConfiguracionTallerController::class, 'update'])->name('configuracion.update');
        });
    });

    // Módulos Exclusivos del Super Administrador (Dueño de la Plataforma SaaS)
    Route::middleware('rol:super_administrador')->prefix('superadmin')->name('superadmin.')->group(function () {
        
        // Gestión de Talleres
        Route::resource('talleres', SuperAdminTallerController::class)->parameters(['talleres' => 'taller']);
        Route::post('talleres/{taller}/toggle-estado', [SuperAdminTallerController::class, 'toggleEstado'])->name('talleres.toggle-estado');
        Route::post('talleres/{taller}/extender-licencia', [SuperAdminTallerController::class, 'extenderLicencia'])->name('talleres.extender-licencia');

        // Gestión de Planes de Licencia
        Route::resource('licencias', SuperAdminPlanLicenciaController::class)->parameters(['licencias' => 'planLicencia']);

        // Centro de Respaldos de Base de Datos y Restauración
        Route::get('respaldos', [SuperAdminRespaldoController::class, 'index'])->name('respaldos.index');
        Route::get('respaldos/global', [SuperAdminRespaldoController::class, 'descargarGlobal'])->name('respaldos.global');
        Route::get('respaldos/taller/{taller}', [SuperAdminRespaldoController::class, 'descargarPorTaller'])->name('respaldos.taller');
        Route::post('respaldos/restaurar', [SuperAdminRespaldoController::class, 'restaurarEmpresa'])->name('respaldos.restaurar');
    });

});
