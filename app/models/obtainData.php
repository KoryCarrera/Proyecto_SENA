<?php
//Funciona para mostrarnos un análasis de los tipos de PQRS del sistema

function obtenerAnalisisDemanda($pdo){
	try {
		$stmt = $pdo->prepare("CALL sp_analisis_demanda()");
		$stmt->execute(); 
		
		$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		
		return $resultados;
	} catch (PDOException $e){
		error_log("Error en obtener el analisis de las demandas: ". $e->getMessage());
		return false;
	}
	}
