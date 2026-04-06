<?php require_once __DIR__ . "/../../controllers/checkSessionAdmin.php"; ?>
<!-- se incluyen los archivos necesarios para procesar los datos y verificar que el usuario este logueado -->

<!DOCTYPE html>
<html lang="es">
<!-- se inicia el documento y le decimos el lenguaje y que tomara el meta tag para caracteres especiales -->

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administrador</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!--CSS propio para colores y fonts-->
  <link rel="stylesheet" href="/assets/css/home-admin.css">

  <script src="/assets/js/jquery-3.7.1.min.js"></script>

</head>

<!-- se inicia el body del documento -->

<body class="antialiased selection:bg-indigo-500 selection:text-white">

  <!-- background de la vista -->
  <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
    <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/20 w-[500px] h-[500px]"></div>
    <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/20 w-[500px] h-[500px] animation-delay-2000"></div>
    <div class="blob-bg top-[40%] left-[20%] bg-cyan-500/10 w-[400px] h-[400px] blur-[100px] animation-delay-4000">
    </div>
    <div
      class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay">
    </div>
  </div>

  <!-- contenedor principal -->
  <div class="flex h-screen overflow-hidden relative z-10">

    <!-- contenedor de la Sidebar -->
    <aside
      class="glass-sidebar w-20 hover:w-64 transition-all duration-300 ease-in-out flex flex-col group fixed h-full z-50">

      <!-- Logo del sena en la sidebar -->
      <div class="h-20 flex items-center justify-center border-b border-white/5">
        <img src="/assets/img/logo_sena.png" alt="SENA" class="w-10 h-10 object-contain group-hover:block">
      </div>

      <!-- Navegacion de la sidebar -->
      <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">

        <!-- se define el link de inicio -->
        <a href="/dashboardAdmin" class="nav-link active">
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
        <h2 class="text-xl font-semibold text-white tracking-tight">Dashboard</h2>

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

      <!-- Contenido principal (main) -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">

        <div class="max-w-7xl mx-auto">
          <!-- seccion de bienvenida  con su respectivo texto -->
          <div class="glass-card p-8 mb-8 relative overflow-hidden">
            <div
              class="absolute -right-10 -top-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none">
            </div>
            <div class="flex items-center justify-between p-2 mb-6 min-w-full">
              <h1 class="text-2xl font-bold text-white mb-4 uppercase tracking-wider">Estadísticas generales anuales
              </h1>
            </div>

            <section class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4 mb-2">

              <a href="/casosAdmin" class="block">
                <div
                  class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all h-full">
                  <i class="bi bi-collection-fill text-indigo-400 text-2xl mb-2"></i>
                  <h2 id="total" class="text-4xl font-bold text-white"></h2>
                  <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold text-center mt-2">Total
                  </p>
                </div>
              </a>

              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-megaphone-fill text-red-400 text-2xl mb-2"></i>
                <h2 id="denuncia" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold text-center mt-2">Denuncias
                </p>
              </div>

              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-envelope-paper-fill text-amber-400 text-2xl mb-2"></i>
                <h2 id="solicitud" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold text-center mt-2">
                  Solicitudes</p>
              </div>

              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-file-text-fill text-blue-400 text-2xl mb-2"></i>
                <h2 id="peticion" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold text-center mt-2">Peticiones
                </p>
              </div>

              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-shield-lock-fill text-cyan-400 text-2xl mb-2"></i>
                <h2 id="tutela" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold text-center mt-2">Tutelas
                </p>
              </div>

              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-check-circle-fill text-emerald-400 text-2xl mb-2"></i>
                <h2 id="atendido" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold text-center mt-2">Atendidos
                </p>
              </div>

              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-clock-history text-rose-400 text-2xl mb-2"></i>
                <h2 id="porAtender" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold text-center mt-2">Por
                  Atender</p>
              </div>

              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-x-circle-fill text-red-500 text-2xl mb-2"></i>
                <h2 id="noAtendidos" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold text-center mt-2">No
                  Atendidos</p>
              </div>

              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all h-full w-full">
                <i class="bi bi-inbox-fill text-fuchsia-400 text-2xl mb-2"></i>
                <h2 id="porAsignar" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold text-center mt-2">Por Asignar
                </p>
              </div>

            </section>
          </div>
          <!-- contenedor de graficas  -->

          <div class="rounded w-full m-2 items-center my-6 flex justify-start items-center">
            <!-- select para filtrar por semana, mes o año -->
            <select id="selectGraficas"
              class="glass-sidebar appearance-none  rounded-lg px-6 py-2 text-base text-white cursor-pointer outline-none hover:bg-white/5 transition-colors bg-indigo-600 ">
              <option selected disabled class="bg-slate-900">Filtrar por...</option>
              <option class="bg-slate-900" value="semana">esta semana</option>
              <option class="bg-slate-900" value="mes">este mes</option>
              <option class="bg-slate-900" value="anual">este año</option>
            </select>
          </div>

          <!-- Charts Grid -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 ">
            <!-- primera grafica,la del tamaño completo de ancho -->
            <div
              class="glass-card p-6 col-span-1 lg:col-span-2 flex flex-col items-center justify-center min-h-[300px]">
              <div class="flex items-center justify-between p-2 m-2 min-w-full">
                <h3 class="text-xl font-bold text-white mb-4 w-full text-left uppercase tracking-widest" id="tituloLineas">
                </h3>
                <p class="text-base text-slate-300 max-w-3xl leading-relaxed" id="contextoLinea"></p>

              </div>
              <!-- se define el contenedor de la grafica -->
              <div class="w-full h-full flex items-center justify-center">
                <canvas id="barChart"></canvas>
              </div>
            </div>
            <!-- segunda grafica -->
            <div class="glass-card p-6 flex flex-col items-center justify-center min-h-[300px]">
              <div class="flex items-center justify-between p-2 m-2 min-w-full">
                <h3 class="text-xl font-bold text-white mb-4 w-full text-left uppercase tracking-widest" id="tituloPolar">
                </h3>
                <p class="text-base text-slate-300 max-w-3xl leading-relaxed" id="contextoPolar"></p>

                <div class="rounded">

                </div>
              </div>
              <div class="w-full h-full flex items-center justify-center">
                <canvas id="polarChart"></canvas>
              </div>

            </div>
            <!-- tercera grafica -->

            <div class="glass-card p-6 flex flex-col items-center justify-start min-h-[400px]">

              <div class="flex items-center justify-between w-full mb-6">
                <h3 class="text-xl font-bold text-white uppercase tracking-widest" id="titulobar">
                </h3>
                <p class="text-base text-slate-300 max-w-3xl leading-relaxed" id="contextoBar"></p>

                <div class="relative">

                </div>
              </div>

              <div class="w-full h-full flex items-center justify-center">
                <canvas id="pieChart"></canvas>
              </div>

            </div>

          </div>
      </main>
    </div>
  </div>

  <!-- script requeridos para la pagina -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
  <!-- script de generalesAdmin(script propio) -->
  <script src="/assets/js/generalesAdmin.js"></script>
  <!-- script de dashboard_admin(script propio) -->
  <script src="/assets/js/dashboard_admin.js"></script>
  <!-- script de cache(script propio) -->
  <script src="/assets/js/cache.js"></script>
  <!-- script de logout(script propio) -->
  <script src="/assets/js/logout.js"></script>
  <!-- script de inactividad(script propio) -->
  <script src="/assets/js/inactividad.js"></script>

</body>

</html>