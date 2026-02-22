<?php

//Instanciamos el router
$router = new AltoRouter();

//definimos la pagina raiz
$router->map('GET', '/', '../Public/landing.php');

//Rutas hacia controladores

$router -> map('POST', '/login/auth', 'controllers/login.php');
$router -> map('POST', '/listarSeguimientos', '/controllers/listarSeguimientos.php');
$router -> map('GET', '/graficasAdmin', 'controllers/dashboardAdmin.php');
$router -> map('GET', '/graficasAdminMes', 'controllers/dashboardAdminMes.php');
$router -> map('GET', '/graficasAdminSemana', 'controllers/dashboardAdminSemana.php');
$router -> map('GET', '/graficasComi', 'controllers/dashboardComi.php');
$router -> map('GET', '/listarCasos', 'controllers/listarCasos.php');
$router -> map('GET', '/listarUsuarios', 'controllers/listarUsuariosAdmin.php');
$router -> map('POST', '/logout', 'controllers/logout.php');
$router -> map('POST', '/modalCasoAdmin', 'controllers/modalCasoAdmin.php');
$router -> map('POST', '/modalUsuario', 'controllers/modalUsuario.php');
$router -> map('POST', '/modalProceso', 'controllers/modalProceso.php');
$router -> map('POST', '/CasosPDF', 'controllers/reporteCasosPDF.php');
$router -> map('POST', '/UsuariosPDF', 'controllers/reporteUsuariosPDF.php');
$router -> map('POST', '/procesosPDF', 'controllers/reporteProcesosPDF.php');
$router -> map('POST', '/registrarProceso', 'controllers/registrarProceso.php');
$router -> map('GET', '/listarProceso', 'controllers/listarProceso.php');
$router -> map('POST', '/desactivarProceso', 'controllers/desactivarProceso.php');
$router -> map('POST', '/reactivarProceso', 'controllers/reactivarProceso.php');
$router -> map('POST', '/generarExcel', 'controllers/reporteExcel.php');
$router -> map('POST', '/registrarCaso', 'controllers/registrarCasos.php');
$router -> map('POST', '/registrarUsuario', 'controllers/crearUsuario.php');
$router -> map('GET', '/opcionesRegistro', 'controllers/listarOpcionesRegistro.php');
$router -> map('POST', '/editarUsuario', '/controllers/editarUsuario.php');
$router -> map('POST', '/cambiarEstadoUsuario', '/controllers/cambiarEstadoUsuario.php');
$router -> map ('GET','/listarCasosComi','/controllers/listarCasosComi.php');
$router -> map('POST', '/gestionarCaso', '/controllers/gestionarCaso.php');
$router -> map('GET', '/estadisticasGenerales', '/controllers/conteoGeneral.php');
$router -> map('GET', '/estadisticasUsuario', '/controllers/conteoPorUsuario.php');

//Rutas hacia views admin

$router -> map('GET', '/dashboardAdmin', 'views/admin/home.php');
$router -> map('GET', '/casosAdmin', 'views/admin/casos.php');
$router -> map('GET', '/generarInforme', 'views/admin/generar_informe.php');
$router -> map('GET', '/usuarios', 'views/admin/crear-usuario.php');
$router -> map('GET', '/notificacionesAdmin', 'views/admin/notificaciones.php');
$router -> map('GET', '/perfilAdmin', 'views/admin/perfil.php');
$router -> map('GET', '/gestionar', 'views/admin/gestionar.php');
$router -> map('GET', '/procesoOrganizacional', 'views/admin/proceso-organizacional.php');
$router -> map('GET', '/editarUsuario', 'views/admin/editar-usuario.php');

//Rutas hacia views comisionado

$router -> map('GET', '/dashboardComi', 'views/comisionado/home.php');
$router -> map('GET', '/casos', 'views/comisionado/caso.php');
$router -> map('GET', '/registrarCasos', 'views/comisionado/Reg-caso.php');
$router -> map('GET', '/notificacionesComi', 'views/comisionado/notificacion.php');
$router -> map('GET', '/perfil', 'views/comisionado/perfil.php');

//Rutas views inicio

$router -> map('GET', '/login', '../Public/pages/entrada.php');
$router -> map('GET', '/saber-mas', '../Public/pages/saber_mas.php');
return $router;
?>
