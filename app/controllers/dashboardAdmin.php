<?php
//Indica que la respuesta y recibimiento de este script siempre será un objeto JSON.
header('Content-Type: application/json');

//Se llaman los archivos con las dependencias que necesitamos
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

try {
    //se crea una instancia de la clase baseHelper para usar sus metodos
    $helper = new baseHelper($pdo);

    //Se llaman los metodos que necesitamos

    $casosTipos = $helper->consultObjectHelper("sp_contear_casos_tipo");

    if ($casosTipos && count($casosTipos) >= 0) {
        $nombres = [];
        $totales = [];

        foreach ($casosTipos as $temp) {
            $nombres[] = $temp['nombre_caso'];  // 
            $totales[] = (int) $temp['total'];    // 
        }
        $casosTipos = [
            'tipos' => $nombres,
            'casos' => $totales
        ];
    }

    $casosComisionado = $helper->consultObjectHelper("sp_casos_por_comisionado");

    if ($casosComisionado) {
        $comisionado = [];
        $total = [];

        foreach ($casosComisionado as $temp) {
            $comisionado[] = $temp['comisionado'];
            $total[] = (int) $temp['total_casos'];
        }

        $casosComisionado = [
            'comisionado' => $comisionado,
            'casos' => $total
        ];
    }

    $casosPorMes = $helper->consultObjectHelper("sp_casos_por_mes");

    if ($casosPorMes) { //Validamos el retorno de datos en la variable
        //Se declaran arrays vacios para evitar undefined variable
        $mes = [];
        $casos = [];

        foreach ($casosPorMes as $temp) { //Se recorren los arrays con la palaba reservada
            $mes[] = $temp['mes']; //Guardamos los valores de mes dentro de su variable
            $casos[] = (int) $temp['total_casos']; //Especificamos el tipo de dato y guardamos casos dentro de su variable
        }

        $casosPorMes =  [
            'mes' => $mes,
            'casos' => $casos
        ]; //Retornamos en array asociativo con los datos corregidos
    }


    //Se asignan los valores que necesitamos en una variable para facilitar su manejo
    $response = [
        'status' => 'ok',
        'labelsPolar' => $casosTipos ? $casosTipos['tipos'] : [],
        'dataPolar' => $casosTipos ? $casosTipos['casos'] : [],
        'labelsPie' => $casosComisionado ? $casosComisionado['comisionado'] : [],
        'dataPie' => $casosComisionado ? $casosComisionado['casos'] : [],
        'labelsBar' => $casosPorMes ? $casosPorMes['mes'] : [],
        'dataBar' => $casosPorMes ? $casosPorMes['casos'] : [],
        'errors' => []
    ];

    echo json_encode($response); //Retornamos el json

} catch (Exception $e) { //Capturamos errores sql
    error_log("Error en dashboardAdmin.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'No hay casos para mostrar'
    ]);
}
exit; //finalizamos el script
