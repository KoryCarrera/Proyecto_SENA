<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_log('DEBUG EMAIL - HOST: ' . getenv('SMTP_HOST') . ' | Email: ' . getenv('MAIL_FROM') . ' | Contraseña: ' . getenv('APP_KEY') . ' | Destino ');

//Funcion para enviar correos con PHPMailer
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

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
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

//
// CORREO DE 2FA
//
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

//
// CORREO DE REGISTRAR USUARIO
//
function correoCrearUsuario($documento, $nombre, $nombreRol, $apellido, $email, $numero, $usuarioRegistrado){
    $asunto = "Nuevo Usuario Registrado - #{$documento}: {$nombre}";

      $cuerpoHTML = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { background-color: #28a745; color: white; padding: 15px; }
            .content { padding: 20px; }
            .detalle { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }
            table { width: 100%; border-collapse: collapse; }
            td { padding: 10px; border-bottom: 1px solid #ddd; }
            .label { font-weight: bold; width: 150px; background-color: #e9ecef; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>Notificación de Nuevo Usuario</h2>
        </div>
        <div class='content'>
            <p>Se ha registrado un nuevo usuario en el sistema:</p>
            <div class='detalle'>
                <table>
                    <tr>
                        <td class='label'>Documento del usuario creado:</td>
                        <td><strong>{$documento}</strong></td>
                    </tr>
                    <tr>
                        <td class='label'>Rol del usuario:</td>
                        <td>{$nombreRol}</td>
                    </tr>
                    <tr>
                        <td class='label'>Nombre:</td>
                        <td>{$nombre}</td>
                    </tr>
                    <tr>
                        <td class='label'>Apellido:</td>
                        <td>{$apellido}</td>
                    </tr>
                    <tr>
                        <td class='label'>Email:</td>
                        <td>{$email}</td>
                    </tr>
                    <tr>
                        <td class='label'>Número:</td>
                        <td>{$numero}</td>   
                    </tr>
                    <tr>
                        <td class='label'><strong>Su contraseña:</strong></td>
                        <td><strong>{$usuarioRegistrado}</strong></td>
                    </tr>
                    <tr>
                        <td class='label'>Fecha de registro:</td>
                        <td>" . date('d/m/Y H:i:s') . "</td>
                    </tr>
                </table>
            </div>";

      $cuerpoHTML .= "
            <p style='margin-top: 20px;'>Este es un mensaje automático, por favor no responder.</p>
        </div>
    </body>
    </html>";

    $cuerpoAlt = "NUEVO USUARIO REGISTRADO\n" .
                 "=====================\n\n" .
                 "Documento del usuario: {$documento}\n" .
                 "Rol: {$nombreRol}\n" .
                 "Nombre: {$nombre}\n" .
                 "Apellido: {$apellido}\n" .
                 "Email: {$email}\n" .
                 "Numero: {$numero}\n" .
                 "Fecha: " . date('d/m/Y H:i:s') . "\n\n" .
                 "Este es un mensaje automático.";


     $destinatarios = [
        [
            'emailUser' => $email, 
            'userName' => $nombre
        ]
     ];
        
    $correoEnviado = enviarCorreo(
        $asunto,                    
        $cuerpoHTML,              
        $cuerpoAlt,              
        $destinatarios,         
        null,                        
        null                         
    );

      if ($correoEnviado) {
        error_log(" Correo enviado para usuario #{$documento}");
    } else {
        error_log(" No se pudo enviar correo para usuario #{$documento}");
    }

    return $correoEnviado;
}
//
// CORREO DE EDITAR USUARIO 
//
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


//
// CORREO DE REGISTRAR UN CASO
//
function correoRegistroCaso($idCaso, $nombreCaso, $proceso, $tipoCaso, $descripcion, $resultadoArchivos){
    
    $asunto = "Nuevo Caso Registrado - #{$idCaso}: {$nombreCaso}";

    $cuerpoHTML = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { background-color: #28a745; color: white; padding: 15px; }
            .content { padding: 20px; }
            .detalle { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }
            table { width: 100%; border-collapse: collapse; }
            td { padding: 10px; border-bottom: 1px solid #ddd; }
            .label { font-weight: bold; width: 150px; background-color: #e9ecef; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>Notificación de Nuevo Caso</h2>
        </div>
        <div class='content'>
            <p>Se ha registrado un nuevo caso en el sistema:</p>
            <div class='detalle'>
                <table>
                    <tr>
                        <td class='label'>ID del Caso:</td>
                        <td><strong>{$idCaso}</strong></td>
                    </tr>
                    <tr>
                        <td class='label'>Nombre del Caso:</td>
                        <td>{$nombreCaso}</td>
                    </tr>
                    <tr>
                        <td class='label'>Proceso:</td>
                        <td>{$proceso}</td>
                    </tr>
                    <tr>
                        <td class='label'>Tipo de Caso:</td>
                        <td>{$tipoCaso}</td>
                    </tr>
                    <tr>
                        <td class='label'>Descripción:</td>
                        <td>{$descripcion}</td>
                    </tr>
                    <tr>
                        <td class='label'>Usuario:</td>
                        <td>{$_SESSION['user']['username']} ({$_SESSION['user']['documento']})</td>
                    </tr>
                    <tr>
                        <td class='label'>Fecha de registro:</td>
                        <td>" . date('d/m/Y H:i:s') . "</td>
                    </tr>
                </table>
            </div>";

    if (isset($resultadoArchivos['success']) && $resultadoArchivos['success']) {
        $cuerpoHTML .= "
            <p style='margin-top: 20px; color: #28a745;'> Archivos subidos exitosamente</p>";
    }

    $cuerpoHTML .= "
            <p style='margin-top: 20px;'>Este es un mensaje automático, por favor no responder.</p>
        </div>
    </body>
    </html>";

    $cuerpoAlt = "NUEVO CASO REGISTRADO\n" .
        "=====================\n\n" .
        "ID del Caso: {$idCaso}\n" .
        "Nombre: {$nombreCaso}\n" .
        "Proceso: {$proceso}\n" .
        "Tipo: {$tipoCaso}\n" .
        "Descripción: {$descripcion}\n" .
        "Usuario: {$_SESSION['user']['username']}\n" .
        "Fecha: " . date('d/m/Y H:i:s') . "\n\n" .
        "Este es un mensaje automático.";


    $destinatarios = [
        [
            'emailUser' => 'isaaccarvajal1356@gmail.com',
            'userName' => 'Administrador'
        ]
    ];

    if (isset($_SESSION['user']['email'])) {
        $destinatarios[] = [
            'emailUser' => $_SESSION['user']['email'],
            'userName' => $_SESSION['user']['username']
        ];
    }

    $correoEnviado = enviarCorreo(
        $asunto,
        $cuerpoHTML,
        $cuerpoAlt,
        $destinatarios,
        null,
        null
    );

    if ($correoEnviado) {
        error_log(" Correo enviado para caso #{$idCaso}");
    } else {
        error_log(" No se pudo enviar correo para caso #{$idCaso}");
    }

    return $correoEnviado;
}
//
// CORREO DE REGISTRAR PROCESO 
//
function correoRegistrarProceso($idProceso, $nombre, $descripcion){
    
        $asunto = "Nuevo proceso registrado - #{$idProceso}: {$nombre}";

    $cuerpoHTML = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { background-color: #28a745; color: white; padding: 15px; }
            .content { padding: 20px; }
            .detalle { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }
            table { width: 100%; border-collapse: collapse; }
            td { padding: 10px; border-bottom: 1px solid #ddd; }
            .label { font-weight: bold; width: 150px; background-color: #e9ecef; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>Notificación de Nuevo Procesox</h2>
        </div>
        <div class='content'>
            <p>Se ha registrado un nuevo caso en el sistema:</p>
            <div class='detalle'>
                <table>
                    <tr>
                        <td class='label'>ID del proceso:</td>
                        <td><strong>{$idProceso}</strong></td>
                    </tr>
                    <tr>
                        <td class='label'>Nombre del proceso:</td>
                        <td>{$nombre}</td>
                    </tr>
                    <tr>
                        <td class='label'>Descripcion:</td>
                        <td>{$descripcion}</td>
                    </tr>
                    <tr>
                        <td class='label'>Usuario:</td>
                        <td>{$_SESSION['user']['username']} ({$_SESSION['user']['documento']})</td>
                    </tr>
                    <tr>
                        <td class='label'>Fecha de registro:</td>
                        <td>" . date('d/m/Y H:i:s') . "</td>
                    </tr>
                </table>
            </div>";

    $cuerpoHTML .= "
            <p style='margin-top: 20px;'>Este es un mensaje automático, por favor no responder.</p>
        </div>
    </body>
    </html>";

    $cuerpoAlt = "NUEVO PROCESO REGISTRADO\n" .
        "=====================\n\n" .
        "ID del Proceso: {$idProceso}\n" .
        "Nombre: {$nombre}\n" .
        "Descripción: {$descripcion}\n" .
        "Usuario: {$_SESSION['user']['documento']}\n" .
        "Fecha: " . date('d/m/Y H:i:s') . "\n\n" .
        "Este es un mensaje automático.";


    $destinatarios = [
        [
            'emailUser' => 'isaaccarvajal1356@gmail.com',
            'userName' => 'Administrador'
        ]
    ];

    $correoEnviado = enviarCorreo(
        $asunto,                    
        $cuerpoHTML,              
        $cuerpoAlt,              
        $destinatarios,         
        null,                        
        null                         
    );

      if ($correoEnviado) {
        error_log(" Correo enviado para proceso #{$idProceso}");
    } else {
        error_log(" No se pudo enviar correo para proceso #{$idProceso}");
    }

    return $correoEnviado;
}

function correoRecuperacionPassword($emailDestino, $nombreUsuario, $linkRecuperacion)
{
    $asunto = "Recuperacion de contrasena - Sistema de Gestion";

    //Cuerpo HTML
    $cuerpoHTML = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; text-align: center; color: #333333; }
            .btn { background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; margin: 20px 0; }
            .footer { background-color: #f8f9fa; color: #6c757d; padding: 15px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Recuperación de Contraseña</h2>
            </div>
            <div class='content'>
                <p>Hola <strong>{$nombreUsuario}</strong>,</p>
                <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.</p>
                <p>Para crear una nueva contraseña, haz clic en el siguiente botón:</p>
                
                <a href='{$linkRecuperacion}' class='btn' style='color: white;'>Restablecer Contraseña</a>
                
                <p style='font-size: 13px; color: #777; margin-top: 25px;'>Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:</p>
                <p style='font-size: 12px; word-break: break-all; color: #007bff;'>{$linkRecuperacion}</p>
                
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 13px;'>Si no solicitaste este cambio, puedes ignorar este correo de forma segura. Tu contraseña actual no cambiará.</p>
            </div>
            <div class='footer'>
                &copy; " . date('Y') . " Sistema de Gestión SENA. Todos los derechos reservados.
            </div>
        </div>
    </body>
    </html>
    ";

    //Cuerpo alternativo en texto plano
    $cuerpoAlt = "Hola {$nombreUsuario},\n\n" .
                 "Hemos recibido una solicitud para restablecer tu contraseña.\n" .
                 "Para crear una nueva contraseña, copia y pega el siguiente enlace en tu navegador:\n\n" .
                 "{$linkRecuperacion}\n\n" .
                 "Si no solicitaste este cambio, ignora este mensaje. Tu contraseña no cambiará.\n\n" .
                 "Sistema de Gestión SENA";

    //Destinatarios
    $destinatarios = [
        [
            'emailUser' => $emailDestino,
            'userName' => $nombreUsuario
        ]
    ];

    //Llamada a tu función principal
    $correoEnviado = enviarCorreo(
        $asunto,
        $cuerpoHTML,
        $cuerpoAlt,
        $destinatarios,
        null,
        null
    );

    //Logs de éxito o error
    if ($correoEnviado) {
        error_log("Correo de recuperación enviado exitosamente a {$emailDestino}");
    } else {
        error_log("Error al enviar correo de recuperación a {$emailDestino}");
    }

    return $correoEnviado;
}