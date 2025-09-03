<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error del Servidor - {{ config('app.name', 'Shalom ERP') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container-500 {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(121, 72, 29, 0.2);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .error-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #E1B240, #79481D);
        }

        .error-number {
            font-size: 8rem;
            font-weight: 700;
            background: linear-gradient(45deg, #E1B240, #79481D);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 1rem;
            position: relative;
        }

        .error-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 6rem;
            color: rgba(225, 178, 64, 0.1);
            z-index: -1;
        }

        .error-title {
            font-size: 2.5rem;
            font-weight: 600;
            color: #79481D;
            margin-bottom: 1rem;
        }

        .error-subtitle {
            font-size: 1.2rem;
            color: #6B7280;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .btn {
            padding: 0.75rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(45deg, #E1B240, #79481D);
            color: white;
            box-shadow: 0 4px 15px rgba(225, 178, 64, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(225, 178, 64, 0.4);
            color: white;
        }

        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .shape-1 {
            top: 10%;
            left: 10%;
            width: 80px;
            height: 80px;
            background: #E1B240;
            border-radius: 50%;
            animation-delay: 0s;
        }

        .shape-2 {
            top: 70%;
            right: 10%;
            width: 60px;
            height: 60px;
            background: #79481D;
            transform: rotate(45deg);
            animation-delay: 2s;
        }

        .shape-3 {
            bottom: 20%;
            left: 20%;
            width: 100px;
            height: 100px;
            background: #E1B240;
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .logo-container {
            margin-bottom: 2rem;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            background: linear-gradient(45deg, #E1B240, #79481D);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(225, 178, 64, 0.3);
        }

        .logo i {
            font-size: 2.5rem;
            color: white;
        }

        .status-info {
            background: rgba(225, 178, 64, 0.05);
            border: 1px solid rgba(225, 178, 64, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .status-title {
            font-size: 1rem;
            font-weight: 600;
            color: #79481D;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .status-text {
            font-size: 0.9rem;
            color: #6B7280;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .error-card {
                padding: 2rem;
                margin: 1rem;
            }

            .error-number {
                font-size: 6rem;
            }

            .error-title {
                font-size: 2rem;
            }

            .error-subtitle {
                font-size: 1rem;
            }

            .btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="container-500">
        <div class="error-card">
            <div class="logo-container">
                <div class="logo">
                    <i class="bi bi-gear"></i>
                </div>
            </div>

            <div class="error-number">
                500
                <div class="error-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
            
            <h1 class="error-title">Error interno del servidor</h1>
            <p class="error-subtitle">
                Estamos experimentando problemas técnicos temporales. 
                Nuestro equipo ha sido notificado y está trabajando para solucionarlo.
            </p>

            <div class="status-info">
                <div class="status-title">
                    <i class="bi bi-info-circle"></i>
                    ¿Qué significa esto?
                </div>
                <div class="status-text">
                    Un error interno del servidor significa que algo salió mal en nuestro sistema. 
                    No es un problema con tu conexión o dispositivo.
                </div>
            </div>

            <div class="action-buttons">
                <a href="javascript:history.back()" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i>
                    Volver a la página anterior
                </a>
            </div>
        </div>
    </div>

    <script>
        // Efecto de paralaje suave
        document.addEventListener('mousemove', (e) => {
            const shapes = document.querySelectorAll('.shape');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;

            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 0.5;
                const xPos = (x - 0.5) * speed * 10;
                const yPos = (y - 0.5) * speed * 10;
                
                shape.style.transform = `translate(${xPos}px, ${yPos}px)`;
            });
        });

        // Animación de entrada
        window.addEventListener('load', () => {
            const card = document.querySelector('.error-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });

        // Auto-refresh cada 30 segundos (opcional)
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>
