<?php require_once __DIR__ . "/../../controllers/checkSessionComi.php"; ?>

<!DOCTYPE html>
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

  <!-- Decorative Background Elements -->
  <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
    <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/20 w-[500px] h-[500px]"></div>
    <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/20 w-[500px] h-[500px] animation-delay-2000"></div>
    <div class="blob-bg top-[40%] left-[20%] bg-cyan-500/10 w-[400px] h-[400px] blur-[100px] animation-delay-4000">
    </div>
    <div
      class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay">
    </div>
  </div>

  <div class="flex h-screen overflow-hidden relative z-10">

    <!-- Sidebar -->
    <aside
      class="glass-sidebar w-20 hover:w-64 transition-all duration-300 ease-in-out flex flex-col group fixed h-full z-50">

      <!-- Logo Area -->
      <div class="h-20 flex items-center justify-center border-b border-white/5">
        <img src="/assets/img/logo_sena.png" alt="SENA" class="w-10 h-10 object-contain group-hover:block">
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">

        <a href="/dashboardComi" class="nav-link active">
          <i class="bi bi-house-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Inicio</span>
        </a>

        <a href="/registrarCasos" class="nav-link">
          <i class="bi bi-file-earmark-person-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Registrar Caso</span>
        </a>

        <a href="/casos" class="nav-link">
          <i class="bi bi-eye-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Casos</span>
        </a>

        <a href="/notificacionesComi" class="nav-link">
          <i class="bi bi-envelope-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Notificaciones</span>
        </a>

        <a href="/perfil" class="nav-link">
          <i class="bi bi-person-circle"></i>
          <span class="text-[10px] mt-1 font-medium">Mi Perfil</span>
        </a>

      </nav>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col ml-20 h-full">

      <!-- Top Bar -->
      <header class="h-20 glass-nav flex items-center justify-between px-6 sticky top-0 z-40">

        <h2 class="text-xl font-semibold text-white tracking-tight">Dashboard</h2>

        <div class="flex items-center gap-6">
          <div class="text-right hidden md:block">
            <?php if (isset($_SESSION['user']['username'])): ?>
              <p class="text-sm font-medium text-white">
                <?php echo $_SESSION['user']['username']; ?>
              </p>
            <?php endif; ?>
            <p class="text-xs text-slate-400">Comisionado</p>
          </div>

          <div class="flex items-center gap-4">
            <a href="/perfil" class="p-2 rounded-full hover:bg-white/5 transition-colors">
              <img src="/assets/img/icon account.png" alt="User" class="w-8 h-8 rounded-full border border-white/10">
            </a>

              <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
              <button type="submit" name="logout" id="logoutButton" value="logout"
                class="text-xs font-medium text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 px-3 py-1.5 rounded-lg transition-colors border border-red-500/20">
                Cerrar Sesión
              </button>
              
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">

        <div class="max-w-7xl mx-auto">
          <!-- Welcome Section -->
          <div class="glass-card p-8 mb-8 relative overflow-hidden">
            <div
              class="absolute -right-10 -top-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none">
            </div>
            <h1 class="text-3xl font-bold text-white mb-4">¡Bienvenido al Sistema de Gestión SENA!</h1>
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

          <div class="glass-card p-8 mb-8 relative overflow-hidden">
            <div
              class="absolute -right-10 -top-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none">
            </div>
            <h2 class="text-3xl font-bold text-white mb-4" id="tituloEstadisticas"></h2>

            <div class="rounded">
              
              <select id="selectEstadisticas"
                class="glass-sidebar appearance-none border border-white/10 rounded-lg px-4 py-1.5 text-xs text-white cursor-pointer outline-none hover:bg-white/5 transition-colors">
                <option selected disabled class="bg-slate-900">Mostrar por...</option>
                <option class="bg-slate-900" value="propios">Propios</option>
                <option class="bg-slate-900" value="generales">Generales</option>
              </select>
            </div>

            <section class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
              <!-- Tarjeta Total -->
              <div class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-collection-fill text-indigo-400 text-xl mb-2"></i>
                <h2 id="total" class="text-2xl font-bold text-white"></h2>
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Total</p>
              </div>

              <!-- Denuncias -->
              <div class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-megaphone-fill text-red-400 text-xl mb-2"></i>
                <h2 id="denuncia" class="text-2xl font-bold text-white"></h2>
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Denuncias</p>
              </div>

              <!-- Solicitudes -->
              <div class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-envelope-paper-fill text-amber-400 text-xl mb-2"></i>
                <h2 id="solicitud" class="text-2xl font-bold text-white"></h2>
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Solicitudes</p>
              </div>

              <!-- Peticiones -->
              <div class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-file-text-fill text-blue-400 text-xl mb-2"></i>
                <h2 id="peticion" class="text-2xl font-bold text-white"></h2>
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Peticiones</p>
              </div>

              <!-- Tutelas -->
              <div class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-shield-lock-fill text-cyan-400 text-xl mb-2"></i>
                <h2 id="tutela" class="text-2xl font-bold text-white"></h2>
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Tutelas</p>
              </div>

              <!-- Atendidos -->
              <div class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-check-circle-fill text-emerald-400 text-xl mb-2"></i>
                <h2 id="atendido" class="text-2xl font-bold text-white"></h2>
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Atendidos</p>
              </div>

              <!-- Por Atender -->
              <div class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-clock-history text-rose-400 text-xl mb-2"></i>
                <h2 id="porAtender" class="text-2xl font-bold text-white"></h2>
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Por Atender</p>
              </div>

              <!-- No Atendidos -->
              <div class="glass-card p-4 flex flex-col items-center justify-center border border-white/10 hover:bg-white/5 transition-all">
                <i class="bi bi-x-circle-fill text-red-500 text-xl mb-2"></i>
                <h2 id="noAtendidos" class="text-2xl font-bold text-white"></h2>
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">No Atendidos</p>
              </div>
            </section>
          </div>

          <!-- Chart 3 (Full Width) -->
          <div class="glass-card p-6 col-span-1 lg:col-span-2 flex flex-col items-center justify-center h-96 mb-8">
            <div class="flex items-center justify-between w-full mb-4">
              <h3 class="text-3xl font-bold text-white mb-4">Casos por proceso organizacional</h3>
              <div class="relative">
                <select class="glass-sidebar appearance-none border border-white/10 rounded-lg px-4 py-1.5 text-xs text-white cursor-pointer outline-none hover:bg-white/5 transition-colors">
                  <option selected disabled class="bg-slate-900">Filtrar por...</option>
                  <option class="bg-slate-900" value="semana">esta semana</option>
                  <option class="bg-slate-900" value="mes">este mes</option>
                  <option class="bg-slate-900" value="anual">este año</option>
                </select>
                <i class="bi bi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-white pointer-events-none"></i>
              </div>
            </div>
            <div class="w-full h-full flex items-center justify-center">
              <canvas id="barChart"></canvas>
            </div>
          </div>

          <!-- Grid para los dos gráficos inferiores -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Chart 1 (Polar) -->
            <div class="glass-card p-6 flex flex-col items-center justify-center h-96">
              <div class="flex items-center justify-between p-2 m-2 min-w-full">
                <h3 class="text-3xl font-bold text-white mb-4">Estadísticas por tipo</h3>
                <div class="rounded">
                  <select class="glass-sidebar appearance-none border border-white/10 rounded-lg px-4 py-1.5 text-xs text-white cursor-pointer outline-none hover:bg-white/5 transition-colors">
                    <option selected disabled class="bg-slate-900">Filtrar por...</option>
                    <option class="bg-slate-900" value="semana">esta semana</option>
                    <option class="bg-slate-900" value="mes">este mes</option>
                    <option class="bg-slate-900" value="anual">este año</option>
                  </select>
                </div>
              </div>
              <div class="w-full h-full flex items-center justify-center">
                <canvas id="polarChart"></canvas>
              </div>
            </div>

            <!-- Chart 2 (Pie) -->
            <div class="glass-card p-6 flex flex-col items-center justify-center h-96">
              <div class="flex items-center justify-between p-2 m-2 min-w-full">
                <h3 class="text-3xl font-bold text-white mb-4">Estadísticas por estado</h3>
                <div class="rounded">
                  <select class="glass-sidebar appearance-none border border-white/10 rounded-lg px-4 py-1.5 text-xs text-white cursor-pointer outline-none hover:bg-white/5 transition-colors">
                    <option selected disabled class="bg-slate-900">Filtrar por...</option>
                    <option class="bg-slate-900" value="semana">esta semana</option>
                    <option class="bg-slate-900" value="mes">este mes</option>
                    <option class="bg-slate-900" value="anual">este año</option>
                  </select>
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


  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
  <script src="/assets/js/generalesComi.js"></script>
  <script src="/assets/js/dashboard_comi.js"></script>
  <script src="/assets/js/cache.js"></script>
  <script src="/assets/js/logout.js"></script>
  
</body>

</html>