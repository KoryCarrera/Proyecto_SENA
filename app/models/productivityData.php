<?php
//Funciona para que podamos obtener el resumen productivo de los comisionados

function obtenerResumenProductividad($pdo)
{
	try{
		$stmt = $pdo->prepare("CALL sp_resumen_productividad_comisionados()");
		$stmt->execute();
		
		$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		
		return $resultados;
		} catch(PDOException $e){
			error_log("Error al obtener resumen de comisionados: " . $e->getMessage());
			return false;
			}
	}
