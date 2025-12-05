<?php require_once "../../controllers/checkSession.php";?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador</title>

    <!--Este es el enlace entre el proyecto y bootstrap-->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    <!--CSS propio para colores y fonts-->

    <link rel="stylesheet" href="../../../Public/assets/css/home-admin.css">
    
    <!--Link de google fonts-->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=ADLaM+Display&family=Roboto:ital,wght@0,100..900;1,100..900&family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap"
        rel="stylesheet">
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&display=swap" rel="stylesheet">

    <script src="../../../public/assets/js/jquery-3.7.1.min.js"></script>

</head>

<body>
    <!--Barra de navegación superior-->
    <div class="top-bar">
        <nav class="navbar m-0 p-0 bg-body-tertiary">
            <div class="container-fluid d-flex align-items-center justify-content-between">
                <img class="ms-3"src="../../../Public/assets/img/logo_sena.png" alt="SENA" width="103" height="100">
                <div class="d-flex align-items-center">
                    <div class="text-end me-3">
                        <h2 class="mb-0 d-none d-md-block">User Name</h2>
                        <h4 class="mb-0 d-none d-md-block">Administrador</h4>
                    </div>
                    <a href="#">
                        <img src="../../../Public/assets/img/icon account.png" alt="User" width="76" height="76">
                    </a>
                    <a href="cerrar_sesion.php">Cerrar Sesion</a>
                </div>
            </div>
        </nav>
    </div>
    <!--SlideBar-->

<div class="side-bar">
  <div class="sidebar container-fluid">
    <ul class="nav flex-column text-center">
    
				<li class="nav-item my-1 active">
					<a href="#" class="nav-link text-none">
						<i class="bi bi-house-fill home-icon d-block"></i>
						<span>Inicio</span>
					</a>
				</li>

      <li class="nav-item my-1">
        <a href="generar_informe.php" class="nav-link text-none">
          <i class="bi bi-file-earmark-text-fill crear-notificacion"></i>
          <br>
	          <span>Generar<br>Informe</span>
        </a>
      </li>

      <li class="nav-item my-1">
        <a href="casos.php" class="nav-link text-none">
          <i class="bi bi-eye-fill ver-caso d-block"></i>
          <span>Casos</span>
        </a>
      </li>

      <li class="nav-item my-1">
        <a href="crear-usuario.php" class="nav-link text-none">
          <i class="bi bi-person-fill-gear usuarios"></i>
          <span>Usuarios</span>
        </a>
      </li>

      <li class="nav-item my-1">
        <a href="notificaciones.php" class="nav-link text-none">
          <i class="bi bi-bell-fill notificacion"></i>
          <span>Notificación</span>
        </a>
      </li>

    </ul>
  </div>
</div>

<!--Contenido de la pagina-->

<div class="main-content">
  <div class="contenido">
    <h1>¡Bienvenido al Sistema de Gestión SENA!</h1>
    <p>
      Como administrador, tienes acceso total a las herramientas y funciones de esta plataforma.
      <br>Desde aquí podrás gestionar usuarios, supervisar solicitudes, generar reportes y mantener actualizada la información institucional.
      <br>Tu rol es fundamental para garantizar el correcto funcionamiento del sistema y apoyar la labor de la Comisión de Personal.
    </p>
    
<div class="row">
  <div class="col-sm-6 mb-3 mb-sm-0">
    <div class="card">
      <div class="card-body">
			<canvas id="polarChart"></canvas>
      </div>
    </div>
  </div>
  <div class="col-sm-6 mb-3 mb-sm-0">
    <div class="card">
      <div class="card-body">
			<canvas id="pieChart"></canvas>
      </div>
    </div>
  </div>
  <div class="col-sm-12 mt-3">
    <div class="card">
      <div class="card-body">
			<canvas id="barChart"></canvas>
      </div>
    </div>
  </div>
</div>

  </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <script src="../../../public/assets/js/dashboard_admin.js"></script>    

</body>

</html>
