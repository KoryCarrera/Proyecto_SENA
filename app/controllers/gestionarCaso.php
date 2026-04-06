<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/casosModel.php";
require_once __DIR__ . "/../models/baseHelper.php"; 
require_once __DIR__ . "/../utils/utilsEmail.php";

try {
    $model = new CasosModel($pdo);
    $helper = new baseHelper($pdo); 
    
    $documentoAdmin = $_SESSION['user']['documento'] ?? null; // Quién hace el movimiento
    $idCaso        = $_POST['idCaso'] ?? null;
    $documentonew  = $_POST['documentoNuevo'] ?? null; // A quién se lo dan
    $motivo        = $_POST['motivo'] ?? '';

    if (!empty($documentonew) && !empty($idCaso)) {
        
        // --- PASO 1: BUSCAR QUIÉN TIENE EL CASO ACTUALMENTE ---
        // Necesitamos saber quién es el dueño "viejo" para avisarle que se lo quitamos
        $sqlActual = "SELECT documento FROM caso WHERE id_caso = :id";
        $stmt = $pdo->prepare($sqlActual);
        $stmt->execute([':id' => $idCaso]);
        $dueñoAnterior = $stmt->fetchColumn();

        if (!$dueñoAnterior) {
            throw new Exception("No se pudo encontrar el responsable actual del caso.");
        }

        // --- PASO 2: OBTENER DATOS DE AMBOS DESDE LA TABLA USUARIO ---
        $paramsViejo = [['value' => $dueñoAnterior, 'type' => PDO::PARAM_STR]];
        $paramsNuevo = [['value' => $documentonew, 'type' => PDO::PARAM_STR]];

        $dataViejo = $helper->consultSimpleWithParams('sp_traer_usuario(?)', $paramsViejo);
        $dataNuevo = $helper->consultSimpleWithParams('sp_traer_usuario(?)', $paramsNuevo);

        if (!$dataViejo || !$dataNuevo) {
            throw new Exception("Error al obtener correos de los comisionados.");
        }

        // --- PASO 3: EJECUTAR REASIGNACIÓN EN BD ---
        $model->reasignarCaso($documentoAdmin, $documentonew, $idCaso, $motivo);
        
        // --- PASO 4: ENVIAR CORREOS ---
        $nomViejo = $dataViejo['nombre'] . ' ' . ($dataViejo['apellido'] ?? '');
        $nomNuevo = $dataNuevo['nombre'] . ' ' . ($dataNuevo['apellido'] ?? '');

        // Enviamos: (Email del que pierde, Email del que gana, Nombre que pierde, Nombre que gana...)
        correoReasignacionCaso(
            $dataViejo['email'], 
            $dataNuevo['email'], 
            $nomViejo, 
            $nomNuevo, 
            $idCaso, 
            $motivo
        );

        echo json_encode(['status' => 'ok', 'mensaje' => 'Reasignacion exitosa y correos enviados']);
        exit;
    }
    
    // ... resto de lógica (cambio estado, seguimiento) ...

} catch(Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}