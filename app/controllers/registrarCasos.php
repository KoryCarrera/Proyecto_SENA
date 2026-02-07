<?php

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/insertData.php";
require_once __DIR__ . '/../../vendor/autoload.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',  
        'mensaje' => 'Método no permitido'
    ]);
    exit;
try {
	$proceso = $_POST["proceso"];
          $estado = $_POST["estado"];
          $tipo = $_POST["tipo"];
          $descripcion = $_POST["descripcion"];
          
           if (!$proceso || !is_string($proceso) || trim($proceso) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El proceso es requerido'
        ]);
        exit;
    }
    
     if (!$estado || !is_string($estado) || trim($estado) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El estado es requerido'
        ]);
        exit;
    }
    
     if (!$tipo || !is_string($tipo) || trim($tipo) === '') {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El tipo es requerido'
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
    
           
          $registrar = registrarCasos($pdo, $_SESSION['user']['documento'], $proceso, $estado, $tipo, $descripcion);

    if ($registrar === true) {
        echo json_encode([
            'status' => 'ok',
            'mensaje' => 'Caso registrado exitosamente'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al registrar el proceso'
        ]);
    }
        } catch (Exception $e) {
    error_log("Error en registrarCasos.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor'
    ]);
        }
}
