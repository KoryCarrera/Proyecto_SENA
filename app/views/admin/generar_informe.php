<?php require_once __DIR__ . "/../../controllers/checkSessionAdmin.php"; ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generación de Informe | Administrador</title>

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
    <link rel="stylesheet" href="/assets/css/generar_informe-admin.css">

    <script src="/assets/js/jquery-3.7.1.min.js"></script>

</head>

<<<<<<< HEAD
<body class="antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Decorative Background Elements -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/20 w-[500px] h-[500px]"></div>
        <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/20 w-[500px] h-[500px] animation-delay-2000"></div>
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay"></div>
=======
<body>
    <!--NavBar admin-->
    <div class="top-bar">
        <nav class="navbar m-0 p-0 bg-body-tertiary">
            <div class="container-fluid d-flex align-items-center justify-content-between">
                <img class="ms-3" src="/assets/img/logo_sena.png" alt="SENA" width="103" height="100">
                <div class="d-flex align-items-center">
                    <div class="text-end me-3">
                        <?php if (isset($_SESSION['user']['username'])): ?>
                            <h2 class='mb-0 d-none d-md-block'>
                                <?php echo $_SESSION['user']['username']; ?>
                            </h2>
                        <?php endif; ?>
                        <h4 class="mb-0 d-none d-md-block">Administrador</h4>
                    </div>
                    <a href="#">
                        <img src="/assets/img/icon account.png" alt="User" width="76" height="76">
                    </a>
                    <form action="/logout" method="POST">
                        <button type="submit" name="logout" value="logout">Cerrar Sesión</button>
                        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
                    </form>
                </div>
            </div>
        </nav>
>>>>>>> 14b1a0e7eeb1f674fa202f7107fe4806a4e2378f
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

        <a href="/generarInforme" class="nav-link active">
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
        
        <h2 class="text-xl font-semibold text-white tracking-tight">Generar Informe</h2>

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
        <div class="max-w-2xl mx-auto">
            
            <div class="glass-card p-8 md:p-10">
                <h2 class="text-2xl font-bold text-white text-center mb-6">Datos de Informe</h2>
                
<<<<<<< HEAD
                <div id="seccion1" class="form-section space-y-6">
                    
                    <div class="relative">
                        <i class="bi bi-file-earmark-arrow-down custom-icon text-indigo-400 absolute left-4 top-1/2 -translate-y-1/2 text-lg"></i>
                        <select class="glass-input w-full p-3 pl-12 rounded-lg text-white appearance-none cursor-pointer" id="formato">
                            <option selected disabled>Selecione el tipo de archivo</option>
                            <option value="1" class="bg-slate-800">PDF</option>
                            <option value="2" class="bg-slate-800">EXCEL</option>
=======
                 <li class="nav-item my-1">
          <a href="/procesoOrganizacional" class="nav-link text-none">
            <i class="bi bi-person-fill-gear usuarios"></i>
            <span>Procesos</span>
          </a>
        </li>

                <li class="nav-item my-1">
                    <a href="/usuarios" class="nav-link text-none">
                        <i class="bi bi-person-fill-gear usuarios"></i>
                        <span>Usuarios</span>
                    </a>
                </li>

                <li class="nav-item my-1">
                    <a href="/notificacionesAdmin" class="nav-link text-none">
                        <i class="bi bi-bell-fill notificacion"></i>
                        <span>Notificación</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>

    <!--Contenido de la pagina-->
    <div class="main-content">

        <div class="container mt-5">
            <h1 class="text-center mb-4">Generación de Informe</h1>
            <div class="custom-form-box mx-auto">
                <h2 class="text-center mb-4">Datos de Informe</h2>
                <div id="seccion1" class="form-section">
                    <div class="input-group mb-4 custom-input-group">
                        <span class="input-group-text custom-icon"><i class="bi bi-person-fill"></i></span>

                        <select class="form-select custom-input" id="formato">
                            <option selected selected value="">Selecione el tipo de archivo</option>
                            <option value="1">PDF</option>
                            <option value="2">EXCEL</option>
>>>>>>> 14b1a0e7eeb1f674fa202f7107fe4806a4e2378f
                        </select>
                        <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    </div>

                    <div class="relative">
                        <i class="bi bi-type-h1 custom-icon text-indigo-400 absolute left-4 top-1/2 -translate-y-1/2 text-lg"></i>
                        <input type="text" class="glass-input w-full p-3 pl-12 rounded-lg text-white placeholder-slate-400" placeholder="Titulo de la observación" id="titulo">
                    </div>

                    <div class="relative">
                        <textarea class="glass-input w-full p-3 rounded-lg text-white placeholder-slate-400 min-h-[120px]" placeholder="Contenido De La Observación/es" id="descripcion"></textarea>
                    </div>

                    <div class="relative">
                        <textarea class="glass-input w-full p-3 rounded-lg text-white placeholder-slate-400 min-h-[100px]" placeholder="Conclusiones Respectiva" id="conclusion"></textarea>
                    </div>

                    <button type="button" class="btn-siguiente w-full flex items-center justify-center gap-2 mt-4" id="informe">
                        <i class="bi bi-download"></i> DESCARGAR
                    </button>

                </div>
            </div>

        </div>
      </main>
    </div>
  </div>


<<<<<<< HEAD
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- JS propio (Keep order) -->
    <script src="/assets/js/cache.js"></script>
    <script src="/assets/js/generarInforme.js"></script>

=======
        <!--JS propio-->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="/assets/js/cache.js"></script>
        <script src="/assets/js/generarInforme.js"></script>
>>>>>>> 14b1a0e7eeb1f674fa202f7107fe4806a4e2378f
</body>

</html>