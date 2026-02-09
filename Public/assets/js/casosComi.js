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

        if (data.status === 'ok' && data.casos.length > 0) {
            renderizarTablaCasos(data.casos, cuerpoTabla);
        } else {
            cuerpoTabla.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <p class="mt-2 mb-0">No hay casos</p>
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
                <td>${caso.fecha_inicio}</td>
                <td>${caso.tipo_caso}</td>
                <td>${caso.fecha_cierre ?? '-'}</td>
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

    cuerpoTabla.innerHTML = htmlFilas;
};
const supervisarCaso = async (id_caso) => {

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
            CargarCasos(); // recargas la tabla
        } else {
            alert(data.message || 'No se pudo supervisar el caso');
        }

    } catch (error) {
        console.error(error);
        alert('Error al supervisar el caso');
    }
};
