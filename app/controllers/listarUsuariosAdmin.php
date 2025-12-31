<?php

header('Content-Type: application/json');

require_once "../config/conexion.php";
require_once "../models/getData.php";

try {
    $usuariosListados = listarUsuarios($pdo);
    
    if ($usuariosListados && $usuariosListados['status'] === 'ok') {
        echo json_encode([
            'status' => 'ok',
            'usuarios' => $usuariosListados['data'],
        ]);
    } else {
        echo json_encode ([
            'status' => 'error',
            'mensaje' => 'No hay usuarios por mostrar'
        ]);
    }

} catch (Exception $e) {
    error_log("Error en listarUsuarios.phg ". $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => '!Erro del servidor¡'
    ]);
}
exit;