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

    <link rel="stylesheet" href="../../../Public/assets/css/casos-admin.css">
    
    <!--Link de google fonts-->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=ADLaM+Display&family=Roboto:ital,wght@0,100..900;1,100..900&family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap"
        rel="stylesheet">
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&display=swap" rel="stylesheet">

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
                </div>
            </div>
        </nav>
    </div>
    <!--Barra de navegación secundaria-->
<div class="second-bar">
<nav class="navbar navbar-expand-lg second-bar">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Seguimiento</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Filtrar
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Nombre Del Caso</a></li>
            <li><a class="dropdown-item" href="#">Fecha de registro</a></li>
            <li><a class="dropdown-item" href="#">Tipo de Caso</a></li>
            <li><a class="dropdown-item" href="#">Fecha de respuesta</a></li>
            <li><a class="dropdown-item" href="#">Estado</a></li>
            <li><a class="dropdown-item" href="#">Proceso</a></li>
            <li><a class="dropdown-item" href="#">Comisionado Encargado</a></li>
          </ul>
        </li>
      </ul>
      <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Palabras Claves" aria-label="Search"/>
        <button class="btn btn-outline-success" type="submit">Buscar</button>
      </form>
    </div>
  </div>
</nav>
</div>
    <!--SlideBar-->

<div class="side-bar">
  <div class="sidebar container-fluid">
    <ul class="nav flex-column text-center">
    
				<li class="nav-item my-1">
					<a href="home.php" class="nav-link text-none">
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

      <li class="nav-item my-1 active">
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
<div class="main-content">
	<div class="contenido">
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th scope="col"># Id</th>
					<th scope="col">Fecha de Registro</th>
					<th scope="col">Tipo de Caso</th>
					<th scope="col">Fecha de respuesta</th>
					<th scope="col">Estado</th>
					<th scope="col">Proceso</th>
					<th scope="col">Comisionado Encargado</th>
					<th scope="col">Gestionar</th>
				</tr>
			</thead>
			<tbody>
				<tr>
				<th scope="row">1</th>
				<td>example</td>
				<td>example</td>
				<td>example</td>
				<td>example</td>
				<td>example</td>
				<td>example</td>
				<td><button class="btn-table">Supervisar</button></td>
				</tr>
				<tr>
				<th scope="row">2</th>
				<td>example</td>
				<td>example</td>
				<td>example</td>
				<td>example</td>
				<td>example</td>
				<td>example</td>
				<td><button class="btn-table">Supervisar</button></td>
				</tr>
				<tr>
				<th scope="row">3</th>
				<td>example</td>
				<td>example</td>
				<td>example</td>
				<td>example</td>
				<td>example</td>
				<td>example</td>
				<td><button class="btn-table">Supervisar</button></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

	<!--JS de bootstrap-->

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>
</html>