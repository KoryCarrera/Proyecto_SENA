
const ENDPOINT_LISTAR = '/listarCasosComi';
const ENDPOINT_OBTENER = '/modalCasoAdmin';

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

const mostrarDetallesCaso = (caso) => {
    const modalBody = document.getElementById('modalCasoBody');
    const modalTitle = document.getElementById('modalCasoLabel');

    modalTitle.textContent = `Gestionar Caso #${caso.id_caso}`;

    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-white uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">ID del Caso</label>
                <p class="text-slate-300 mb-0">${caso.id_caso}</p>
                <input type="hidden" name="id_caso" value="${caso.id_caso}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-white uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">Tipo de Caso</label>
                <p class="text-slate-300 mb-0">${caso.tipo_caso || 'N/A'}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-white uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">Fecha Inicio</label>
                <p class="text-slate-300 mb-0">${formatearFecha(caso.fecha_inicio)}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-white uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">Proceso</label>
                <p class="text-slate-300 mb-0">${caso.proceso || 'N/A'}</p>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="fw-bold text-indigo-400 uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                <i class="bi bi-gear-fill me-1"></i> Acción de Gestión
            </label>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="text-xs text-slate-400 mb-1 d-block">Actualizar Estado</label>
                    <select name="id_estado" id="selectEstado" class="contenido" required>
                        <option value="" disabled>Seleccione un estado</option>
                        <!-- Opciones cargadas dinámicamente -->
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="text-xs text-slate-400 mb-1 d-block">Agregar Observación / Seguimiento</label>
                    <textarea name="observacion" class="contenido" rows="3" placeholder="Escriba aquí los avances o detalles de la gestión..."></textarea>
                </div>
            </div>
        </div>

        ${caso.description || caso.descripcion ? `
        <div class="mb-2 p-3 bg-slate-800/40 rounded-lg border border-white/5">
            <label class="fw-bold text-slate-400 uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Descripción Original</label>
            <p class="text-slate-400 mb-0 mt-1" style="font-size: 0.9rem; white-space: pre-wrap;">${caso.description || caso.descripcion}</p>
        </div>
        ` : ''}
    `;

    cargarEstados(caso.estado);
};

const formatearFecha = (fecha) => {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const anio = date.getFullYear();
    return `${dia}/${mes}/${anio}`;
};

const obtenerBadgeEstado = (estado) => {
    const estadoLower = (estado || '').toLowerCase();
    switch (estadoLower) {
        case 'por atender': return `<span class="badge bg-primary">${estado}</span>`;
        case 'no atendido': return `<span class="badge bg-warning text-dark">${estado}</span>`;
        case 'atendido': return `<span class="badge bg-success">${estado}</span>`;
        default: return `<span class="badge bg-secondary">${estado}</span>`;
    }
};

// Eventos para cerrar el modal
const cargarEstados = async (estadoActual) => {
    const select = document.getElementById('selectEstado');
    if (!select) return;

    try {
        const response = await fetch('/opcionesRegistro');
        const data = await response.json();

        if (data.status === 'ok' && data.estados) {
            select.innerHTML = '<option value="" disabled>Seleccione un estado</option>';
            data.estados.forEach(estado => {
                const selected = estado.estado.toLowerCase() === estadoActual.toLowerCase() ? 'selected' : '';
                select.innerHTML += `<option value="${estado.id_estado}" ${selected} class="bg-slate-800">${estado.estado}</option>`;
            });
        }
    } catch (error) {
        console.error('Error al cargar estados:', error);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalCaso');
    const form = document.getElementById('formGestionarCaso');
    const btnCerrar = document.getElementById('cerrar-modal');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);

            // Mostrar cargando con SweetAlert
            Swal.fire({
                title: 'Procesando...',
                text: 'Actualizando la gestión del caso',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch('/gestionarCaso', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'ok') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.mensaje,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                    CargarCasos(); // Recargar la tabla
                } else {
                    throw new Error(data.mensaje || 'Error al gestionar el caso');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message
                });
            }
        });
    }

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
