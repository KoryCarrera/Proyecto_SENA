<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/usuariosModel.php';
require_once __DIR__ . '/../models/baseHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡Metodo no permitido!'
    ]);
    exit;
};

$codigo = strip_tags(htmlspecialchars(trim($_POST['codigo'])));
$documento = $_SESSION['user']['documento'];

if (!$codigo){
    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡El codigo es obligatorio!'
    ]);
    exit;
};

if (!$documento) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => '¡Usuario no autentificado!'
    ]);
    exit;
};




