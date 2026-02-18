<?php
require_once __DIR__ . "/../../controllers/checkSessionAdmin.php";
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestionar Cuentas de Usuario</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <!-- Bootstrap CSS (Required for JS compatibility with Modals) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!--CSS propio-->
  <link rel="stylesheet" href="/assets/css/crear-usuario.css">

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

        <a href="/procesoOrganizacional" class="nav-link">
          <i class="bi bi-diagram-3-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Procesos</span>
        </a>

        <a href="/usuarios" class="nav-link active">
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

        <h2 class="text-xl font-semibold text-white tracking-tight">Gestión de Usuarios</h2>

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
        <div class="max-w-7xl mx-auto space-y-8">

          <!-- Botón para abrir el modal de crear usuario -->
          <div class="flex justify-end">
            <button type="button" id="abrirModalCrear" class="btn-usuario flex items-center gap-2">
              <i class="bi bi-person-plus-fill"></i> Crear Nuevo Usuario
            </button>
          </div>

          <!-- Users Table -->
          <section class="tabla-usuarios">
            <div class="glass-card p-0 overflow-hidden">
              <div class="overflow-x-auto">
                <table class="glass-table w-full text-left text-sm text-slate-300">
                  <thead class="bg-slate-800/50 text-xs uppercase text-slate-400">
                    <tr>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Documento</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Nombre</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Apellido</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Correo</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Estado</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider">Rol</th>
                      <th scope="col" class="px-6 py-4 font-medium tracking-wider text-right">Gestionar</th>
                    </tr>
                  </thead>
                  <tbody id="tablaUsuarios" class="divide-y divide-slate-700/50">
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

  <!-- Modal Wrapper -->
  <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content glass-card border-slate-700">
        <div class="modal-header border-slate-700 p-6">
          <h5 class="modal-title text-xl font-bold text-white flex items-center gap-2" id="modalUsuarioLabel">
            <i class="bi bi-person-badge text-indigo-400"></i> Detalles del Usuario
          </h5>
          <button type="button" class="btn-close btn-close-white opacity-50 hover:opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-6 text-slate-300" id="modalUsuarioBody">
          <!-- El contenido se carga dinámicamente con JavaScript -->
        </div>
        <div class="modal-footer border-slate-700 p-6 bg-slate-900/20" id="modalFooter">
          <button type="button" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors text-sm font-medium" data-bs-dismiss="modal">Cerrar</button>

          <button type="button" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors text-sm font-medium ml-2" onclick="habilitarEdicion()">
            <i class="bi bi-pencil"></i> Editar Usuario
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Crear Usuario (Custom Modal) -->
  <div id="modalCrearUsuario" class="modal">
    <div class="contenido-modal">
      <h2 class="titulo-modal">Crear Nuevo Usuario</h2>
      <form id="formCrearUsuario" class="formulario-crear">

        <div class="mb-4">
          <select id="crearRol" name="rol" required class="contenido glass-input">
            <option value="" disabled selected>Escoge un rol</option>
            <option value="1" class="bg-slate-800">Administrador</option>
            <option value="2" class="bg-slate-800">Comisionado</option>
          </select>
        </div>

        <div class="mb-4">
          <input type="text" id="crearNombre" name="nombre" required placeholder="Nombre" class="contenido glass-input">
        </div>

        <div class="mb-4">
          <input type="text" id="crearApellido" name="apellido" required placeholder="Apellido" class="contenido glass-input">
        </div>

        <div class="mb-4">
          <input type="text" id="crearDocumento" name="documento" required placeholder="Documento" class="contenido glass-input">
        </div>

        <div class="mb-4">
          <input type="email" id="crearEmail" name="email" required placeholder="Correo Electrónico" class="contenido glass-input">
        </div>

        <div class="mb-4">
          <input type="password" id="crearContrasena" name="contrasena" required placeholder="Contraseña" class="contenido glass-input">
        </div>

        <div class="botones">
          <button type="submit" id="guardar-modal-crear" class="boton flex items-center gap-2">
            <i class="bi bi-person-plus-fill"></i> Crear Usuario
          </button>
          <button type="button" id="cerrar-modal-crear" class="boton flex items-center gap-2">
            <i class="bi bi-x-lg"></i> Cerrar
          </button>
        </div>

      </form>
    </div>
  </div>

  <!-- Hidden anchor tag preservation from original file -->
  <a href="/editarUsuario" style="display:none;">a</a>

  <!-- Bootstrap Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script src="/assets/js/usuariosAdmin.js"></script>
  <script src="/assets/js/modal_usuarios_Admin.js"></script>
  <script src="/assets/js/cache.js"></script>

</body>

</html>