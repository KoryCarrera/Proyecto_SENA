//definimos todos nuestros endpoints
const ENDPOINT_LISTAR = '/listarProceso';
const ENDPOINT_CREAR = '/registrarProceso';
const ENDPOINT_DESACTIVAR = '/desactivarProceso';
const ENDPOINT_REACTIVAR = '/reactivarProceso';

const cargarProcesos = async () => { //utilizamos una arrow function para cargar los procesos

    //capturamos el tbody
    const cuerpoTabla = document.getElementById("tablaProcesos");

    //validamos la captura
    if (!cuerpoTabla) {
        console.error('No se encuentra  la tabla');
        return;
    }

    cuerpoTabla.innerHTML = `
        <tr>
            <td colspan="5" class="text-center">
                 Cargando procesos
            </td>
        </tr>
    `;

    try {
        //Esperamos una respuesta y un formato json de la misma respuesta
        const response = await fetch(ENDPOINT_LISTAR);
        const data = await response.json();

        //Validamos que el status de la respuesta es ok y su longitud es mayor a 0
        if (data.status === 'ok' && data.procesos.length > 0) {

            //renderizamos la tabla con una funcion que declararemos mas adelante
            renderizarTablaProcesos(data.procesos, cuerpoTabla);

        } else {
            // en caso de que no hayan procesos,esto es lo que se muestra
            cuerpoTabla.innerHTML = `
            
                <tr>
                    <td colspan="5" class="text-center">
                        No hay procesos registrados
                    </td>
                </tr>
            `;
        }

    } catch (error) {
        console.error('Error:', error);
        //en este se muestra el error en caso de que no se pueda conectar
        cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-danger">
                    Error al cargar procesos
                    <button onclick="cargarProcesos()">Reintentar</button>
                </td>
            </tr>
        `;
    }
};
const renderizarTablaProcesos = (procesos, cuerpoTabla) => { //Procedemos a definir la arrow function anterior
    let htmlFilas = '';

    //recorremos el json y los vamos almacenando en una variable
    procesos.forEach((proceso) => {
        //Determinar qué botón mostrar según el estado
        const botonGestion = proceso.estado == 1
            ? `<button class="btn-gestionar btn-desactivar" 
                       onclick="desactivarProceso(${proceso.id_proceso})">
                   Desactivar
               </button>`
            : `<button class="btn-gestionar btn-reactivar" 
                       onclick="reactivarProceso(${proceso.id_proceso})">
                   Reactivar
               </button>`;

        htmlFilas += `
            <tr>
                <td>${proceso.nombre_proceso}</td>
                <td>${proceso.descripcion}</td>
                <td>${proceso.fecha_creacion}</td>
                <td>${proceso.documento}</td>
                <td>${proceso.nombre_creador}</td>
                <td>${botonGestion}</td>
            </tr>
        `;
    });

    //insertamos el recorrido en el cuerpo de la tabla
    cuerpoTabla.innerHTML = htmlFilas;
};

const desactivarProceso = async (id_Proceso) => {
    // confirmacion de desactivacion

    Swal.fire({
        icon: 'question',
        title: '¿Estas seguro que deseas desactivar este proceso?',
        showDenyButton: true,
        denyButtonText: 'No desactivar',
        confirmButtonText: 'Si, desactivar',
        theme: 'dark',
    }).then(async (response) => {

        if (response.isConfirmed) {
            try {
                const response = await fetch(ENDPOINT_DESACTIVAR, { //hacemos un fetch al endpoint de desactivar
                    method: 'POST', //definimos el motodo de la request
                    headers: {
                        'Content-Type': 'application/json' //header de la request
                    },
                    body: JSON.stringify({ id: id_Proceso }) //body de la request
                });

                const data = await response.json(); //transformamos la respuesta a json

                if (data.status === 'ok') { //validamos el estado
                    Swal.fire({
                        icon: 'success',
                        title: 'Proceso desactivado exitosamente',
                        theme: 'dark',
                        showConfirmButton: false,
                        timer: 1000,
                    });
                    cargarProcesos();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al desactivar el proceso',
                        text: data.mensaje,
                        theme: 'dark',
                        showConfirmButton: false,
                        timer: 1000,
                    });
                }

            } catch (error) { //capturamos errores
                console.error('Error:', error); //capturamos el error en la consola
                Swal.fire({
                    icon: 'error',
                    title: 'Error al desactivar el proceso',
                    text: 'Ocurrió un error al intentar desactivar el proceso.',
                    theme: 'dark',
                    showConfirmButton: false,
                    timer: 1000,
                });
            }
        }
    })
};

const reactivarProceso = async (id_Proceso) => {

    Swal.fire({
        icon: 'question',
        title: '¿Estas seguro que deseas reactivar este proceso?',
        showDenyButton: true,
        denyButtonText: 'No reactivar',
        confirmButtonText: 'Si, reactivar',
        theme: 'dark',
    }).then(async (response) => {

        if (response.isConfirmed) {
            try {
                const response = await fetch(ENDPOINT_REACTIVAR, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id_Proceso })
                });

                const data = await response.json();

                if (data.status === 'ok') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Proceso reactivado exitosamente',
                        theme: 'dark',
                        showConfirmButton: false,
                        timer: 1000,
                    });
                    cargarProcesos();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al reactivar el proceso',
                        text: data.mensaje,
                        theme: 'dark',
                        showConfirmButton: false,
                        timer: 1000,
                    });
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al reactivar el proceso',
                    text: 'Ocurrió un error al intentar reactivar el proceso.',
                    theme: 'dark',
                    showConfirmButton: false,
                    timer: 1000,
                });
            };
        }
    });
};


//Configuramos el modal y le damos interactividad (abrir, cerrar, guardar)
document.addEventListener('DOMContentLoaded', () => {
    cargarProcesos();
});
document.addEventListener('DOMContentLoaded', () => {

    const botonAbrir = document.getElementById('abrirModal');
    const botonCerrar = document.getElementById('cerrar-modal');
    const botonGuardar = document.getElementById('guardar-modal');
    const modal = document.getElementById('modal');
    const formulario = document.querySelector('.formulario');

    if (!botonAbrir || !botonCerrar || !modal || !formulario) {
        return;
    }

    console.log('Elementos del modal encontrados');

    botonAbrir.addEventListener('click', () => {
        console.log(' Abriendo modal...');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });

    botonCerrar.addEventListener('click', () => {
        console.log(' Cerrando modal...');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        formulario.reset();
    });

    modal.addEventListener('click', (evento) => {
        if (evento.target === modal) {
            console.log(' Cerrando modal (clic fuera)...');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            formulario.reset();
        }
    });

    document.addEventListener('keydown', (evento) => {
        if (modal.style.display === 'flex' && evento.key === 'Escape') {
            console.log(' Cerrando modal (tecla ESC)...');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            formulario.reset();
        }
    });


    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();



        const nombreProceso = document.getElementById('nombre-proceso').value;
        const descripcion = document.getElementById('descripcion').value;

        // Verifica los campos se hayan llenado
        if (!nombreProceso.trim()) {
            Swal.fire({
                icon: 'error',
                title: 'Por favor ingresa el nombre del proceso',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
            document.getElementById('nombre-proceso').focus();
            return;
        }

        if (!descripcion.trim()) {
            Swal.fire({
                icon: 'error',
                title: 'Por favor ingresa la descripción',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
            document.getElementById('descripcion').focus();
            return;
        }

        botonGuardar.disabled = true;
        botonGuardar.textContent = 'Creando...';

        try {
            const response = await fetch(ENDPOINT_CREAR, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nombre: nombreProceso,
                    descripcion: descripcion
                })
            });

            if (!response.ok) {
                throw new Error(`Error HTTP ${response.status}`);
            }

            const data = await response.json();

            if (data.status === 'ok') {
                console.log(' Proceso creado');
                Swal.fire({
                    icon: 'success',
                    title: 'Proceso creado exitosamente',
                    theme: 'dark',
                    showConfirmButton: false,
                    timer: 1000,
                });

                // Cierra el modal cambiando el estilo a none 
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                formulario.reset();


                cargarProcesos();
            } else {
                throw new Error(data.mensaje || 'Error al crear');
            }

        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error al crear el proceso',
                text: `Error: ${error.message}`,
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
        } finally {
            botonGuardar.disabled = false;
            botonGuardar.textContent = 'crear proceso';
        }
    });

    cargarProcesos();
});