<?php
header('Content-Type: application/json');

require_once __DIR__ . "/checkSessionAdmin.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/insertData.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

try {

    $documentoUsuario = $_SESSION['user']['documento'];
    $nombre = $_POST["nombre-proceso"] ?? null;
    $descripcion = $_POST["descripcion"] ?? null;

    if (!$nombre || !is_string($nombre) || trim($nombre) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El nombre es requerido'
        ]);
        exit;
    }

    if (!$descripcion || !is_string($descripcion) || trim($descripcion) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'La descripción es requerida'
        ]);
        exit;
    }

    $resultado = registrarProceso($pdo, $descripcion, $nombre, $documentoUsuario);

    if ($resultado === true) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Proceso registrado exitosamente'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al registrar el proceso'
        ]);
    }

    $idProceso = $registrar['data']['id_proceso'];

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
            'emailUser' => 'kory.carrera.dev@gmail.com',
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

} catch (Exception $e) {
    error_log("Error en registrarProceso.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
