<x-guest-layout>
    <style>
.page-wrapper {
    min-height: 100vh;

    background:
        linear-gradient(to bottom, rgba(0,0,0,0) 25%, #065f46 55%),
        url('/images/reciclado.png'),
        linear-gradient(135deg, #0f766e, #065f46, #022c22);

    background-size: 
        cover,        /* imagen */
        auto,         /* fallback */
        cover;        /* gradiente base */

    background-position: 
        top center,
        center,
        center;

    background-repeat: no-repeat;

    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

        .verify-container {
            width: 100%;
            max-width: 440px; /* Consistencia de tamaño estricta */
            text-align: center;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Marca - Link interactivo */
        .brand-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
            text-decoration: none;
            color: white;
            transition: transform 0.2s ease;
        }

        .brand-link:hover {
            transform: scale(1.03);
        }

        .brand-link span {
            font-size: 42px;
        }

        .brand-link h1 {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: -0.5px;
            text-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        /* Tarjeta */
        .card {
            background: rgba(255, 255, 255, 0.97);
            padding: 35px 30px;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            text-align: left;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card h2 {
            color: #0f766e;
            margin-bottom: 15px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            text-align: center;
        }

        /* Ilustración o Icono de espera */
        .mail-icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 15px;
            display: block;
        }

        /* Texto explicativo */
        .info-text {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 25px;
            text-align: center;
        }

        /* Mensaje de éxito al reenviar */
        .success-status {
            background-color: #ecfdf5;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 12px;
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            line-height: 1.4;
        }

        /* Grid para los dos formularios de acción */
        .actions-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 10px;
        }

        /* Botón primario de Reenvío */
        .btn-primary {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            font-weight: 700;
            font-size: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
            box-shadow: 0 15px 20px -3px rgba(5, 150, 105, 0.4);
        }

        /* Botón secundario para Cerrar Sesión */
        .btn-logout {
            background: none;
            border: none;
            color: #6b7280;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: underline;
            cursor: pointer;
            transition: color 0.2s;
            width: 100%;
            text-align: center;
            padding: 5px;
        }

        .btn-logout:hover {
            color: #111827;
        }

        /* ADAPTABILIDAD MULTIPLATAFORMA */
        @media (max-width: 640px) {
            .page-wrapper {
                padding: 15px;
            }
            .verify-container {
                max-width: 100%;
            }
            .card {
                padding: 25px 20px;
                border-radius: 16px;
            }
            .brand-link h1 {
                font-size: 30px;
            }
            .brand-link span {
                font-size: 36px;
            }
        }
    </style>

    <div class="page-wrapper">
        <div class="verify-container">

            <!-- Identidad Visual -->
            <a href="{{ url('/') }}" class="brand-link" title="Ir al inicio">
                <span>🌱</span>
                <h1>IT Conecta</h1>
            </a>

            <div class="card">
                <span class="mail-icon">📩</span>
                <h2>Verifica tu Correo</h2>
                
                <p class="info-text">
                    ¡Gracias por unirte! Antes de comenzar a registrar tus actividades ambientales, necesitamos que verifiques tu cuenta haciendo clic en el enlace que te acabamos de enviar a tu correo. ¿No lo has recibido? Con gusto te enviamos otro.
                </p>

                <!-- Status de sesión cuando se reenvía el link con éxito -->
                @if (session('status') == 'verification-link-sent')
                    <div class="success-status">
                        Se ha enviado un nuevo enlace de verificación al correo que registraste. ¡Revisa también tu bandeja de Spam!
                    </div>
                @endif

                <div class="actions-container">
                    <!-- Formulario 1: Reenviar Email de Verificación -->
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn-primary">
                            Reenviar correo de verificación
                        </button>
                    </form>

                    <!-- Formulario 2: Cerrar Sesión (por si se equivocó de mail o quiere salir) -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-logout">
                            Cerrar sesión actual
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-guest-layout>