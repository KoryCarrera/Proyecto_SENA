<?php require_once __DIR__ . "/../../controllers/checkSessionAdmin.php"; ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administrador | Casos</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <!-- Bootstrap CSS (Required for JS compatibility with Modals/Table classes used in JS) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!--CSS propio para colores y fonts-->
  <link rel="stylesheet" href="/assets/css/casos-admin.css">

  <script src="/assets/js/jquery-3.7.1.min.js"></script>

  <style>
    /* Override Bootstrap defaults to match Tailwind/Glass theme */
    .table {
      --bs-table-bg: transparent;
      --bs-table-color: #e2e8f0;
      --bs-table-border-color: rgba(255, 255, 255, 0.1);
      --bs-table-hover-bg: rgba(255, 255, 255, 0.05);
    }
  </style>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"> <!-- DataTables CSS -->

</head>

<body class="antialiased selection:bg-indigo-500 selection:text-white">

  <!-- Decorative Background Elements -->
  <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
    <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/20 w-[500px] h-[500px]"></div>
    <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/20 w-[500px] h-[500px] animation-delay-2000"></div>
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

        <a href="/dashboardAdmin" class="nav-link">
          <i class="bi bi-house-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Inicio</span>
        </a>

        <a href="/generarInforme" class="nav-link">
          <i class="bi bi-file-earmark-text-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Generar Informe</span>
        </a>

        <a href="/casosAdmin" class="nav-link active">
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

        <a href="/perfilAdmin" class="nav-link">
          <i class="bi bi-person-circle"></i>
          <span class="text-[10px] mt-1 font-medium">Mi Perfil</span>
        </a>

      </nav>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col ml-20 h-full">

      <!-- Top Bar -->
      <header class="h-20 glass-nav flex items-center justify-between px-6 sticky top-0 z-40">

        <h2 class="text-xl font-semibold text-white tracking-tight">Gestión de Casos</h2>

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
            <a href="/perfilAdmin" class="p-2 rounded-full hover:bg-white/5 transition-colors">
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

      <!-- Filter Bar (Secondary Nav Replacement) -->
      <div class="px-6 py-4 glass-nav z-30 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="flex items-center gap-2 w-full md:w-auto">
          <div class="relative">
            <label class="text-slate-400 text-xs uppercase font-bold mr-2">Ver:</label>
            <select id="filtroCantidad"
              class="bg-slate-800/50 border border-slate-700 text-slate-200 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none cursor-pointer hover:bg-slate-700/50 transition-colors">
              <option class="px-6 py-4 font-medium tracking-wider" value="10">10 casos</option>
              <option class="px-6 py-4 font-medium tracking-wider" value="25">25 casos</option>
              <option class="px-6 py-4 font-medium tracking-wider" value="50">50 casos</option>
              <option class="px-6 py-4 font-medium tracking-wider" value="100">100 casos</option>
            </select>
          </div>
        </div>

        <form class="flex gap-2 w-full md:w-auto" role="search">
          <div class="relative w-full md:w-64">
            <input
              class="glass-search w-full px-4 py-2 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
              type="search" id="buscarAdmin" placeholder="Buscar palabras clave..." aria-label="Search">
            <i class="bi bi-search absolute right-3 top-2.5 text-slate-400"></i>
          </div>
        </form>
      </div>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">
        <div class="max-w-full mx-auto ">

          <div class="glass-card p-0 overflow-hidden">
            <div class="overflow-x-auto">
              <table id="tablaCaso" class="glass-table w-full text-center text-base text-slate-300 h-full"
               >
                <thead class="bg-slate-800/50 text-base text-center uppercase text-slate-400">
                  <tr>
                    <th scope="col" class="px-6 py-4 font-medium tracking-wider"># Id</th>
                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Fecha de Registro</th>
                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Tipo de Caso</th>
                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Fecha de respuesta</th>
                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Estado</th>
                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Proceso</th>
                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Comisionado</th>
                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Reasignaciones</th>
                    <th scope="col" class="px-6 py-4 font-medium tracking-wider">Acciones</th>
                  </tr>
                </thead>
                <tbody id="tablaCasos" class="divide-y divide-slate-700/50">
                  <!-- JS Injected Rows go here -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- Paginación externa — JS la controla dinámicamente -->
          <div id="paginacionCasos" class="flex justify-center mt-4">
            <nav class="flex items-center gap-x-1" aria-label="Pagination">

              <!-- Botón Anterior -->
              <button type="button" id="btnPaginaAnterior"
                class="py-2 px-3 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition-colors disabled:opacity-30 disabled:pointer-events-none"
                aria-label="Previous" disabled>
                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round">
                  <path d="m15 18-6-6 6-6" />
                </svg>
                <span>Anterior</span>
              </button>

              <!-- Números de página — generados por JS -->
              <div id="pagBotones" class="flex items-center gap-x-1"></div>

              <!-- Botón Siguiente -->
              <button type="button" id="btnPaginaSiguiente"
                class="py-2 px-3 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition-colors disabled:opacity-30 disabled:pointer-events-none"
                aria-label="Next" disabled>
                <span>Siguiente</span>
                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round">
                  <path d="m9 18 6-6-6-6" />
                </svg>
              </button>

            </nav>
          </div>

        </div>
      </main>
    </div>
  </div>

  <!-- Modal Wrapper (Maintains Bootstrap compatibility but styled) -->
  <div class="modal fade" id="modalCaso" tabindex="-1" aria-labelledby="modalCasoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content glass-card border-slate-700">
        <div class="modal-header border-slate-700 p-6">
          <h5 class="modal-title text-xl font-bold text-white flex items-center gap-2" id="modalCasoLabel">
            <i class="bi bi-file-earmark-text text-indigo-400"></i> Detailles del Caso
          </h5>
          <button type="button" class="btn-close btn-close-white opacity-50 hover:opacity-100" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body p-6 text-slate-300" id="modalCasoBody">
          <!-- El contenido se carga dinámicamente con JavaScript -->
        </div>
        <div class="modal-footer border-slate-700 p-6 bg-slate-900/20">
          <button type="button"
            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors text-sm font-medium"
            data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

</body>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="/assets/js/logout.js"></script>
<script src="/assets/js/casosAdmin.js"></script>
<script src="/assets/js/cache.js"></script>




</html>