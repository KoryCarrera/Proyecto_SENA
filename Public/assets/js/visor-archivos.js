// ============================================================
// FUNCIONES PARA EL VISOR DE ARCHIVOS ADJUNTOS (Avanzado)
// ============================================================
if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = '/assets/js/pdf.worker.min.js';
}

window.abrirModalArchivos = async (idCaso) => {
    const modalArchivos = document.getElementById('modalArchivos');
    const galeria = document.getElementById('galeriaArchivos');

    if (!modalArchivos || !galeria) return;

    modalArchivos.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Estado de carga inicial
    galeria.innerHTML = `
        <div class="col-span-full flex flex-col items-center py-12">
            <div class="spinner-border text-emerald-400" role="status"></div>
            <p class="mt-4 text-slate-400 italic">Buscando adjuntos del caso...</p>
        </div>
    `;

    try {
        const formData = new FormData();
        formData.append('id_caso', idCaso);

        const response = await fetch(ENDPOINT_ARCHIVOS, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status === 'ok') {
            renderizarArchivos(data.archivos);
        } else {
            galeria.innerHTML = `
                <div class="col-span-full text-center py-12 bg-slate-800/30 rounded-2xl border border-dashed border-slate-700">
                    <i class="bi bi-folder-x text-5xl text-slate-600 mb-4 block"></i>
                    <p class="text-slate-400 font-medium">${data.mensaje || 'Error al cargar los archivos'}</p>
                </div>
            `;
        }
    } catch (error) {
        galeria.innerHTML = `<div class="col-span-full alert alert-danger">Error de conexión con el servidor</div>`;
    }
};

const renderizarArchivos = (archivos) => {
    const galeria = document.getElementById('galeriaArchivos');
    if (!galeria) return;

    if (!archivos || archivos.length === 0) {
        galeria.innerHTML = `
            <div class="col-span-full text-center py-12 bg-slate-800/30 rounded-2xl border border-dashed border-slate-700">
                <i class="bi bi-inbox text-5xl text-slate-600 mb-4 block"></i>
                <p class="text-slate-400 font-medium">Este caso no tiene archivos adjuntos.</p>
            </div>
        `;
        return;
    }

    galeria.innerHTML = '';
    archivos.forEach((archivo) => {
        const extension = archivo.ruta.split('.').pop().toLowerCase();
        const urlBridge = `/verArchivo?ruta=${encodeURIComponent(archivo.ruta)}`;
        
        const card = document.createElement('div');
        card.className = "group relative bg-slate-800/40 border border-slate-700 hover:border-emerald-500/50 rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-2xl hover:shadow-emerald-500/10";
        
        card.innerHTML = `
            <div class="aspect-video bg-slate-900 flex items-center justify-center overflow-hidden relative border-b border-slate-700/50">
                <!-- Contenedor del Preview -->
                <div id="preview-${archivo.id_archivo}" class="w-full h-full flex items-center justify-center bg-slate-950/20">
                    <div class="spinner-border spinner-border-sm text-slate-700"></div>
                </div>
                
                <!-- Overlay de Acciones (Hover) -->
                <div class="absolute inset-0 bg-slate-950/70 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center gap-4 backdrop-blur-[2px]">
                    <button onclick="abrirLightbox('${urlBridge}', '${archivo.nombre_archivo}')" class="w-12 h-12 flex items-center justify-center bg-emerald-500 hover:bg-emerald-400 text-white rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300" title="Previsualizar">
                        <i class="bi bi-eye-fill text-xl"></i>
                    </button>
                    <a href="${urlBridge}" download="${archivo.nombre_archivo}" class="w-12 h-12 flex items-center justify-center bg-blue-600 hover:bg-blue-500 text-white rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300 delay-75" title="Descargar">
                        <i class="bi bi-download text-xl"></i>
                    </a>
                </div>
            </div>
            
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <span class="px-2 py-0.5 rounded-md bg-slate-700/50 text-[10px] font-bold text-slate-400 uppercase tracking-widest border border-slate-600/50">${extension}</span>
                    <span class="text-[10px] text-slate-500 font-medium">${archivo.tipo_archivo}</span>
                </div>
                <p class="text-sm text-slate-100 font-semibold truncate mb-1" title="${archivo.nombre_archivo}">
                    ${archivo.nombre_archivo}
                </p>
                <div class="flex justify-between items-center mt-4 pt-3 border-t border-slate-700/40">
                    <span class="text-[10px] text-slate-500 flex items-center gap-1.5">
                        <i class="bi bi-calendar3"></i> ${archivo.fecha_subida.split(' ')[0]}
                    </span>
                    <a href="${urlBridge}" download="${archivo.nombre_archivo}" class="text-xs font-bold text-emerald-400 hover:text-emerald-300 flex items-center gap-1.5 no-underline transition-colors uppercase tracking-tight">
                        <i class="bi bi-cloud-arrow-down-fill"></i> Descargar
                    </a>
                </div>
            </div>
        `;
        
        galeria.appendChild(card);
        setTimeout(() => generarPreviewRemoto(urlBridge, archivo, `preview-${archivo.id_archivo}`), 100);
    });
};

async function generarPreviewRemoto(url, archivo, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    try {
        const extension = archivo.ruta.split('.').pop().toLowerCase();
        
        if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(extension)) {
            container.innerHTML = `<img src="${url}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="Preview">`;
        } 
        else if (extension === 'pdf') {
            try {
                const response = await fetch(url);
                const blob = await response.blob();
                const arrayBuffer = await blob.arrayBuffer();
                
                const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
                const page = await pdf.getPage(1);
                const viewport = page.getViewport({ scale: 0.4 });
                
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                
                await page.render({ canvasContext: context, viewport: viewport }).promise;
                container.innerHTML = '';
                container.appendChild(canvas);
                canvas.className = "max-w-full max-h-full object-contain shadow-md rounded";
            } catch (e) {
                container.innerHTML = `<i class="bi bi-file-earmark-pdf text-5xl text-red-500/80 drop-shadow-lg"></i>`;
            }
        }
        else if (extension === 'docx') {
            try {
                const response = await fetch(url);
                const arrayBuffer = await response.arrayBuffer();
                const result = await mammoth.convertToHtml({ arrayBuffer: arrayBuffer });
                container.innerHTML = `<div class="p-3 text-[7px] text-slate-500 h-full overflow-hidden leading-tight bg-white/[0.03] w-full select-none">${result.value}</div>`;
            } catch (e) {
                container.innerHTML = `<i class="bi bi-file-earmark-word text-5xl text-blue-500/80 drop-shadow-lg"></i>`;
            }
        }
        else {
            const iconMap = {
                'doc': 'bi-file-earmark-word text-blue-400',
                'xls': 'bi-file-earmark-excel text-emerald-400',
                'xlsx': 'bi-file-earmark-excel text-emerald-400',
                'txt': 'bi-file-earmark-text text-slate-400',
                'mp4': 'bi-play-circle text-purple-400'
            };
            const icon = iconMap[extension] || 'bi-file-earmark text-slate-500';
            container.innerHTML = `<i class="bi ${icon} text-5xl drop-shadow-md"></i>`;
        }
    } catch (error) {
        container.innerHTML = `<i class="bi bi-exclamation-triangle text-3xl text-amber-500/50"></i>`;
    }
}

window.abrirLightbox = async (url, nombre) => {
    const lightbox = document.getElementById('modalLightbox');
    const contenido = document.getElementById('contenidoLightbox');
    const footer = document.getElementById('footerLightbox');

    if (!lightbox || !contenido || !footer) return;

    contenido.innerHTML = '<div class="spinner-border text-white"></div>';
    footer.textContent = nombre;
    lightbox.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    const extension = nombre.split('.').pop().toLowerCase();

    try {
        if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(extension)) {
            contenido.innerHTML = `<img src="${url}" class="max-w-full max-h-[80vh] object-contain shadow-2xl animate-fade-in" alt="${nombre}">`;
        } 
        else if (extension === 'pdf') {
            contenido.innerHTML = `<iframe src="${url}" class="w-full max-w-5xl h-[80vh] rounded-xl border-0 shadow-2xl" title="${nombre}"></iframe>`;
        }
        else {
            contenido.innerHTML = `
                <div class="text-center p-12 bg-slate-800/50 rounded-3xl border border-slate-700 backdrop-blur-xl">
                    <i class="bi bi-file-earmark-arrow-down text-7xl text-indigo-400 mb-6 block"></i>
                    <p class="text-white text-xl mb-6">Vista previa no disponible para este formato</p>
                    <a href="${url}" download="${nombre}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition-all font-bold uppercase tracking-tight">
                        <i class="bi bi-download"></i> Descargar Archivo
                    </a>
                </div>
            `;
        }
    } catch (e) {
        contenido.innerHTML = `<div class="alert alert-danger">No se pudo cargar la vista previa</div>`;
    }
}

window.cerrarLightbox = () => {
    const lightbox = document.getElementById('modalLightbox');
    const contenido = document.getElementById('contenidoLightbox');
    const footer = document.getElementById('footerLightbox');
    if (lightbox) lightbox.style.display = 'none';
    if (contenido) contenido.innerHTML = '';
    if (footer) footer.textContent = '';
    document.body.style.overflow = 'auto';
};

window.cerrarModalArchivos = () => {
    const modal = document.getElementById('modalArchivos');
    const galeria = document.getElementById('galeriaArchivos');
    if (modal) modal.style.display = 'none';
    if (galeria) galeria.innerHTML = '';
    document.body.style.overflow = 'auto';
};

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        cerrarLightbox();
        cerrarModalArchivos();
    }
});

// Configuración de listeners únicos para cerrar al hacer clic fuera (Auto-inicialización)
document.addEventListener('DOMContentLoaded', () => {
    const modalArchivos = document.getElementById('modalArchivos');
    if (modalArchivos) {
        modalArchivos.addEventListener('click', (e) => {
            if (e.target === modalArchivos) cerrarModalArchivos();
        });
    }

    const modalLightbox = document.getElementById('modalLightbox');
    if (modalLightbox) {
        modalLightbox.addEventListener('click', (e) => {
            // Si el clic es en el fondo o en el contenedor flex que centra el contenido
            if (e.target === modalLightbox || e.target.classList.contains('flex-col')) {
                cerrarLightbox();
            }
        });
    }
});
