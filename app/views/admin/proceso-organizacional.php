<?php require_once __DIR__ . "/../../controllers/checkSessionAdmin.php"; ?>
<!-- se incluyen los archivos necesarios para procesar los datos y verificar que el usuario este logueado -->

<!DOCTYPE html>
<html lang="es">
<!-- se inicia el documento y le decimos el lenguaje y que tomara el meta tag para caracteres especiales -->

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

<!-- se inicia el body del documento -->
<body class="antialiased selection:bg-indigo-500 selection:text-white">

  <!-- background de la vista -->
  <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
    <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/20 w-[500px] h-[500px]"></div>
    <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/20 w-[500px] h-[500px] animation-delay-2000"></div>
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
        <a href="/procesoOrganizacional" class="nav-link active">
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
        <h2 class="text-xl font-semibold text-white tracking-tight">Proceso Organizacional</h2>

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

      <!-- Contenido interno de la pagina, donde se muestra la tabla de procesos -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">
        <div class="max-w-full mx-auto space-y-6">

          <!-- Barra de filtros -->
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
<!-- este es el buscador de la pagina utilizado con datatables -->
            <form class="flex gap-2 w-full md:w-auto" role="search" onsubmit="return false;">
              <div class="relative w-full md:w-64">
                <input
                  class="bg-slate-800/50 border border-slate-700 text-slate-200 w-full px-4 py-2 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                  type="search" id="buscarProcesos" placeholder="Buscar proceso..." aria-label="Buscar">
                <i class="bi bi-search absolute right-3 top-2.5 text-slate-400"></i>
              </div>
            </form>
          </div>

          <!-- Botón para abrir el modal de crear proceso -->
          <div class="flex justify-start">
            <button type="button" id="abrirModal" class="btn-modal flex items-center gap-2">
              <i class="bi bi-plus-lg"></i> Crear Proceso
            </button>
          </div>

          <!-- Tabla de procesos -->
          <section class="tabla-procesos">
            <div class="glass-card p-0 overflow-hidden">
              <div class="overflow-x-auto">
                <!-- se crea la tabla de procesos con sus cabeceras -->
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
                    <!-- se  inyecta la informacion con js -->
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Paginación externa — JS la controla -->
            <div id="paginacionProcesos" class="flex justify-center mt-4">
              <nav class="flex items-center gap-x-1" aria-label="Paginación procesos">
                <!-- boton de anterior -->
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
                <!-- boton de siguiente --> 
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

  <!-- Modal Crear Proceso (Custom Modal) -->
  <div id="modal" class="modal">
    <div class="contenido-modal">
      <h2 class="titulo-modal">Crear Proceso</h2>
      <div id="formProceso" class="formulario">

        <!-- input para el nombre del nuevo proceso -->
        <div class="mb-4">
          <input type="text" id="nombre-proceso" name="nombre-proceso" placeholder="Nombre de proceso"
            class="contenido glass-input">
        </div>

        <!-- textarea para la descripcion del nuevo proceso -->
        <div class="mb-4">
          <textarea name="descripcion" id="descripcion" cols="30" rows="4" placeholder="Descripción"
            class="contenido glass-input"></textarea>
        </div>

        <div class="botones">
          <!-- boton para guardar el proceso -->
          <button type="button" id="btnRegistrarProceso" class="boton flex items-center gap-2">
            <i class="bi bi-save"></i> Crear Proceso
          </button>
          <!-- boton para cerrar el modal -->
          <button type="button" id="cerrar-modal" class="boton flex items-center gap-2">
            <i class="bi bi-x-lg"></i> Cerrar
          </button>
        </div>

      </div>
    </div>
  </div>
  <!-- script requeridos para la pagina  -->
  <script src="/assets/js/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- script de logout(script propio) -->
  <script src="/assets/js/logout.js"></script>
  <!-- script de registrarProceso(script propio) -->
  <script src="/assets/js/registrarProceso.js"></script>
  <!-- script de procesos-modal(script propio) -->
  <script src="/assets/js/procesos-modal.js"></script>
  <!-- script de inactividad(script propio) -->
  <script src="/assets/js/inactividad.js"></script>

</body>

</html>