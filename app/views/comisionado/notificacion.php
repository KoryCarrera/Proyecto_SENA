<?php require_once __DIR__ . "/../../controllers/checkSession.php"; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="/assets/css/notificacion-comisionado.css">
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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
                        <h4 class="mb-0 d-none d-md-block">comisionado</h4>
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

    <!--contenedor barra lateral-->
    <div class="side-bar">
        <div class="container-fluid">
            <ul class="nav flex-column text-center">

                <li class="nav-item my-3">
                    <a href="/dashboardAdmin" class="nav-link text-dark">
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


    <main>
        <div class="notificaciones">
            <ul class="noti">
                <li class="notis">
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim dolore dolores autem cupiditate
                        eaque illum consequuntur fuga tempora aliquid, officiis sint provident? Illum minus porro quod
                        doloribus voluptatum expedita molestias!</p>
                </li>
                <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt
                    ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non
                    doloremque pariatur nihil vel aspernatur.</li>
                <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt
                    ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non
                    doloremque pariatur nihil vel aspernatur.</li>
                <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt
                    ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non
                    doloremque pariatur nihil vel aspernatur.</li>
                <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt
                    ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non
                    doloremque pariatur nihil vel aspernatur.</li>
                <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt
                    ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non
                    doloremque pariatur nihil vel aspernatur.</li>
                <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt
                    ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non
                    doloremque pariatur nihil vel aspernatur.</li>
                <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt
                    ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non
                    doloremque pariatur nihil vel aspernatur.</li>
                <li class="notis">Lorem ipsum dolor sit amet consectetur adipisicing elit. Aperiam doloribus deserunt
                    ducimus quod! Laboriosam ex accusamus harum repellendus. Esse libero nobis sed aliquid dolore non
                    doloremque pariatur nihil vel aspernatur.</li>
            </ul>
        </div>
    </main>
    <script src="/assets/js/cache.js"></script>
</body>

</html>