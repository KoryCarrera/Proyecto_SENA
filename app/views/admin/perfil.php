<?php require_once __DIR__ . "/../../controllers/checkSessionAdmin.php"; ?>
<!DOCTYPE html>
<html lang="es">

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
        <a href="/usuarios" class="nav-link">
          <i class="bi bi-person-fill-gear"></i>
          <span class="text-[10px] mt-1 font-medium">Usuarios</span>
        </a>
        <a href="/notificacionesAdmin" class="nav-link">
          <i class="bi bi-bell-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Notificación</span>
        </a>
        <a href="/perfilAdmin" class="nav-link active">
          <i class="bi bi-person-circle"></i>
          <span class="text-[10px] mt-1 font-medium">Mi Perfil</span>
        </a>
      </nav>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col ml-20 h-full">
      <!-- Top Bar -->
      <header class="h-20 glass-nav flex items-center justify-between px-6 sticky top-0 z-10">
        <h2 class="text-xl font-semibold text-white tracking-tight">Mi Perfil Administrativo</h2>
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
            <a href="/perfilAdmin" class="p-2 rounded-full hover:bg-white/5 transition-colors border border-indigo-500/30">
               <img src="/assets/img/icon account.png" alt="User" class="w-8 h-8 rounded-full border border-white/10">
            </a>
            <form action="/logout" method="POST">
              <button type="submit" name="logout" value="logout" class="text-xs font-medium text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 px-3 py-1.5 rounded-lg transition-colors border border-red-500/20">
                Cerrar Sesión
              </button>
            </form>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">
        <div class="max-w-4xl mx-auto">
          
          <div class="glass-card p-8 md:p-10">
            <div class="flex flex-col md:flex-row gap-10">
              
              <!-- Avatar Section -->
              <div class="flex flex-col items-center text-center space-y-4">
                <div class="relative group">
                  <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                  <img src="/assets/img/icon account.png" alt="Profile" class="relative w-32 h-32 rounded-full border-4 border-slate-900 object-cover shadow-2xl">
                  <button class="absolute bottom-0 right-0 p-2 bg-indigo-600 rounded-full text-white hover:bg-indigo-500 transition-colors shadow-lg">
                    <i class="bi bi-camera-fill"></i>
                  </button>
                </div>
                <div>
                  <h3 class="text-lg font-bold text-white"><?php echo $_SESSION['user']['username'] ?? 'Admin'; ?></h3>
                  <p class="text-xs text-indigo-400 font-medium tracking-widest uppercase">Admin General</p>
                </div>
              </div>

              <!-- Form Section -->
              <div class="flex-1">
                <form id="formPerfilAdmin" class="space-y-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-slate-300 mb-2">Documento </label>
                      <input type="text" name="documento" class="glass-input w-full p-3 rounded-lg text-white opacity-60" 
                             value="<?php echo $_SESSION['user']['documento'] ?? ''; ?>" readonly>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-slate-300 mb-2">Rol</label>
                      <input type="text" class="glass-input w-full p-3 rounded-lg text-white opacity-60" value="Administrador" readonly>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-slate-300 mb-2">Nombre</label>
                      <input type="text" name="nombre" class="glass-input w-full p-3 rounded-lg text-white" placeholder="Nombre completo">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-slate-300 mb-2">Apellido</label>
                      <input type="text" name="apellido" class="glass-input w-full p-3 rounded-lg text-white" placeholder="Apellidos completos">
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Email de Administración</label>
                    <input type="email" name="email" class="glass-input w-full p-3 rounded-lg text-white" placeholder="admin@sena.edu.co">
                  </div>

                  <div class="pt-4 border-t border-white/5">
                    <label class="block text-sm font-medium text-indigo-400 mb-4">Credenciales de Acceso</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Nueva Contraseña</label>
                        <input type="password" name="password" class="glass-input w-full p-3 rounded-lg text-white" placeholder="••••••••">
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Confirmar Contraseña</label>
                        <input type="password" name="confirm_password" class="glass-input w-full p-3 rounded-lg text-white" placeholder="••••••••">
                      </div>
                    </div>
                  </div>

                  <div class="flex justify-end pt-6">
                    <button type="button" class="btn-usuario px-10 py-3 rounded-xl font-bold flex items-center gap-2">
                       Actualizar Perfil
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

  <!--JS de bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
  <script src="/assets/js/cache.js"></script>

</body>

</html>
