<?php
//Indica que la respuesta y recibimiento de este script siempre será un objeto JSON.
header('Content-Type: application/json');

//Se llaman los archivos con las dependencias que necesitamos
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

try {
    $model = new baseHelper($pdo);
    //Se llaman las funciones que necesitamos
    $casosTiposSemana = $model->consultObjectHelper('sp_contear_casos_tipo_semana');

    if ($casosTiposSemana && count($casosTiposSemana) > 0) {
        $nombres = [];
        $totales = [];

        foreach ($casosTiposSemana as $temp) {
            $nombres[] = $temp['nombre_caso'];
            $totales[] = (int)$temp['total'];
        }

        $casosTiposSemana = [
            'tipos' => $nombres,
            'casos' => $totales
        ];
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al encontrar los casos por tipo de esta semana'
        ]);
        exit;
    }

    $casosComisionadoSemana = $model->consultObjectHelper('sp_casos_por_comi_semana');

        if ($casosComisionadoSemana) {
        $comisionado = [];
        $totales = [];

        foreach ($casosComisionadoSemana as $temp) {
            $comisionado[] = $temp['comisionado'];
            $totales[] = (int)$temp['total_casos'];
        }

        $casosComisionadoSemana = [
            'comisionado' => $comisionado,
            'casos' => $totales
        ];
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al encontrar los casos de los comisionados de esta semana'
        ]);
        exit;
    }

    $casosPorSemana = $model->consultObjectHelper('sp_casos_por_semana');

    if ($casosPorSemana) { //Validamos el retorno de casosPorUnMes en la variable
        //Se declaran arrays vacios para evitar undefined variable
        $dia = [];
        $casos = [];

        foreach ($casosPorSemana as $temp) { //Se recorren los arrays con la palaba reservada
            $dia[] = $temp['dia_semana']; //Guardamos los valores de mes dentro de su variable
            $casos[] = (int) $temp['casos_dia']; //Especificamos el tipo de dato y guardamos casos dentro de su variable
        }

        $casosPorSemana = [
            'dia' => $dia,
            'casos' => $casos
        ]; //Retornamos en array asociativo con los casosPorUnMes corregidos
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al encontrar los casos de esta semana'
        ]);
        exit;
    }


    //Se asignan los valores que necesitamos en una variable para facilitar su manejo
    $response = [
        'status' => 'ok',
        'labelsPolar' => $casosTiposSemana ? $casosTiposSemana['tipos'] : [],
        'dataPolar' => $casosTiposSemana ? $casosTiposSemana['casos'] : [],
        'labelsPie' => $casosComisionadoSemana ? $casosComisionadoSemana['comisionado'] : [],
        'dataPie' => $casosComisionadoSemana ? $casosComisionadoSemana['casos'] : [],
        'labelsBar' => $casosPorSemana ? $casosPorSemana['dia'] : [],
        'dataBar' => $casosPorSemana ? $casosPorSemana['casos'] : [],
    ];

    echo json_encode($response); //Retornamos el json

} catch (Exception $e) { //Capturamos errores sql
    error_log("Error en dashboardAdminSemana.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'No hay datos para mostrar'
    ]);
}
exit; //finalizamos el script