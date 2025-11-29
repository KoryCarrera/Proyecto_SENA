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

function casosPorComisionado($pdo, $document)
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
		
		if($conteo) {
				$nombres = [];
				$totales = [];
				
				foreach ($conteo as $flecha) {
						$nombres[] = $flecha['nombre_caso'];
						$totales[] = (int)$flecha['total'];
					}
				return [
					'labels' => $nombres,
					'total' => $totales
				];
			} else {
				return false;
				}
	} catch (PDOException $e) {
		error_log("Error SQL en casosPorTipo: ". $e->getMessage());
		return false;
		}
}
