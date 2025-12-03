<?php require_once "../../controllers/checkSession.php"; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comisionado</title>
  <!--css propio-->
  <link rel="stylesheet" href="../../../Public/assets/css/home-comisionado.css">

  <!--Google fonts-->
  <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

  <!--Bootstrap-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <script src="../../../public/assets/js/jquery-3.7.1.min.js"></script>
</head>

<body>
  <!--Barra de navegación superior-->
  <div class="top-bar">
    <nav class="navbar m-0 p-0 bg-body-tertiary">
      <div class="container-fluid d-flex align-items-center justify-content-between">
        <img class="ms-3" src="../../../Public/assets/img/logo_sena.png" alt="SENA" width="103" height="100">
        <div class="d-flex align-items-center">
          <div class="text-end me-3">
            <h2 class="mb-0 d-none d-md-block">User Name</h2>
            <h4 class="mb-0 d-none d-md-block">Comisionado</h4>
          </div>
          <a href="#">
            <img src="../../../Public/assets/img/icon account.png" alt="User" width="76" height="76">
          </a>
        </div>
      </div>
    </nav>
  </div>
  <!--contenedor barra lateral-->
  <div class="side-bar">
    <div class="container-fluid">
      <ul class="nav flex-column text-center">

        <li class="nav-item my-3">
          <a href="home.php" class="nav-link text-dark">
            <i class="bi bi-house-fill home-icon d-block"></i>
            <span>Inicio</span>
          </a>
        </li>

        <li class="nav-item my-3">
          <a href="Reg-caso.php" class="nav-link text-dark">
            <i class="bi bi-file-earmark-person-fill reg-caso d-block"></i>
            <span>Registrar <br> Caso</span>
          </a>
        </li>

        <li class="nav-item my-3">
          <a href="caso.php" class="nav-link text-dark">
            <i class="bi bi-eye-fill ver-caso d-block"></i>
            <span>Casos</span>
          </a>
        </li>

        <li class="nav-item my-3">
          <a href="notificacion.php" class="nav-link text-dark">
            <i class="bi bi-envelope-fill noti-icon d-block"></i>
            <span>Notificaciones</span>
          </a>
        </li>

        <li class="nav-item my-3">
          <a href="#" class="nav-link text-dark">
            <i class="bi bi-envelope-plus-fill crear-icon d-block"></i>
            <span>Crear <br> Notificación</span>
          </a>
        </li>

      </ul>
    </div>
  </div>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="../../../public/assets/js/dashboard_comi.js"></script>
</body>

</html>