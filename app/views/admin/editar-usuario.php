<?php
require_once __DIR__ . "/../../models/updateData.php";
require_once __DIR__ . "/../../controllers/checkSessionAdmin.php";
?>
<!-- se incluyen los archivos necesarios para procesar los datos y verificar que el usuario este logueado -->

<!DOCTYPE html>
<html lang="es">
<!-- se inicia el documento y le decimos el lenguaje y que tomara el meta tag para caracteres especiales -->

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Actualizar Usuario | Administrador</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <!-- Bootstrap CSS (Required for consistency with other pages) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!--CSS propio-->
  <link rel="stylesheet" href="/assets/css/crear-usuario.css">

  <script src="/assets/js/jquery-3.7.1.min.js"></script>

</head>

<!-- se inicia el body del documento -->
<body class="antialiased selection:bg-indigo-500 selection:text-white">

  <!-- background de la vista -->
  <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
    <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/20 w-[500px] h-[500px]"></div>
    <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/20 w-[500px] h-[500px] animation-delay-2000"></div>
    <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay"></div>
  </div>

  <!-- contenedor principal -->
  <div class="flex h-screen overflow-hidden relative z-10">

    <!-- contenedor de la Sidebar -->
    <aside class="glass-sidebar w-20 hover:w-64 transition-all duration-300 ease-in-out flex flex-col group fixed h-full z-50">

      <!-- Logo del sena en la sidebar -->
      <div class="h-20 flex items-center justify-center border-b border-white/5">
        <img src="/assets/img/logo_sena.png" alt="SENA" class="w-10 h-10 object-contain group-hover:block">
      </div>

      <!-- Navegacion de la sidebar -->
      <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">

        <!-- se define el link de inicio -->
        <a href="/dashboardAdmin" class="nav-link">
          <i class="bi bi-house-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Inicio</span>
        </a>

        <!-- se define el link de generar informe -->
        <a href="/generarInforme" class="nav-link">
          <i class="bi bi-file-earmark-text-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Generar Informe</span>
        </a>

        <!-- se define el link de casos -->
        <a href="/casosAdmin" class="nav-link">
          <i class="bi bi-eye-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Casos</span>
        </a>

        <!-- se define el link de procesos -->
        <a href="/procesoOrganizacional" class="nav-link">
          <i class="bi bi-diagram-3-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Procesos</span>
        </a>

        <!-- se define el link de usuarios -->
        <a href="/usuarios" class="nav-link active">
          <i class="bi bi-person-fill-gear"></i>
          <span class="text-[10px] mt-1 font-medium">Usuarios</span>
        </a>

        <!-- se define el link de notificaciones -->
        <a href="/notificacionesAdmin" class="nav-link">
          <i class="bi bi-bell-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Notificación</span>
        </a>

        <!-- se define el link de perfil -->
        <a href="/perfilAdmin" class="nav-link">
          <i class="bi bi-person-circle"></i>
          <span class="text-[10px] mt-1 font-medium">Mi Perfil</span>
        </a>

      </nav>
    </aside>

    <!-- Contenedor principal -->
    <div class="flex-1 flex flex-col ml-20 h-full">

      <!-- Barra superior -->
      <header class="h-20 glass-nav flex items-center justify-between px-6 sticky top-0 z-40">

        <!-- titulo de la barra superior -->
        <h2 class="text-xl font-semibold text-white tracking-tight">Actualizar Usuario</h2>

        <!-- se define el contenedor de los iconos -->
        <div class="flex items-center gap-6">
          <!-- se define el contenedor del nombre del usuario y el rol -->
          <div class="text-right hidden md:block">
            <?php if (isset($_SESSION['user']['username'])): ?>
              <p class="text-sm font-medium text-white">
                <?php echo $_SESSION['user']['username']; ?>
              </p>
            <?php endif; ?>
            <p class="text-xs text-slate-400">Administrador</p>
          </div>

          <!-- se define el contenedor de los iconos -->
          <div class="flex items-center gap-4">
            <!-- icono de perfil que es un enlace a la vista de perfil-->
            <a href="/perfilAdmin" class="p-2 rounded-full hover:bg-white/5 transition-colors">
              <img src="/assets/img/icon account.png" alt="User" class="w-8 h-8 rounded-full border border-white/10">
            </a>

            <!-- se define el token de seguridad -->
            <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
            <!-- se define el boton de cerrar sesion -->
            <button type="submit" name="logout" id="logoutButton" value="logout"
              class="text-xs font-medium text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 px-3 py-1.5 rounded-lg transition-colors border border-red-500/20">
              Cerrar Sesión
            </button>

          </div>
        </div>
      </header>

      <!-- Contenido interno de la pagina, donde se muestra el formulario para editar usuario -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">
        <div class="max-w-3xl mx-auto">

          <!-- formulario para editar un usuario -->
          <section class="formulario-crear-usuario">
            <div class="glass-card p-8 md:p-10 relative">

              <!-- boton para inhabilitar el usuario -->
              <div class="absolute top-4 right-4">
                <a href="inhabilitar-usuario.php" class="text-xs font-medium text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 px-3 py-1.5 rounded-lg transition-colors border border-red-500/20 flex items-center gap-1">
                  <i class="bi bi-person-x-fill"></i> Inhabilitar Usuario
                </a>
              </div>

              <form action="" method="POST" class="grid grid-cols-1 gap-6 pt-6">
                <h2 class="text-2xl font-bold text-white text-center mb-2">Editor de Usuario</h2>

                <!-- input para el documento del usuario -->
                <div>
                  <label for="documento" class="block text-sm font-medium text-slate-300 mb-2">Documento</label>
                  <input type="text" name="documento" id="documento" class="glass-input w-full p-3 rounded-lg text-white placeholder-slate-400 form-control-plaintext">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- input para el nombre del usuario -->
                  <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-300 mb-2">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="glass-input w-full p-3 rounded-lg text-white placeholder-slate-400">
                  </div>
                  <!-- input para el apellido del usuario -->
                  <div>
                    <label for="apellido" class="block text-sm font-medium text-slate-300 mb-2">Apellido</label>
                    <input type="text" name="apellido" id="apellido" class="glass-input w-full p-3 rounded-lg text-white placeholder-slate-400">
                  </div>
                </div>

                <!-- input para el correo electronico del usuario -->
                <div>
                  <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Correo Electrónico</label>
                  <input type="text" name="email" id="email" class="glass-input w-full p-3 rounded-lg text-white placeholder-slate-400">
                </div>

                <!-- select de tipo de rol del usuario -->
                <div>
                  <label for="rol" class="block text-sm font-medium text-slate-300 mb-2">Rol</label>
                  <select name="rol" id="rol" class="glass-input w-full p-3 rounded-lg text-white appearance-none">
                    <option selected disabled>Seleccione un Rol</option>
                    <option value="1" class="bg-slate-800">Administrador</option>
                    <option value="2" class="bg-slate-800">Comisionado</option>
                  </select>
                </div>

                <!-- boton para actualizar el usuario -->
                <div class="flex justify-center mt-4">
                  <button type="submit" class="btn-usuario w-full md:w-auto min-w-[200px]" id="btn-usuario">
                    <i class="bi bi-save mr-2"></i> Actualizar
                  </button>
                </div>
              </form>
            </div>
          </section>

        </div>
      </main>
    </div>
  </div>

  <!-- script requeridos para la pagina  -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Bootstrap Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
      <!-- script de logout(script propio) -->
      <script src="/assets/js/logout.js"></script>
      <!-- script de inactividad(script propio) -->
      <script src="/assets/js/inactividad.js"></script>

</body>

</html>