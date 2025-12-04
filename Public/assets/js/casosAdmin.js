const ENDPOINT_LISTAR = '../../../app/controllers/listarCasos.php';
const ENDPOINT_OBTENER = '../../../app/controllers/modalCasoAdmin.php';

const cargarCasos = async () => {
    const cuerpoTabla = document.getElementById('tablaCasos');
    
    if (!cuerpoTabla) {
        console.error('!No se encontró el cuerpo de la tabla¡');
        return;
    }

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

    try {
        const response = await fetch(ENDPOINT_LISTAR);

        if (!response.ok) {
            throw new Error(`Error HTTP ${response.status}: No se pudo conectar con el servidor`);
        }
        
        const data = await response.json();
        
        if (data.status !== 'ok') {
            throw new Error(data.mensaje || 'Error desconocido');
        }

        if (!data.casos || !Array.isArray(data.casos) || data.casos.length === 0) {
            cuerpoTabla.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4 text-warning">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                        <p class="mt-2 mb-0 fw-bold">No hay casos registrados</p>
                    </td>
                </tr>
            `;
            return;
        }

        renderizarTablaCasos(data.casos, cuerpoTabla);
        
    } catch (error) {
        console.error('Error al cargar los casos:', error);
        cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-danger">
                    <p class="fw-bold">Error: ${error.message}</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="cargarCasos()">
                        Reintentar
                    </button>
                </td>
            </tr>
        `;
    }
};

// ✅ Nombre correcto de la función
const renderizarTablaCasos = (casos, cuerpoTabla) => {
    let htmlFilas = '';

    casos.forEach((caso) => {
        const fechaInicio = caso.fecha_inicio
            ? formatearFecha(caso.fecha_inicio)
            : 'No registrada';

        const fechaCierre = caso.fecha_cierre
            ? formatearFecha(caso.fecha_cierre)
            : '<span class="badge bg-warning text-dark">Pendiente</span>';

        const estadoBadge = obtenerBadgeEstado(caso.estado);

        htmlFilas += `
            <tr>
                <th scope="row">${caso.id_caso}</th>
                <td>${fechaInicio}</td>
                <td>${caso.tipo_caso || 'N/A'}</td>
                <td>${fechaCierre}</td>
                <td>${estadoBadge}</td>
                <td>${caso.proceso || 'N/A'}</td>
                <td>${caso.comisionado}</td>
                <td>
                    <button class="btn-table" onclick="supervisarCaso(${caso.id_caso})">
                        <i class="bi bi-eye"></i> Supervisar
                    </button>
                </td>
            </tr>
        `;
    });

    cuerpoTabla.innerHTML = htmlFilas;
    console.log(`Se renderizaron ${casos.length} casos`);
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
        case 'por atender':
            return `<span class="badge bg-primary">${estado}</span>`;
        case 'no atendido':
            return `<span class="badge bg-warning text-dark">${estado}</span>`;
        case 'atendido':
            return `<span class="badge bg-success">${estado}</span>`;
        default:
            return `<span class="badge bg-secondary">${estado}</span>`;
    }
};

const supervisarCaso = async (idCaso) => {
    console.log(`Supervisando caso ID: ${idCaso}`);
    
    const modalElement = document.getElementById('modalCaso');
    const modal = new bootstrap.Modal(modalElement);
    
    document.getElementById('modalCasoBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3">Cargando detalles del caso...</p>
        </div>
    `;
    
    modal.show();
    
    try {
        const response = await fetch(ENDPOINT_OBTENER, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_caso=${idCaso}`
        });
        
        if (!response.ok) {
            throw new Error(`Error HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        console.log('Caso obtenido:', data);
        
        if (data.status === 'ok' && data.caso) {
            mostrarDetallesCaso(data.caso);
        } else {
            throw new Error(data.mensaje || 'No se pudo obtener el caso');
        }
        
    } catch (error) {
        console.error('Error al obtener el caso:', error);
        document.getElementById('modalCasoBody').innerHTML = `
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
    
    modalTitle.textContent = `Caso #${caso.id_caso} - ${caso.tipo_caso}`;
    
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">ID del Caso:</label>
                <p class="form-control-plaintext">${caso.id_caso}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tipo de Caso:</label>
                <p class="form-control-plaintext">${caso.tipo_caso || 'N/A'}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Fecha de Inicio:</label>
                <p class="form-control-plaintext">${formatearFecha(caso.fecha_inicio)}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Fecha de Cierre:</label>
                <p class="form-control-plaintext">${caso.fecha_cierre ? formatearFecha(caso.fecha_cierre) : '<span class="badge bg-warning text-dark">Pendiente</span>'}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Estado:</label>
                <p class="form-control-plaintext">${obtenerBadgeEstado(caso.estado)}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Proceso:</label>
                <p class="form-control-plaintext">${caso.proceso || 'N/A'}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Comisionado Encargado:</label>
                <p class="form-control-plaintext">${caso.comisionado || 'Sin asignar'}</p>
            </div>
        </div>
        
        ${caso.descripcion ? `
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Descripción:</label>
                <p class="form-control-plaintext">${caso.descripcion}</p>
            </div>
        </div>
        ` : ''}
    `;
};

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Página cargada, iniciando carga de casos...');
    cargarCasos();
});