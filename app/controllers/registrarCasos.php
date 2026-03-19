<?php

header('Content-Type: application/json');
session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/casosModel.php";
require_once __DIR__ . "/../models/insertData.php";
require_once __DIR__ . "/../models/fileManager.php";
require_once __DIR__ . '/../../vendor/autoload.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

try {

    $modelCaso = new CasosModel($pdo);

    // Captura de datos
    $nombreCaso = $_POST["nombreCaso"] ?? null;
    $proceso = $_POST["proceso"] ?? null;
    $tipoCaso = $_POST["tipoCaso"] ?? null;
    $descripcion = $_POST["descripcion"] ?? null;

    // Validación del nombre
    if (!$nombreCaso || trim($nombreCaso) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El nombre del caso es requerido'
        ]);
        exit;
    }

    // Validación del proceso
    if (!$proceso || trim($proceso) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El proceso es requerido'
        ]);
        exit;
    }

    // Validación del tipo de caso
    if (!$tipoCaso || trim($tipoCaso) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El tipo es requerido'
        ]);
        exit;
    }

    // Validación de la descripción
    if (!$descripcion || trim($descripcion) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'La descripción es requerida'
        ]);
        exit;
    }

    // Registrar el caso
    $registrar = $modelCaso->registrarCaso(
        $_SESSION['user']['documento'],
        $proceso,
        $tipoCaso,
        $descripcion,
        $nombreCaso
    );

    // Verificar resultado
    if (!$registrar['success']) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al registrar el caso'
        ]);
        exit;
    }

    $idCaso = $registrar['data']['id_caso'];

    // Procesar archivos
    if (isset($_FILES['archivos']) && !empty($_FILES['archivos']['name'][0])) {
        try {
            $fileManager = new FileManager($pdo);
            $fileManager->guardarArchivosCaso($idCaso, $_FILES['archivos']);
        } catch (Exception $e) {
            // Si fallan los archivos, el caso ya se creó, así que solo avisamos por log
            error_log("Error subiendo archivos: " . $e->getMessage());
        }
    }
    echo json_encode(['success' => true, 'mensaje' => 'Caso registrado correctamente']);

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
            'emailUser' => 'kory.carrera.dev@gmail.com',
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

    // Respuesta exitosa
    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'Caso registrado exitosamente' .
            ($correoEnviado ? ' y notificación enviada' : '')
    ]);
} catch (Exception $e) {
    error_log("Error en registrarCasos.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
