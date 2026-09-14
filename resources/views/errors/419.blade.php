<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión Expirada - ServiGest</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background: #0b1120; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: #ffffff; border-radius: 24px; padding: 40px; max-width: 460px; width: 100%; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
        .icon { width: 70px; height: 70px; background: #fffbeb; color: #d97706; border: 2px solid #fde68a; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 20px; }
        h2 { font-size: 22px; font-weight: 900; color: #0f172a; margin-bottom: 8px; }
        p { font-size: 13.5px; color: #64748b; line-height: 1.5; margin-bottom: 28px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 14px; background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); color: #fff; font-weight: 800; font-size: 14px; border-radius: 14px; text-decoration: none; transition: transform 0.2s; }
        .btn:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <h2>Página o Sesión Expirada (419)</h2>
        <p>Por seguridad, el formulario de acceso caducó debido al tiempo de inactividad. Haz clic abajo para recargar e ingresar nuevamente.</p>
        <a href="{{ route('login') }}" class="btn">
            <i class="fa-solid fa-arrow-rotate-right"></i>
            <span>Recargar Pantalla de Acceso</span>
        </a>
    </div>
</body>
</html>
