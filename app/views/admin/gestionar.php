<<<<<<< HEAD
  <?php require_once __DIR__ . "/../../controllers/checkSession.php"; ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gestion de usuarios</title>

    <!--Icon de la pagina-->
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

    <link
      rel="stylesheet" href="/assets/css/gestionar.css">
    <!--Google fonts-->
    <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!--CSS propio para colores y fonts-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  </head>

  <body>
    <div class="top-bar">
      <nav class="navbar_m-0_p-0">
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
    <div class="side-bar">
      <div class="sidebar container-fluid">
        <ul class="nav-flex">

          <li class="nav-item-my-1">
            <a href="/dashboardAdmin" class="nav-link text-none">
              <i class="bi bi-house-fill home-icon d-block"></i>
              <span>Inicio</span>
            </a>
          </li>

          <li class="nav-item-my-1">
            <a href="/generarInforme" class="nav-link text-none">
              <i class="bi bi-file-earmark-text-fill crear-notificacion"></i>
              <br>
              <span>Generar<br>Informe</span>
            </a>
          </li>
          <div>
            <li class="nav-item-my-1">
              <a href="/casosAdmin" class="nav-link text-none">
                <i class="bi bi-eye-fill ver-caso d-block"></i>
                <span>Casos</span>
              </a>
            </li>
          </div>
           <li class="nav-item my-1">
          <a href="/procesoOrganizacional" class="nav-link text-none">
            <i class="bi bi-person-fill-gear usuarios"></i>
            <span>procesos</span>
          </a>
        </li>
          <li class="nav-item-my-1">
            <a href="/usuarios" class="nav-link text-none">
              <i class="bi bi-person-fill-gear usuarios"></i>
              <br>
              <span>Usuarios</span>
            </a>
          </li>

          <li class="nav-item-my-1">
            <a href="/notificacionesAdmin" class="nav-link text-none">
              <i class="bi bi-bell-fill notificacion"></i>
              <br>
              <span>Notificación</span>
            </a>
          </li>

        </ul>
      </div>
    </div>
    <main class="main">
      <div class="formulario">
        <h2>Gestion de usuarios</h2>
        <input type="text" id="nombre" name="nombre" required placeholder="Nombre de usuario" class="formulario-gestion">
        <input type="text" id="apellido" name="apellido" required placeholder="Apellido de usuario" class="formulario-gestion">
        <input type="text" id="documento" name="documento" required placeholder="Documento de usuario" class="formulario-gestion">
        <select id="rol" name="rol">
          <option value="" disabled selected hidden>rol del usuario</option>
          <option value="admin">Administrador</option>
          <option value="usuario">Usuario</option>
        </select>
        <input type="text" id="correo" name="correo" required placeholder="Correo de usuario" class="formulario-gestion">
        <input type="text" id="fecha_registro" name="fecha_registro" required placeholder="Fecha de registro" class="formulario-gestion">
        <select name="estado" id="estado" placeholder="Estado">
          <option value="" disabled selected hidden>Estado del usuario</option>
          <option value="activo">Activo</option>
          <option value="inactivo">Inactivo</option>
        </select>
        <input type="text" id="contraseña" name="contraseña" required placeholder="Contraseña de usuario" class="formulario-gestion">
        <button class="btn-actualizar" id="btn-actualizar" name="btn-actualizar">actualizar</button>
      </div>
    </main>
    <script src="/assets/js/cache.js"></script>
  </body>

  </html>