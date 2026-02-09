
const ENDPOINT_LISTAR = '/listarCasosComi';

const CargarCasos = async () => {

    const cuerpoTabla = document.getElementById("tablaCasos"); /* capturamos el cuerpo de la tabla */

    if (!cuerpoTabla) {/* validamos,en caso de no encontrar la tabla,ponemos un error */
        console.error('No se encontró la tabla');
        return;
    }

    try {
/*         insertamos el "cargando..." mientras nos llegan datos del endpoint" */
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
        console.log('RESPUESTA REAL DEL BACKEND:', data);
        console.log('OBJ casos:', data.casos);


/* validamos el estado de la r espuesta  y si hay casos */
const casos = Array.isArray(data.casos)
    ? data.casos
    : data.casos
        ? [data.casos]
        : [];

if (data.status === 'ok' && casos.length > 0) {
    renderizarTablaCasos(casos, cuerpoTabla);
        } else {
            /* si no hay casos enseñamos un mensaje diciendo que no hay casos */
            cuerpoTabla.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <p class="mt-2 mb-0">No hay casos</p>
                    </td>
                </tr>
            `;
        }
/*si falla,mostramos que hubo un error */
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

    // Iteramos sobre cada caso y creamos una fila de tabla para cada caso
    casos.forEach((caso) => {
        htmlFilas += `
            <tr>
                <th>${caso.id_caso}</th>
                <td>${caso.fecha_inicio}</td>
                <td>${caso.tipo_caso}</td>
                <td>${caso.fecha_cierre ?? 'N/A'}</td>
                <td>${caso.estado}</td>
                <td>${caso.proceso}</td>
                <td>${caso.comisionado}</td>
                <td>
                    <button class="btn-table" onclick="supervisarCaso(${caso.id_caso})">
                        <i class="bi bi-eye"></i> Supervisar
                    </button>
                </td>
            </tr>
        `;
    });
/**insertamos en el cuerpo de la tabla */
    cuerpoTabla.innerHTML = htmlFilas;
};
const supervisarCaso = async (id_caso) => {
    /* confirmamos la accion de supervisar */

    if (!confirm('¿Quieres supervisar el caso?')) return;

    try {

        const response = await fetch(ENDPOINT_SUPERVISAR, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id_caso })
        });

        const data = await response.json();

        if (data.status === 'ok') {
            alert('Caso supervisado correctamente');
            CargarCasos(); /* recargas la tabla*/
        } else {
            alert(data.message || 'No se pudo supervisar el caso');
        }

    } catch (error) {
        console.error(error);
        alert('Error al supervisar el caso');
    }   
};
document.addEventListener('DOMContentLoaded', CargarCasos);
