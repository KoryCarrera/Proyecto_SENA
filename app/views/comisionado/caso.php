<?php require_once __DIR__ . "/../../controllers/checkSessionComi.php"; ?>
<!-- se incluye el archivo de sesion para verificar que el usuario este logueado -->

<!--comienzo del documento y la vista de casos de comisionado -->
<!DOCTYPE html>
<!-- seleccionamos el lenguaje-->
<html lang="es"> 
   <!--encabezado de la pagina-->
<head>
  <!-- seleccionamos el utf-8 para poder tener caracteres especiales -->
  <meta charset="UTF-8">
  <!-- seleccionamos el viewport para que la pagina sea responsiva -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- titulo de la pagina -->
  <title>Casos | Comisionado</title>

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
  <link rel="stylesheet" href="/assets/css/casos-comisionado.css">
  <script src="/assets/js/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"> <!-- DataTables CSS -->

</head>
<!-- cuerpo de la pagina -->
<body class="antialiased selection:bg-indigo-500 selection:text-white">

  <!-- fondo de la pagina -->
  <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
    <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/20 w-[500px] h-[500px]"></div>
    <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/20 w-[500px] h-[500px] animation-delay-2000"></div>
    <!-- gradiente del fondo extraido de vercel -->
    <div
      class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay">
    </div>
  </div>

  <!-- contenedor principal -->
  <div class="flex h-screen overflow-hidden relative z-10">

    <!-- cuerpo de la sidebar -->
    <aside
      class="glass-sidebar w-20 hover:w-64 transition-all duration-300 ease-in-out flex flex-col group fixed h-full z-50">

      <!-- logo de la sidebar -->
      <div class="h-20 flex items-center justify-center border-b border-white/5">
        <img src="/assets/img/logo_sena.png" alt="SENA" class="w-10 h-10 object-contain group-hover:block">
      </div>

      <!-- cuerpo de la sidebar -->

      <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">

        <!-- enlace de la pagina de inicio -->

        <a href="/dashboardComi" class="nav-link">
          <i class="bi bi-house-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Inicio</span>
        </a>

        <!-- enlace de la pagina de registrar casos -->

        <a href="/registrarCasos" class="nav-link">
          <i class="bi bi-file-earmark-person-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Registrar Caso</span>
        </a>

        <!-- enlace de la pagina de casos -->

        <a href="/casos" class="nav-link active">
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

    <!--  -->
    <div class="flex-1 flex flex-col ml-20 h-full">

      <!-- top bar -->
      <header class="h-20 glass-nav flex items-center justify-between px-6 sticky top-0 z-10">

        <!-- titulo de la pagina -->

        <h2 class="text-xl font-semibold text-white tracking-tight">Seguimiento de Casos</h2>

        <!-- se toma el nombre del usuario y se muestra en la top -->
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

      <!-- esta es la barra de filtro de cantidades de Datatables con nuestro id y estilos propios -->
      <div class="px-6 py-4 glass-nav z-30 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="flex items-center gap-2 w-full md:w-auto">
          <div class="relative">
            <label class="text-slate-400 text-xs uppercase font-bold mr-2">Ver:</label>
            <select id="filtroCantidadComi"
              class="bg-slate-800/50 border border-slate-700 text-slate-200 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 outline-none cursor-pointer hover:bg-slate-700/50 transition-colors">
              <!-- aqui esta el select para filtrar la cantidad de casos -->
              <option class="px-6 py-4 font-medium tracking-wider" value="10">10 casos</option>
              <option class="px-6 py-4 font-medium tracking-wider" value="25">25 casos</option>
              <option class="px-6 py-4 font-medium tracking-wider" value="50">50 casos</option>
              <option class="px-6 py-4 font-medium tracking-wider" value="100">100 casos</option>
            </select>
          </div>
        </div>

        <!-- esta es la barra de busqueda de Datatables con nuestro id y estilos propios -->

        <form class="flex gap-2 w-full md:w-auto" role="search">
          <div class="relative w-full md:w-64">
            <input
              class="glass-search w-full px-4 py-2 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
              type="search" id="buscarComi" placeholder="Buscar palabras clave..." aria-label="Search">
            <i class="bi bi-search absolute right-3 top-2.5 text-slate-400"></i>
          </div>
         
          
        </form>
      </div>

      <!-- este es el main de la pagina de casos, especialmente en la que esta la tabla de casos -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">
        <div class="max-w-full mx-auto ">

          <!-- esta es la tabla de casos -->
          <section>
            <div class="glass-card p-0 overflow-hidden">
              <div class="overflow-x-auto">
                <table id="tablaCasoComi" class="glass-table w-full text-center text-base text-slate-300 h-full">
                  <thead class="bg-slate-800/50 text-base text-center uppercase text-slate-400">
                    <tr>
                      <!-- aqui estan los encabezados de la tabla -->

                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Id de caso</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Fecha de Registro</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Tipo de Caso</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Fecha de respuesta</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Estado</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Proceso</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Comisionado Encargado</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Gestionar</th>
                    </tr>
                  </thead>
                  <tbody class="cont-tabla divide-y divide-slate-700/50" id="tablaCasos">

                    <!-- aqui se insertan las filas de la tabla por medio del javascript -->

                  </tbody>
                </table>
              </div>
            </div>
          </section>

          <!-- Paginación externa — JS la controla dinámicamente -->
          <div id="paginacionCasosComi" class="flex justify-center mt-4">
            <nav class="flex items-center gap-x-1" aria-label="Pagination">

              <!-- Botón Anterior -->
              <button type="button" id="btnPaginaAnteriorComi"
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
              <div id="pagBotonesComi" class="flex items-center gap-x-1"></div>

              <!-- Botón Siguiente -->
              <button type="button" id="btnPaginaSiguienteComi"
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

  <!-- Modal Detalles del Caso -->
  <div id="modalCaso" class="modal">
    <div class="contenido-modal">
      <div class="titulo-modal" id="modalCasoLabel">Gestionar Caso</div>

      <form id="formGestionarCaso">
        <div class="modal-body p-0" id="modalCasoBody">
          <!-- Contenido dinámico via JS -->
        </div>
        <div class="div-boton-tabla mb-4 px-3">

        </div>
        <div class="botones">
          <button type="button" id="cerrar-modal" class="boton bg-slate-600 hover:bg-slate-700">
            <i class="bi bi-x-lg"></i> Cerrar
          </button>
          <button type="submit" class="boton flex items-center gap-2" id="guardarCambios">
            <i class="bi bi-check-circle"></i> Guardar Cambios
          </button>
        </div>
      </form>
    </div>
  </div>

  <!--JS de bootstrap-->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
  <!--JS Propio-->
  <script src="/assets/js/cache.js"></script>
  <script src="/assets/js/casosComi.js"></script>
  <script src="/assets/js/logout.js"></script>
  <script src="/assets/js/inactividad.js"></script>

</body>

</html>