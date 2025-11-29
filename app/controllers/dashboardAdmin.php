<?php

header('Content-Type: application/json');

require_once "../config/conexion.php";
require_once "../models/getData.php";

	$casosConteados = casosPorTipo($pdo);
	
	if ($casosConteados) {
		echo json_encode ([
			'status' => 'ok',
			'labels' => $casosConteados['labels'],
			'data' => $casosConteados['total']
		]);
		} else {
			echo json_encode ([
				'status' => 'error',
				'mensaje' => 'Ocurrió un error en la obtención de datos'
			]);
			}
exit;

?>
