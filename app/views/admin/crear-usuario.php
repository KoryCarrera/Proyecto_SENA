<?php
require_once __DIR__ . "/../../controllers/checkSessionAdmin.php";
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>gestionar cuentas de usuario</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <link
    rel="stylesheet" href="/assets/css/crear-usuario.css">
  <!--Google fonts-->
  <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

  <!--Bootstrap-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
            <button type="submit" name="logout" value="logout">Cerrar Sesión</button>
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
            <span>Procesos</span>
          </a>
        </li>
        <li class="nav-item-my-1 active">
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
    <section class="formulario-crear-usuario">

      <form action="" method="POST">
        <h2>Crear Usuario</h2>

        <select id="rol" name="rol" required class="formulario">
          <option value="" disabled selected>Escoge un rol</option>
          <option value="1">Administrador</option>
          <option value="2">Comisionado</option>
        </select>

        <input type="text" id="nombre" name="nombre" required placeholder="Nombre" class="formulario">
        <input type="text" id="apellido" name="apellido" required placeholder="Apellido" class="formulario">
        <input type="text" id="documento" name="documento" required placeholder="Documento" class="formulario">
        <input type="text" id="email" name="email" required placeholder="Email" class="formulario">
        <input type="password" id="contrasena" name="contrasena" required placeholder="Contraseña" class="formulario">

        <button type="button" class="btn-usuario" id="btn-usuario">Siguiente</button>
      </form>
    </section>
    <section class="tabla-usuarios">
      <table>
        <thead>
          <tr class="head-tabla">
            <th scope="col">Documento</th>
            <th scope="col">Nombre</th>
            <th scope="col">Apellido</th>
            <th scope="col">Correo</th>
            <th scope="col">Estado</th>
            <th scope="col">Rol</th>
            <th scope="col">Gestionar</th>
          </tr>
        </thead>
        <tbody class="cont-tabla" id="tablaUsuarios">
        </tbody>
      </table>
    </section>

  </main>
  <!--Modal de supervisar-->
  <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalUsuarioLabel">Detalles del Caso</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="modalUsuarioBody">
          <!-- El contenido se carga dinámicamente con JavaScript -->
        </div>
        <div class="modal-footer" id="modalFooter">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" onclick="habilitarEdicion()">
            <i class="bi bi-pencil"></i> Editar Usuario
          </button>
          <a href="/editarUsuario">a</a>
          <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
          <script src="/assets/js/jquery-3.7.1.min.js"></script>
          <script src="/assets/js/usuariosAdmin.js"></script>
          <script src="/assets/js/cache.js"></script>

</body>

</html>