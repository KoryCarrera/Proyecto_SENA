<?php
require_once __DIR__ . "/../controllers/checkSession.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/insertData.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comisionado</title>
  <!--css propio-->
  <link rel="stylesheet" href="/assets/css/com-reg-caso.css">

  <!--Google fonts-->
  <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

  <!--Bootstrap-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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
            <h4 class="mb-0 d-none d-md-block">Comisionado</h4>
          </div>
          <a href="#">
            <img src="/assets/img/icon account.png" alt="User" width="76" height="76">
          </a>
          <form action="/logout.php" method="POST">
            <button type="submit" name="logout" value="logout">Cerrar Sesion</button>
            <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
          </form>
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
  <!--contenido de la pagina de registrar caso-->
  <div class="main-content">
    <div class="container mt-5">
      <h1 class="text-center mb-4">Registro de Caso</h1>

      <div class="custom-form-box mx-auto">
        <h2 class="text-center mb-4">Información del Caso</h2>

        <form id="registroForm" method="POST">
          <div id="seccion1" class="form-section">

            <div class="input-group mb-4 custom-input-group">
              <span class="input-group-text custom-icon"><i class="bi bi-person-fill"></i></span>
              <select class="form-select custom-input" id="tipo-usuario">
                <option selected disabled>Selecione el Tipo de Usuario</option>
                <option value="aprendiz">Aprendiz</option>
                <option value="empleado">Empleado</option>
                <option value="anonimo">Anonimo</option>
              </select>
            </div>

            <div class="input-group mb-4 custom-input-group">
              <input name="documento" type="text" class="form-control custom-input" placeholder="Documento">
            </div>

            <div class="input-group mb-4 custom-input-group">
              <input name="proceso" type="number" class="form-control custom-input" placeholder="Id Proceso">
            </div>

            <div class="input-group mb-4 custom-input-group">
              <span class="input-group-text custom-icon"><i class="bi bi-person-fill"></i></span>
              <select name="estado" class="form-select custom-input" id="tipo-usuario">
                <option selected disabled>Seleccione el estado</option>
                <option value="1">Atendido</option>
                <option value="2">Por atender</option>
                <option value="3">No atendido</option>
              </select>
            </div>

            <div class="input-group mb-4 custom-input-group">
              <span class="input-group-text custom-icon"><i class="bi bi-person-fill"></i></span>
              <select name="tipo" class="form-select custom-input" id="tipo-usuario">
                <option selected disabled>Seleccione el Tipo de caso</option>
                <option value="1">peticion</option>
                <option value="2">queja</option>
                <option value="3">reclamo</option>
                <option value="4">sugerencia</option>
                <option value="5">denuncia</option>
              </select>
            </div>

            <div class="input-group mb-4 custom-input-group">
              <input name="descripcion" type="text" class="form-control custom-input" placeholder="Descripcion">
            </div>
            <div class="input-group mb-4 custom-input-group">
              <input type="submit" class="form-control custom-input" placeholder="ENVIAR">
            </div>
          </div>
        </form>
        <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
          $documento = $_POST["documento"];
          $proceso = $_POST["proceso"];
          $estado = $_POST["estado"];
          $tipo = $_POST["tipo"];
          $descripcion = $_POST["descripcion"];
          if ($documento && $proceso && $estado && $tipo && $descripcion) {
            $registrar = registrarCasos($pdo, $documento, $proceso, $estado, $tipo, $descripcion);
            if ($registrar) {
              echo "caso registrado con exito";
            } else {
              echo "error al registar caso";
            }
          } else {
            echo "Ingrese valores validos";
          }
        } else {
          echo "Rellene todos los campos";
        }
        ?>
      </div>
    </div>
    <script src="/assets/js/cache.js"></script>
</body>

</html>