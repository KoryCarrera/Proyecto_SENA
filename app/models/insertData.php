<?php

//FUNCIÓN: REGISTRAR CASOS
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

        //Enviar notificacion por cada creación de caso
        if ($casoRegistrado) {

            try {
                $resend = Resend::client($_ENV['RESEND_API_KEY']);

                $resend->emails->send([
                    'from' => 'onboarding@resend.dev',
                    'to' => $_ENV['MAIL_FROM'],
                    'subject' => '🔔 Nuevo caso ingresado al sistema',
                    'html' => '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        
                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px 40px; text-align: center;">
                                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">
                                    Nuevo Caso Registrado
                                </h1>
                            </td>
                        </tr>
                        
                        <!-- Content -->
                        <tr>
                            <td style="padding: 40px;">
                                <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                    Se ha registrado un nuevo caso en el sistema. A continuación, encontrará los detalles:
                                </p>
                                
                                <!-- Details Table -->
                                <table width="100%" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                                    <tr>
                                        <td style="padding: 12px 15px; background-color: #f8f9fa; border-left: 4px solid #667eea;">
                                            <strong style="color: #667eea; font-size: 14px;">COMISIONADO</strong><br>
                                            <span style="color: #333333; font-size: 15px;">' . htmlspecialchars($casoRegistrado['comisionado']) . '</span>
                                        </td>
                                    </tr>
                                    <tr><td style="height: 10px;"></td></tr>
                                    <tr>
                                        <td style="padding: 12px 15px; background-color: #f8f9fa; border-left: 4px solid #667eea;">
                                            <strong style="color: #667eea; font-size: 14px;">DOCUMENTO</strong><br>
                                            <span style="color: #333333; font-size: 15px;">' . htmlspecialchars($documento) . '</span>
                                        </td>
                                    </tr>
                                    <tr><td style="height: 10px;"></td></tr>
                                    <tr>
                                        <td style="padding: 12px 15px; background-color: #f8f9fa; border-left: 4px solid #667eea;">
                                            <strong style="color: #667eea; font-size: 14px;">FECHA Y HORA</strong><br>
                                            <span style="color: #333333; font-size: 15px;">' . htmlspecialchars($casoRegistrado['fecha_inicio']) . '</span>
                                        </td>
                                    </tr>
                                    <tr><td style="height: 10px;"></td></tr>
                                    <tr>
                                        <td style="padding: 12px 15px; background-color: #f8f9fa; border-left: 4px solid #667eea;">
                                            <strong style="color: #667eea; font-size: 14px;">FECHA Y HORA DE CIERRE</strong><br>
                                            <span style="color: #333333; font-size: 15px;">' . htmlspecialchars($casoRegistrado['fecha_cierre'] ?? 'N/A') . '</span>
                                        </td>
                                    </tr>
                                    <tr><td style="height: 10px;"></td></tr>
                                    <tr>
                                        <td style="padding: 12px 15px; background-color: #f8f9fa; border-left: 4px solid #667eea;">
                                            <strong style="color: #667eea; font-size: 14px;">ESTADO</strong><br>
                                            <span style="color: #333333; font-size: 15px;">' . htmlspecialchars($casoRegistrado['estado']) . '</span>
                                        </td>
                                    </tr>
                                    <tr><td style="height: 10px;"></td></tr>
                                    <tr>
                                        <td style="padding: 12px 15px; background-color: #f8f9fa; border-left: 4px solid #667eea;">
                                            <strong style="color: #667eea; font-size: 14px;">TIPO DE CASO</strong><br>
                                            <span style="color: #333333; font-size: 15px;">' . htmlspecialchars($casoRegistrado['tipo_caso']) . '</span>
                                        </td>
                                    </tr>
                                    <tr><td style="height: 10px;"></td></tr>
                                    <tr>
                                        <td style="padding: 12px 15px; background-color: #f8f9fa; border-left: 4px solid #667eea;">
                                            <strong style="color: #667eea; font-size: 14px;">PROCESO ASIGNADO</strong><br>
                                            <span style="color: #333333; font-size: 15px;">' . htmlspecialchars($casoRegistrado['proceso']) . '</span>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- CTA Button -->
                                <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                    <tr>
                                        <td align="center">
                                            <a href="http://localhost:8000/" style="display: inline-block; padding: 14px 35px; background-color: #667eea; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: 600; font-size: 15px;">
                                                Ver Detalles en el Sistema
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style="color: #666666; font-size: 14px; line-height: 1.6; margin: 20px 0 0 0;">
                                    Para ver la descripción completa y más información, por favor ingrese al sistema.
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: #f8f9fa; padding: 20px 40px; text-align: center; border-top: 1px solid #e9ecef;">
                                <p style="color: #999999; font-size: 12px; margin: 0; line-height: 1.5;">
                                    Este es un mensaje automático del sistema de gestión de casos.<br>
                                    Por favor, no responda a este correo.
                                </p>
                            </td>
                        </tr>
                        
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    '
                ]);
            } catch (Exception $e) {
                error_log('Error al enviar notificacion email ' . $e->getMessage());
            }
            return [
                'success' => true,
                'id_caso' => $casoRegistrado['id_caso'],
                'data' => $casoRegistrado
            ];
        }

        return ['success' => false];
        
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