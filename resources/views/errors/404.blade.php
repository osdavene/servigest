<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página No Encontrada (404) - ServiGest</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background: #0b1120; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: #ffffff; border-radius: 24px; padding: 40px; max-width: 480px; width: 100%; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
        .icon { width: 70px; height: 70px; background: #f0f9ff; color: #0284c7; border: 2px solid #bae6fd; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 20px; }
        .badge { display: inline-block; background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; margin-bottom: 12px; letter-spacing: 0.5px; text-transform: uppercase; }
        h2 { font-size: 22px; font-weight: 900; color: #0f172a; margin-bottom: 8px; }
        p { font-size: 13.5px; color: #64748b; line-height: 1.5; margin-bottom: 28px; }
        .actions { display: flex; flex-direction: column; gap: 10px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 14px; background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); color: #fff; font-weight: 800; font-size: 14px; border-radius: 14px; text-decoration: none; transition: transform 0.2s; }
        .btn:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <i class="fa-solid fa-compass"></i>
        </div>
        <span class="badge">Error 404 · No encontrado</span>
        <h2>Página No Encontrada</h2>
        <p>El enlace que buscas no existe, cambió de dirección o ha sido desactivado temporalmente. Verifica la dirección web o regresa al panel de control.</p>
        <div class="actions">
            <a href="{{ url('/') }}" class="btn">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Volver al Inicio</span>
            </a>
        </div>
    </div>
</body>
</html>
