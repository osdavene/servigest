# 🛠️ ServiGest SaaS — Sistema Integral para Talleres y Servicio Técnico

<div align="center">

![PHP Version](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Laravel Version](https://img.shields.io/badge/Laravel-11%20%2F%2012-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-Multi--Tenant%20SaaS-0284c7?style=for-the-badge)
![Deploy](https://img.shields.io/badge/AWS-CloudPanel%20CI%2FCD-FF9900?style=for-the-badge&logo=amazonwebservices&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**Plataforma SaaS Multi-Empresa diseñada para talleres de servicio técnico de electrodomésticos, cómputo, smartphones, climatización y herramientas industriales.**

[Características](#-características-principales) • [Instalación Rápida](#-instalación-rápida) • [Credenciales Demo](#-cuentas-y-credenciales-demo) • [Arquitectura](#-arquitectura-técnica)

</div>

---

## 🌟 Características Principales

### 🏢 1. Arquitectura Multi-Tenant (Multi-Empresa Aislada)
- **Aislamiento Estricto por Empresa (`taller_id`)**: Cada taller administra sus propios clientes, equipos, técnicos, órdenes de trabajo, evidencias fotográficas y políticas de garantía con aislamiento en base de datos.
- **Membresías y Planes de Licencia**: Control de suscripciones (Mensual, Anual, Período de Prueba) con bloqueo automático por vencimiento y gestión desde el panel de SuperAdmin.

### 📱 2. Integración Directa con WhatsApp y Portal Público de Seguimiento
- **Notificación por WhatsApp**: Genera enlaces directos con mensajes parametrizados para enviar cotizaciones y estados de servicio al cliente en 1 clic.
- **Portal Web de Consulta para el Cliente Final**: Enlace público seguro y tokenizado (`/consulta/{token}`) donde el cliente puede consultar el estado en tiempo real, desglose de costos, evidencias fotográficas y comprobante de entrega sin requerir login.

### 📸 3. Compresión Inteligente de Fotografías tipo WhatsApp
- **Optimización Automática GD en Servidor**: Las fotos tomadas desde celulares (5 MB - 10 MB) se comprimen automáticamente a ~250 KB - 400 KB en Full HD (1600px max, 80% calidad).
- **Corrección de Rotación EXIF**: Corrige automáticamente la orientación de fotos tomadas con teléfonos móviles en vertical/horizontal.
- **Clasificación Técnica por Etapas**:
  - `📥 Cómo se recibe el equipo` (Estado físico / Rayones / Daños iniciales)
  - `🔍 Falla encontrada / Diagnóstico` (Componentes dañados, cortos en placa)
  - `🛠️ Durante la reparación` (Repuestos nuevos montados, procedimientos)
  - `📤 Cómo se devuelve el equipo` (Equipo probado, limpio y funcionando)
- **Visor Lightbox en Alta Definición**: Visualizador modal en pantalla completa para inspeccionar fotografías al detalle tanto en el panel como en la vista pública de WhatsApp.

### ✍️ 4. Captura de Firma Digital Táctil
- Lienzo HTML5 Canvas responsivo para capturar la firma del cliente al momento de entregar el equipo, compatible con celulares, tablets y mouse de PC.

### 📄 5. Generación de Órdenes de Servicio e Informes en PDF
- **PDF Membretado Oficial**: Incluye el logo del taller, datos fiscales, diagnóstico del técnico, liquidación desglosada (Mano de Obra + Repuestos), galería fotográfica del servicio, firma digital y términos de garantía.
- **Descarga Directa y Envío Digital**.

### 🛡️ 6. Copias de Seguridad (Backup) en `.ZIP` & Restauración Atómica
- **Respaldo Completo de la Empresa**: Los administradores de taller pueden exportar y descargar un archivo comprimido `.ZIP` con el 100% de su información (Base de Datos en JSON + Fotos físicas de evidencias + Logo).
- **Política de Retención Automática**: Conserva únicamente los 3 respaldos más recientes en el servidor para optimizar espacio en disco.
- **Motor de Restauración Exclusivo para SuperAdmin**: Reconstrucción íntegra de la empresa en transacciones atómicas de base de datos (`DB::transaction`).

### 📊 7. Analíticas Gerenciales e Informes Financieros
- **KPIs en Tiempo Real**: Facturación total, ingresos por mano de obra, repuestos, ticket promedio, efectividad de reparación y tiempo promedio de entrega.
- **Desglose de Productividad por Técnico y Categorías**.
- **Exportación Ejecutiva a PDF y CSV para Excel**.

### ⏰ 8. Alertas de Mantenimiento Preventivo
- Cálculo automático de ciclos de servicio según la categoría del equipo (ej. Aires acondicionados cada 90 días, Laptops cada 180 días, Neveras cada 365 días) con alertas visuales de vencimiento.

---

## 🚀 Instalación Rápida (Local o Servidor)

### Requisitos del Sistema
- **PHP** >= 8.2 (Recomendado PHP 8.4) con extensiones: `pdo`, `sqlite`/`mysql`, `gd`, `zip`, `mbstring`, `openssl`.
- **Composer** >= 2.x
- **Node.js** & **NPM** (opcional para compilar assets).

### Pasos de Instalación:

1. **Clonar el repositorio**:
   ```bash
   git clone https://github.com/TU_USUARIO/servigest.git
   cd servigest
   ```

2. **Instalar dependencias de PHP**:
   ```bash
   composer install
   ```

3. **Configurar el archivo de entorno**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Crear el enlace de almacenamiento para fotos públicas**:
   ```bash
   php artisan storage:link
   ```

5. **Ejecutar migraciones y poblar la Base de Datos con Datos Demo**:
   ```bash
   # Para SQLite (crea el archivo database/database.sqlite si no existe)
   touch database/database.sqlite
   php artisan migrate:fresh --seed
   ```

6. **Iniciar el servidor local**:
   ```bash
   php artisan serve
   ```
   Accede a la aplicación en: `http://127.0.0.1:8000` (o `http://servigest.test` en Laravel Herd).

---

## 👥 Cuentas y Credenciales Demo

*(Todas las cuentas de prueba tienen la contraseña predeterminada: `password123`)*

| Rol | Empresa / Taller | Correo Electrónico | Especialidad |
| :--- | :--- | :--- | :--- |
| 👑 **Super Administrador** | *Plataforma ServiGest* | `superadmin@servigest.com` | Gestión de todas las empresas y respaldos |
| 🏢 **Administrador** | **ElectroTech Soluciones** (Bogotá) | `admin@electrotech.com` | Laptops, Computadores, Línea Blanca, TVs |
| 🛠️ Técnico 1 | ElectroTech Soluciones | `diego@electrotech.com` | Especialista en hardware y laptops |
| 🛠️ Técnico 2 | ElectroTech Soluciones | `javier@electrotech.com` | Especialista en refrigeración y TVs |
| 🏢 **Administrador** | **Climatización & Frío del Norte** (Barranquilla) | `admin@climatizacionnorte.com` | Aires acondicionados, Cuartos fríos |
| 🛠️ Técnico | Climatización & Frío del Norte | `guillermo@climatizacionnorte.com` | Técnico en refrigeración industrial |
| 🏢 **Administrador** | **MacroFix Móviles & Gaming Pro** (Medellín) | `admin@macrofix.com` | Celulares iPhone/Samsung, PS5, Xbox |
| 🛠️ Técnico | MacroFix Móviles & Gaming Pro | `alejandro@macrofix.com` | Microsoldadura y consolas de videojuegos |
| 🏢 **Administrador** | **ElectroHogar del Valle** (Cali) | `admin@electrohogarvalle.com` | Empresa en período de prueba |

---

## 🏗️ Arquitectura Técnica

- **Framework**: Laravel 11 / 12
- **Base de Datos**: SQLite (desarrollo/producción ligera), compatible con MySQL / PostgreSQL.
- **Frontend**: Blade Components + Alpine.js (reactividad ligera sin sobrecarga de frameworks pesados) + CSS Autónomo Responsivo (Mobile-First).
- **Procesamiento de Imágenes**: PHP GD nativo (`App\Services\OptimizadorImagenes`).
- **Generación de Documentos**: Barryvdh DomPDF (`App\Services\ExportadorInformesService`).
- **Control de Acceso**: Middleware personalizado `InquilinoActivo` y control granular por roles (`super_administrador`, `administrador`, `tecnico`).

---

## 📄 Licencia

Este proyecto es de código abierto bajo la licencia [MIT](LICENSE).
