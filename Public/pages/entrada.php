<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$token = $_SESSION['csrf_token'];

?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="/assets/js/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="/assets/css/login.css">
</head>

<body class="antialiased selection:bg-indigo-500 selection:text-white relative">

    <!-- Decorative Background Elements -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <!-- Top Left - Indigo -->
        <div class="blob-bg top-[-10%] left-[-20%] bg-indigo-500/30 w-[600px] h-[600px] blur-[100px]"></div>

        <!-- Bottom Right - Purple -->
        <div class="blob-bg bottom-[-10%] right-[-20%] bg-purple-500/30 w-[600px] h-[600px] blur-[100px] animation-delay-2000"></div>

        <!-- Mid Left - Pink/Rose -->
        <div class="blob-bg top-[40%] left-[-10%] bg-pink-500/25 w-[400px] h-[400px] blur-[90px] animation-delay-4000"></div>

        <!-- Top Right - Cyan/Blue -->
        <div class="blob-bg top-[-5%] right-[-10%] bg-cyan-500/25 w-[400px] h-[400px] blur-[90px] animation-delay-3000"></div>

        <!-- Noise Overlay -->
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay"></div>
    </div>

    <!-- Login Container -->
    <div class="w-full max-w-md mx-auto p-6 relative z-10 animate-[fadeInUp_0.6s_ease-out_forwards]">

        <!-- Back Link -->
        <a href="/" class="inline-flex items-center text-sm text-slate-400 hover:text-white transition-colors mb-8 group">
            <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver al inicio
        </a>

        <div class="glass-card rounded-[2rem] p-8 md:p-12 border border-slate-700/50 relative overflow-hidden group hover:border-indigo-500/30 transition-colors duration-500">

            <!-- Subtle internal glow -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none group-hover:bg-indigo-500/20 transition-all duration-500"></div>

            <div class="text-center mb-10 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-500/30 mx-auto mb-4">
                    S
                </div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Bienvenido de nuevo</h2>
                <p class="text-slate-400 mt-2 text-sm">Ingresa tus credenciales para acceder</p>
            </div>

            <div class="space-y-6">
                <div>
                    <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
                    <label for="documento" class="block text-sm font-medium text-slate-300 mb-2">Documento</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                            </svg>
                        </div>
                        <input type="number" id="documento" name="documento" required
                            class="glass-input w-full rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-400 focus:outline-none focus:ring-0 transition-all duration-200 spin-none"
                            placeholder="Ej. 1234567890">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="glass-input w-full rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-400 focus:outline-none focus:ring-0 transition-all duration-200"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="text-sm">
                        <button id="olvidarContrasena" type="button" class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors">¿Olvidaste tu contraseña?</button>
                    </div>
                </div>

                <button type="button" id="ingresar" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-indigo-500 transition-all duration-300 hover:scale-[1.02] hover:shadow-lg hover:shadow-indigo-500/20">
                    Ingresar
                </button>
            </div>

        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/assets/js/login.js"></script>
</body>

</html>