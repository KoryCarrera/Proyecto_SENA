<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/casosModel.php";

// se valida que el metodo sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
    exit;
}
    // se toma un try catch para manejar errores  
try {
    $model = new CasosModel($pdo);
    
    // Captura de datos generales
    $documentoUser = $_SESSION['user']['documento'] ?? null;
    $idCaso        = $_POST['idCaso'] ?? null;
    $motivo        = $_POST['motivo'] ?? '';
    $seguimiento   = $_POST['observacion'] ?? '';
    
    // Captura de datos específicos
    $documentonew  = $_POST['documentoNuevo'] ?? null;
    $nuevoEstado   = $_POST['idEstado'] ?? null;

    // LÓGICA DE DECISIÓN
    // Si el documento nuevo tiene contenido, reasignamos.
    if (!empty($documentonew)) {
        
        $model->reasignarCaso($documentoUser, $documentonew, $idCaso, $motivo);
        echo json_encode(['status' => 'ok', 'mensaje' => '¡El caso ha sido reasignado exitosamente!']);

    } 
    // Si no hay documento nuevo, pero sí hay un nuevo estado, actualizamos.
    elseif ($nuevoEstado) {
        
        $model->cambiarEstadoCaso($nuevoEstado, $documentoUser, $idCaso);
        echo json_encode(['status' => 'ok', 'mensaje' => '¡Estado actualizado con éxito!']);

    } elseif ($seguimiento) {

     $model->registrarSeguimiento($seguimiento, $idCaso, $documentoUser);
        echo json_encode([
            'status' => 'ok',
            'mensaje' => '¡Seguimiento registrado con exito!'
        ]);
        
    } else {
        // Si llega aquí, es porque $_POST['documento_nuevo'] y $_POST['nuevo_estado'] están vacíos o no existen.
        echo json_encode([
            'status' => 'error', 
            'mensaje' => 'No se pudo determinar la acción. Revisa que los nombres de los campos en el HTML sean correctos.'
        ]);
    }

} catch(Exception $e) {
    // IMPORTANTE: Devolvemos el JSON con el error real del modelo (permisos, estados, etc)
    echo json_encode([
        'status' => 'error',
        'mensaje' => $e->getMessage()
    ]);
}