<?php

header('Content-Type: application/json');

require_once "../config/conexion.php";
require_once "../models/getData.php";

	$casosTipos = casosPorTipo($pdo);
	$casosEstado = casosPorEstado($pdo);
	$casosProceso = casosPorProceso($pdo);
	
	if ($casosTipos || $casosEstado || $casosProceso) {

		echo json_encode ([
			'status' => 'ok',
			'labelsPolar' => $casosTipos['tipos'],
			'dataPolar' => $casosTipos['casos'],
			'labelsPie' => $casosEstado['estado'],
			'dataPie' => $casosEstado['casos'],
			'labelsBar' => $casosProceso['proceso'],
			'dataBar' => $casosPorMes['casos']
		]);
		} else {
			echo json_encode ([
				'status' => 'error',
				'mensaje' => 'Ocurrió un error en la obtención de datos'
			]);
			}
exit;
?>