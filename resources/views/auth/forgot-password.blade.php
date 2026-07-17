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

        .forgot-container {
            width: 100%;
            max-width: 440px; /* Consistencia de tamaño con Login y Register */
            text-align: center;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Marca - Link interactivo idéntico */
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

        /* Mensaje informativo adaptado */
        .info-text {
            font-size: 14.5px;
            color: #4b5563; /* Gris legible */
            line-height: 1.6;
            margin-bottom: 25px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #065f46;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Input coherente */
        input[type="email"] {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            font-size: 15px;
            color: #1f2937;
            background-color: #f9fafb;
            transition: all 0.2s ease-in-out;
        }

        input[type="email"]:focus {
            outline: none;
            border-color: #10b981;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }

        /* Botón estilo Eco Premium */
        .btn {
            width: 100%;
            margin-top: 10px;
            padding: 15px;
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

        .btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
            box-shadow: 0 15px 20px -3px rgba(5, 150, 105, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Enlaces inferiores */
        .actions-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 25px;
            font-size: 13.5px;
        }

        .actions-wrapper a {
            color: #065f46;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .actions-wrapper a:hover {
            text-decoration: underline;
            color: #0d9488;
        }

        /* Mensajes de error */
        .error {
            font-size: 12.5px;
            color: #b91c1c;
            margin-top: 8px;
            font-weight: 500;
        }

        /* Status de sesión para cuando el correo se envía con éxito */
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
        }

        /* ADAPTABILIDAD (PC, TABLET, MOVIL) */
        @media (max-width: 640px) {
            .page-wrapper {
                padding: 15px;
            }
            .forgot-container {
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
        <div class="forgot-container">

            <!-- Marca con link al Home -->
            <a href="{{ url('/') }}" class="brand-link" title="Ir al inicio">
                <span>🌱</span>
                <h1>IT Conecta</h1>
            </a>

            <div class="card">
                <h2>¿Olvidaste tu contraseña?</h2>
                
                <p class="info-text">
                    No te preocupes. Cuéntanos qué correo electrónico usas y te enviaremos un enlace de restauración para que crees una contraseña nueva.
                </p>

                <!-- Estado del envío de sesión (si Laravel ya mandó el mail con éxito) -->
                @if (session('status'))
                    <div class="success-status">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="sofia@colegio.cl">
                        @if ($errors->get('email'))
                            <div class="error">{{ $errors->first('email') }}</div>
                        @endif
                    </div>

                    <!-- Botón Enviar Enlace -->
                    <button type="submit" class="btn">
                        Enviar enlace de restauración
                    </button>

                    <!-- Regresar a Iniciar Sesión -->
                    <div class="actions-wrapper">
                        <a href="{{ route('login') }}">
                            ← Volver al inicio de sesión
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-guest-layout>