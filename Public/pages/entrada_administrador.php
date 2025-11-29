<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$token = $_SESSION['csrf_token'];

?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../assets/css/inicio-entrada-cartas.css" />
  <title>pagina de inicio del sistema de gestion</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="../assets/css/inicio-entrada-cartas.css" />
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
    crossorigin="anonymous" />
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
</head>

<body>
  <!--barra de navegacion extraida de bootstrap-->
  <section class="menu">
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">SENA</a>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNavDropdown"
          aria-controls="navbarNavDropdown"
          aria-expanded="false"
          aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="../index.php">inicio</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="nosotros.php">nosotros</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">cuentas</a>
            </li>
            <li class="nav-item dropdown">
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </section>

  <!--contenedores con los los tipos de usuarios,estaprimera es la del comisionado-->
  <main class="contenedor">
    <section class="tipos_usuarios">
      <h2>selecciona tu tipo de cuenta</h2>
      <div class="cartas_usuarios">
        <div class="formulario_entrada_admin">
          <i class="fa-solid fa-user-tie"></i> <br>
            <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="number" placeholder="ingrese su cedula" name="documento" id="documento"><br>
            <input type="password" placeholder="ingrese su contraseña" name="contraseña" id="password"><br>
            <button id="ingresar">ingrese</button>
        </div>
        <div class="carta_administrador_login">
          <i class="fa-solid fa-user-gear"></i>
          <h3>administrador</h3>
          <p>bienvenido de nuevamente administrador</p>
        </div>
      </div>
      <a href="../index.php" class="boton-volver">Volver al inicio</a>
    </section>
  </main>
  <!--pie de pagina, toca segir buscando que tipos de lincks y contenido pondremos-->
  <footer>
    <div class="links_paginas">
      <ul class="links">
        <li><a href="#">servicios</a></li>
        <li><a href="#">mapas de sitio</a></li>
        <li><a href="#">terminos"</a></li>
      </ul>
    </div>
    <!--los separe en tres div para presentarlo como columnas,cosa que no aparecia en el mockup-->
    <div class="a_cerca_de">
      <ul class="links">
        <li><a href="#">acerca de nosotros</a></li>
        <li><a href="#">contactanos</a></li>
      </ul>
    </div>
    <div class="servicio_al_cliente">
      <ul class="links">
        <li><a href="#">servicio al cliente</a></li>
        <li><a href="#">ayuda</a></li>
      </ul>
    </div>
  </footer>
  <script src="../assets/js/loginAdmin.js"></script>
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>

</body>

</html>