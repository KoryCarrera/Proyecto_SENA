<?php require_once __DIR__ . "/../../controllers/checkSessionAdmin.php"; ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administrador</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!--CSS propio para colores y fonts-->
  <link rel="stylesheet" href="/assets/css/home-admin.css">

  <script src="/assets/js/jquery-3.7.1.min.js"></script>

</head>

<body class="antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Decorative Background Elements -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/20 w-[500px] h-[500px]"></div>
        <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/20 w-[500px] h-[500px] animation-delay-2000"></div>
        <div class="blob-bg top-[40%] left-[20%] bg-cyan-500/10 w-[400px] h-[400px] blur-[100px] animation-delay-4000"></div>
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay"></div>
    </div>

  <div class="flex h-screen overflow-hidden relative z-10">

    <!-- Sidebar -->
    <aside class="glass-sidebar w-20 hover:w-64 transition-all duration-300 ease-in-out flex flex-col group fixed h-full z-50">
      
      <!-- Logo Area -->
      <div class="h-20 flex items-center justify-center border-b border-white/5">
        <img src="/assets/img/logo_sena.png" alt="SENA" class="w-10 h-10 object-contain group-hover:block">
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">
        
        <a href="/dashboardAdmin" class="nav-link active">
          <i class="bi bi-house-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Inicio</span>
        </a>

        <a href="/generarInforme" class="nav-link">
          <i class="bi bi-file-earmark-text-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Generar Informe</span>
        </a>

        <a href="/casosAdmin" class="nav-link">
          <i class="bi bi-eye-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Casos</span>
        </a>

        <a href="/procesoOrganizacional" class="nav-link">
          <i class="bi bi-diagram-3-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Procesos</span>
        </a>

        <a href="/usuarios" class="nav-link">
          <i class="bi bi-person-fill-gear"></i>
          <span class="text-[10px] mt-1 font-medium">Usuarios</span>
        </a>

        <a href="/notificacionesAdmin" class="nav-link">
          <i class="bi bi-bell-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Notificación</span>
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
            <p class="text-xs text-slate-400">Administrador</p>
          </div>

          <div class="flex items-center gap-4">
            <a href="#" class="p-2 rounded-full hover:bg-white/5 transition-colors">
               <img src="/assets/img/icon account.png" alt="User" class="w-8 h-8 rounded-full border border-white/10">
            </a>
            
            <form action="/logout" method="POST">
              <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
              <button type="submit" name="logout" value="logout" class="text-xs font-medium text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 px-3 py-1.5 rounded-lg transition-colors border border-red-500/20">
                Cerrar Sesión
              </button>
            </form>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">
        
        <div class="max-w-7xl mx-auto">
            <!-- Welcome Section -->
            <div class="glass-card p-8 mb-8 relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <h1 class="text-3xl font-bold text-white mb-4">¡Bienvenido al Sistema de Gestión SENA!</h1>
                <p class="text-slate-300 max-w-3xl leading-relaxed">
                    Como administrador, tienes acceso total a las herramientas y funciones de esta plataforma.
                    Desde aquí podrás gestionar usuarios, supervisar solicitudes, generar reportes y mantener actualizada la información institucional.
                    Tu rol es fundamental para garantizar el correcto funcionamiento del sistema y apoyar la labor de la Comisión de Personal.
                </p>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Chart 1 -->
                <div class="glass-card p-6 flex flex-col items-center justify-center min-h-[300px]">
                    <h3 class="text-sm font-semibold text-slate-400 mb-4 w-full text-left uppercase tracking-wider">Estadísticas Generales</h3>
                    <div class="w-full h-full flex items-center justify-center">
                        <canvas id="polarChart"></canvas>
                    </div>
                </div>

                <!-- Chart 2 -->
                <div class="glass-card p-6 flex flex-col items-center justify-center min-h-[300px]">
                    <h3 class="text-sm font-semibold text-slate-400 mb-4 w-full text-left uppercase tracking-wider">Distribución de Casos</h3>
                    <div class="w-full h-full flex items-center justify-center">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
                
                <!-- Chart 3 (Full Width) -->
                <div class="glass-card p-6 col-span-1 lg:col-span-2 flex flex-col items-center justify-center min-h-[300px]">
                    <h3 class="text-sm font-semibold text-slate-400 mb-4 w-full text-left uppercase tracking-wider">Rendimiento Mensual</h3>
                    <div class="w-full h-full flex items-center justify-center">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
  <script src="/assets/js/dashboard_admin.js"></script>
  <script src="/assets/js/cache.js"></script>

</body>

</html>