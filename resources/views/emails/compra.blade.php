<!DOCTYPE html>
<html>

<head>
    <title>Tu Cartón de Bingo</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #3b82f6;
            color: white;
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo {
            max-height: 80px;
            max-width: 100%;
        }

        .content {
            padding: 30px;
            color: #4b5563;
        }

        .card-info {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
        }

        .footer {
            background-color: #1e40af;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }

        .social-links {
            margin: 15px 0;
        }

        .social-links a {
            margin: 0 10px;
            text-decoration: none;
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }

        p {
            margin-bottom: 15px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <!-- Logo centrado -->
            <div class="logo-container">
                <img src="{{ $message->embed(public_path('images/logo.webp')) }}" alt="Bingo Full Gaming Logo"
                    class="logo">
            </div>
            <h1>¡Gracias por tu compra!</h1>
        </div>

        <div class="content">
            <p>Hola {{ $user->name }},</p>

            <p>Pronto confirmaremos su pago en <strong>Bingo Full Gaming</strong>:</p>

            <div class="card-info">
                Número de serie: <strong>{{ $serial_number }}</strong>
            </div>

            <p>Una vez sea confirme su pago, se le enviar&aacute; un correo con informaci&oacute;n de su compra.</p>

            <p>Adjunto encontrarás una imagen con tu cartón de bingo. Recuerda que puedes ver todos tus cartones
                accediendo a tu cuenta en cualquier momento.</p>

            <p>¡Mucha suerte en el juego!</p>

            {{-- <center>
                <a href="{{ route('user.cards') }}" class="btn">Ver mis cartones</a>
            </center> --}}
        </div>

        <div class="footer">
            <div class="social-links">
                <a href="https://www.facebook.com/profile.php?id=61573950633258"><img
                        src="{{ $message->embed(public_path('images/facebook.png')) }}" alt="Facebook" width="24"></a>
                {{-- <a href="#"><img src="{{ $message->embed(public_path('images/instagram.png')) }}" alt="Instagram"
                        width="24"></a> --}}
                <a href="https://wa.link/u181s7"><img src="{{ $message->embed(public_path('images/whatsapp.png')) }}"
                        alt="WhatsApp" width="24"></a>
            </div>
            <p>Bingo Full Gaming &copy; {{ date('Y') }} - Todos los derechos reservados</p>
            <p>Soporte: administrador@bingofullgaming.com</p>
            <p><small>Por favor no responda a este mensaje, es un envío automático.</small></p>
        </div>
    </div>
</body>

</html>