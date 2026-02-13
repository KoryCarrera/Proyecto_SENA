<?php require_once __DIR__ . "/../../controllers/checkSessionComi.php"; ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Casos | Comisionado</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Bootstrap CSS (Required for JS compatibility) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!--CSS propio-->
  <link rel="stylesheet" href="/assets/css/casos-comisionado.css">

</head>

<body class="antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Decorative Background Elements -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/20 w-[500px] h-[500px]"></div>
        <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/20 w-[500px] h-[500px] animation-delay-2000"></div>
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
        
        <a href="/dashboardComi" class="nav-link">
          <i class="bi bi-house-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Inicio</span>
        </a>

        <a href="/registrarCasos" class="nav-link">
          <i class="bi bi-file-earmark-person-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Registrar Caso</span>
        </a>

        <a href="/casos" class="nav-link active">
          <i class="bi bi-eye-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Casos</span>
        </a>

        <a href="/notificacionesComi" class="nav-link">
          <i class="bi bi-envelope-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Notificaciones</span>
        </a>

        <a href="#" class="nav-link">
          <i class="bi bi-envelope-plus-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Crear Notificación</span>
        </a>

      </nav>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col ml-20 h-full">
      
      <!-- Top Bar -->
      <header class="h-20 glass-nav flex items-center justify-between px-6 sticky top-0 z-40">
        
        <h2 class="text-xl font-semibold text-white tracking-tight">Seguimiento de Casos</h2>

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

      <!-- Filter Bar -->
      <div class="glass-nav px-6 py-3 flex items-center justify-between border-b border-white/5">
        <div class="flex items-center gap-4">
          <div class="dropdown">
            <button class="btn-filter dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-funnel-fill me-1"></i> Filtrar
            </button>
            <ul class="dropdown-menu glass-dropdown">
              <li><a class="dropdown-item" href="#">Nombre Del Caso</a></li>
              <li><a class="dropdown-item" href="#">Fecha de registro</a></li>
              <li><a class="dropdown-item" href="#">Tipo de Caso</a></li>
              <li><a class="dropdown-item" href="#">Fecha de respuesta</a></li>
              <li><a class="dropdown-item" href="#">Estado</a></li>
              <li><a class="dropdown-item" href="#">Proceso</a></li>
              <li><a class="dropdown-item" href="#">Comisionado Encargado</a></li>
            </ul>
          </div>
        </div>
        <form class="d-flex" role="search">
          <input class="glass-input form-control me-2 text-white" type="search" placeholder="Palabras Claves" aria-label="Search">
          <button class="btn-search" type="submit">Buscar</button>
        </form>
      </div>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Cases Table -->
            <section>
                <div class="glass-card p-0 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="glass-table w-full text-left text-sm text-slate-300">
                            <thead class="bg-slate-800/50 text-xs uppercase text-slate-400">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Id de caso</th>
                                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Fecha de Registro</th>
                                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Tipo de Caso</th>
                                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Fecha de respuesta</th>
                                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Proceso</th>
                                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Comisionado Encargado</th>
                                    <th scope="col" class="px-6 py-4 font-medium tracking-wider text-right">Gestionar</th>
                                </tr>
                            </thead>
                            <tbody class="cont-tabla divide-y divide-slate-700/50" id="tablaCasos">
                                <!-- JS Injected Rows go here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </div>
      </main>
    </div>
  </div>

  <!--JS de bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
  <script src="/assets/js/cache.js"></script>
  <script src="/assets/js/casosComi.js"></script>

</body>

</html>