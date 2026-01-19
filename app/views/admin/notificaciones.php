<?php require_once __DIR__ . "/../../controllers/checkSession.php"; ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="/assets/css/notificaciones.css">
  <title>Notificaciones</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <link />
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

        <li class="nav-item my-1 active">
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

        <li class="nav-item-my-1">
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

        <li class="nav-item-my-1">
          <a href="/usuarios" class="nav-link text-none">
            <i class="bi bi-person-fill-gear usuarios"></i><br>
            <span>Usuarios</span>
          </a>
        </li>

        <li class="nav-item-my-1 active">
          <a href="/notificacionesAdmin" class="nav-link text-none">
            <i class="bi bi-bell-fill notificacion"></i><br>
            <span>Notificación</span>
          </a>
        </li>

      </ul>
    </div>
  </div>
  <main>
    <div class="notificaciones">
      <ul class="noti">
        <li class="notis">
          <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim dolore dolores autem cupiditate eaque illum consequuntur fuga tempora aliquid, officiis sint provident? Illum minus porro quod doloribus voluptatum expedita molestias!</p>
        </li>
        <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non doloremque pariatur nihil vel aspernatur.</li>
        <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non doloremque pariatur nihil vel aspernatur.</li>
        <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non doloremque pariatur nihil vel aspernatur.</li>
        <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non doloremque pariatur nihil vel aspernatur.</li>
        <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non doloremque pariatur nihil vel aspernatur.</li>
        <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non doloremque pariatur nihil vel aspernatur.</li>
        <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non doloremque pariatur nihil vel aspernatur.</li>
        <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non doloremque pariatur nihil vel aspernatur.</li>
      </ul>
    </div>
  </main>
  <script src="/assets/js/cache.js"></script>
</body>

</html>