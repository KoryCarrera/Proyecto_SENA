<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gestion de usuarios</title>
         <link
      rel="stylesheet" href="../../../public/assets/css/gestionar.css">
          <!--Google fonts-->
    <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!--CSS propio para colores y fonts-->
       <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
     <div class="top-bar">
        <nav class="navbar_m-0_p-0">
            <div class="container-fluid d-flex align-items-center justify-content-between">
                <img class="ms-3"src="../../../public/assets/img/logo_sena.png" alt="SENA" width="103" height="100">
                <div class="d-flex align-items-center">
                    <div class="text-end me-3">
                        <h2 class="mb-0 d-none d-md-block">User Name</h2>
                        <h4 class="mb-0 d-none d-md-block">Administrador</h4>
                    </div>
                    <a href="#">
                        <img src="../../../public/assets/img/icon account.png" alt="User" width="76" height="76">
                    </a>
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
      <li class="nav-item-my-1">
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

</body>
</html>