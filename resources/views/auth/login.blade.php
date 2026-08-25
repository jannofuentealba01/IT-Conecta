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


/* 
        .login-container {
            width: 100%;
            max-width: 440px; /*  
            text-align: center;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
        }*/

        /* Marca - Ahora es un link interactivo hacia el home/welcome */
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

        /* Tarjeta con look moderno, más ancha y con mejor espaciado */
        .card {
            background: rgba(255, 255, 255, 0.97);
            padding: 35px 30px;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            text-align: left;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
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

        /* Inputs modernos y estables en PC/Celular */
        input[type="email"],
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

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--brand-blue);
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }

        /* Botón estilo Gamer/Sostenible premium */
        .btn {
            width: 100%;
            margin-top: 25px;
            padding: 15px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand-blue), var(--brand-blue-dark));
            color: white;
            font-weight: 700;
            font-size: 16px;
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

        /* Alineación Multiplataforma de Extras */
        .extra {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            font-size: 13px;
        }

        label.remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4b5563;
            cursor: pointer;
            margin: 0;
            user-select: none;
            font-weight: 500;
            text-transform: none;
            letter-spacing: normal;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--brand-blue);
            cursor: pointer;
            margin: 0;
        }

        .extra a {
            color: var(--brand-blue-dark);
            text-decoration: none;
            font-weight: 600;
        }

        .extra a:hover {
            text-decoration: underline;
            color: #0d9488;
        }

        .error {
            font-size: 12.5px;
            color: #b91c1c;
            margin-top: 8px;
            font-weight: 500;
        }

        /* AJUSTES ADAPTABLES (PC, TABLET, MÓVIL) */
        @media (max-width: 640px) {
            .page-wrapper {
                padding: 15px;
            }
            .login-container {
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

        /* Vista ultra-pequeña (celulares antiguos) */
        @media (max-width: 400px) {
            .extra {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }
    </style>







    <div class="page-wrapper">
        <div class="login-container">

            <!-- Marca linkeada al Home (welcome) sin logo de laravel -->
            <a href="{{ url('/') }}" class="brand-link" title="Ir al inicio">
                <span>🌱</span>
                <h1>IT Conecta</h1>
            </a>

            <div class="card">
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email">Correo Electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="ejemplo@colegio.cl">
                        @if ($errors->get('email'))
                            <div class="error">{{ $errors->first('email') }}</div>
                        @endif
                    </div>

                    <!-- Password -->
                    <div style="margin-top: 20px;">
                        <label for="password">Contraseña</label>
                        <input id="password" type="password" name="password" required placeholder="••••••••">
                        @if ($errors->get('password'))
                            <div class="error">{{ $errors->first('password') }}</div>
                        @endif
                    </div>

                    <!-- Extras perfectamente alineados -->
                    <div class="extra">
                        <label class="remember">
                            <input type="checkbox" name="remember">
                            <span>Recordar</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                        @endif
                    </div>

                    <!-- Botón -->
                    <button type="submit" class="btn">
                        Iniciar sesión
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-guest-layout>
