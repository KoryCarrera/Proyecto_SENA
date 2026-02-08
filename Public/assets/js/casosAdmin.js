//Definimos los endpoints en constantes para facilidad
const ENDPOINT_LISTAR = '/listarCasos';
const ENDPOINT_OBTENER = '/modalCasoAdmin';

//Definimos una función Async de cargar casos
const cargarCasos = async () => {
    //capturamos el cuerpo de la tabla
    const cuerpoTabla = document.getElementById('tablaCasos');
    
    //validamos que exista
    if (!cuerpoTabla) {
        console.error('!No se encontró el cuerpo de la tabla¡');
        return;
    }

    //Insertamos el "cargando..." mientras nos llegan datos 
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
        //Hacemos fetch al endpoint para obtener los datos
        const response = await fetch(ENDPOINT_LISTAR);

        //Validamos el status devuelto por el endpoint
        if (!response.ok) {
            throw new Error(`Error HTTP ${response.status}: No se pudo conectar con el servidor`);
        }
        
        //Transformamos a JSON
        const data = await response.json();
        
        //Personalizacion de errores
        if (data.status !== 'ok') {
            throw new Error(data.mensaje || 'Error desconocido');
        }

        //validamos datos, tipo y longitud
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

        //declaramos una función que definiremos mas adelante
        renderizarTablaCasos(data.casos, cuerpoTabla);
        
    //personalizamos captacion de error catch
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

//definimos la funcion previamente declarada
const renderizarTablaCasos = (casos, cuerpoTabla) => {
    let htmlFilas = ''; //Inicializamos la variable vacia para guardar el html

    casos.forEach((caso) => { //Capturamos la fecha de inicio
        const fechaInicio = caso.fecha_inicio
            ? formatearFecha(caso.fecha_inicio) //Fecha estetica
            : 'No registrada';

            //capturamos fecha de cierre del argumento
        const fechaCierre = caso.fecha_cierre
            ? formatearFecha(caso.fecha_cierre) //estilizamos la fecha recibida
            : '<span class="badge bg-warning text-dark">Pendiente</span>';

        //Declaramos variable que definiremos mas adelante
        const estadoBadge = obtenerBadgeEstado(caso.estado);

        //insertamos cada iteracion en html
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

    cuerpoTabla.innerHTML = htmlFilas; //insertamos en el cuerpo de la tabla
};

const formatearFecha = (fecha) => {
    if (!fecha) return 'N/A'; //estilizamos el NULL

    //estilizamos fecha
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const anio = date.getFullYear();

    //devolvemos fecha estilizada
    return `${dia}/${mes}/${anio}`;
};

//funcion para estilizar estado segun tipo
const obtenerBadgeEstado = (estado) => {
    const estadoLower = (estado || '').toLowerCase();
    
    //Usamos switch para cada caso
    //utilizamos clases de bootstrap para estilizar con colores
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

//funcion para supervisar claso
const supervisarCaso = async (idCaso) => {
    
    //capturamos body del modal
    const modalElement = document.getElementById('modalCaso');
    //inicializamos clase de bootstrap
    const modal = new bootstrap.Modal(modalElement);

    //mostramos el cargando
    document.getElementById('modalCasoBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3">Cargando detalles del caso...</p>
        </div>
    `;
    
    modal.show(); //Renderizamos
    
    try {
        //Hacemos fetch al endpoint donde devolverá el caso a supervisar
        const response = await fetch(ENDPOINT_OBTENER, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_caso=${idCaso}`
        });
        
        //personalizacion de errores
        if (!response.ok) {
            throw new Error(`Error HTTP ${response.status}`);
        }
        
        //transformamos a json la response
        const data = await response.json();
        
        //Validamos que el status de la response ok
        if (data.status === 'ok' && data.caso) {
            //mostramos los detalles del caso con una función que declararemos más adelante
            mostrarDetallesCaso(data.caso);
        } else {
            //Mandamos un error personalizado
            throw new Error(data.mensaje || 'No se pudo obtener el caso');
        }
    //capturamos errores
    } catch (error) {
        document.getElementById('modalCasoBody').innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    }
};

//definimos la función previamente declarada
const mostrarDetallesCaso = (caso) => {
    //capturamos partes del modal
    const modalBody = document.getElementById('modalCasoBody');
    const modalTitle = document.getElementById('modalCasoLabel');
    
    //definimos el titulo
    modalTitle.textContent = `Caso #${caso.id_caso} - ${caso.tipo_caso}`;
    
    //insertamos el html con los datos del caso
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
    cargarCasos();
});