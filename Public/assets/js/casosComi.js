
const ENDPOINT_LISTAR = '/listarCasosComi';
const ENDPOINT_OBTENER = '/modalCasoAdmin';
const ENDPOINT_GESTIONAR = '/gestionarCaso';
const ENDPOINT_SEGUIMIENTOS = '/listarSeguimientos';
const ENDPOINT_ARCHIVOS = '/listarArchivosCaso';


const btnGuardarCambios = document.getElementById('guardarCambios');

const CargarCasos = async () => {
    const cuerpoTabla = document.getElementById("tablaCasos");

    if (!cuerpoTabla) {
        console.error('No se encontró la tabla');
        return;
    }

    try {
        cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 mb-0">Cargando casos...</p>
                </td>
            </tr>
        `;
        const response = await fetch(ENDPOINT_LISTAR);
        const data = await response.json();

        const casos = Array.isArray(data.casos)
            ? data.casos
            : data.casos
                ? [data.casos]
                : [];

        if (data.status === 'ok' && casos.length > 0) {
            renderizarTablaCasos(casos, cuerpoTabla);
        } else {
            cuerpoTabla.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <p class="mt-2 mb-0">No hay casos registrados</p>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('error', error);
        cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <p class="mt-2 mb-0">Error al cargar los casos</p>
                </td>
            </tr>
        `;
    }
};

const renderizarTablaCasos = (casos, cuerpoTabla) => {
    let htmlFilas = '';

    casos.forEach((caso) => {
        htmlFilas += `
            <tr>
                <th>${caso.id_caso}</th>
                <td>${formatearFecha(caso.fecha_inicio)}</td>
                <td>${caso.tipo_caso}</td>
                <td>${caso.fecha_cierre ? formatearFecha(caso.fecha_cierre) : '<span class="badge bg-warning text-dark">Pendiente</span>'}</td>
                <td>${obtenerBadgeEstado(caso.estado)}</td>
                <td>${caso.proceso}</td>
                <td>${caso.comisionado}</td>
                <td>
                    <button class="btn-table" onclick="gestionarCaso(${caso.id_caso})">
                        <i class="bi bi-gear-fill"></i> Gestionar
                    </button>
                </td>
            </tr>
        `;
    });
    cuerpoTabla.innerHTML = htmlFilas;

    // Si ya existe una instancia de DataTables, la destruimos antes de crear una nueva
    if ($.fn.DataTable.isDataTable("#tablaCasoComi")) {
        $("#tablaCasoComi").DataTable().destroy();
    }

    // Inicializamos DataTables DESPUÉS de que los datos estén en el DOM
    var table = $("#tablaCasoComi").DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        dom: "rti", // Sin paginación interna, usamos la visual personalizada
        autoWidth: false,
        drawCallback: function () {
            actualizarPaginacionVisualComi(table);
        },
    });

    $("#buscarComi").on("keyup", function () {
        table.search(this.value).draw();
    });

    // El select de cantidad debe estar aquí dentro donde 'table' existe
    $("#filtroCantidadComi")
        .off("change")
        .on("change", function () {
            const valor = parseInt($(this).val());
            table.page.len(valor).draw();
        });


};

const gestionarCaso = async (id_caso) => {
    const modal = document.getElementById('modalCaso');
    const modalBody = document.getElementById('modalCasoBody');
    const modalTitle = document.getElementById('modalCasoLabel');

    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3">Obteniendo detalles del caso...</p>
        </div>
    `;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    try {
        const response = await fetch(ENDPOINT_OBTENER, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_caso=${id_caso}`
        });

        const data = await response.json();

        if (data.status === 'ok' && data.caso) {
            mostrarDetallesCaso(data.caso);
        } else {
            throw new Error(data.mensaje || 'Error al obtener el caso');
        }

    } catch (error) {
        console.error(error);
        modalBody.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    }
};

function llenarTablaSeguimientos(id_caso) {

    const tablaBody = document.getElementById('tablaSeguimientosBody');

    //Insertamos el cargando
    tablaBody.innerHTML = `
<tr class="bg-transparent">
        <td colspan="4" class="text-center py-4 text-slate-500 italic bg-transparent border-0">
            <span class="spinner-border spinner-border-sm me-2"></span> Cargando historial...
        </td>
    </tr>
    `;

    //Hacemos la request para obtener los seguimientos del caso

    $.ajax({
        url: ENDPOINT_SEGUIMIENTOS,
        method: 'POST',
        data: { 'idcaso': id_caso },
        success: function (response) {

            //validamos que el status sea ok y que existan seguimientos
            if (response.status === 'ok' && response.seguimientos && response.seguimientos.length > 0) {

                //limpiamos la tabla
                tablaBody.innerHTML = '';

                //insertamos los seguimientos en la tabla
                response.seguimientos.forEach(seg => {
                    tablaBody.innerHTML += `
                    <tr class="border-bottom border-slate-700/30 bg-transparent">
                        <td class="bg-transparent text-indigo-300 fw-bold">${seg.id_seguimiento}</td>
                        <td class="bg-transparent">${formatearFecha(seg.fecha_seguimiento)}</td>
                        <td class="bg-transparent text-slate-200">${seg.usuario}</td>
                        <td class="bg-transparent text-slate-400" style="white-space: pre-wrap;">${seg.observacion}</td>
                    </tr>
                    `;
                });
            } else {
                tablaBody.innerHTML = `
                <tr class="bg-transparent">
                    <td colspan="4" class="text-center py-4 text-slate-500 bg-transparent">${response.mensaje}.</td>
                </tr>`;
            }

            if (response.status === 'error') {
                tablaBody.innerHTML = `
            <tr class="bg-transparent">
                    <td colspan="4" class="text-center py-4 text-warning bg-transparent">${response.mensaje}.</td>
                </tr>`;
            }
        }, error: function (xhr, status, error) {
            console.error('Error al cargar los seguimientos:', error);
            tablaBody.innerHTML = `
            <tr class="bg-transparent">
                <td colspan="4" class="text-center py-4 text-danger bg-transparent font-bold">Error de conexión al servidor</td>
            </tr>`;
        }
    });
}


const mostrarDetallesCaso = (caso) => {
    const modalBody = document.getElementById('modalCasoBody');
    const modalTitle = document.getElementById('modalCasoLabel');

    modalTitle.textContent = `Gestionar Caso #${caso.id_caso} `;

    modalBody.innerHTML = `
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="fw-bold text-white uppercase" style="font-size: 1rem; letter-spacing: 0.05em;">ID del Caso</label>
            <p class="text-slate-300 mb-0">${caso.id_caso}</p>
            <input type="hidden" name="id_caso" id="idCaso" value="${caso.id_caso}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="fw-bold text-white uppercase" style="font-size: 1rem; letter-spacing: 0.05em;">Tipo de Caso</label>
            <p class="text-slate-300 mb-0">${caso.tipo_caso || 'N/A'}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="fw-bold text-white uppercase" style="font-size: 1rem; letter-spacing: 0.05em;">Fecha Inicio</label>
            <p class="text-slate-300 mb-0">${formatearFecha(caso.fecha_inicio)}</p>
        </div>
        <div class="col-md-6 mb-3">
            <label class="fw-bold text-white uppercase" style="font-size: 1rem; letter-spacing: 0.05em;">Proceso</label>
            <p class="text-slate-300 mb-0">${caso.proceso || 'N/A'}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="fw-bold text-white uppercase" style="font-size: 1rem; letter-spacing: 0.05em;">Estado</label>
            <p class="text-slate-300 mb-0">${obtenerBadgeEstado(caso.estado)}</p>
        </div>
        <div class="col-md-6 mb-3">
            <label class="fw-bold text-white uppercase" style="font-size: 1rem; letter-spacing: 0.05em;">Seguimientos</label>
            <p class="text-slate-300 mb-0">${caso.seguimientos || 'N/A'}</p>
        </div>
    </div>

    ${caso.description || caso.descripcion ? `
    <div class="row">
        <div class="col-12 mb-4">
            <div class="bg-slate-800/40 rounded-lg">
                <label class="fw-bold text-white uppercase" style="font-size: 1rem; letter-spacing: 0.05em;">Descripción Original</label>
                <p class="descripcion text-slate-300 mb-0 mt-1" style="font-size: 1rem; white-space: pre-wrap;">${caso.description || caso.descripcion}</p>
            </div>
        </div>
    </div>
    ` : ''}

    <div class="row mb-4">
        <div class="col-12">
            <button type="button" class="boton-tabla" id="btnMostrar">
                <i class="bi bi-table"></i> Mostrar tabla de seguimiento
            </button>

            <div id="tablaSeguimientosContainer" class="bg-slate-800/40 rounded-lg p-3 border border-slate-700/50 w-100" style="display: none;">
                <label class="fw-bold text-indigo-400 uppercase mb-2 d-block" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                    <i class="bi bi-clock-history me-1"></i> Historial de Seguimientos
                </label>
                <div class="table-responsive w-100" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-dark table-borderless align-middle mb-0 bg-transparent w-100">
                        <thead class="text-slate-400 border-bottom border-slate-700 bg-transparent" style="font-size: 0.7rem;">
                            <tr>
                                <th class="pb-2 bg-transparent">ID</th>
                                <th class="pb-2 bg-transparent">FECHA</th>
                                <th class="pb-2 bg-transparent">USUARIO</th>
                                <th class="pb-2 bg-transparent">OBSERVACIÓN</th>
                            </tr>
                        </thead>
                        <tbody id="tablaSeguimientosBody" class="text-slate-300 bg-transparent" style="font-size: 0.85rem;">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
        
    <div class="mb-4">
        <label class="fw-bold text-indigo-400 uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
            <i class="bi bi-gear-fill me-1"></i> Acción de Gestión
        </label>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="text-xs text-slate-400 mb-1 d-block">Actualizar Estado</label>
                <select name="id_estado" id="selectEstado" class="contenido">
                    <option value="" selected disabled class="bg-slate-900">Seleccione un estado</option>
                    <option value="1" class="bg-slate-900">Atendido</option>
                    <option value="2" class="bg-slate-900">Por Atender</option>
                    <option value="3" class="bg-slate-900">No Atendido</option>
                </select>
                <span class="badge bg-warning text-dark" id="mensajeEstado"></span>
            </div>
            
            <div class="col-md-12" id="contenedorMotivo" style="display: none;">
                <label class="text-xs text-slate-400 mb-1 d-block">Motivo del Cambio de Estado</label>
                <textarea name="motivo_cambio" class="contenido" rows="2" placeholder="Especifique el motivo por el cual se cambia a este estado..." id="motivoCambio"></textarea>
            </div>
            
            <div class="col-md-12">
                <label class="text-xs text-slate-400 mb-1 d-block">Agregar Observación / Seguimiento</label>
                <textarea name="observacion" class="contenido" rows="3" placeholder="Escriba aquí los avances o detalles de la gestión..." id="observacion"></textarea>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-2 mb-1">
        <button type="button"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-emerald-400 bg-emerald-900/20 border border-emerald-500/30 rounded-lg hover:bg-emerald-500/20 transition-colors"
            id="btnVerArchivos"
            onclick="abrirModalArchivos(${caso.id_caso})">
            <i class="bi bi-paperclip"></i> Ver archivos adjuntos
        </button>
    </div>
    `;

    const contenedorMotivo = document.getElementById('contenedorMotivo');
    const inputMotivo = document.getElementById('motivoCambio');

    document.getElementById('selectEstado').addEventListener('change', (e) => {
        const estadoSeleccionado = e.target.value;
        // '1' = Atendido | '2' = Por Atender
        if (estadoSeleccionado === '1' || estadoSeleccionado === '2') {
            contenedorMotivo.style.display = 'block';
        } else {
            contenedorMotivo.style.display = 'none';
            inputMotivo.value = ''; // Limpiamos el valor por si el usuario lo llenó y luego cambió de opinión
        }
    });

    //Capturamos el boton para mostrar el historial de seguimientos
    const btnSeguimientos = document.getElementById('btnMostrar');

    //capturamos el contenedor de la tabla de seguimientos
    const tablaSeguimientosContainer = document.getElementById('tablaSeguimientosContainer');

    btnSeguimientos.addEventListener('click', () => {

        if (tablaSeguimientosContainer.style.display === 'none') {
            tablaSeguimientosContainer.style.display = 'block';
            btnSeguimientos.innerHTML = `<i class="bi bi-eye-slash-fill me-1"></i> Ocultar Historial de Seguimientos`;
        } else {
            tablaSeguimientosContainer.style.display = 'none';
            btnSeguimientos.innerHTML = `<i class="bi bi-table"></i> Mostrar tabla de seguimiento`;
        }

        llenarTablaSeguimientos(caso.id_caso);

    });

    // Auto-scroll del modal cuando se redimensiona el textarea
    const textareaObservacion = document.getElementById('observacion');
    if (textareaObservacion) {
        const contenidoModal = document.querySelector('.contenido-modal');
        let prevHeight = textareaObservacion.offsetHeight;

        const observer = new ResizeObserver(() => {
            const currentHeight = textareaObservacion.offsetHeight;
            if (currentHeight > prevHeight) {
                // Hacer scroll hacia abajo al agrandar el textarea
                contenidoModal.scrollTop = contenidoModal.scrollHeight;
            }
            prevHeight = currentHeight;
        });

        observer.observe(textareaObservacion);
    }
};

const formatearFecha = (fecha) => {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const anio = date.getFullYear();
    return `${dia}/${mes}/${anio} `;
};

const obtenerBadgeEstado = (estado) => {
    const estadoLower = (estado || '').toLowerCase();

    switch (estadoLower) {
        case 'por atender':
            return `<span class="badge bg-warning text-white"> ${estado}</span> `;
        case 'no atendido':
            return `<span class="badge bg-danger"> ${estado}</span> `;
        case 'atendido':
            return `<span class="badge bg-success"> ${estado}</span> `;
        case "Por asignar":
            return `<span class="badge bg-primary">${estado}</span>`;
        default:
            return `<span class="badge bg-secondary"> ${estado}</span> `;
    }
};

// ─── Paginación visual personalizada ─────────────────────────────────────────
const actualizarPaginacionVisualComi = (table) => {
    if (!table) return;

    const info = table.page.info(); // { page (0-based), pages, ... }
    const paginaActual = info.page;
    const totalPaginas = info.pages;

    const btnPrev = document.getElementById("btnPaginaAnteriorComi");
    const btnNext = document.getElementById("btnPaginaSiguienteComi");
    const contenedor = document.getElementById("pagBotonesComi");

    if (!btnPrev || !btnNext || !contenedor) return;

    // ── Estilos de los botones de número ──────────────────────────────────────
    const claseNormal = [
        "w-9",
        "h-9",
        "flex",
        "justify-center",
        "items-center",
        "text-white/70",
        "hover:text-white",
        "hover:bg-blue-600/80",
        "text-sm",
        "rounded-lg",
        "transition-colors",
        "cursor-pointer",
        "font-medium",
    ].join(" ");

    const claseActiva = [
        "w-9",
        "h-9",
        "flex",
        "justify-center",
        "items-center",
        "bg-blue-600",
        "text-white",
        "text-sm",
        "rounded-lg",
        "font-semibold",
        "shadow",
        "shadow-blue-500/40",
        "cursor-default",
    ].join(" ");

    // ── Generar botones de número según páginas reales ─────────────────────────
    contenedor.innerHTML = "";

    // Ventana deslizante: máx 5 páginas visibles a la vez
    const ventana = 5;
    let desde = Math.max(0, paginaActual - Math.floor(ventana / 2));
    const hasta = Math.min(totalPaginas, desde + ventana);
    if (hasta - desde < ventana) desde = Math.max(0, hasta - ventana);

    for (let i = desde; i < hasta; i++) {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.textContent = String(i + 1);
        btn.className = i === paginaActual ? claseActiva : claseNormal;
        if (i === paginaActual) {
            btn.setAttribute("aria-current", "page");
        } else {
            btn.addEventListener("click", () => table.page(i).draw("page"));
        }
        contenedor.appendChild(btn);
    }

    // ── Botón Anterior ────────────────────────────────────────────────────────
    // Clonar para limpiar listeners previos
    const nuevoPrev = btnPrev.cloneNode(true);
    btnPrev.parentNode.replaceChild(nuevoPrev, btnPrev);

    if (paginaActual === 0) {
        nuevoPrev.disabled = true;
    } else {
        nuevoPrev.disabled = false;
        nuevoPrev.addEventListener("click", () =>
            table.page("previous").draw("page"),
        );
    }

    // ── Botón Siguiente ───────────────────────────────────────────────────────
    const nuevoNext = btnNext.cloneNode(true);
    btnNext.parentNode.replaceChild(nuevoNext, btnNext);

    if (paginaActual >= totalPaginas - 1) {
        nuevoNext.disabled = true;
    } else {
        nuevoNext.disabled = false;
        nuevoNext.addEventListener("click", () => table.page("next").draw("page"));
    }
};
// ─────────────────────────────────────────────────────────────────────────────

// Eventos para cerrar el modal
document.addEventListener('DOMContentLoaded', () => {
    // Configuración de listeners únicos para modales (Evita fugas de eventos)
    const modalArchivos = document.getElementById('modalArchivos');
    if (modalArchivos) {
        modalArchivos.addEventListener('click', (e) => {
            if (e.target === modalArchivos) cerrarModalArchivos();
        });
    }

    const modalLightbox = document.getElementById('modalLightbox');
    if (modalLightbox) {
        modalLightbox.addEventListener('click', (e) => {
            if (e.target === modalLightbox || e.target.classList.contains('flex-col')) {
                cerrarLightbox();
            }
        });
    }

    const modal = document.getElementById('modalCaso');
    const btnGuardarCambios = document.getElementById('guardarCambios');
    const btnCerrar = document.getElementById('cerrar-modal');

    btnGuardarCambios.addEventListener('click', (e) => {

        const idCaso = document.getElementById('idCaso').value;
        const idEstado = document.getElementById('selectEstado').value;
        const observacion = document.getElementById('observacion').value
        const motivoElemento = document.getElementById('motivoCambio');
        const motivoCambio = motivoElemento ? motivoElemento.value : '';

        e.preventDefault();
        const cambiosCasos = {
            'idCaso': idCaso,
            'idEstado': idEstado,
            'observacion': observacion,
            'motivo': motivoCambio
        }

        $.ajax({
            url: ENDPOINT_GESTIONAR,
            method: 'POST',
            data: cambiosCasos,
            success: function (response) {

                if (response.status == 'ok') {
                    //Se recarga la tabla para mostrar los cambios realizados
                    CargarCasos();

                    // Después de guardar los cambios, puedes cerrar el modal
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';

                    Swal.fire({
                        icon: 'success',
                        title: 'Caso actualizado',
                        text: response.mensaje,
                        showConfirmButton: false,
                        timer: 2000,
                        theme: 'dark'
                    });
                }

                if (response.status == 'error') {

                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';

                    Swal.fire({
                        icon: 'error',
                        title: 'Error al actualizar',
                        text: response.mensaje,
                        showConfirmButton: false,
                        timer: 2000,
                        theme: 'dark'
                    });
                }
            },
            error: function (xhr, status, error) {

                modal.style.display = 'none';
                document.body.style.overflow = 'auto';

                console.error('Error al actualizar el caso:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al actualizar',
                    text: 'Error al actualizar el caso',
                    showConfirmButton: false,
                    timer: 2000,
                    theme: 'dark'
                });
            }
        })
    });
    if (btnCerrar) {
        btnCerrar.addEventListener('click', () => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    }

    modal.addEventListener('click', (evento) => {
        if (evento.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape' && modal.style.display === 'flex') {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    CargarCasos();
});

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
            contenido.innerHTML = `<iframe src="${url}" class="w-full h-[80vh] rounded-xl border-0" title="${nombre}"></iframe>`;
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
