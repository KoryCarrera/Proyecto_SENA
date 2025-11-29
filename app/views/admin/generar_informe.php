<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador</title>

    <!--Este es el enlace entre el proyecto y bootstrap-->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    <!--CSS propio para colores y fonts-->

    <link rel="stylesheet" href="../../../Public/assets/css/generar_informe-admin.css">
    
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
                <img class="ms-3"src="../../../Public/assets/img/logo_sena.png" alt="SENA" width="103" height="100">
                <div class="d-flex align-items-center">
                    <div class="text-end me-3">
                        <h2 class="mb-0 d-none d-md-block">User Name</h2>
                        <h4 class="mb-0 d-none d-md-block">Administrador</h4>
                    </div>
                    <a href="#">
                        <img src="../../../Public/assets/img/icon account.png" alt="User" width="76" height="76">
                    </a>
                </div>
            </div>
        </nav>
    </div>
	<!--SideBar admin-->
	
	<div class="side-bar">
  <div class="sidebar container-fluid">
    <ul class="nav flex-column text-center">
    
				<li class="nav-item my-1">
					<a href="home.php" class="nav-link text-none">
						<i class="bi bi-house-fill home-icon d-block"></i>
						<span>Inicio</span>
					</a>
				</li>

				<li class="nav-item my-1 active">
					<a href="generar_informe.php" class="nav-link text-none">
						<i class="bi bi-file-earmark-text-fill crear-notificacion"></i>
						<br>
						<span>Generar<br>Informe</span>
						</a>
				</li>

				<li class="nav-item my-1">
					<a href="casos.php" class="nav-link text-none">
						<i class="bi bi-eye-fill ver-caso d-block"></i>
						<span>Casos</span>
					</a>
				</li>

				<li class="nav-item my-1">
					<a href="crear-usuario.php" class="nav-link text-none">
						<i class="bi bi-person-fill-gear usuarios"></i>
						<span>Usuarios</span>
					</a>
				</li>

				<li class="nav-item my-1">
					<a href="notificaciones.php" class="nav-link text-none">
						<i class="bi bi-bell-fill notificacion"></i>
						<span>Notificación</span>
					</a>
				</li>

    </ul>
  </div>
</div>

<!--Contenido de la pagina-->
<div class="main-content">

<div class="container mt-5">
    <h1 class="text-center mb-4">Generación de Informe</h1>
    <div class="custom-form-box mx-auto">
        <h2 class="text-center mb-4">Datos de Informe</h2>
        <form id="registroForm">
            <div id="seccion1" class="form-section">
                <div class="input-group mb-4 custom-input-group">
                    <span class="input-group-text custom-icon"><i class="bi bi-person-fill"></i></span>

                <select class="form-select custom-input" id="tipo-usuario">
                    <option selected selected>Selecione el tipo de archivo</option>
                    <option value="PDF">PDF</option>
                    <option value="excel">EXCEL</option>
                </select>
                </div>

                <div class="input-group mb-4 custom-input-group">
                    <input type="text" class="form-control custom-input" placeholder="Titulo">
                </div>

                <div class="input-group mb-4 custom-input-group">

                    <textarea type="text" class="form-control custom-input" placeholder="Contenido"></textarea>
				</div>
                
				<button type="button" class="btn btn-block w-100 btn-IMPORTAR" onclick="mostrarSeccion('seccion2')">IMPORTAR ARCHIVO</button>
                <button type="button" class="btn btn-block w-100 btn-siguiente" onclick="mostrarSeccion('seccion2')">SIGUIENTE</button>
            </div>
        </form>
    </div>
</div>

<!--JS de Bootstrap-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>
</html>
