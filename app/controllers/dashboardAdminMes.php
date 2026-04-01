<?php
//Indica que la respuesta y recibimiento de este script siempre será un objeto JSON.
header('Content-Type: application/json');

//Se llaman los archivos con las dependencias que necesitamos
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

try {
    //Se instancia la clase baseHelper para usar sus funciones
    $helper = new baseHelper($pdo);
    //Se llaman las metodos que necesitamos
    $casosTiposMes = $helper->consultObjectHelper("sp_contear_casos_tipo_mes");

    //se valida que los datos sean correctos

    if ($casosTiposMes && count($casosTiposMes) > 0) {
        $nombres = [];
        $totales = [];

        //se recorren los arrays con la palabra reservada

        foreach ($casosTiposMes as $temp) {
            $nombres[] = $temp['nombre_caso'];  // 
            $totales[] = (int) $temp['total'];    // 
        }

        //se retorna en array asociativo con los datos corregidos

        $casosTiposMes = [
            'tipos' => $nombres,
            'casos' => $totales
        ];
    }

    //se consulta el total de casos por comisionado

    $casosComisionadoMes = $helper->consultObjectHelper("sp_casos_por_comi_mes");

    //se valida que los datos sean correctos

    if ($casosComisionadoMes) {
        $comisionado = [];
        $total = [];

        foreach ($casosComisionadoMes as $temp) {
            $comisionado[] = $temp['comisionado'];
            $total[] = (int)$temp['total_casos'];
        }

        $casosComisionadoMes = [
            'comisionado' => $comisionado,
            'casos' => $total
        ];
    }

    $casosPorUnMes = $helper->consultObjectHelper("sp_casos_por_un_mes");

    if ($casosPorUnMes) { //Validamos el retorno de casosPorUnMes en la variable
        //Se declaran arrays vacios para evitar undefined variable
        $dia = [];
        $casos = [];

        foreach ($casosPorUnMes as $temp) { //Se recorren los arrays con la palaba reservada
            $dia[] = $temp['dia']; //Guardamos los valores de mes dentro de su variable
            $casos[] = (int) $temp['total_casos']; //Especificamos el tipo de dato y guardamos casos dentro de su variable
        }

        $casosPorUnMes = [
            'dia' => $dia,
            'casos' => $casos
        ]; //Retornamos en array asociativo con los casosPorUnMes corregidos
    }

    //Se asignan los valores que necesitamos en una variable para facilitar su manejo
    $response = [
        'status' => 'ok',
        'labelsPolar' => $casosTiposMes ? $casosTiposMes['tipos'] : [],
        'dataPolar' => $casosTiposMes ? $casosTiposMes['casos'] : [],
        'labelsPie' => $casosComisionadoMes ? $casosComisionadoMes['comisionado'] : [],
        'dataPie' => $casosComisionadoMes ? $casosComisionadoMes['casos'] : [],
        'labelsBar' => $casosPorUnMes ? $casosPorUnMes['dia'] : [],
        'dataBar' => $casosPorUnMes ? $casosPorUnMes['casos'] : [],
        'errors' => []
    ];


    echo json_encode($response); //Retornamos el json

} catch (Exception $e) { //Capturamos errores sql
    error_log("Error en dashboardAdminMes.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'ok',
        'mensaje' => 'No hay casos para mostrar'
    ]);
}
exit; //finalizamos el script