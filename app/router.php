<?php

//Instanciamos el router
$router = new AltoRouter();

//definimos la pagina raiz
$router->map('GET', '/', '../Public/landing.php');

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
$router -> map('POST', '/registrarProceso', 'controllers/registrarProceso.php');
$router -> map('GET', '/listarProceso', 'controllers/listarProceso.php');
$router -> map('POST', '/desactivarProceso', 'controllers/desactivarProceso.php');
$router -> map('POST', '/reactivarProceso', 'controllers/reactivarProceso.php');
$router -> map('POST', '/generarExcel', 'controllers/reporteExcel.php');
$router -> map('POST', '/registrarCaso', 'controllers/registrarCasos.php');
$router -> map('POST', '/registrarUsuario', 'controllers/crearUsuario.php');
$router -> map('GET', '/listarOpcionesRegistro', 'controllers/listarOpcionesRegistro.php');

//Rutas hacia views admin

$router -> map('GET', '/dashboardAdmin', 'views/admin/home.php');
$router -> map('GET', '/casosAdmin', 'views/admin/casos.php');
$router -> map('GET', '/generarInforme', 'views/admin/generar_informe.php');
$router -> map('GET', '/usuarios', 'views/admin/crear-usuario.php');
$router -> map('GET', '/notificacionesAdmin', 'views/admin/notificaciones.php');
$router -> map('GET', '/gestionar', 'views/admin/gestionar.php');
$router -> map('GET', '/procesoOrganizacional', 'views/admin/proceso-organizacional.php');
$router -> map('GET', '/editarUsuario', 'views/admin/editar-usuario.php');

//Rutas hacia views comisionado

$router -> map('GET', '/dashboardComi', 'views/comisionado/home.php');
$router -> map('GET', '/casos', 'views/comisionado/caso.php');
$router -> map('GET', '/registrarCasos', 'views/comisionado/Reg-caso.php');
$router -> map('GET', '/notificacionesComi', 'views/comisionado/notificacion.php');

//Rutas views inicio

$router -> map('GET', '/entradaAdmin', '../Public/pages/entrada_administrador.php');
$router -> map('GET', '/entradaComi', '../Public/pages/entrada_comisionado.php');

return $router;
?>
