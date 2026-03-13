<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarCorreo($asunto, $cuerpoHTML, $cuerpoAlt, $destinatarios, $conCopia, $conCopiaOculta)
{
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = getenv('MAIL_FROM');
        $mail->Password = getenv('APP_KEY');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Origen del correo
        $mail->setFrom(getenv('MAIL_FROM'), 'Sistema Gestion');

        //validamos que hay al menos un destinatar

        if (!$destinatarios) {
            return false;
        }

        //Validamos si hay mas de un destinatario
            foreach ($destinatarios as $dest) {

                $mail->addAddress($dest['emailUser'], $dest['userName']);
            };

        if ($conCopia) {

                foreach ($conCopia as $dest) {

                    $mail->addCC($dest['emailUser'], $dest['userName']);
                };
        };

        if ($conCopiaOculta) {

                foreach ($conCopiaOculta as $dest) {

                    $mail->addBCC($dest['emailUser'], $dest['userName']);
                };
        };

        //contenido del mensaje
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpoHTML;
        $mail->AltBody = $cuerpoAlt;

        //Capturar errores de envio

        if (!$mail->send()) {

            error_log('Ha ocurrido un error al enviar la notificacion via gmail ' . $mail->ErrorInfo);
            return false;
        } else {
            return true;
        }
    } catch (Exception $e) {

        error_log('Ha ocurrido un error critico al enviar el correo, errores: 1: ' . $mail->ErrorInfo . '2: ' . $e->getMessage());

        return false;
    }
};

function enviarCodigo2FA($codigo2FA, $nombreUsuario, $emailDestino, $tiempoExpiracion = 10, $nombreApp = "Sistema de Gestion") {
    
    $asunto = "Código de verificación para tu inicio de sesión";

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
    $cuerpoAlt = "Hola {$nombreUsuario},\n\n" .
                 "Tu código de verificación es: {$codigo2FA}\n" .
                 "Este código es válido por {$tiempoExpiracion} minutos. Si no solicitaste este código, ignora este mensaje.\n\n" .
                 "Gracias.";

    // Destinatario en el formato esperado por la función enviarCorreo
    $destinatarios = [
        [
            'emailUser' => $emailDestino,
            'userName'  => $nombreUsuario
        ]
    ];

    // Llamar a la función de envío
    $correoEnviado = enviarCorreo(
        $asunto,
        $cuerpoHTML,
        $cuerpoAlt,
        $destinatarios,
        null, // adjuntos
        null  // copia (CC)
    );

    //Registrar el resultado
    if ($correoEnviado) {
        error_log("Correo 2FA enviado a {$emailDestino}");
    } else {
        error_log("Error al enviar correo 2FA a {$emailDestino}");
    }

    return $correoEnviado;
}
?>