<?php
require_once '../config/conexion.php';

function listarCasos($pdo)
{

    $stmt = $pdo->prepare("CALL sp_listar_casos()");


    try {
        $stmt->execute();
        $casos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $casos;
    } catch (PDOException $e) {
        error_log('Error al obtener los casos: ' . $e->getMessage());
        return null;
    }
}

function filtrarPorComisionado($pdo, $document)
{
    $stmt = $pdo->prepare("CALL sp_listar_casos_por_comisionado(:documento)");
    $stmt->bindParam(':documento', $document, PDO::PARAM_INT);

    try {
        $stmt->execute();
        $casos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $casos;
    } catch (PDOException $e) {
        error_log("Error al filtrar los casos: " . $e->getMessage());
        return null;
    }
}

function obtenerSeguimientosPorCaso($pdo, $idCaso)
{

    $stmt = $pdo->prepare("CALL sp_listar_seguimientos_por_caso(:id)");
    $stmt->bindParam(':id', $idCaso, PDO::PARAM_INT);

    try {
        $stmt->execute();
        $seguminetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $seguminetos;
    } catch (PDOException $e) {
        error_log("Error al filtrar los seguimientos: " . $e->getMessage());
        return null;
    }
}

function buscarUsuario($pdo, $document, $name)
{
    $stmt = $pdo->prepare("CALL sp_buscar_usuario(:documento, :nombre)");
    $stmt->bindParam(':documento', $document, PDO::PARAM_INT);
    $stmt->bindParam(':nombre', $name, PDO::PARAM_STR);


    try {
        $stmt->execute();
        $usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $usuario;
    } catch (PDOException $e) {
        error_log("Error al buscar el usuario: " . $e->getMessage());
        return null;
    }
}

function resumenGeneral($pdo)
{
    $stmt = $pdo->prepare("CALL sp_resumen_general()");

    try {
        $stmt->execute();
        $resumenGeneral = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return [
            'status' => 'ok',
            'data' => $resumenGeneral
        ];
    } catch (PDOException $e) {
        error_log("Error al obtener el resumen general: " . $e->getMessage());
        return [
            'status' => 'error',
            'mensaje' => 'Error al obtener el resumen general'
        ];
    }
}

function loginUsuario($pdo, $documento, $contrasena)
{
    error_log("-> DEBUG LOGIN: Intentando login para Documento: " . $documento . " y Contraseña: " . $contrasena);
    $stmt = $pdo->prepare("CALL sp_login_usuario(?)");
    $stmt->bindParam(1, $documento, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("-> DEBUG LOGIN: Resultado de fetch() para el usuario: " . print_r($data, true));
        $stmt->closeCursor();

        if ($data && password_verify($contrasena, $data['contraseña'])) {
            return [
                'status' => 'ok',
                'mensaje' => 'Usuario válido',
                'data' => $data
            ];
        }else{
            return [
                'status' => 'error',
                'mensaje' => 'Credenciales inválidas'
            ];
        }

    } catch (PDOException $e) {
        error_log("-> DEBUG LOGIN: Error SQL en loginUsuario: " . $e->getMessage());
        return [
            'status' => 'error',
            'mensaje' => 'error SQL'
        ];
    }
}

function casosPorTipo($pdo)
{
    $stmt = $pdo->prepare("CALL sp_contear_casos_tipo");
    
    try {
        $stmt->execute();
        $conteo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        if($conteo && count($conteo) > 0) {
            $nombres = [];
            $totales = [];
            
            foreach ($conteo as $temp) {
                $nombres[] = $temp['nombre_caso'];  // ← Coincide con el SP
                $totales[] = (int)$temp['total'];    // ← Coincide con el SP
            }
            return [
                'tipos' => $nombres,
                'casos' => $totales
            ];
        } else {
            error_log("sp_contear_casos_tipo no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error SQL en casosPorTipo: ". $e->getMessage());
        return false;
    }
}

function casosPorComisionado($pdo) {
    $stmt = $pdo->prepare("CALL sp_casos_por_comisionado");

    try {
        $stmt->execute();
        $casosComisionados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($casosComisionados) {
            $comisionado = [];
            $total = [];

            foreach($casosComisionados as $temp) {
                $comisionado[] = $temp['comisionado'];
                $total[] = (int)$temp['total_casos'];
            }

            return [
                'comisionado' => $comisionado,
                'casos' => $total
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("error en la obtención de casos por comisionado". $e->getMessage());
        return false;
    }
}

function casosPorMes ($pdo) {
    $stmt = $pdo->prepare("CALL sp_casos_por_mes");

    try {
        $stmt->execute();
        $mesesCasos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($mesesCasos) {
            $mes = [];
            $casos = [];

            foreach ($mesesCasos as $temp) {
                $mes[] = $temp['mes'];
                $casos[] = (int)$temp['total_casos'];
            }

            return [
                'mes' => $mes,
                'casos' => $casos
            ];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error en la obtencion de los datos por mes ". $e->getMessage());
        return false;
    }
}

function casosPorEstado($pdo) {
    $stmt = $pdo->prepare("CALL sp_casos_por_estado");

    try {
        $stmt->execute();
        $casosEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if($casosEstado && count($casosEstado) > 0) {
            $estados = [];
            $casos = [];
            
            foreach ($casosEstado as $temp) {
                $estados[] = $temp['nombre_estado'];  // ← Coincide con el SP
                $casos[] = (int)$temp['total_casos']; // ← Coincide con el SP
            }
            
            return [
                'estado' => $estados,
                'casos' => $casos
            ];
        } else {
            error_log("sp_casos_por_estado no devolvió filas");
            return false;
        }

    } catch (PDOException $e) {
        error_log("Ha ocurrido un error al obtener los casos por estado: ". $e->getMessage());
        return false;
    }
}


function casosPorProceso($pdo) {
    $stmt = $pdo->prepare("CALL sp_casos_por_proceso");

    try {
        $stmt->execute();
        $casosProceso = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if($casosProceso && count($casosProceso) > 0) {
            $proceso = [];
            $casos = [];

            foreach ($casosProceso as $temp) {
                $proceso[] = $temp['proceso'];           // ← Coincide con el SP
                $casos[] = (int)$temp['total_casos'];    // ← Coincide con el SP
            }

            return [
                'proceso' => $proceso,
                'casos' => $casos
            ];
        } else {
            error_log("sp_casos_por_proceso no devolvió filas");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Error al obtener los casos por proceso: ". $e->getMessage());
        return false;
    }
}