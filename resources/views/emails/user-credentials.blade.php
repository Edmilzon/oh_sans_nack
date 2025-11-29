<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Credenciales de Acceso</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 90%; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { font-size: 24px; font-weight: bold; color: #444; }
        .credentials { background-color: #f9f9f9; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; }
        .footer { font-size: 12px; color: #777; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <p class="header">¡Hola, {{ $nombreUsuario }}!</p>
        <p>Te damos la bienvenida a la plataforma. Se ha creado una cuenta para ti con el rol de <strong>{{ $rol }}</strong>.</p>
        <p>A continuación, encontrarás tus credenciales para acceder al sistema.</p>
        
        <div class="credentials">
            <p><strong>Usuario:</strong> {{ $email }}</p>
            <p><strong>Contraseña:</strong> {{ $password }}</p>
        </div>

        <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
        <p>Saludos cordiales,<br>El equipo de oh_Sansi</p>

        <p class="footer">Este es un correo electrónico generado automáticamente, por favor no respondas a este mensaje.</p>
    </div>
</body>
</html>
