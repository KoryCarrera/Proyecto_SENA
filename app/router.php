<?php

//Instanciamos el router
$router = new AltoRouter();

//definimos la pagina raiz
$router->map('GET', '/', '../Public/index.php');

//Rutas hacia controladores

$router -> map('POST', '/loginAdmin/auth', 'controllers/loginAdmin.php');
$router -> map('GET', '/graficasAdmin', 'controllers/dashboardAdmin.php');
$router -> map('GET', '/graficasComi', 'controllers/dashboardComi.php');
$router -> map('GET', '/listarCasos', 'controllers/listarCasos.php');
$router -> map('GET', '/listarUsuarios', 'controllers/listarUsuariosAdmin.php');
$router -> map('POST', '/loginComisionado/auth', 'controllers/loginComisionado.php');
$router -> map('POST', '/logout', 'controllers/logout.php');
$router -> map('POST', '/modalCasoAdmin', 'controllers/modalCasoAdmin.php');
$router -> map('POST', '/modalUsuario', 'controllers/modalUsuario.php');
$router -> map('POST', '/generarPDF', 'controllers/reportePDF.php');

//Rutas hacia views admin

$router -> map('GET', '/dashboardAdmin', 'views/admin/home.php');
$router -> map('GET', '/casosAdmin', 'views/admin/casos.php');
$router -> map('GET', '/generarInforme', 'views/admin/generar_informe.php');
$router -> map('GET', '/usuarios', 'views/admin/crear-usuario.php');
$router -> map('GET', '/notifaciones', 'views/admin/notificaciones.php');
$router -> map('GET', '/gestionar', 'views/admin/gestionar.php');

//Rutas hacia views comisionado

$router -> map('GET', '/dashboardComi', 'views/comisionado/home.php');
$router -> map('GET', '/casos', 'views/comisionado/casos.php');
$router -> map('GET', '/registrarCasos', 'views/comisionado/Reg-caso.php');
$router -> map('GET', '/notificaciones', 'views/comisionado/notificaciones.php');

return $router;
?>