<x-guest-layout>
    <style>
.page-wrapper {
    min-height: 100vh;

    background:
        linear-gradient(to bottom, rgba(0,0,0,0) 25%, var(--brand-blue-dark) 55%),
        url('/images/reciclado.png'),
        linear-gradient(135deg, var(--brand-blue), var(--brand-blue-dark), var(--text-primary));

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

        .confirm-container {
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
            color: var(--brand-blue-dark);
            margin-bottom: 15px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            text-align: center;
        }

        /* Texto explicativo amigable */
        .info-text {
            font-size: 14px;
            color: #4b5563;
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
            color: var(--brand-blue-dark);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Input idéntico */
        input[type="password"] {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            font-size: 15px;
            color: #1f2937;
            background-color: #f9fafb;
            transition: all 0.2s ease-in-out;
        }

        input[type="password"]:focus {
            outline: none;
            border-color: var(--brand-blue);
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }

        /* Botón estilo Eco Premium */
        .btn {
            width: 100%;
            margin-top: 10px;
            padding: 15px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand-blue), var(--brand-blue-dark));
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

        .error {
            font-size: 12.5px;
            color: #b91c1c;
            margin-top: 8px;
            font-weight: 500;
        }

        /* ADAPTABILIDAD MULTIPLATAFORMA */
        @media (max-width: 640px) {
            .page-wrapper {
                padding: 15px;
            }
            .confirm-container {
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
        <div class="confirm-container">

            <!-- Logo e Identidad -->
            <a href="{{ url('/') }}" class="brand-link" title="Ir al inicio">
                <span>🌱</span>
                <h1>IT Conecta</h1>
            </a>

            <div class="card">
                <h2>Confirma tu Contraseña</h2>
                
                <p class="info-text">
                    Estás intentando ingresar a una zona segura de EcoImpact. Por favor, confirma tu contraseña antes de continuar para verificar tu identidad escolar.
                </p>

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                        @if ($errors->get('password'))
                            <div class="error">{{ $errors->first('password') }}</div>
                        @endif
                    </div>

                    <!-- Botón Confirmar -->
                    <button type="submit" class="btn">
                        Confirmar Contraseña
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-guest-layout>
