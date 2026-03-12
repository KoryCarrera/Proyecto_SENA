<?php

header('Content-Type: application/json');

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

$helper = new baseHelper($pdo);

try {
    $usuariosListados = $helper->consultSimpleHelper('sp_listar_usuarios()');
    
    if ($usuariosListados) {
        echo json_encode([
            'status' => 'ok',
            'usuarios' => $usuariosListados,
        ]);
    } else {
        echo json_encode ([
            'status' => 'error',
            'mensaje' => 'No hay usuarios por mostrar'
        ]);
    }

} catch (Exception $e) {
    error_log("Error en listarUsuarios.php ". $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => '!Erro del servidor¡'
    ]);
}
exit;