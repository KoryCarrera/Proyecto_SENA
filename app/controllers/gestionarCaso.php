<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/casosModel.php";
require_once __DIR__ . "/../models/baseHelper.php"; 
require_once __DIR__ . "/../utils/utilsEmail.php";

// Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
    exit;
}

try {
    $model = new CasosModel($pdo);

    $documentoAdmin = $_SESSION['user']['documento'] ?? null; // Quién hace el movimiento
    $idCaso        = $_POST['idCaso'] ?? null;
    $documentonew  = $_POST['documentoNuevo'] ?? null; // A quién se lo dan
    $motivo        = $_POST['motivo_cambio'] ?? $_POST['motivo'] ?? ''; // Por si viene del select o de reasignación
    $nuevoEstado   = $_POST['idEstado'] ?? null;
    $seguimiento   = $_POST['observacion'] ?? null;

    if (!empty($documentonew) && !empty($idCaso)) {
        
        $dataCaso = [
            [ 'value' => $idCaso, 'type' => PDO::PARAM_STR]
        ];

        // Asumimos que consultSimpleWithParams está en baseHelper o heredado en el modelo
        $findCase = $model->consultSimpleWithParams('sp_obtener_caso_por_id(?)', $dataCaso);

        if (!$findCase) {
            echo json_encode(['status' => 'error', 'mensaje' => "Error al encontrar los datos del usuario"]);
        }

        // --- PASO 2: OBTENER DATOS DE AMBOS DESDE LA TABLA USUARIO ---
        $paramsViejo = [['value' => $findCase['documento'], 'type' => PDO::PARAM_STR]];
        $paramsNuevo = [['value' => $documentonew, 'type' => PDO::PARAM_STR]];

        $dataViejo = $model->consultSimpleWithParams('sp_traer_usuario(?)', $paramsViejo);
        $dataNuevo = $model->consultSimpleWithParams('sp_traer_usuario(?)', $paramsNuevo);

        if (!$dataViejo || !$dataNuevo) {
            echo json_encode(['status' => 'error', 'mensaje' => "Error al obtener correos de los usuarios involucrados."]);
        }

        // --- PASO 3: EJECUTAR REASIGNACIÓN EN BD ---
        $model->reasignarCaso($documentoAdmin, $documentonew, $idCaso, $motivo);
        
        // --- PASO 4: ENVIAR CORREOS ---
        $nomViejo = trim($dataViejo['nombre'] . ' ' . ($dataViejo['apellido'] ?? ''));
        $nomNuevo = trim($dataNuevo['nombre'] . ' ' . ($dataNuevo['apellido'] ?? ''));

        // Enviamos: (Email del que pierde, Email del que gana, Nombre que pierde, Nombre que gana...)
        correoReasignacionCaso(
            $dataViejo['email'], 
            $dataNuevo['email'], 
            $nomViejo, 
            $nomNuevo, 
            $idCaso, 
            $motivo
        );

        echo json_encode(['status' => 'ok', 'mensaje' => 'Reasignación exitosa y correos enviados.']);
        exit;
    }

    if (!empty($nuevoEstado) && !empty($idCaso)) {

        // Se actualiza el estado (agregué el motivo asumiendo que tu método lo soporta)
        $model->cambiarEstadoCaso($nuevoEstado, $documentoAdmin, $idCaso, $motivo);
        
        // Si además del cambio de estado, el usuario escribió una observación, la registramos.
        if (!empty($seguimiento)) {
            $model->registrarSeguimiento($seguimiento, $idCaso, $documentoAdmin);
        }

        echo json_encode(['status' => 'ok', 'mensaje' => '¡Estado actualizado con éxito!']);
        exit;
    }

    if (!empty($seguimiento) && !empty($idCaso)) {
        
        $model->registrarSeguimiento($seguimiento, $idCaso, $documentoAdmin);
        
        echo json_encode(['status' => 'ok', 'mensaje' => '¡Seguimiento registrado con éxito!']);
        exit;
    }

    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Datos insuficentes'
    ]);
    exit;

} catch(Exception $e) {
    error_log("Error en Controlador de Casos: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}