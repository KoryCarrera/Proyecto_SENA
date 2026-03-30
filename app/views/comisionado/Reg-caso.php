<?php
require_once __DIR__ . "/../../controllers/checkSessionComi.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../models/insertData.php";
?>
<!-- se incluye el archivo de sesion para verificar que el usuario este logueado-->
<!-- tamvien esta el archivo insertData.php que se encarga de insertar los datos en la base de datos y el config de conexion-->

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Caso | Comisionado</title>

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
  <link rel="stylesheet" href="/assets/css/com-reg-caso.css">
  <script src="/assets/js/jquery-3.7.1.min.js"></script>

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

    <!-- barra lateral -->
    <aside
      class="glass-sidebar w-20 hover:w-64 transition-all duration-300 ease-in-out flex flex-col group fixed h-full z-50">

      <!-- logo de la barra lateral -->
      <div class="h-20 flex items-center justify-center border-b border-white/5">
        <img src="/assets/img/logo_sena.png" alt="SENA" class="w-10 h-10 object-contain group-hover:block">
      </div>

      <!-- barra de navegacion de la barra lateral -->
      <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">

        <!-- enlace de la pagina de inicio -->
        <a href="/dashboardComi" class="nav-link">
          <i class="bi bi-house-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Inicio</span>
        </a>

        <!-- enlace de la pagina de registrar casos -->
        <a href="/registrarCasos" class="nav-link active">
          <i class="bi bi-file-earmark-person-fill"></i>
          <span class="text-[10px] mt-1 font-medium">Registrar Caso</span>
        </a>

        <!-- enlace de la pagina de casos -->
        <a href="/casos" class="nav-link">
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

    <!-- contenedor principal -->
    <div class="flex-1 flex flex-col ml-20 h-full">

      <!-- barra superior -->
      <header class="h-20 glass-nav flex items-center justify-between px-6 sticky top-0 z-40">

        <!--aqui se muestra el titulo de la pagina -->
        <h2 class="text-xl font-semibold text-white tracking-tight">Registrar Caso</h2>

        <!--aqui se muestra el nombre del usuario y su rol -->
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

      <!-- Contenido principal -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 animate-fade-in-up">
        <div class="max-w-2xl mx-auto">

          <!--aqui se muestra el formulario de registro -->
          <div class="glass-card p-8 relative overflow-hidden">
            <div
              class="absolute -right-10 -top-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none">
            </div>

            <h1 class="text-2xl font-bold text-white mb-2 text-center">Registro de Caso</h1>
            <p class="text-slate-400 text-sm text-center mb-8">Información del Caso</p>

            <div id="registroForm">
              <div id="seccion1" class="form-section space-y-6">

                <!-- Nombre del Caso -->
                <div>
                  <label for="nombreCaso" class="form-label text-sm font-semibold text-slate-300 mb-2 block">
                    <i class="bi bi-card-heading me-1"></i> Nombre del Caso
                    <span class="text-red-400">*</span>
                  </label>
                  <div class="input-group custom-input-group">
                    <span class="input-group-text custom-icon">
                      <i class="bi bi-fonts"></i>
                    </span>
                    <input type="text" name="nombreCaso" id="nombreCaso" class="form-control custom-input"
                      placeholder="Ej: Queja por ruido en salones" maxlength="255" required>
                  </div>
                  <small class="text-slate-500 text-xs mt-1 block">
                    <i class="bi bi-info-circle-fill"></i> Máximo 255 caracteres
                  </small>
                </div>

                <!-- Radicado -->
                <div>
                  <label for="radicado" class="form-label text-sm font-semibold text-slate-300 mb-2 block">
                    <i class="bi bi-hash me-1"></i> Radicado
                  </label>
                  <div class="input-group custom-input-group">
                    <span class="input-group-text custom-icon">
                      <i class="bi bi-123"></i>
                    </span>
                    <input type="text" name="radicado" id="radicado" class="form-control custom-input"
                      placeholder="Ej: ASH13854" maxlength="50" required>
                  </div>
                  <small class="text-slate-500 text-xs mt-1 block">
                    <i class="bi bi-info-circle-fill"></i> Máximo 50 caracteres
                  </small>
                </div>
                <!-- Proceso Organizacional -->
                <div>
                  <label for="proceso" class="form-label text-sm font-semibold text-slate-300 mb-2 block">
                    <i class="bi bi-hash me-1"></i> Proceso Organizacional
                    <span class="text-red-400">*</span>
                  </label>
                  <div class="input-group custom-input-group">
                    <span class="input-group-text custom-icon">
                      <i class="bi bi-diagram-3-fill"></i>
                    </span>
                    <select name="proceso" id="proceso" class="form-select custom-input" required>
                      <option selected disabled value="">Cargando procesos...</option>
                    </select>
                  </div>
                </div>

                <!-- Tipo de Solicitud -->
                <div>
                  <label for="tipoCaso" class="form-label text-sm font-semibold text-slate-300 mb-2 block">
                    <i class="bi bi-list-task me-1"></i> Tipo de Solicitudes
                    <span class="text-red-400">*</span>
                  </label>
                  <div class="input-group custom-input-group">
                    <span class="input-group-text custom-icon">
                      <i class="bi bi-list-task"></i>
                    </span>
                    <select name="tipo" class="form-select custom-input" id="tipoCaso" required>
                      <option selected disabled value="">Cargando tipos...</option>
                    </select>
                  </div>
                </div>

                <!-- Descripción -->
                <div>
                  <label for="descripcion" class="form-label text-sm font-semibold text-slate-300 mb-2 block">
                    <i class="bi bi-file-text me-1"></i> Descripción Detallada
                    <span class="text-red-400">*</span>
                  </label>
                  <div class="input-group custom-input-group">
                    <textarea name="descripcion" id="descripcion" class="form-control custom-input" rows="5"
                      placeholder="Describa los hechos del caso de manera detallada..." maxlength="2000"
                      required></textarea>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-slate-500 text-xs">
                      <i class="bi bi-info-circle"></i> Sea lo más específico posible
                    </small>
                    <small class="text-slate-500 text-xs">
                      <span id="contadorCaracteres">0</span> / 2000 caracteres
                    </small>
                  </div>
                </div>

                <!-- Archivos Adjuntos -->
                <div>
                  <label for="archivos" class="form-label text-sm font-semibold text-slate-300 mb-2 block">
                    <i class="bi bi-paperclip me-1"></i> Archivos Adjuntos
                    <span class="badge bg-indigo-500/20 text-indigo-300 ms-2 text-xs">Opcional</span>
                  </label>
                  <div class="input-group custom-input-group">
                    <span class="input-group-text custom-icon">
                      <i class="bi bi-file-earmark-arrow-up"></i>
                    </span>
                    <input type="file" name="archivos[]" id="archivos" class="form-control custom-input" multiple
                      accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                  </div>

                  <!--aqui hay un mensaje en el que dice la cantidad de archivos que se pueden subir-->
                  <small class="text-slate-500 text-xs mt-1 block">
                    <i class="bi bi-exclamation-triangle"></i>
                    Máximo 3 archivos. Formatos permitidos: imágenes, videos, PDF, Word, Excel
                  </small>

                  <!-- Vista previa de archivos seleccionados -->
                  <div id="vistaArchivos" class="mt-3"></div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                  <button type="button" id="btnRegistrarcaso"
                    class="btn-siguiente w-full bg-indigo-500 hover:bg-indigo-600 px-8 py-3 rounded-xl">
                    <i class="bi bi-send-fill me-2 "></i> ENVIAR REGISTRO
                  </button>
                </div>

              </div>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="/assets/js/logout.js"></script>
  <script src="assets/js/registrarCaso.js"></script>
  <script src="assets/js/cache.js"></script>

</body>

</html>