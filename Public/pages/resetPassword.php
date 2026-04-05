<?php
// Atrapamos el token desde el match de AltoRouter
$token = $match['params']['token'] ?? '';

if (empty($token)) {
    header("Location: /login?error=token_invalido");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña | SENA</title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/2fa.css"> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/assets/js/jquery-3.7.1.min.js"></script>
</head>

<body class="antialiased selection:bg-indigo-500 selection:text-white flex items-center justify-center min-h-screen p-4">

    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/30 w-[500px] h-[500px]"></div>
        <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/30 w-[500px] h-[500px] animation-delay-2000"></div>
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay"></div>
    </div>

    <div class="relative z-10 w-full max-w-md animate-fade-in-up">
        <div class="glass-card px-8 py-10">

            <div class="flex flex-col items-center mb-8 text-center">
                <div class="w-16 h-16 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center mb-5">
                    <i class="bi bi-key-fill text-3xl text-indigo-400"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Nueva Contraseña</h1>
                <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
                    Estás a un paso de recuperar tu cuenta. Ingresa una contraseña segura.
                </p>
            </div>

            <form id="formResetPassword" novalidate>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="space-y-5">
                    <div>
                        <label class="block text-slate-400 text-xs font-medium mb-2 ml-1">NUEVA CONTRASEÑA</label>
                        <div class="relative">
                            <i class="bi bi-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                            <input type="password" name="password" id="password"
                                class="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-11 pr-4 text-white placeholder:text-slate-600 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-xs font-medium mb-2 ml-1">CONFIRMAR CONTRASEÑA</label>
                        <div class="relative">
                            <i class="bi bi-shield-check absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                            <input type="password" name="confirm_password" id="confirm_password"
                                class="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-11 pr-4 text-white placeholder:text-slate-600 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all"
                                placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <button type="submit" id="btnActualizar"
                    class="btn-verify w-full py-3 rounded-xl text-white font-semibold text-sm flex items-center justify-center gap-2 mt-8 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    <i class="bi bi-check-circle-fill"></i>
                    Actualizar Contraseña
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-white/5 text-center">
                <a href="/login" class="text-slate-500 hover:text-slate-300 text-xs transition-colors inline-flex items-center gap-1">
                    <i class="bi bi-arrow-left"></i>
                    Cancelar y volver
                </a>
            </div>
        </div>
    </div>

    <script src="/assets/js/resetPassword.js"></script>
</body>
</html>