<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . "/../models/getData.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/updateData.php";
require_once __DIR__ . "/../models/insertData.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
    exit;
}

try {
    $idCaso = $_POST['idCaso'] ?? null;
    $idEstado = $_POST['idEstado'] ?? null;
    $observacion = $_POST['observacion'] ?? '';
    $documento =  $_SESSION['user']['documento'] ?? null;

    if (!$idCaso) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Faltan datos obligatorios']);
        exit;
    }

    $validarCaso = traerCaso($pdo, $idCaso);

    if(!$validarCaso){
        echo json_encode(['status' => 'error', 'mensaje' => 'El caso no existe']);
        exit;
    }

    if ($validarCaso['data']['documento'] !== $documento) {
        echo json_encode([
            'status' => 'error', 
            'mensaje' => 'No tienes permiso de cambiar este caso, no eres el responsable asignado'
        ]);
        exit;
    }

    // Iniciar transacción si el motor lo soporta para asegurar consistencia
    $pdo->beginTransaction();

    if ($idEstado) {
        // 1. Actualizar el estado del caso
        $validarEstado = validarEstado($pdo, $idCaso);

        if($validarEstado == '3'){
            echo json_encode(['status' => 'error', 'mensaje' => 'Solo el administrador puede cambiar el estado de un caso No atendido']);
        exit;
        }

        $actualizado = actualizarEstadoCaso($pdo, $idCaso, $idEstado, $documento);

        if (!$actualizado) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al actualizar el estado del caso']);
            exit;
        }
    }
    // 2. Registrar el seguimiento si hay observación
    if (!empty(trim($observacion))) {
        $seguimiento = registrarSeguimiento($pdo, $observacion, $idCaso, $documento);
        if (!$seguimiento) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al registrar el seguimiento']);
            exit;
        }
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'Caso gestionado exitosamente'
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error en gestionarCaso.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
