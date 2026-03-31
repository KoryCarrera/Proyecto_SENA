<?php require_once __DIR__ . "/../../controllers/checkSessionAdmin.php"; ?>
<!-- se incluyen los archivos necesarios para procesar los datos y verificar que el usuario este logueado -->

<!DOCTYPE html>
<html lang="es">
<!-- se inicia el documento y le decimos el lenguaje y que tomara el meta tag para caracteres especiales -->

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Perfil | Administrador</title>

  <!--Icon de la pagina-->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <!-- Bootstrap CSS (Required for consistency and components) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!--CSS propio-->
  <link rel="stylesheet" href="/assets/css/home-admin.css">
  <script src="/assets/js/jquery-3.7.1.min.js"></script>
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
        <a href="/perfilAdmin" class="nav-link active">
          <i class="bi bi-person-circle"></i>
          <span class="text-[10px] mt-1 font-medium">Mi Perfil</span>
        </a>
      </nav>
    </aside>

    <!-- Contenedor principal -->
    <div class="flex-1 flex flex-col ml-20 h-full">
      <!-- Barra superior -->
      <header class="h-20 glass-nav flex items-center justify-between px-6 sticky top-0 z-10">
        <!-- titulo de la barra superior -->
        <h2 class="text-xl font-semibold text-white tracking-tight">Mi Perfil Administrativo</h2>
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
            <a href="/perfilAdmin"
              class="p-2 rounded-full hover:bg-white/5 transition-colors border border-indigo-500/30">
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

      <!-- Contenido interno de la pagina, donde se muestra el formulario y los datos de perfil del usuario -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">
        <div class="max-w-4xl mx-auto">

          <!-- formulario y datos del usuario -->
          <div class="glass-card p-8 md:p-10">
            <div class="flex flex-col md:flex-row gap-10">

              <!-- Seccion de la foto de perfil del usuario -->
              <div class="flex flex-col items-center text-center space-y-4">
                <div class="relative group">
                  <div
                    class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200">
                  </div>
                  <img src="/assets/img/icon account.png" alt="Profile"
                    class="relative w-32 h-32 rounded-full border-4 border-slate-900 object-cover shadow-2xl">
                </div>
                <div>
                  <h3 class="text-lg font-bold text-white"><?php echo $_SESSION['user']['username'] ?? 'Admin'; ?></h3>
                  <p class="text-xs text-indigo-400 font-medium tracking-widest uppercase">Admin General</p>
                </div>
              </div>

              <!-- Formulario de edicion de perfil de usuario -->
              <div class="flex-1">
                <form id="formPerfil" class="space-y-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- input no editable del documento de identidad -->
                    <div>
                      <label class="block text-sm font-medium text-slate-300 mb-2">Documento de Identidad</label>
                      <input type="text" name="documento"
                        class="glass-input w-full p-3 rounded-lg text-white opacity-60"
                        value="<?php echo $_SESSION['user']['documento'] ?? ''; ?>" readonly>
                      <span class="text-[10px] text-slate-500 mt-1 block">No editable por seguridad</span>
                    </div>
                    <!-- input no editable del rol del usuario -->
                    <div>
                      <label class="block text-sm font-medium text-slate-300 mb-2">Cargo / Rol</label>
                      <input type="text" class="glass-input w-full p-3 rounded-lg text-white opacity-60"
                        value="Administrador" readonly>
                      <span class="text-[10px] text-slate-500 mt-1 block">No editable por seguridad</span>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- input para el nombre del usuario -->
                    <div>
                      <label class="block text-sm font-medium text-slate-300 mb-2">Nombre(s)</label>
                      <input type="text" name="nombre" class="glass-input w-full p-3 rounded-lg text-white"
                        placeholder="Ingresa tu nuevo nombre" id="nuevoNombre">
                    </div>
                    <!-- input para el apellido del usuario -->
                    <div>
                      <label class="block text-sm font-medium text-slate-300 mb-2">Apellido(s)</label>
                      <input type="text" name="apellido" class="glass-input w-full p-3 rounded-lg text-white"
                        placeholder="Ingresa tu nuevo apellido" id="nuevoApellido">
                    </div>
                  </div>

                  <!-- input para el telefono de celular del usuario -->
                  <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Numero de celular</label>
                    <input type="number" name="numero" class="glass-input w-full p-3 rounded-lg text-white"
                      placeholder="Ejemplo: 3101234567" id="numeroNuevo">
                  </div>

                  <!-- input para el correo electronico del usuario -->
                  <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Correo Electrónico</label>
                    <input type="email" name="email" class="glass-input w-full p-3 rounded-lg text-white"
                      placeholder="ejemplo@sena.edu.co" id="nuevoEmail">
                  </div>

                  <!-- seccion de edicion de contraseñas -->
                  <div class="pt-4 border-t border-white/5">
                    <label class="block text-sm font-medium text-indigo-400 mb-4">Seguridad</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <!-- input de nueva contraseña -->
                      <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Nueva Contraseña</label>
                        <input type="password" name="password" class="glass-input w-full p-3 rounded-lg text-white"
                          placeholder="••••••••" id="contrasenaNueva">
                      </div>
                      <!-- input de confirmar nueva contraseña -->
                      <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Confirmar Contraseña</label>
                        <input type="password" name="confirm_password"
                          class="glass-input w-full p-3 rounded-lg text-white" placeholder="••••••••"
                          id="confirmarContrasena">
                      </div>
                    </div>
                  </div>

                  <div class="flex justify-between pt-6">
                    <!-- boton de activar 2FA -->
                    <div class="flex items-center">
                      <input type="checkbox" id="activar2FA" class="hidden peer" autocomplete="off">
                      <label for="activar2FA" class="cursor-pointer select-none flex items-center gap-2 px-6 py-3 rounded-xl font-bold bg-white/5 border border-white/10 text-slate-400 hover:bg-white/10 transition-all peer-checked:bg-indigo-600/20 peer-checked:text-indigo-400 peer-checked:border-indigo-500/40 peer-checked:shadow-[0_0_15px_rgba(99,102,241,0.2)]">
                        <i class="bi bi-shield-check text-lg"></i>
                        <span> Verificación en dos pasos</span>
                      </label>
                    </div>

                    <!-- boton de actualizar perfil -->
                    <button type="button" class="btn-search px-10 py-3 rounded-xl font-bold flex items-center gap-2"
                      id="btnActualizar">
                      <i class="bi bi-save2-fill"></i> Actualizar
                    </button>
                  </div>
                </form>
              </div>

            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- script requeridos para la pagina -->
  <!--JS de bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
  <!-- script de perfilUsuario(script propio) -->
  <script src="/assets/js/perfilUsuario.js"></script>
  <!-- script de logout(script propio) -->
  <script src="/assets/js/logout.js"></script>
  <!-- script de cache(script propio) -->
  <script src="/assets/js/cache.js"></script>
  <!-- script de inactividad(script propio) -->
  <script src="/assets/js/inactividad.js"></script>

</body>

</html>