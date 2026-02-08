<?php

//FUNCIÓN: REGISTRAR CASOS
function registrarCasos($pdo, $documento, $proceso, $estado, $tipoCaso, $descripcion)
{
    // PREPARACIÓN DE LA LLAMADA AL PROCEDIMIENTO ALMACENADO
    $stmt = $pdo->prepare("CALL sp_registrar_caso(?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);
    $stmt->bindParam(2, $proceso, PDO::PARAM_INT);
    $stmt->bindParam(3, $estado, PDO::PARAM_INT);
    $stmt->bindParam(4, $tipoCaso, PDO::PARAM_INT);
    $stmt->bindParam(5, $descripcion, PDO::PARAM_STR);

    // EJECUCIÓN Y MANEJO DE ERRORES (PDOException)
    try {
        $stmt->execute();
        $casoRegistrado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // Limpiar el cursor después de la ejecución

        //Enviar notificacion por cada creación de caso
        if ($casoRegistrado) {

        try {
            $resend = Resend::client($_ENV['RESEND_API_KEY']);

            $resend->emails->send([
                'from' => 'onboarding@resend.dev',
                'to' => $_ENV['MAIL_FROM'],
                'subject' => '¡Se ha ingresado un nuevo caso en el sistema!',
                'html' => '<p>El usuario con documento <strong>' . $documento .
                    '</strong> y nombre <strong>' . $casoRegistrado['comisionado'] .
                    '</strong> ha ingresado un caso en la fecha y hora <strong>' . $casoRegistrado['fecha_inicio'] .
                    '</strong> con el estado <strong>' . $casoRegistrado['estado'] .
                    '</strong> con el tipo <strong>' . $casoRegistrado['tipo_caso'] .
                    '</strong> Y asignado al proceso <strong>' . $casoRegistrado['proceso'] .
                    '</strong> Si desea ver la descripción o mas informacion ingrese al sistema.' . '<p>'
            ]);
            } catch(Exception $e) {
                error_log('Error al enviar notificacion email '. $e->getMessage());
            }
            return true;
        }

        return true;
    } catch (PDOException) {
        return false;
    }
}

//FUNCIÓN: REGISTRAR UN SEGUIMIENTO
function registrarSeguimiento($pdo, $observacion)
{
    // PREPARACIÓN DE LA LLAMADA AL PROCEDIMIENTO ALMACENADO
    $stmt = $pdo->prepare("CALL sp_registrar_seguimiento(?)");
    $stmt->bindParam(1, $observacion, PDO::PARAM_STR);

    // EJECUCIÓN Y MANEJO DE ERRORES (PDOException)
    try {
        $stmt->execute();
        $stmt->closeCursor(); // Limpiar el cursor después de la ejecución
        return true;
    } catch (PDOException) {
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
function registrarInforme($pdo, $documento, $formato, $conclusiones)
{
    //Preparamos la llamada y ejecución del sp
    $stmt = $pdo->prepare("CALL sp_registrar_informe(?, ?, ?)");

    //Asignamos valores a los parametros
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);
    $stmt->bindParam(2, $formato, PDO::PARAM_STR);
    $stmt->bindParam(3, $conclusiones, PDO::PARAM_STR);

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
function registrarProceso($pdo, $nombre, $descripcion, $documentoUsuario)
{
    $stmt = $pdo->prepare("CALL sp_registrar_proceso_organizacional(?, ?, ?)");

    $stmt->bindParam(1, $nombre, PDO::PARAM_STR);
    $stmt->bindParam(2, $descripcion, PDO::PARAM_STR);
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
