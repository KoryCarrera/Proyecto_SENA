<?php
// ============================================================
// listarArchivosCaso.php — Lista los archivos adjuntos de un caso
// Método: POST | Parámetro: id_caso (int)
// ============================================================

header('Content-Type: application/json');

// Verificar sesión activa
session_start();
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Sesión no iniciada']);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/baseHelper.php';

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
    exit;
}

try {
    $helper = new baseHelper($pdo);

    // Obtener y validar el id del caso
    $idCaso = $_POST['id_caso'] ?? null;

    if (!$idCaso || !is_numeric($idCaso)) {
        echo json_encode(['status' => 'error', 'mensaje' => 'ID de caso inválido']);
        exit;
    }

    // Consultar archivos del caso mediante el SP
    $dataParams = [
        ['value' => $idCaso, 'type' => PDO::PARAM_INT]
    ];
    
    $archivos = $helper->consultObjectWithParams("sp_listar_archivos_caso(?)", $dataParams);

    if ($archivos) {
        echo json_encode([
            'status'   => 'ok',
            'archivos' => $archivos
        ]);
    } else {
        echo json_encode([
            'status'   => 'ok',
            'archivos' => [],
            'mensaje'  => 'Este caso no tiene archivos adjuntos'
        ]);
    }

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] LISTAR_ARCHIVOS: ERROR: " . $e->getMessage() . "\n", 3, __DIR__ . '/../debug_custom.log');
    echo json_encode([
        'status'  => 'error',
        'mensaje' => 'Error al consultar los archivos del servidor'
    ]);
}
exit;
