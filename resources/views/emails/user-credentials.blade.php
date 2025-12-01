<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Credenciales de Acceso</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #004080;
            color: #ffffff;
            padding: 15px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            padding: 20px;
        }
        .credentials-box {
            background-color: #f9f9f9;
            border-left: 5px solid #004080;
            padding: 15px;
            margin: 20px 0;
        }
        .credentials-box p {
            margin: 5px 0;
            font-family: 'Courier New', Courier, monospace;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .btn {
            display: inline-block;
            background-color: #004080;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Bienvenido a las Olimpiadas Académicas</h2>
        </div>

        <div class="content">
            <p>Hola <strong>{{ $nombreUsuario }}</strong>,</p>

            <p>Se ha registrado tu cuenta exitosamente en el sistema con el perfil de: <strong>{{ $rol }}</strong>.</p>

            <p>A continuación, encontrarás tus credenciales de acceso. Por favor, guárdalas en un lugar seguro o cambia tu contraseña al ingresar.</p>

            <div class="credentials-box">
                <p><strong>Usuario/Correo:</strong> {{ $email }}</p>
                <p><strong>Contraseña Temporal:</strong> {{ $password }}</p>
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="btn">Ingresar al Sistema</a>
            </div>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático del Sistema de Olimpiadas "Oh Sansi".<br>
            Por favor, no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>
