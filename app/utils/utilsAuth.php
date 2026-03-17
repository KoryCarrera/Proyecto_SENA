<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_log('DEBUG EMAIL - Usuario: ' . getenv('SMTP_HOST') . 'Email: ' . getenv('MAIL_FROM') . ' | Contraseña: ' . getenv('APP_KEY'));

function enviarCorreo($asunto, $cuerpoHTML, $cuerpoAlt, $destinatarios, $conCopia = null, $conCopiaOculta = null)
{
    $mail = new PHPMailer(true);
    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = getenv('MAIL_FROM');
        $mail->Password = getenv('APP_KEY');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        // Origen del correo
        $mail->setFrom(getenv('MAIL_FROM'), 'Sistema Gestion');

        // Validamos que haya al menos un destinatario
        if (empty($destinatarios)) {
            return false;
        }
        // Agregamos destinatarios
        foreach ($destinatarios as $dest) {
            $mail->addAddress($dest['emailUser'], $dest['userName']);
        }
        if (!empty($conCopia)) {
            foreach ($conCopia as $dest) {
                $mail->addCC($dest['emailUser'], $dest['userName']);
            }
        }
        if (!empty($conCopiaOculta)) {
            foreach ($conCopiaOculta as $dest) {
                $mail->addBCC($dest['emailUser'], $dest['userName']);
            }
        }
        // Contenido del mensaje
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpoHTML;
        $mail->AltBody = $cuerpoAlt;
        // Enviar el correo
        $mail->send();
        return true; // Si llega aquí, se envió correctamente
    } catch (Exception $e) {
        // Capturamos el error directamente de la excepción de PHPMailer
        error_log('Ha ocurrido un error al enviar el correo. Mailer Error: ' . $mail->ErrorInfo . ' | Excepción: ' . $e->getMessage());
        return false;
    }
}
function enviarCodigo2FA($codigo2FA, $nombreUsuario, $emailDestino, $tiempoExpiracion = 10, $nombreApp = "Sistema de Gestion SENA")
{
    $asunto = "Codigo de verificacion para tu inicio de sesion";
    // Cuerpo HTML
    $cuerpoHTML = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; text-align: center; }
            .codigo { font-size: 36px; font-weight: bold; color: #007bff; background-color: #e9ecef; padding: 15px; border-radius: 8px; display: inline-block; margin: 20px 0; letter-spacing: 5px; }
            .footer { background-color: #f8f9fa; color: #6c757d; padding: 15px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Verificación de dos factores</h2>
            </div>
            <div class='content'>
                <p>Hola <strong>{$nombreUsuario}</strong>,</p>
                <p>Para completar tu inicio de sesión, utiliza el siguiente código de verificación:</p>
                <div class='codigo'>{$codigo2FA}</div>
                <p>Este código es válido por {$tiempoExpiracion} minutos. Si no solicitaste este código, ignora este mensaje.</p>
            </div>
            <div class='footer'>
                &copy; " . date('Y') . " {$nombreApp}. Todos los derechos reservados.
            </div>
        </div>
    </body>
    </html>
    ";
    // Cuerpo alternativo en texto plano
    $cuerpoAlt =
        "Tu código de verificación es: {$codigo2FA}\n" .
        "Este código es válido por {$tiempoExpiracion} minutos. Si no solicitaste este código, ignora este mensaje.\n\n" .
        "Gracias.";
    // Destinatario en el formato esperado por la función enviarCorreo

    $destinatarios = [
        [
            'emailUser' => $emailDestino,
            'userName' => $nombreUsuario
        ]
    ];
    // Llamar a la función de envío (corregimos los parámetros null)
    $correoEnviado = enviarCorreo(
        $asunto,
        $cuerpoHTML,
        $cuerpoAlt,
        $destinatarios,
        null, // CC
        null  // BCC
    );
    // Registrar el resultado
    if ($correoEnviado) {
        error_log("Correo 2FA enviado a {$emailDestino}");
    } else {
        error_log("Error al enviar correo 2FA a {$emailDestino}");
    }
    return $correoEnviado;
}

function correoEdicionUsuario($token, $nombre, $email, $rol)
{

    $token ?? 'Tu contraseña no ha cambiado';

    $badgeRol = ($rol == 1) ? 'Administrador' : 'Comisionado';
    $asunto = 'Tus datos de acceso han sido actualizados';

    $cuerpoHTML = "<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; text-align: center; }
        .contrasena { font-size: 24px; font-weight: bold; color: #28a745; background-color: #e9ecef; padding: 15px; border-radius: 8px; display: inline-block; margin: 20px 0; letter-spacing: 2px; }
        .footer { background-color: #f8f9fa; color: #6c757d; padding: 15px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Actualización de tu cuenta</h2>
        </div>
        <div class='content'>
            <p>Hola <strong>$nombre con rol $badgeRol</strong>,</p>
            <p>Un administrador ha modificado los datos de tu cuenta. Ingresa al sistema para verificarlos, tu froma de accesso es:</p>
            <div class='contrasena'>$token</div>
            <p>Por razones de seguridad, te recomendamos cambiar esta contraseña una vez que inicies sesión.</p>
            <p>Si no solicitaste este cambio, contacta inmediatamente al administrador del sistema.</p>
        </div>
        <div class='footer'>
            &copy; " . date('Y') . " Sistema de Gestion SENA. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>";

    $alt = "Hola $nombre,
Un administrador ha modificado los datos de tu cuenta. Tu nueva contraseña es: $token
Por razones de seguridad, te recomendamos cambiarla una vez que inicies sesión.
Si no solicitaste este cambio, contacta al administrador.";

    $destinatarios = [
        [
            'emailUser' => $email,
            'userName' => $nombre
        ]
    ];

    $correoEnviado = enviarCorreo($asunto, $cuerpoHTML, $alt, $destinatarios);

    if ($correoEnviado) {
        error_log("Notificación de cambio enviada a {$email}");
    } else {
        error_log("Error al enviar notificación de cambio a {$email}");
    }

    return $correoEnviado;
}