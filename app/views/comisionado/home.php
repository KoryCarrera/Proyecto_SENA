<?php require_once __DIR__ . "/../../controllers/checkSessionComi.php"; ?>
<!-- se incluye el archivo de sesion para verificar que el usuario este logueado -->

<!-- comienzo del documento y la vista de inicio de comisionado -->
<!DOCTYPE html>
<!-- lenguaje del documento -->
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comisionado</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!--CSS propio-->
  <link rel="stylesheet" href="/assets/css/home-comisionado.css">

  <script src="/assets/js/jquery-3.7.1.min.js"></script>

</head>

<body class="antialiased selection:bg-indigo-500 selection:text-white">

  <!-- fondo de la pagina -->
  <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
    <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/20 w-[500px] h-[500px]"></div>
    <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/20 w-[500px] h-[500px] animation-delay-2000"></div>
    <div class="blob-bg top-[40%] left-[20%] bg-cyan-500/10 w-[400px] h-[400px] blur-[100px] animation-delay-4000">
    </div>
    <!-- gradiente del fondo extraido de vercel -->
    <div
      class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay">
    </div>
  </div>

  <!-- contenedor principal -->
  <div class="flex h-screen overflow-hidden relative z-10">

    <!-- Sidebar -->
    <aside
      class="glass-sidebar w-20 hover:w-64 transition-all duration-300 ease-in-out flex flex-col group fixed h-full z-50">

      <!-- logo de la sidebar -->
      <div class="h-20 flex items-center justify-center border-b border-white/5">
        <img src="/assets/img/logo_sena.png" alt="SENA" class="w-10 h-10 object-contain group-hover:block">
      </div>

      <!-- barra de navegacion de la sidebar -->

      <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">

        <!-- enlace de la pagina de inicio -->

        <a href="/dashboardComi" class="nav-link active">
          <i class="bi bi-house-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Inicio</span>
        </a>

        <!-- enlace de la pagina de registrar casos -->

        <a href="/registrarCasos" class="nav-link">
          <i class="bi bi-file-earmark-person-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Registrar Caso</span>
        </a>

        <!-- enlace de la pagina de casos -->

        <a href="/casos" class="nav-link">
          <i class="bi bi-eye-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Casos</span>
        </a>

        <!-- enlace de la pagina de generar informe -->

        <a href="/generarInformeComi" class="nav-link">
          <i class="bi bi-file-earmark-text-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Generar Informe</span>
        </a>

        <!-- enlace de la pagina de notificaciones -->

        <a href="/notificacionesComi" class="nav-link">
          <i class="bi bi-bell-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Notificación</span>
        </a>

        <!-- enlace de la pagina de perfil -->

        <a href="/perfil" class="nav-link">
          <i class="bi bi-person-circle"></i>
          <span class="text-[10px] mt-1 font-medium">Mi Perfil</span>
        </a>

      </nav>
    </aside>

    <!-- contenedor principal -->
    <div class="flex-1 flex flex-col ml-20 h-full">

      <!-- top bar(barra superior) -->
      <header class="h-20 glass-nav flex items-center justify-between px-6 sticky top-0 z-40">

        <!-- titulo de la pagina -->

        <h2 class="text-xl font-semibold text-white tracking-tight">Dashboard</h2>

        <!-- se toma el nombre del usuario y se muestra en la top bar junto con su rol-->

        <div class="flex items-center gap-6">
          <div class="text-right hidden md:block">
            <?php if (isset($_SESSION['user']['username'])): ?>
              <p class="text-sm font-medium text-white">
                <?php echo $_SESSION['user']['username']; ?>
              </p>
            <?php endif; ?>
            <p class="text-xs text-slate-400">Comisionado</p>
          </div>

          <!--aqui hay un enlace para ir a la pagina de perfil -->

          <div class="flex items-center gap-4">
            <a href="/perfil" class="p-2 rounded-full hover:bg-white/5 transition-colors">
              <img src="/assets/img/icon account.png" alt="User" class="w-8 h-8 rounded-full border border-white/10">
            </a>

            <!--aqui hay un boton para cerrar sesion -->

            <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
            <button type="submit" name="logout" id="logoutButton" value="logout"
              class="text-xs font-medium text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 px-3 py-1.5 rounded-lg transition-colors border border-red-500/20">
              Cerrar Sesión
            </button>

          </div>
        </div>
      </header>

      <!-- contenido de la pagina -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">

        <div class="max-w-7xl mx-auto">
          <!-- seccion de bienvenida -->
          <div class="glass-card p-8 mb-8 relative overflow-hidden">
            <div
              class="absolute -right-10 -top-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none">
            </div>

            <!-- titulo de la bienvenida a la pagina -->
            <h1 class="text-3xl font-bold text-white mb-4">¡Bienvenido al Sistema de Gestión SENA!</h1>

            <!-- descripcion de la bienvenida a la pagina -->
            <p class="text-slate-300 max-w-3xl leading-relaxed">
              Como comisionado, cuentas con acceso a las funciones necesarias para cumplir tu labor dentro de la
              plataforma.
              Desde aquí podrás revisar y evaluar solicitudes, participar en la toma de decisiones, consultar
              información
              relevante y hacer seguimiento a los casos asignados.
              Tu rol es fundamental para garantizar la transparencia, objetividad y el adecuado funcionamiento de la
              Comisión de Personal.
            </p>
          </div>

          <!-- seccion de estadisticas -->
          <div class="glass-card p-8 mb-8 relative overflow-hidden">
            <div
              class="absolute -right-10 -top-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none">
            </div>

            <!-- titulo de la seccion de estadisticas -->
            <div class="flex items-center justify-between p-2 mb-6 min-w-full">
              <h2 class="text-3xl font-bold text-white mb-4" id="tituloEstadisticas">Estadísticas</h2>

              <!-- seccion de estadisticas en las que podemos tomar por propios o por generales -->
              <div class="rounded">
                <select id="selectEstadisticas"
                  class="glass-sidebar appearance-none border border-white/10 rounded-lg px-4 py-1.5 text-xs text-white cursor-pointer outline-none hover:bg-white/5 transition-colors">
                  <option selected disabled class="bg-slate-900">Mostrar por...</option>
                  <option class="bg-slate-900" value="propios">Propios</option>
                  <option class="bg-slate-900" value="generales">Generales</option>
                </select>
              </div>
            </div>

            <!-- targetas de contenido de solicitudes -->
            <section class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">

              <!-- total de solicitudes -->
              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="b/i bi-collection-fill text-indigo-400 text-xl mb-2"></i>
                <h2 id="total" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Total</p>
              </div>

              <!-- Denuncias -->
              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-megaphone-fill text-red-400 text-xl mb-2"></i>
                <h2 id="denuncia" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Denuncias</p>
              </div>

              <!-- Solicitudes -->
              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-envelope-paper-fill text-amber-400 text-xl mb-2"></i>
                <h2 id="solicitud" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Solicitudes</p>
              </div>

              <!-- Peticiones -->
              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-file-text-fill text-blue-400 text-xl mb-2"></i>
                <h2 id="peticion" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Peticiones</p>
              </div>

              <!-- Tutelas -->
              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-shield-lock-fill text-cyan-400 text-xl mb-2"></i>
                <h2 id="tutela" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Tutelas</p>
              </div>

              <!-- Atendidos -->
              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-check-circle-fill text-emerald-400 text-xl mb-2"></i>
                <h2 id="atendido" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Atendidos</p>
              </div>

              <!-- Por Atender -->
              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-clock-history text-rose-400 text-xl mb-2"></i>
                <h2 id="porAtender" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Por Atender</p>
              </div>

              <!-- No Atendidos -->
              <div
                class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-x-circle-fill text-red-500 text-xl mb-2"></i>
                <h2 id="noAtendidos" class="text-4xl font-bold text-white"></h2>
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">No Atendidos</p>
              </div>
            </section>
          </div>

          <!-- seccion de graficas -->
           
          <div class="rounded  w-full m-2 items-center my-6 flex justify-start items-center">
            <select id="selectGraficas"
              class="glass-sidebar appearance-none rounded-lg px-6 py-2 text-base text-white cursor-pointer outline-none hover:bg-white/5 transition-colors
              bg-indigo-600 ">
              <option selected disabled class="bg-slate-900">Filtrar graficas por...</option>
              <option class="bg-slate-900" value="semana">esta semana</option>
              <option class="bg-slate-900" value="mes">este mes</option>
              <option class="bg-slate-900" value="anual">este año</option>
            </select>
          </div>

          <!-- grafica de barras -->
          <div class="glass-card p-6 col-span-1 lg:col-span-3 flex flex-col items-center justify-center h-96 mb-8">
            <div class="flex items-center justify-between w-full mb-4">
              <h3 id="tituloLineas" class="text-3xl font-bold text-white mb-4">
              </h3>
              <p id="contextoLinea" class="text-slate-300 max-w-3xl leading-relaxed"></p>
            </div>
            <div class="relative w-full flex-1 min-h-0 flex items-center justify-center">
              <canvas id="barChart"></canvas>
            </div>
          </div>

          <!-- Grid para los dos gráficos inferiores -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Chart 1 (Polar) -->
            <div class="glass-card p-6 flex flex-col items-center justify-center h-96 text-right w-full ">
              <div class="flex items-center justify-between p-2 m-2 min-w-full">
                <h3 id="tituloPolar" class="text-3xl font-bold text-white mb-4"></h3>
                <p id="contextoPolar" class="text-white max-w-3xl leading-relaxed"></p>
              </div>
              <div class="relative w-full flex-1 min-h-0 flex items-center justify-center">
                <canvas id="polarChart"></canvas>
              </div>
            </div>

            <!-- Chart 2 (Pie) po-->
            <div class="glass-card p-6 flex flex-col items-center justify-center h-96 ">
              <div class="flex items-center justify-between p-2 m-2 min-w-full">
                <h3 id="titulobar" class="text-3xl font-bold text-white mb-4"></h3>

                <p id="contextoBar" class="text-slate-300 max-w-3xl leading-relaxed"></p>

              </div>
              <div class="relative w-full flex-1 min-h-0 flex items-center justify-center">
                <canvas id="pieChart"></canvas>
              </div>
            </div>
      </main>
    </div>
  </div>

  <!-- seccion de scripts -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
  <script src="/assets/js/generalesComi.js"></script>
  <script src="/assets/js/dashboard_comi.js"></script>
  <script src="/assets/js/cache.js"></script>
  <script src="/assets/js/logout.js"></script>

</body>

</html>