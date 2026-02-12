<?php require_once __DIR__ . "/../../controllers/checkSessionAdmin.php"; ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>proceso organizacional</title>

    <!--Icon de la pagina-->
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

    <!--Este es el enlace entre el proyecto y bootstrap-->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!--CSS propio para colores y fonts-->

    <link rel="stylesheet" href="/assets/css/procesoOrganizacional.css">

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
    <!--NavBar admin-->
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
                        <input type="hidden" name="csrf_token" id="csrf_token"
                            value="<?php echo htmlspecialchars($token); ?>">
                    </form>
                </div>
            </div>
        </nav>
    </div>
    <!--SideBar admin-->

    <div class="side-bar">
        <div class="sidebar container-fluid">
            <ul class="nav flex-column text-center">

                <li class="nav-item my-1">
                    <a href="/dashboardAdmin" class="nav-link text-none">
                        <i class="bi bi-house-fill home-icon d-block"></i>
                        <span>Inicio</span>
                    </a>
                </li>

                <li class="nav-item my-1 active">
                    <a href="/generarInforme" class="nav-link text-none">
                        <i class="bi bi-file-earmark-text-fill crear-notificacion"></i>
                        <br>
                        <span>Generar<br>Informe</span>
                    </a>
                </li>

                <li class="nav-item my-1">
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
    <main class="main">
        <button type="button" id="abrirModal" class="btn-modal">crear proceso</button>
       <section class="tabla-procesos">
    <table>
        <thead>
            <tr class="cabecera-tabla">
                <th scope="col">Nombre<br>Proceso</th>
                <th scope="col">descripcion</th>
                <th scope="col">Fecha de<br>Creacion</th>
                <th scope="col">documento</th>
                <th scope="col">Creador del<br>Proceso</th>
                <th scope="col">Gestionar</th>
            </tr>
        </thead>
        <tbody class="cont-tabla" id="tablaProcesos">
        </tbody>
    </table>
</section>
    </main>
    <div id="modal" class="modal">
        <div class="contenido-modal">
            <h2 class="titulo-modal">crear proceso</h2>
            <div id="formProceso" class="formulario">
                <input type="text" id="nombre-proceso" name="nombre-proceso" placeholder="Nombre de proceso"
                    class="contenido">
                <textarea name="descripcion" id="descripcion" cols="30" rows="4" placeholder="Descripcion"
                    class="contenido"></textarea>
                <div class="botones">
                    <button type="button" id="btnRegistrarProceso" class="boton">crear proceso</button>
                    <button type="button" id="cerrar-modal" class="boton">cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/procesos-modal.js"></script>
    <script src="assets/js/registrarProceso.js"></script>
</body>