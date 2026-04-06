<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión PQRS | Proyecto SENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="/assets/js/landing.js"></script>
    <link rel="stylesheet" href="/assets/css/landing.css">
</head>

<body class="antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Decorative Background Elements -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="blob-bg top-[-10%] left-[-10%] bg-indigo-500/20"></div>
        <div class="blob-bg bottom-[-10%] right-[-10%] bg-purple-500/20 animation-delay-2000"></div>
        <div
            class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay">
        </div>
    </div>

    <!-- Navigation -->
    <div class="fixed top-6 w-full z-50 flex justify-center px-4">
        <nav
            class="glass-nav mirror-effect rounded-full px-8 py-4 flex items-center justify-between transition-all duration-300 w-full max-w-5xl">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center gap-3 cursor-pointer group relative z-10">
                <div
                    class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform duration-300">
                    S
                </div>
                <span
                    class="font-semibold text-xl tracking-tight text-white group-hover:text-indigo-300 transition-colors">Sistema
                    De Gestion De Casos</span>
            </div>

            <!-- CTA Button -->
            <div class="relative z-10">
                <a href="/login"
                    class="relative inline-flex items-center justify-center px-6 py-2 overflow-hidden font-medium text-white transition duration-300 ease-out border border-white/20 rounded-full hover:bg-white/10 group">
                    <span
                        class="absolute inset-0 flex items-center justify-center w-full h-full text-white duration-300 -translate-x-full bg-indigo-600/80 group-hover:translate-x-0 ease">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </span>
                    <span
                        class="absolute flex items-center justify-center w-full h-full text-white transition-all duration-300 transform group-hover:translate-x-full ease">Acceder</span>
                    <span class="relative invisible">Acceder</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Hero Section -->
    <section class="relative z-10 pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

            <h1
                class="text-5xl md:text-7xl font-bold tracking-tight mb-8 animate-[fadeInUp_0.8s_ease-out_0.2s_forwards] opacity-0 text-glow">
                Gestión Eficiente de <br class="hidden md:block" />
                <span
                    class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400">Casos</span>
            </h1>

            <p
                class="mt-6 max-w-2xl mx-auto text-lg md:text-xl text-slate-400 leading-relaxed animate-[fadeInUp_0.8s_ease-out_0.4s_forwards] opacity-0">
                Administra peticiones y situaciones que generan riesgo en los funcionarios del SENA para ser tramitados
                por la comisión de personal
            </p>

            <div class="mt-10 flex gap-4 justify-center animate-[fadeInUp_0.8s_ease-out_0.6s_forwards] opacity-0">
                <a href="/login"
                    class="px-8 py-4 rounded-full bg-slate-100 text-slate-900 font-semibold hover:bg-white hover:scale-105 transition-all duration-300 shadow-[0_0_20px_rgba(255,255,255,0.3)]">
                    Comenzar Ahora
                </a>
                <a href="/saber-mas"
                    class="px-8 py-4 rounded-full glass-card hover:bg-slate-800/50 text-white font-medium transition-all duration-300">
                    Saber Más
                </a>
            </div>

            <!-- Hero Image / Visual -->
            <div class="mt-20 relative animate-[fadeInUp_1s_ease-out_0.8s_forwards] opacity-0">
                <div
                    class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl blur opacity-20">
                </div>
                <div class="relative rounded-2xl overflow-hidden glass-card border border-slate-700/50 shadow-2xl">
                    <!-- Placeholder for hero visual -->
                    <div
                        class="aspect-[16/9] w-full bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900/20 flex items-center justify-center group overflow-hidden">
                        <div
                            class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-40 mix-blend-overlay">
                        </div>
                        <div
                            class="w-3/4 h-3/4 bg-indigo-500/10 rounded-full blur-3xl absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 group-hover:bg-indigo-500/20 transition-all duration-700">
                        </div>

                        <!-- Abstract UI Representation: Carrusel de Imágenes -->
                        <div
                            class="relative z-10 w-full max-w-4xl p-6 opacity-90 transform group-hover:scale-[1.02] transition-transform duration-500 w-full h-full ">
                            <!-- Contenedor del Carrusel -->
                            <div id="image-carousel"
                                class="flex overflow-x-auto gap-4 snap-x snap-mandatory hide-scrollbar pb-2 w-full h-full"
                                style="scrollbar-width: none; -ms-overflow-style: none;">
                                <!-- Item 1 -->
                                <div
                                    class="snap-center shrink-0 w-[calc(100%)] aspect-video bg-slate-800/50 rounded-xl flex items-center justify-center border border-white/10 overflow-hidden relative backdrop-blur-sm shadow-xl">
                                    <!-- Reemplaza el src con la ruta de tu imagen y quita la clase 'hidden' -->
                                    <img src="/assets/img/carrusel-1.png" alt="Imagen 1"
                                        class="w-full h-full object-cover z-10 relative">
                                        

                                </div>
                                <!-- Item 2 -->
                                <div
                                    class="snap-center shrink-0 w-[calc(100%-1rem)] aspect-video bg-slate-800/50 rounded-xl flex items-center justify-center border border-white/10 overflow-hidden relative backdrop-blur-sm shadow-xl">
                                    <!-- Reemplaza el src con la ruta de tu imagen y quita la clase 'hidden' -->
                                    <img src="#" alt=""
                                        class="w-full h-full object-cover  z-10 relative">
                                        <span class="text-white justify-center items-center absolute font-bold text-2xl">Proximamente...</span>

                                </div>
                                <!-- Item 3 -->
                                <div
                                    class="snap-center shrink-0 w-[calc(100%-1rem)] aspect-video bg-slate-800/50 rounded-xl flex items-center justify-center border border-white/10 overflow-hidden relative backdrop-blur-sm shadow-xl">
                                    <!-- Reemplaza el src con la ruta de tu imagen y quita la clase 'hidden' -->
                                    <img src="#" alt=""
                                        class="w-full h-full object-cover  z-10 relative">
                                        <span class="text-white justify-center items-center absolute font-bold text-2xl">Proximamente...</span>

                                </div>
                            </div>
                            <style>
                                .hide-scrollbar::-webkit-scrollbar {
                                    display: none;
                                }
                            </style>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-sm font-semibold text-indigo-400 tracking-wide uppercase">Características</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">Todo lo que necesitas</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-card p-8 rounded-2xl group cursor-default">
                    <div
                        class="w-12 h-12 rounded-lg bg-indigo-500/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3 group-hover:text-indigo-300 transition-colors">
                        Radicación Simple</h3>
                    <p class="text-slate-400 leading-relaxed">Envía tus solicitudes en pocos pasos. Un proceso
                        optimizado para que tu voz sea escuchada sin complicaciones.</p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-card p-8 rounded-2xl group cursor-default">
                    <div
                        class="w-12 h-12 rounded-lg bg-purple-500/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3 group-hover:text-purple-300 transition-colors">
                        Seguimiento en Tiempo Real</h3>
                    <p class="text-slate-400 leading-relaxed">Conoce el estado de tus PQRS al instante. Notificaciones y
                        actualizaciones transparentes en cada etapa.</p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-card p-8 rounded-2xl group cursor-default">
                    <div
                        class="w-12 h-12 rounded-lg bg-pink-500/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3 group-hover:text-pink-300 transition-colors">
                        Respuesta Garantizada</h3>
                    <p class="text-slate-400 leading-relaxed">Comprometidos con la eficiencia. Nuestro sistema asegura
                        tiempos de respuesta óptimos para cada caso.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-800 bg-slate-900/50 backdrop-blur-md relative z-10">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center flex-col md:flex-row gap-6">
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                        P</div>
                    <p class="text-slate-400 text-sm">© 2024 Proyecto SENA. Todos los derechos reservados.</p>
                </div>
                <div class="flex space-x-6">
                    <a href="https://github.com/KoryCarrera/Proyecto_SENA" target="_blank"
                        class="text-slate-400 hover:text-white transition-colors">
                        <span class="sr-only">GitHub</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>