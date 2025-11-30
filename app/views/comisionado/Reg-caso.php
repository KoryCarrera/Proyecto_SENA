<?php require_once "../../controllers/checkSession.php";?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comisionado</title>
    <!--css propio-->
    <link rel="stylesheet" href="../../../Public\assets\css\com-reg-caso.css">

    <!--Google fonts-->
    <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
  <!--Barra de navegación superior-->
<div class="top-bar">
        <nav class="navbar m-0 p-0 bg-body-tertiary">
            <div class="container-fluid d-flex align-items-center justify-content-between">
                <img class="ms-3" src="../../../Public/assets/img/logo_sena.png" alt="SENA" width="103" height="100">
                <div class="d-flex align-items-center">
                    <div class="text-end me-3">
                        <h2 class="mb-0 d-none d-md-block">User Name</h2>
                        <h4 class="mb-0 d-none d-md-block">Comisionado</h4>
                    </div>
                    <a href="#">
                        <img src="../../../Public/assets/img/icon account.png" alt="User" width="76" height="76">
                    </a>
                </div>
            </div>
        </nav>
</div>
<!--contenedor barra lateral-->
<div class="side-bar">
  <div class="container-fluid">
    <ul class="nav flex-column text-center">

      <li class="nav-item my-3">
        <a href="../../../modules/comisionado/views/home.html" class="nav-link text-dark">
          <i class="bi bi-house-fill home-icon d-block"></i>
          <span>Inicio</span>
        </a>
      </li>

      <li class="nav-item my-3">
        <a href="../../../modules/comisionado/views/Reg-caso.html" class="nav-link text-dark">
          <i class="bi bi-file-earmark-person-fill reg-caso d-block"></i>
          <span>Registrar <br> Caso</span>
        </a>
      </li>

      <li class="nav-item my-3">
        <a href="#" class="nav-link text-dark">
          <i class="bi bi-eye-fill ver-caso d-block"></i>
          <span>Casos</span>
        </a>
      </li>

      <li class="nav-item my-3">
        <a href="#" class="nav-link text-dark">
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

        <form id="registroForm">
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
                    <input type="text" class="form-control custom-input" placeholder="Nombre:">
                </div>

                <div class="input-group mb-4 custom-input-group">
                    <input type="text" class="form-control custom-input" placeholder="Apellido:">
                </div>

                <div class="input-group mb-4 custom-input-group">
                    <input type="text" class="form-control custom-input" placeholder="Documento:">
                </div>

                <button type="button" class="btn btn-block w-100 btn-siguiente" onclick="mostrarSeccion('seccion2')">
                    SIGUIENTE
                </button>
            </div>

            <div id="seccion2" class="form-section d-none">
                <p class="text-center py-4">Aquí iría la siguiente parte del formulario.</p>
                
                <button type="button" class="btn btn-secondary btn-block w-100 mb-3" onclick="mostrarSeccion('seccion1')">
                    ANTERIOR
                </button>
                <button type="submit" class="btn btn-success btn-block w-100">
                    FINALIZAR REGISTRO
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>