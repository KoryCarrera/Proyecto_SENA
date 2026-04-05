
const ENDPOINT_LISTAR = '/listarCasosComi';
const ENDPOINT_OBTENER = '/modalCasoAdmin';
const ENDPOINT_GESTIONAR = '/gestionarCaso';
const ENDPOINT_SEGUIMIENTOS = '/listarSeguimientos';

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
    </ >

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
                <div class=" bg-slate-800/40 rounded-lg">
                    <label class="fw-bold text-white uppercase" style="font-size: 1rem; letter-spacing: 0.05em;">Descripción Original</label>
                    <p class="descripcion text-slate-300 mb-0 mt-1" style="font-size: 1rem; white-space: pre-wrap;">${caso.description || caso.descripcion}</p>
                </div>
            </div>
        </div>
        ` : ''
        }

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
                <div class="col-md-12">
                    <label class="text-xs text-slate-400 mb-1 d-block">Agregar Observación / Seguimiento</label>
                    <textarea name="observacion" class="contenido" rows="3" placeholder="Escriba aquí los avances o detalles de la gestión..." id="observacion"></textarea>
                </div>
            </div>
        </div>
            `;

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



    if (caso.estado === 'No atendido') {
        const mensajeEstado = document.getElementById('mensajeEstado');
        mensajeEstado.innerHTML = `Solo el administrador puede cambiar el estado de este caso.`;

        const selectEstado = document.getElementById('selectEstado');
        selectEstado.disabled = true;
    }

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
    const modal = document.getElementById('modalCaso');
    const btnGuardarCambios = document.getElementById('guardarCambios');
    const btnCerrar = document.getElementById('cerrar-modal');

    btnGuardarCambios.addEventListener('click', (e) => {

        e.preventDefault();
        const cambiosCasos = {
            'idCaso': document.getElementById('idCaso').value,
            'idEstado': document.getElementById('selectEstado').value,
            'observacion': document.getElementById('observacion').value
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
