<?php

//Llamamos el autoloader de vendor para inicializar altorouter
require_once __DIR__ . '/../vendor/autoload.php';

//Cargamos el mapa de rutas (El archivo router.php)
$router = require_once __DIR__ . '/../app/router.php';

//Ejecutamos el motor de busqueda de la libreria

$match = $router->match();

//Ya ejecutamos la logica de redirección

if ($match) {
  //Si hay match cargamos el archivo correspondiente

  //Construimos la ruta real
  $destino = __DIR__. '/../app/' . $match['target'];

  //validamos existencia del archivo
  if (file_exists($destino)) {
  
  require_once $destino;
  
    } else {
   
    //Agregamos un manejo de errores interno
    header($_SERVER['SERVER_PROTOCOL'] . '500 Internar Error!');
    echo "Error: el archivo <b>{$match['target']}</b> No existe";
  }

  } else {
    
    //Agregamos el manejo de error de Not Found (404)
    header($_SERVER['SERVER_PROTOCOL'] . '404 Not Found!');

    echo "<h1>¡Ruta no encontrada!<h1>";

  }

?>
