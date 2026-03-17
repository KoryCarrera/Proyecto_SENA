<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verificación de Dos Pasos | SENA</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!-- CSS propio 2FA -->
  <link rel="stylesheet" href="/assets/css/2fa.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="/assets/js/jquery-3.7.1.min.js"></script>
</head>

<body class="antialiased selection:bg-indigo-500 selection:text-white flex items-center justify-center min-h-screen p-4">

  <!-- Fondo decorativo (igual que el resto de vistas) -->
  <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
    <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/30 w-[500px] h-[500px]"></div>
    <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/30 w-[500px] h-[500px] animation-delay-2000"></div>
    <div class="blob-bg top-[40%] left-[30%] bg-cyan-500/10 w-[400px] h-[400px]" style="animation-delay:4s"></div>
    <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay"></div>
  </div>

  <!-- Contenedor principal -->
  <div class="relative z-10 w-full max-w-md animate-fade-in-up">

    <!-- Card glassmorphism -->
    <div class="glass-card px-8 py-10">

      <!-- Encabezado -->
      <div class="flex flex-col items-center mb-8 text-center">
        <div class="w-16 h-16 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center mb-5">
          <i class="bi bi-shield-lock-fill text-3xl text-indigo-400"></i>
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">Verificación en dos pasos</h1>
        <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
          Ingresa el código de 6 dígitos que enviamos a tu correo electrónico registrado.
        </p>
      </div>

      <!-- Alerta de error -->
      <div id="alertaError" class="alerta-error" role="alert">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
        <span id="mensajeError">Código incorrecto. Inténtalo de nuevo.</span>
      </div>

      <!-- Formulario 2FA -->
      <form id="form2FA" method="POST" action="/verificar2fa" novalidate>

        <!-- Campos OTP -->
        <div class="flex justify-center gap-3 mb-8">
          <input type="text" class="otp-input" id="otp1" name="otp[]" maxlength="1"
            inputmode="text" pattern="[A-Za-z0-9]*" autocomplete="one-time-code" aria-label="Dígito 1">
          <input type="text" class="otp-input" id="otp2" name="otp[]" maxlength="1"
            inputmode="text" pattern="[A-Za-z0-9]*" aria-label="Dígito 2">
          <input type="text" class="otp-input" id="otp3" name="otp[]" maxlength="1"
            inputmode="text" pattern="[A-Za-z0-9]*" aria-label="Dígito 3">
          <input type="text" class="otp-input" id="otp4" name="otp[]" maxlength="1"
            inputmode="text" pattern="[A-Za-z0-9]*" aria-label="Dígito 4">
          <input type="text" class="otp-input" id="otp5" name="otp[]" maxlength="1"
            inputmode="text" pattern="[A-Za-z0-9]*" aria-label="Dígito 5">
          <input type="text" class="otp-input" id="otp6" name="otp[]" maxlength="1"
            inputmode="text" pattern="[A-Za-z0-9]*" aria-label="Dígito 6">
        </div>

        <!-- Campo oculto con el código completo -->
        <input type="hidden" name="codigo_2fa" id="codigo_2fa">

        <!-- Botón verificar -->
        <button type="submit" id="btnVerificar"
          class="btn-verify w-full py-3 rounded-xl text-white font-semibold text-sm flex items-center justify-center gap-2 mb-6">
          <i class="bi bi-shield-check text-base"></i>
          Verificar Código
        </button>

        <!-- Reenviar código -->
        <div class="text-center">
          <p class="text-slate-500 text-xs mb-2">¿No recibiste el código?</p>
          <button type="button" id="btnReenviar"
            class="text-indigo-400 hover:text-indigo-300 text-sm font-medium transition-colors
                   disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none"
            disabled>
            Reenviar código
            <span id="countdownText" class="text-slate-500 font-normal ml-1">
              (en <span id="countdown">30</span>s)
            </span>
          </button>
        </div>

      </form>

      <!-- Volver al login -->
      <div class="mt-8 pt-6 border-t border-white/5 text-center">
        <a href="/login"
          class="text-slate-500 hover:text-slate-300 text-xs transition-colors inline-flex items-center gap-1">
          <i class="bi bi-arrow-left"></i>
          Volver al inicio de sesión
        </a>
      </div>

      <!-- 🔗 Enlace temporal de prueba -->
      <div class="mt-4 text-center">
        <a href="nosotros.php"
          class="text-indigo-400 hover:text-indigo-300 text-xs transition-colors inline-flex items-center gap-1 border border-indigo-500/20 rounded-lg px-3 py-1.5 bg-indigo-500/10 hover:bg-indigo-500/20">
          <i class="bi bi-arrow-right-circle"></i>
          Ir a Nosotros (temp)
        </a>
      </div>

    </div>

    <!-- Pie de página -->
    <p class="text-center text-slate-600 text-xs mt-5 flex items-center justify-center gap-1">
      <i class="bi bi-lock-fill"></i>
      Conexión segura &bull; Sistema de Gestión SENA
    </p>

  </div>

  <script src="/assets/js/2FA.js"></script>

</body>

</html>