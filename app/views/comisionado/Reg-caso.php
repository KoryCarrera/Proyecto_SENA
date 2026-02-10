<?php
require_once __DIR__ . "/../../controllers/checkSessionComi.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../models/insertData.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comisionado</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <!--css propio-->
  <link rel="stylesheet" href="/assets/css/com-reg-caso.css">

  <!--Google fonts-->
  <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

  <!--Bootstrap-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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
          <form action="/logout" method="POST">
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
          <a href="/dashboardComi" class="nav-link text-dark">
            <i class="bi bi-house-fill home-icon d-block"></i>
            <span>Inicio</span>
          </a>
        </li>

        <li class="nav-item my-3">
          <a href="/registrarCasos" class="nav-link text-dark">
            <i class="bi bi-file-earmark-person-fill reg-caso d-block"></i>
            <span>Registrar <br> Caso</span>
          </a>
        </li>

        <li class="nav-item my-3">
          <a href="/casos" class="nav-link text-dark">
            <i class="bi bi-eye-fill ver-caso d-block"></i>
            <span>Casos</span>
          </a>
        </li>

        <li class="nav-item my-3">
          <a href="/notificacionesComi" class="nav-link text-dark">
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

        <div id="registroForm">
          <div id="seccion1" class="form-section">

            <div class="mb-4">
              <label for="fecha_inicio" class="form-label fw-bold text-secondary ms-1">
                <i class="bi bi-calendar-event-fill"></i> Fecha de Inicio
              </label>
              <div class="input-group custom-input-group">
                <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" class="form-control custom-input" required>
              </div>
            </div>

            <div class="mb-4">
              <label for="fecha_cierre" class="form-label fw-bold text-secondary ms-1">
                <i class="bi bi-calendar-check-fill"></i> Fecha de Cierre
              </label>
              <div class="input-group custom-input-group">
                <input type="datetime-local" id="fecha_cierre" name="fecha_cierre" class="form-control custom-input">
              </div>
            </div>

            <div class="mb-4">
              <label for="proceso" class="form-label fw-bold text-secondary ms-1">
                <i class="bi bi-hash"></i> Proceso Organizacional
              </label>
              <div class="input-group custom-input-group">
                <span class="input-group-text custom-icon"><i class="bi bi-diagram-3-fill"></i></span>
                <select name="proceso" id="proceso" class="form-select custom-input">
                  <option selected disabled value="">Cargando procesos...</option>
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label for="estado" class="form-label fw-bold text-secondary ms-1">Estado del Caso</label>
              <div class="input-group custom-input-group">
                <span class="input-group-text custom-icon"><i class="bi bi-info-circle-fill"></i></span>
                <select name="estado" class="form-select custom-input" id="estado">
                  <option selected disabled value="">Cargando estados...</option>
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label for="tipoCaso" class="form-label fw-bold text-secondary ms-1">Tipo de Solicitud</label>
              <div class="input-group custom-input-group">
                <span class="input-group-text custom-icon"><i class="bi bi-list-task"></i></span>
                <select name="tipo" class="form-select custom-input" id="tipoCaso">
                  <option selected disabled value="">Cargando tipos...</option>
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label for="descripcion" class="form-label fw-bold text-secondary ms-1">Descripción Detallada</label>
              <div class="input-group custom-input-group">
                <textarea name="descripcion" id="descripcion" class="form-control custom-input" rows="3" placeholder="Describa los hechos del caso..."></textarea>
              </div>
            </div>

            <div class="mt-4">
              <button type="button" id="btnRegistrarcaso" class="btn w-100 fw-bold custom-input" style="background-color: #39A900; color: white;">
                ENVIAR REGISTRO
              </button>
            </div>

          </div>

        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/registrarCaso.js"></script>
    <script src="assets/js/cache.js"></script>
</body>

</html>