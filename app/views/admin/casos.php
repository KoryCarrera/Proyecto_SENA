<?php require_once __DIR__ . "/../../controllers/checkSession.php"; ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administrador</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <!--Este es el enlace entre el proyecto y bootstrap-->

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

  <!--CSS propio para colores y fonts-->

  <link rel="stylesheet" href="/assets/css/casos-admin.css">

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
        <img class="ms-3" src="/assets/img/logo_sena.png" alt="SENA" width="103" height="100">
        <div class="d-flex align-items-center">
          <div class="text-end me-3">
            <?php if (isset($_SESSION['user']['username'])): ?>
              <h2 class='mb-0 d-none d-md-block'>
                <?php echo $_SESSION['user']['username']; ?>
              </h2>
            <?php endif; ?>
            <h4 class="mb-0 d-none d-md-block">Administrador</h4>
          </div>
          <a href="#">
            <img src="/assets/img/icon account.png" alt="User" width="76" height="76">
          </a>
          <form action="/logout" method="POST">
            <button type="submit" name="logout" value="logout">Cerrar Sesion</button>
            <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
          </form>
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
            <input class="form-control me-2" type="search" placeholder="Palabras Claves" aria-label="Search" />
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
          <a href="/dashboardAdmin" class="nav-link text-none">
            <i class="bi bi-house-fill home-icon d-block"></i>
            <span>Inicio</span>
          </a>
        </li>

        <li class="nav-item my-1">
          <a href="/generarInforme" class="nav-link text-none">
            <i class="bi bi-file-earmark-text-fill crear-notificacion"></i>
            <br>
            <span>Generar<br>Informe</span>
          </a>
        </li>

        <li class="nav-item my-1 active">
          <a href="/casosAdmin" class="nav-link text-none">
            <i class="bi bi-eye-fill ver-caso d-block"></i>
            <span>Casos</span>
          </a>
        </li>
         <li class="nav-item my-1">
          <a href="/procesoOrganizacional" class="nav-link text-none">
            <i class="bi bi-person-fill-gear usuarios"></i>
            <span>procesos</span>
          </a>
        </li>

        <li class="nav-item my-1">
          <a href="/usuarios" class="nav-link text-none">
            <i class="bi bi-person-fill-gear usuarios"></i>
            <span>Usuarios</span>
          </a>
        </li>

        <li class="nav-item my-1">
          <a href="/notificacionesAdmin" class="nav-link text-none">
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
        <tbody id="tablaCasos">

        </tbody>
      </table>
    </div>
  </div>
  <!--Modal de supervisar-->
  <div class="modal fade" id="modalCaso" tabindex="-1" aria-labelledby="modalCasoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCasoLabel">Detalles del Caso</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="modalCasoBody">
          <!-- El contenido se carga dinámicamente con JavaScript -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <!--<button type="button" class="btn btn-primary" onclick="editarCaso()">
            <i class="bi bi-pencil"></i> Editar Caso
          </button>-->
        </div>
      </div>
    </div>
  </div>
  <!--JS de bootstrap-->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>

  <script src="/assets/js/casosAdmin.js"></script>
  <script src="/assets/js/cache.js"></script>
</body>

</html>