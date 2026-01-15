<?php
//Funciona para que nos traiga los casos que los comisionados han hecho en el mes

function obtenerCaracterizacionUsuarios($pdo)
{
	try{
		$stmt = $pdo->prepare("CALL sp_caracterizacion_usuarios()");
		$stmt->execute;
		
		$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor;
		
		return $resultados;
	} catch(PDOException $e){
		error_log("Error al obtener casos de los usuarios: " . $e->getMessage());
		return false;
		}
	}
