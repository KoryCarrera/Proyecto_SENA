<?php
//Especificamos el tipo de comunicacion que tendra el script
header('Content-Type: application/json');

//Cargamos la session activa
session_start();

//Llamamos la credenciales necesarias
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

$helper = new baseHelper($pdo);

try {
    $idCaso = $_POST['idcaso'];

    $data = [
        ['value' => $idCaso, 'type' => PDO::PARAM_INT]
    ];

    $listarSeguimientos = $helper->consultObjectWithParams('sp_listar_seguimientos_por_caso(?)', $data);

    if ($listarSeguimientos) {
        echo json_encode([
            'status' => 'ok',
            'seguimientos' => $listarSeguimientos
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'No hay seguimientos para listar'
        ]);
    }
} catch (Exception $e) { //captura de errores sql
    error_log("Error en listarSeguimientos.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
}
exit;
