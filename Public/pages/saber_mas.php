<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saber Más | Gestión PQRS Proyecto SENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/landing.css">
    <style>
        .glass-panel {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        @keyframes fadeInUpCustom {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-up {
            animation: fadeInUpCustom 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="antialiased font-['Inter'] selection:bg-indigo-500 selection:text-white bg-slate-950 text-slate-200 overflow-hidden h-screen flex flex-col relative w-full">

    <!-- Decorative Background Elements -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-[20%] -left-[10%] bg-indigo-600/30 w-[600px] h-[600px] rounded-full filter blur-[120px] mix-blend-screen animate-pulse duration-10000"></div>
        <div class="absolute -bottom-[20%] -right-[10%] bg-purple-600/30 w-[600px] h-[600px] rounded-full filter blur-[120px] mix-blend-screen animate-pulse duration-10000" style="animation-delay: 2s;"></div>
        <div class="absolute top-[30%] left-[60%] bg-pink-500/20 w-[400px] h-[400px] rounded-full filter blur-[100px] mix-blend-screen animate-pulse duration-7000"></div>
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay"></div>
    </div>

    <!-- Navigation Header -->
    <div class="w-full z-50 flex justify-center px-6 pt-6 absolute top-0">
        <nav class="glass-nav rounded-full px-6 py-3 flex items-center justify-between w-full max-w-7xl border border-white/5 bg-slate-900/50 backdrop-blur-xl">
            <a href="/" class="flex-shrink-0 flex items-center gap-3 cursor-pointer group">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl shadow-[0_0_20px_rgba(99,102,241,0.5)] group-hover:scale-105 transition-transform duration-300 border border-white/20">
                    S
                </div>
                <span class="font-semibold text-lg tracking-tight text-white group-hover:text-indigo-300 transition-colors hidden sm:block">Gestión de Casos</span>
            </a>
            
            <div class="flex items-center gap-2 sm:gap-4">
                <a href="/" class="text-slate-400 hover:text-white transition-colors text-sm font-medium px-4 py-2 rounded-full hover:bg-white/5">
                    <span class="hidden sm:inline">Volver a </span>Inicio
                </a>
                <a href="/login" class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-bold text-white transition-all duration-300 border border-indigo-500/50 rounded-full bg-gradient-to-r from-indigo-600 to-indigo-800 hover:from-indigo-500 hover:to-indigo-700 hover:scale-105 shadow-[0_4px_20px_rgba(99,102,241,0.4)]">
                    Acceder al Sistema
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content Grid -->
    <main class="relative z-10 flex-1 flex items-center justify-center px-6 pt-20">
        <div class="max-w-7xl w-full grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center">
            
            <!-- Left Header Area (Takes 5 columns) -->
            <div class="lg:col-span-5 space-y-8 animate-fade-up opacity-0" style="animation-delay: 0.1s;">
                 <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-indigo-500/40 bg-indigo-500/10 text-indigo-300 text-xs font-bold uppercase tracking-widest backdrop-blur-md shadow-[0_0_15px_rgba(99,102,241,0.15)]">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 relative">
                        <span class="absolute inset-0 rounded-full bg-indigo-400 animate-ping opacity-75"></span>
                    </span>
                    Información General
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold tracking-tight text-white leading-[1.1]">
                    Sistema<br>   
                    <span class="bg-clip-text text-transparent bg-gradient-to-br from-indigo-400 via-purple-400 to-pink-400">Integral</span>
                </h1>
                
                <p class="text-lg text-slate-300 leading-relaxed border-l-4 border-indigo-500/50 pl-5 py-2 bg-gradient-to-r from-slate-800/40 to-transparent rounded-r-xl">
                    Protegemos y damos voz a los funcionarios del SENA optimizando la recepción y gestión de situaciones críticas en un entorno 100% confidencial y trazable.
                </p>
                
                <div class="pt-4 flex w-full">
                     <a href="/login" class="w-full sm:w-auto text-center px-10 py-4 rounded-full bg-white text-slate-950 font-bold hover:bg-indigo-50 hover:scale-[1.03] transition-all duration-300 shadow-[0_0_40px_rgba(255,255,255,0.2)] text-lg">
                        Radicar tu Caso Ahora
                    </a>
                </div>
            </div>

            <!-- Right Grid Area (Takes 7 columns) -->
            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-5 animate-fade-up opacity-0" style="animation-delay: 0.3s;">
                
                <!-- Card 1 -->
                <div class="glass-panel p-8 rounded-3xl group hover:bg-slate-800/40 hover:border-indigo-500/30 transition-all duration-500 hover:-translate-y-2 relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/20 transition-all"></div>
                    <div class="w-14 h-14 rounded-2xl bg-indigo-500/20 flex items-center justify-center mb-6 text-indigo-400 group-hover:scale-110 group-hover:bg-indigo-500/30 transition-all border border-indigo-500/20 shadow-[0_0_15px_rgba(99,102,241,0.2)]">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Transparencia</h3>
                    <p class="text-slate-400 leading-relaxed font-medium">Trazabilidad inalterable desde la radicación inicial hasta la respuesta definitiva de la comisión de personal.</p>
                </div>

                <!-- Card 2 -->
                <div class="glass-panel p-8 rounded-3xl group hover:bg-slate-800/40 hover:border-purple-500/30 sm:translate-y-8 transition-all duration-500 hover:-translate-y-2 relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-all"></div>
                    <div class="w-14 h-14 rounded-2xl bg-purple-500/20 flex items-center justify-center mb-6 text-purple-400 group-hover:scale-110 group-hover:bg-purple-500/30 transition-all border border-purple-500/20 shadow-[0_0_15px_rgba(168,85,247,0.2)]">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Agilidad</h3>
                    <p class="text-slate-400 leading-relaxed font-medium">Flujos de trabajo sincronizados que eliminan la burocracia y aseguran atención veloz a las peticiones urgentes.</p>
                </div>

                <!-- Card 3 -->
                <div class="glass-panel p-8 rounded-3xl group hover:bg-slate-800/40 hover:border-pink-500/30 transition-all duration-500 hover:-translate-y-2 relative overflow-hidden">
                    <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-pink-500/10 rounded-full blur-2xl group-hover:bg-pink-500/20 transition-all"></div>
                    <div class="w-14 h-14 rounded-2xl bg-pink-500/20 flex items-center justify-center mb-6 text-pink-400 group-hover:scale-110 group-hover:bg-pink-500/30 transition-all border border-pink-500/20 shadow-[0_0_15px_rgba(236,72,153,0.2)]">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Rigurosa Privacidad</h3>
                    <p class="text-slate-400 leading-relaxed font-medium">Control estricto de accesos y encriptación. Tu integridad está en un entorno seguro e inviolable.</p>
                </div>

                <!-- Card 4 -->
                <div class="glass-panel p-8 rounded-3xl group hover:bg-slate-800/40 hover:border-blue-500/30 sm:translate-y-8 transition-all duration-500 hover:-translate-y-2 relative overflow-hidden">
                    <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all"></div>
                    <div class="w-14 h-14 rounded-2xl bg-blue-500/20 flex items-center justify-center mb-6 text-blue-400 group-hover:scale-110 group-hover:bg-blue-500/30 transition-all border border-blue-500/20 shadow-[0_0_15px_rgba(59,130,246,0.2)]">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Notificaciones</h3>
                    <p class="text-slate-400 leading-relaxed font-medium">Te mantenemos informado minuto a minuto de las resoluciones o cambios en tu radicado de forma automatizada.</p>
                </div>

            </div>
        </div>
    </main>
    
    <!-- Footer Minimalista Oculto para ahorrar scroll -->
    <div class="absolute bottom-6 w-full text-center z-10 hidden md:block opacity-60 pointer-events-none">
        <p class="text-slate-500 text-[10px] tracking-[0.2em] font-bold uppercase drop-shadow-md">Protegiendo el bienestar en el SENA</p>
    </div>
    
</body>
</html>
