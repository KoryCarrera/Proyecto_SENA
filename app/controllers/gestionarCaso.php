<?php
header('Content-Type: application/json; charset=utf-8'); // Forzamos el charset a UTF-8
session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/casosModel.php";
require_once __DIR__ . "/../models/baseHelper.php"; 
require_once __DIR__ . "/../utils/utilsEmail.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Metodo no permitido']);
    exit;
}

try {
    $model = new CasosModel($pdo);
    $helper = new baseHelper($pdo); 
    
    $documentoUser = $_SESSION['user']['documento'] ?? null;
    $idCaso        = $_POST['idCaso'] ?? null;
    $motivo        = $_POST['motivo'] ?? '';
    $seguimiento   = $_POST['observacion'] ?? '';
    $documentonew  = $_POST['documentoNuevo'] ?? null;
    $nuevoEstado   = $_POST['idEstado'] ?? null;

    // --- LÓGICA DE DECISIÓN ---

    if (!empty($documentonew)) {
        
        // 1. Limpiamos los documentos para evitar espacios accidentales
        $docActual = trim($documentoUser);
        $docNuevo  = trim($documentonew);

        // 2. Buscamos los datos de los usuarios involucrados
        $paramsActual = [['value' => $docActual, 'type' => PDO::PARAM_STR]];
        $paramsNuevo  = [['value' => $docNuevo, 'type' => PDO::PARAM_STR]];

        // Usamos el SP tal cual aparece en tu imagen: sp_traer_usuario
        $dataRemitente = $helper->consultSimpleWithParams('sp_traer_usuario(?)', $paramsActual);
        $dataDestino   = $helper->consultSimpleWithParams('sp_traer_usuario(?)', $paramsNuevo);

        // Validamos que existan los datos y que tengan email
        if (!$dataRemitente || !$dataDestino || empty($dataDestino['email'])) {
            throw new Exception("No se pudieron validar los correos de los usuarios para la notificacion.");
        }

        // 3. Ejecutamos la reasignación en la base de datos
        $model->reasignarCaso($docActual, $docNuevo, $idCaso, $motivo);
        
        // 4. Enviamos el correo (Usando los campos que trae tu SP: nombre, apellido, email)
        $nombreRem = $dataRemitente['nombre'] . ' ' . ($dataRemitente['apellido'] ?? '');
        $nombreDes = $dataDestino['nombre'] . ' ' . ($dataDestino['apellido'] ?? '');

        correoReasignacionCaso(
            $dataRemitente['email'], 
            $dataDestino['email'], 
            $nombreRem, 
            $nombreDes, 
            $idCaso, 
            $motivo
        );

        echo json_encode(['status' => 'ok', 'mensaje' => 'Caso reasignado y comisionados notificados']);
        
    } elseif ($nuevoEstado) {
        
        $model->cambiarEstadoCaso($nuevoEstado, $documentoUser, $idCaso);
        echo json_encode(['status' => 'ok', 'mensaje' => 'Estado actualizado con exito']);

    } elseif ($seguimiento) {

        $model->registrarSeguimiento($seguimiento, $idCaso, $documentoUser);
        echo json_encode(['status' => 'ok', 'mensaje' => 'Seguimiento registrado con exito']);
        
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'No se detecto ninguna accion valida']);
    }

} catch(Exception $e) {
    error_log("Error en controlador de casos: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => $e->getMessage()
    ]);
}