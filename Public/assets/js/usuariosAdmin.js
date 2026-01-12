const ENDPOINT_LISTAR = '/listarUsuarios';
const ENDPOINT_OBTENER = '/modalUsuario';

console.log(ENDPOINT_OBTENER);

const cargarUsuarios = async () => {
    const cuerpoTabla = document.getElementById("tablaUsuarios");

    if(!cuerpoTabla) {
        console.error('No se encontró el cuerpo de la tabla');
        return;
    }

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
        const response = await fetch(ENDPOINT_LISTAR);

        if (!response.ok) {
            throw new Error(`Error HTTP ${response.status}: No se pudo conectar con el servidor`);
        }

        const data = await response.json();

        console.log('Datos recibidos:', data);

        if(data.status !== 'ok') {
            throw new Error(data.mensaje || 'Error desconocido');
        }

        if (!data.usuarios || !Array.isArray(data.usuarios) || data.usuarios.length === 0) {
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

        renderizarTablaUsuarios(data.usuarios, cuerpoTabla);

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

const renderizarTablaUsuarios = (usuarios, cuerpoTabla) => {
    let htmlFilas = '';

    usuarios.forEach((usuario) => {  
        const estadoUsuario = obtenerEstadoUsuario(usuario.id_estado);
        const rolUsuario = obtenerRolUsuario(usuario.id_rol);
        
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
    console.log(`✅ Se renderizaron ${usuarios.length} usuarios`);
};

// ✅ CORREGIDO: Sin 's' al final
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

// ✅ CORREGIDO: Parámetro 'rol' en lugar de 'estado'
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

const gestionarUsuario = async (documento) => {
    console.log(`👤 Gestionando usuario documento: ${documento}`);

    const modalElement = document.getElementById('modalUsuario');
    const modal = new bootstrap.Modal(modalElement);

    document.getElementById('modalUsuarioBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3">Cargando detalles del usuario...</p>
        </div>
    `;

    modal.show();

    try {
        const response = await fetch(ENDPOINT_OBTENER, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `usuario=${documento}`  // ✅ CORREGIDO: sin comilla extra
        });

        if (!response.ok) {
            throw new Error(`Error HTTP ${response.status}`);
        }

        const data = await response.json();

        console.log('Usuario obtenido:', data);

        if(data.status === 'ok' && data.usuario) {
            mostrarDetallesUsuario(data.usuario);
        } else {
            throw new Error(data.mensaje || 'No se pudo obtener el usuario');
        }
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

const mostrarDetallesUsuario = (usuario) => {
    const modalBody = document.getElementById('modalUsuarioBody');
    const modalTitle = document.getElementById('modalUsuarioLabel');

    modalTitle.textContent = `Usuario: ${usuario.nombre} ${usuario.apellido}`;

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

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Página cargada, iniciando carga de usuarios...');
    cargarUsuarios();
});