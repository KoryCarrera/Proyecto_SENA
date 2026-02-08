//definimos los endpoints en unas constantes
const ENDPOINT_LISTAR = '/listarUsuarios';
const ENDPOINT_OBTENER = '/modalUsuario';

const cargarUsuarios = async () => { //Realizamos una async function
    const cuerpoTabla = document.getElementById("tablaUsuarios"); //capturamos el cuerpo de la tabla

    if(!cuerpoTabla) { //Validamos que encontramos la tabla
        console.error('No se encontró el cuerpo de la tabla');
        return;
    }

    //Insertamos el "cargando..." mientras esperamos una response por parte del endpoint
    cuerpoTabla.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 mb-0">Cargando usuarios...</p>
            </td>
        </tr>
    `;

    try {
        //hacemos un fecth al endpoint a listar
        const response = await fetch(ENDPOINT_LISTAR);

        if (!response.ok) { //Manejamos errores personalizados
            throw new Error(`Error HTTP ${response.status}: No se pudo conectar con el servidor`);
        }

        const data = await response.json(); //convertimos la respuesta a json

        if(data.status !== 'ok') { //Verificamos que el status NO es ok y lanzamos un error en tal caso
            throw new Error(data.mensaje || 'Error desconocido');
        }

        if (!data.usuarios || !Array.isArray(data.usuarios) || data.usuarios.length === 0) { //Validamos si hay usuarios o no
            cuerpoTabla.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-warning">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                        <p class="mt-2 mb-0 fw-bold">No hay usuarios registrados</p>
                    </td>
                </tr>
            `;
            return;
        }

        renderizarTablaUsuarios(data.usuarios, cuerpoTabla); //ejecutamos una funcion que definimeros mas adelante

        //manejo de errores
    } catch (error) {
        console.error('Error al cargar los usuarios:', error);
        cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-danger">
                    <p class="fw-bold">Error: ${error.message}</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="cargarUsuarios()">
                        Reintentar
                    </button>
                </td>
            </tr>
        `;
    }
};

const renderizarTablaUsuarios = (usuarios, cuerpoTabla) => { //definimos la funcion anteriormente declarada
    let htmlFilas = ''; //inicializamos la variable de html vacia

    usuarios.forEach((usuario) => {  //*recorremos los roles y usuarios para personalizar su aspecto segun su contenido
        const estadoUsuario = obtenerEstadoUsuario(usuario.id_estado);
        const rolUsuario = obtenerRolUsuario(usuario.id_rol);
        
        //Recorremos e insertamos datos en la variable html
        htmlFilas += `
            <tr>
                <th scope="row">${usuario.documento}</th>
                <td>${usuario.nombre}</td>
                <td>${usuario.apellido}</td>
                <td>${usuario.email}</td>
                <td>${estadoUsuario}</td>
                <td>${rolUsuario}</td>
                <td>
                    <button class="btn-table" onclick="gestionarUsuario('${usuario.documento}')">
                        <i class="bi bi-eye"></i> Gestionar
                    </button>
                </td>
            </tr>           
        `;
    });

    cuerpoTabla.innerHTML = htmlFilas;
    //insertamos el html previamente hecho
};

//funcion para personalizar segun estado usando clases de bootstrap
const obtenerEstadoUsuario = (idEstado) => {
    switch (idEstado) {
        case 1:
            return `<span class="badge bg-success">Activo</span>`;
        case 2:
            return `<span class="badge bg-secondary">Inactivo</span>`;
        default:
            return `<span class="badge bg-secondary">Desconocido</span>`;
    }
};

//funcion para personalizar segun Rol usando clases de bootstrap
const obtenerRolUsuario = (idRol) => {
    switch (idRol) {
        case 1:
            return `<span class="badge bg-primary">Administrador</span>`;
        case 2:
            return `<span class="badge bg-info">Comisionado</span>`;
        default:
            return `<span class="badge bg-secondary">Sin rol</span>`;
    }
};

//Definimos funcion para gestionar usuarios anteriorimente declarada en el html
const gestionarUsuario = async (documento) => {

    //capturamos el modal
    const modalElement = document.getElementById('modalUsuario');
    const modal = new bootstrap.Modal(modalElement);

    //Hacemos la view de carga mientras busca el usuario
    document.getElementById('modalUsuarioBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3">Cargando detalles del usuario...</p>
        </div>
    `;

    modal.show(); //mostramos modal utilizando el metodo de la clase bootstrap

    try {
        //hacemos fetch al endpoint que nos va a retornar el usuario que buscamos
        const response = await fetch(ENDPOINT_OBTENER, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `usuario=${documento}`
        });

        //manejamos errores
        if (!response.ok) {
            throw new Error(`Error HTTP ${response.status}`);
        }

        //Convertimos la respuesta a json
        const data = await response.json();


        if(data.status === 'ok' && data.usuario) { //verificamos el status de la response
            mostrarDetallesUsuario(data.usuario);
        } else {
            throw new Error(data.mensaje || 'No se pudo obtener el usuario');
        }
        //Manejo de errores
    } catch (error) {
        console.error('Error al obtener el usuario:', error);
        document.getElementById('modalUsuarioBody').innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    }
};

//definimos la funcion anteriormente declarada
const mostrarDetallesUsuario = (usuario) => {

    //capturamos partes del modal
    const modalBody = document.getElementById('modalUsuarioBody');
    const modalTitle = document.getElementById('modalUsuarioLabel');

    //Insertamos el titulo del modal
    modalTitle.textContent = `Usuario: ${usuario.nombre} ${usuario.apellido}`;

    //Insertamos los datos del usuario en el modal
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Documento:</label>
                <p class="form-control-plaintext">${usuario.documento}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nombre Completo:</label>
                <p class="form-control-plaintext">${usuario.nombre} ${usuario.apellido}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Email:</label>
                <p class="form-control-plaintext">${usuario.email}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Rol:</label>
                <p class="form-control-plaintext">${obtenerRolUsuario(usuario.id_rol)}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Estado:</label>
                <p class="form-control-plaintext">${obtenerEstadoUsuario(usuario.id_estado)}</p>
            </div>
        </div>
    `;
};

//Agregamos un evento que al cargar el DOM ejecute la funcion de cargarUsuarios
document.addEventListener('DOMContentLoaded', () => {
    cargarUsuarios();
});