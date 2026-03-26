<?php require_once __DIR__ . "/../../controllers/checkSessionAdmin.php"; ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proceso Organizacional | Administrador</title>

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
  <link rel="stylesheet" href="/assets/css/procesoOrganizacional.css">

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

        <a href="/casosAdmin" class="nav-link">
          <i class="bi bi-eye-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Casos</span>
        </a>

        <a href="/procesoOrganizacional" class="nav-link active">
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

        <h2 class="text-xl font-semibold text-white tracking-tight">Proceso Organizacional</h2>

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

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">
        <div class="max-w-7xl mx-auto space-y-6">

          <!-- Barra de herramientas: cantidad + búsqueda -->
          <div class="flex flex-col md:flex-row gap-4 items-center justify-between mb-4">
            <div class="flex items-center">
              <label class="text-slate-400 text-xs uppercase font-bold mr-2">Ver:</label>
              <select id="filtroCantidadProcesos"
                class="bg-slate-800/50 border border-slate-700 text-slate-200 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none cursor-pointer hover:bg-slate-700/50 transition-colors">
                <option value="10">10 procesos</option>
                <option value="25">25 procesos</option>
                <option value="50">50 procesos</option>
                <option value="100">100 procesos</option>
              </select>
            </div>

            <form class="flex gap-2 w-full md:w-auto" role="search" onsubmit="return false;">
              <div class="relative w-full md:w-64">
                <input
                  class="bg-slate-800/50 border border-slate-700 text-slate-200 w-full px-4 py-2 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                  type="search" id="buscarProcesos" placeholder="Buscar proceso..." aria-label="Buscar">
                <i class="bi bi-search absolute right-3 top-2.5 text-slate-400"></i>
              </div>
              <button
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors"
                type="submit">Buscar</button>
            </form>
          </div>

          <div class="flex justify-start">
            <button type="button" id="abrirModal" class="btn-modal flex items-center gap-2">
              <i class="bi bi-plus-lg"></i> Crear Proceso
            </button>
          </div>

          <!-- Processes Table -->
          <section class="tabla-procesos">
            <div class="glass-card p-0 overflow-hidden">
              <div class="overflow-x-auto">
                <table id="tablaProcesos" class="glass-table w-full text-center text-base text-slate-300 h-full">
                  <thead class="bg-slate-800/50 text-base text-center uppercase text-slate-400">
                    <tr>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Nombre Proceso</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Descripción</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Fecha Creación</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Documento</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Creador</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Gestionar</th>
                    </tr>
                  </thead>
                  <tbody id="tablaProcesosBody" class="divide-y divide-slate-700/50">
                    <!-- JS Injected Rows go here -->
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Paginación externa — JS la controla -->
            <div id="paginacionProcesos" class="flex justify-center mt-4">
              <nav class="flex items-center gap-x-1" aria-label="Paginación procesos">
                <button type="button" id="btnProcesoAnterior"
                  class="py-2 px-3 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition-colors disabled:opacity-30 disabled:pointer-events-none"
                  aria-label="PreviousProceso" disabled>
                  <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                  </svg>
                  <span>Anterior</span>
                </button>

                <div id="pagBotonesProcesos" class="flex items-center gap-x-1"></div>

                <button type="button" id="btnProcesoSiguiente"
                  class="py-2 px-3 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition-colors disabled:opacity-30 disabled:pointer-events-none"
                  aria-label="NextProceso" disabled>
                  <span>Siguiente</span>
                  <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6" />
                  </svg>
                </button>
              </nav>
            </div>

          </section>

        </div>
      </main>
    </div>
  </div>

  <!-- Custom Modal (Preserved Structure) -->
  <div id="modal" class="modal">
    <div class="contenido-modal">
      <h2 class="titulo-modal">Crear Proceso</h2>
      <div id="formProceso" class="formulario">

        <div class="mb-4">
          <input type="text" id="nombre-proceso" name="nombre-proceso" placeholder="Nombre de proceso"
            class="contenido glass-input">
        </div>

        <div class="mb-4">
          <textarea name="descripcion" id="descripcion" cols="30" rows="4" placeholder="Descripción"
            class="contenido glass-input"></textarea>
        </div>

        <div class="botones">
          <button type="button" id="btnRegistrarProceso" class="boton flex items-center gap-2">
            <i class="bi bi-save"></i> Crear Proceso
          </button>
          <button type="button" id="cerrar-modal" class="boton flex items-center gap-2">
            <i class="bi bi-x-lg"></i> Cerrar
          </button>
        </div>

      </div>
    </div>
  </div>
  <!-- Scripts: jQuery primero, luego DataTables, luego app -->
  <script src="/assets/js/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="/assets/js/logout.js"></script>
  <script src="/assets/js/registrarProceso.js"></script>
  <script src="/assets/js/procesos-modal.js"></script>

</body>

</html>