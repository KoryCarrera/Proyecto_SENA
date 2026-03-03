<?php

//FUNCIÓN: REGISTRAR CASOS

use Complex\Functions;

function registrarCasos($pdo, $documento, $proceso, $tipoCaso, $descripcion, $nombre)
{
    // PREPARACIÓN DE LA LLAMADA AL PROCEDIMIENTO ALMACENADO
    $stmt = $pdo->prepare("CALL sp_registrar_caso(?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);
    $stmt->bindParam(2, $proceso, PDO::PARAM_INT);
    $stmt->bindParam(3, $tipoCaso, PDO::PARAM_INT);
    $stmt->bindParam(4, $descripcion, PDO::PARAM_STR);
    $stmt->bindParam(5, $nombre, PDO::PARAM_STR);

    // EJECUCIÓN Y MANEJO DE ERRORES (PDOException)
    try {
        $stmt->execute();
        $casoRegistrado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // Limpiar el cursor después de la ejecución

        if (!$casoRegistrado) {
            return ['success' => false, 'data' => $casoRegistrado];
        }
        return ['success' => true, 'data' => $casoRegistrado];
    } catch (PDOException $e) {
        error_log("Error al registrar caso: " . $e->getMessage());
        return ['success' => false];
    }
}

//FUNCIÓN: REGISTRAR UN SEGUIMIENTO
function registrarSeguimiento($pdo, $observacion, $idCaso, $documento)
{
    // PREPARACIÓN DE LA LLAMADA AL PROCEDIMIENTO ALMACENADO
    $stmt = $pdo->prepare("CALL sp_registrar_seguimiento(?, ?, ?)");
    $stmt->bindParam(1, $observacion, PDO::PARAM_STR);
    $stmt->bindParam(2, $idCaso, PDO::PARAM_INT);
    $stmt->bindParam(3, $documento, PDO::PARAM_STR);

    // EJECUCIÓN Y MANEJO DE ERRORES (PDOException)
    try {
        $stmt->execute();
        $stmt->closeCursor(); // Limpiar el cursor después de la ejecución
        return true;
    } catch (PDOException $e) {
        error_log("Error en registrarSeguimiento: " . $e->getMessage());
        return false;
    }
}

//FUNCIÓN: REGISTRAR UN NUEVO USUARIO (LLAMADA A sp_registrar_usuario)
function registrarUsuario($pdo, $documento, $nombre, $apellido, $email, $rol, $contraseña)
{
    // GENERACIÓN DEL HASH SEGURO DE LA CONTRASEÑA
    $passhash = password_hash($contraseña, PASSWORD_BCRYPT);

    // PREPARACIÓN DE LA LLAMADA AL PROCEDIMIENTO ALMACENADO
    $stmt = $pdo->prepare("CALL sp_registrar_usuario(?, ?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);
    $stmt->bindParam(2, $nombre, PDO::PARAM_STR);
    $stmt->bindParam(3, $apellido, PDO::PARAM_STR);
    $stmt->bindParam(4, $email, PDO::PARAM_STR);
    $stmt->bindParam(5, $rol, PDO::PARAM_INT);
    $stmt->bindParam(6, $passhash, PDO::PARAM_STR);

    // EJECUCIÓN Y MANEJO DE ERRORES (PDOException)
    try {
        $stmt->execute();
        $stmt->closeCursor(); // Limpiar el cursor después de la ejecución
        return true;
    } catch (PDOException) {
        return false;
    }
}

//FUNCIÓN: REGISTRAR UN MONITOREO
function registrarMonitoreo($pdo, $documento, $tipo, $descripcion)
{
    // PREPARACIÓN DE LA LLAMADA AL PROCEDIMIENTO ALMACENADO
    $stmt = $pdo->prepare("CALL sp_registrar_monitoreo(?, ?, ?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_STR); //Isaac carechimba, el documento es string
    $stmt->bindParam9(2, $tipo, PDO::PARAM_STR);
    $stmt->bindParam(3, $descripcion, PDO::PARAM_STR);

    // EJECUCIÓN Y MANEJO DE ERRORES (PDOException)
    try {
        $stmt->execute();
        $stmt->Closecursor(); // Limpiar el cursor después de la ejecución
        return true;
    } catch (PDOException) {
        return false;
    }
}

//FUNCIÔN: REGISTRAR GENERACION DE UN INFORME
function registrarInforme($pdo, $documento, $formato, $descripcion)
{
    //Preparamos la llamada y ejecución del sp
    $stmt = $pdo->prepare("CALL sp_registrar_informe(?, ?, ?)");

    //Asignamos valores a los parametros
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);
    $stmt->bindParam(2, $formato, PDO::PARAM_STR);
    $stmt->bindParam(3, $descripcion, PDO::PARAM_STR);

    //Ejecucion y manejo de errores (PDOException)
    try {
        $stmt->execute();
        $datosGenerados = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $datosGenerados;
    } catch (PDOException $e) {
        error_log("Error en la obtencion o registro de datos" . $e->getMessage());
        return false;
    }
}

//FUNCIÔN: REGISTRAR UN PROCESO ORGANIZACIONAL
function registrarProceso($pdo, $descripcion, $nombre, $documentoUsuario)
{
    $stmt = $pdo->prepare("CALL sp_registrar_proceso_organizacional(?, ?, ?)");

    $stmt->bindParam(1, $descripcion, PDO::PARAM_STR);
    $stmt->bindParam(2, $nombre, PDO::PARAM_STR);
    $stmt->bindParam(3, $documentoUsuario, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $stmt->closeCursor();

        return true;
    } catch (PDOException $e) {
        error_log("Error en registrarProceso: " . $e->getMessage());
        return false;
    }
}

function generarToken($pdo, $documento)
{
    $stmt = $pdo->prepare("CALL sp_generar_token_recuperacion(?)");

    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $token = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $token;
    } catch (PDOException $e) {
        error_log("Error al generar el token: " . $e->getMessage());
        return false;
    }
}

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
        $totalDest = count($destinatarios);

        //Multiples destinatarios
        if ($totalDest > 1) {

            foreach ($destinatarios as $dest) {

                $mail->addAddress($dest['emailUser'], $dest['userName']);
            };
        } else {

            $mail->addAddress($destinatarios['emailUser'], $destinatarios['userName']);
        };

        if ($conCopia) {

            $totalCC = count($conCopia);

            if ($totalCC > 1) {

                foreach ($conCopia as $dest) {

                    $mail->addCC($dest['emailUser'], $dest['userName']);
                };
            } else {

                $mail->addCC($conCopia['emailUser'], $conCopia['userName']);
            };
        };

        if ($conCopiaOculta) {

            $totalBCC = count($conCopiaOculta);

            if ($totalBCC > 1) {
                foreach ($conCopiaOculta as $dest) {

                    $mail->addBCC($dest['emailUser'], $dest['userName']);
                };
            } else {
                $mail->addBCC($conCopiaOculta['emailUser'], $conCopiaOculta['userName']);
            }
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

function listarNotiAdmin($pdo){
    $stmt = $pdo->prepare("CALL sp_listar_noti_admin()");
    try {
        $stmt->execute();
        $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $notificaciones;
    } catch (PDOException $e) {
        error_log("Error en listarNotiAdmin: " . $e->getMessage());
    }
};
        