<?php
require_once __DIR__ . "../../controllers/checkSession.php";
require_once __DIR__ . "../../config/conexion.php";
require_once __DIR__ . "../../models/insertData.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>gestionar cuentas de usuario</title>
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
          <form action="../../controllers/logout.php" method="POST">
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
          <a href="home.php" class="nav-link text-none">
            <i class="bi bi-house-fill home-icon d-block"></i>
            <span>Inicio</span>
          </a>
        </li>

        <li class="nav-item-my-1">
          <a href="generar_informe.php" class="nav-link text-none">
            <i class="bi bi-file-earmark-text-fill crear-notificacion"></i>
            <br>
            <span>Generar<br>Informe</span>
          </a>
        </li>
        <div>
          <li class="nav-item-my-1">
            <a href="casos.php" class="nav-link text-none">
              <i class="bi bi-eye-fill ver-caso d-block"></i>
              <span>Casos</span>
            </a>
          </li>
        </div>
        <li class="nav-item-my-1 active">
          <a href="crear-usuario.php" class="nav-link text-none">
            <i class="bi bi-person-fill-gear usuarios"></i>
            <br>
            <span>Usuarios</span>
          </a>
        </li>

        <li class="nav-item-my-1">
          <a href="notificaciones.php" class="nav-link text-none">
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
        <input type="text" id="rol" name="rol" required placeholder="Rol" class="formulario">
        <input type="text" id="name" name="name" required placeholder="Nombre" class="formulario">
        <input type="text" id="apellido" name="apellido" required placeholder="Apellido" class="formulario">
        <input type="text" id="documento" name="documento" required placeholder="documento" class="formulario">
        <input type="text" id="email" name="email" required placeholder="email" class="formulario">
        <input type="password" id="password" name="password" required placeholder="Contraseña" class="formulario">
        <button type="submit" class="btn-usuario" id="btn-usuario">siguiente</button>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
          $rol = $_POST["rol"];
          $nombre = $_POST["name"];
          $apellido = $_POST["apellido"];
          $documento = $_POST["documento"];
          $email = $_POST["email"];
          $contraseña = $_POST["password"];
          if ($rol && $nombre && $apellido && $email && $documento && $contraseña) {
            $registrar = registrarUsuario($pdo, $documento, $nombre, $apellido, $email, $rol, $contraseña);
            if ($registrar) {
              echo "registrado con exito";
            } else {
              echo "error al registrar usuario";
            }
          } else {
            echo "ingrese valores validos";
          }
        }
        ?>
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
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" onclick="editarUsuario()">
            <i class="bi bi-pencil"></i> Editar Usuario
          </button>
          <a href="../../controllers/editar_usuario.php">a</a>
          <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
          <script src="/assets/js/usuariosAdmin.js"></script>
          <script src="/assets/js/cache.js"></script>

</body>

</html>