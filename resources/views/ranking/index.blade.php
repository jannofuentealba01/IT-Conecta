<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ranking - EcoImpact</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #b7e4c7, #95d5b2);
        }

        /* HEADER */
        .header {
            width: 90%;
            margin: 20px auto;
            background: #ffffffcc;
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #1b4332;
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            background: #d8f3dc;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            color: #1b4332;
        }

        .logout {
            background: #1b4332;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 10px;
            cursor: pointer;
        }

        /* CONTENEDOR PRINCIPAL */
        .main {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        /* CARD BLANCA */
        .card {
            background: white;
            width: 420px;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .card h1 {
            margin: 0;
            color: #1b4332;
        }

        .subtitle {
            color: #555;
            margin-bottom: 20px;
        }

        /* TABLA */
        .ranking-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ranking-table th {
            text-align: left;
            padding-bottom: 10px;
            color: #1b4332;
        }

        .ranking-table td {
            padding: 10px 0;
        }

        .medal {
            margin-right: 8px;
        }

        .points {
            color: #f4a261;
            font-weight: bold;
        }

        .you {
            font-weight: bold;
            color: #2d6a4f;
        }

        .back {
            margin-top: 15px;
            display: inline-block;
            color: #5a189a;
            text-decoration: none;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            margin-top: 60px;
            color: #1b4332;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="logo">🌱 EcoImpact</div>

        <div class="user-box">
            <div class="user-info">👤 alejandro ⭐ 71 puntos</div>
            <button class="logout">Cerrar sesión</button>
        </div>
    </div>

    <!-- CONTENIDO -->
    <div class="main">
        <div class="card">

            <h1>🏆 Ranking de Usuarios</h1>
            <div class="subtitle">Clasificación ecológica según puntos acumulados</div>

            <table class="ranking-table">
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Puntos</th>
                </tr>

                <tr>
                    <td>🥇</td>
                    <td>Gabriel</td>
                    <td class="points">⭐ 80 pts</td>
                </tr>

                <tr>
                    <td>🥈</td>
                    <td class="you">alejandro (Tú)</td>
                    <td class="points">⭐ 71 pts</td>
                </tr>
            </table>

            <a href="{{ route('dashboard') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg shadow-md transition">
                ⬅ Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        EcoImpact © 2026 - Panel Escolar de Impacto Ambiental 🌍
    </div>

</body>
</html>