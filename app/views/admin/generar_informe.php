<?php require_once __DIR__ . "/../../controllers/checkSessionAdmin.php"; ?>
<!-- se incluyen los archivos necesarios para procesar los datos y verificar que el usuario este logueado -->

<!DOCTYPE html>
<html lang="es">
<!-- se inicia el documento y le decimos el lenguaje y que tomara el meta tag para caracteres especiales -->

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Generación de Informe | Administrador</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <!-- Bootstrap CSS (Required for JS compatibility) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!--CSS propio-->
  <link rel="stylesheet" href="/assets/css/generar_informe-admin.css">

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
        <a href="/generarInforme" class="nav-link active">
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
        <a href="/usuarios" class="nav-link">
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
        <h2 class="text-xl font-semibold text-white tracking-tight">Generar Informe</h2>

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

      <!-- Contenido interno de la pagina, donde se muestra el formulario para generar informe -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">
        <div class="max-w-2xl mx-auto">

          <div class="glass-card p-8 md:p-10">
            <!-- titulo del formulario -->
            <h2 class="text-2xl font-bold text-white text-center mb-6" id="tituloForm"></h2>

            <div id="seccion1" class="form-section space-y-6">

              <!-- select para el formato del informe -->
              <div class="relative" id="excluido">
                <select class="glass-input w-full p-3 pl-12 rounded-lg text-white appearance-none cursor-pointer" id="formato">
                  <option selected disabled class="bg-slate-800">Selecione el tipo de archivo</option>
                  <option value="1" class="bg-slate-800">PDF</option>
                  <option value="2" class="bg-slate-800">EXCEL</option>
                </select>
              </div>

              <!-- select para el tipo de reporte -->
              <div class="relative">
                <select class="glass-input w-full p-3 pl-12 rounded-lg text-white appearance-none cursor-pointer" id="tipoReporte">
                  <option selected disabled class="bg-slate-800">Selecione el tipo de reporte anual</option>
                  <option value="1" class="bg-slate-800">Reporte de Casos</option>
                  <option value="2" class="bg-slate-800">Reporte de Usuarios</option>
                  <option value="3" class="bg-slate-800">Reporte de Procesos Organizacionales</option>
                </select>
              </div>

              <!-- boton para descargar el informe -->
              <button type="button" class="btn-siguiente w-full flex items-center justify-center gap-2 mt-4 text-white bg-indigo-500 hover:bg-indigo-600 px-8 py-3 rounded-xl " id="informe">
                <i class="bi bi-download"></i> DESCARGAR
              </button>

            </div>
          </div>

        </div>
      </main>
    </div>
  </div>

  <!-- script requeridos para la pagina -->
  <!-- Bootstrap Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- JS propio (Keep order) -->
  <!-- script de logout(script propio) -->
  <script src="/assets/js/logout.js"></script>
  <!-- script de cache(script propio) -->
  <script src="/assets/js/cache.js"></script>
  <!-- script de generarInforme(script propio) -->
  <script src="/assets/js/generarInforme.js"></script>
  <!-- script de inactividad(script propio) -->
  <script src="/assets/js/inactividad.js"></script>

</body>

</html>