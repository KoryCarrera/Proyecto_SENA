//definimos los endpoints en unas constantes
const ENDPOINT_LISTAR = '/listarUsuarios';
const ENDPOINT_OBTENER = '/modalUsuario';
const ENDPOINT_INSERTAR = '/registrarUsuario';
const ENDPOINT_EDITAR = '/editarUsuario'
const ENDPOINT_ESTADO = '/cambiarEstadoUsuario'

// La lógica de crear usuario ahora está en modal_usuarios_Admin.js


const cargarUsuarios = async () => { //Realizamos una async function
    const cuerpoTabla = document.getElementById("tablaUsuarios"); //capturamos el cuerpo de la tabla

    if (!cuerpoTabla) { //Validamos que encontramos la tabla

        Swal.fire({
            icon: 'error',
            title: 'No existe la tabla casos!',
            showConfirmButton: false,
            timer: 1000,
            theme: 'dark',
        })

        return;
    }

        if ($.fn.DataTable.isDataTable("#tablaUsuario")) {
            $("#tablaUsuario").DataTable().destroy();
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

        if (data.status !== 'ok') { //Verificamos que el status NO es ok y lanzamos un error en tal caso
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

        Swal.fire({
            icon: 'error',
            title: '¡Error al cargar usuarios!',
            showConfirmButton: false,
            timer: 1000,
            theme: 'dark',
        });
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

const renderizarTablaUsuarios = async (usuarios, cuerpoTabla) => { //definimos la funcion anteriormente declarada
    let htmlFilas = ''; //inicializamos la variable de html vacia

    usuarios.forEach((usuario) => {  //*recorremos los roles y usuarios para personalizar su aspecto segun su contenido
        const estadoUsuario = obtenerEstadoUsuario(usuario.id_estado);
        const rolUsuario = obtenerRolUsuario(usuario.id_rol);

        //Recorremos e insertamos datos en la variable html]
        htmlFilas += `
            <tr>
                <th scope="row">${usuario.documento}</th>
                <td>${usuario.nombre}</td>
                <td>${usuario.apellido}</td>
                <td>${usuario.email}</td>
                <td>${estadoUsuario}</td>
                <td>${usuario.ultimo_inicio_sesion ?? 'N/A'}</td>
                <td>${usuario.vigencia_usuario}</td>
                <td>${rolUsuario}</td>
                <td>
                    <button class="btn-table bg-blue-600 hover:bg-blue-500 px-3 py-2 rounded-lg text-white" onclick="gestionarUsuario('${usuario.documento}')">
                        <i class="bi bi-gear-wide-connected"></i> 
                    </button>
                </td>
            </tr>           
        `;


    });

    cuerpoTabla.innerHTML = htmlFilas;
    //insertamos el html previamente hecho

    // Inicializamos DataTables DESPUÉS de que los datos estén en el DOM
    var table = $("#tablaUsuario").DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        autoWidth: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        dom: "rti", // Sin paginación interna, usamos la visual personalizada
        drawCallback: function () {
            actualizarPaginacionVisualUsuarios(table);
        },

    });

    $("#buscarUsuarios").on("keyup", function () {
        table.search(this.value).draw();
    });

    // El select de cantidad debe estar aquí dentro donde 'table' existe
    $("#filtroCantidadUsuarios")
        .off("change")
        .on("change", function () {
            const valor = parseInt($(this).val());
            table.page.len(valor).draw();
        });
};

//funcion para personalizar segun estado usando clases de bootstrap
const obtenerEstadoUsuario = (idEstado) => {
    switch (idEstado) {
        case 1:
            return `<span class="badge bg-success px-3 py-2 rounded-lg text-white">Activo</span>`;
        case 0:
            return `<span class="badge bg-secondary px-3 py-2 rounded-lg text-white">Inactivo</span>`;
        default:
            return `<span class="badge bg-secondary px-3 py-2 rounded-lg text-white">Desconocido</span>`;
    }
};

//funcion para personalizar segun Rol usando clases de bootstrap
const obtenerRolUsuario = (idRol) => {
    switch (idRol) {
        case 1:
            return `<span class="badge bg-primary px-3 py-2 rounded-lg text-white">Administrador</span>`;
        case 2:
            return `<span class="badge bg-info px-3 py-2 rounded-lg text-white">Comisionado</span>`;
        default:
            return `<span class="badge bg-secondary px-3 py-2 rounded-lg text-white">Sin rol</span>`;
    }
};

//inicializamos una variable vacia para luego usarla para editar al usuario
let usarioEditable = null;

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


        if (data.status === 'ok' && data.usuario) { //verificamos el status de la response
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

//funcion para cambiar estado de usuarios
const cambiarEstadoUsuario = async (nuevoDocumento, nuevoEstado) => {
    cerrarModal();

    //Definimos los textos dinámicamente según el estado
    const esDesactivar = nuevoEstado == 0;
    const accion = esDesactivar ? 'desactivar' : 'reactivar';
    const colorBtn = esDesactivar ? '#dc3545' : '#198754';
    const advertencia = esDesactivar
        ? 'Al desactivar, sus casos pasarán a "Por asignar".'
        : 'Volverá a tener acceso al sistema con su rol previo.';

    //Pedimos confirmación y el motivo en una sola alerta
    const { value: motivo, isConfirmed } = await Swal.fire({
        title: `¿Estás seguro de querer ${accion} este usuario?`,
        text: advertencia,
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Indique el motivo de esta acción:',
        inputPlaceholder: 'Escriba brevemente el porqué...',
        inputAttributes: { 'aria-label': 'Escriba el motivo' },
        showCancelButton: true,
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: colorBtn,
        theme: 'dark',
        //No deja continuar si no se escribe el motivo
        inputValidator: (value) => {
            if (!value) return '¡El motivo es obligatorio para el historial!';
        }
    });

    // Si canceló la alerta, no hacemos nada
    if (!isConfirmed) return;

    //Ejecutamos la petición AJAX
    try {
        const res = await $.ajax({
            url: ENDPOINT_ESTADO,
            type: 'POST',
            dataType: 'json',
            data: {
                documento: nuevoDocumento,
                estado: nuevoEstado,
                motivo: motivo
            }
        });

        if (res.status !== 'ok') throw new Error(res.mensaje);

        Swal.fire({
            icon: 'success',
            title: `El usuario se ha ${accion} con éxito`,
            theme: 'dark',
            timer: 1500,
            showConfirmButton: false
        });

        cargarUsuarios(); //Recargamos la tabla

    } catch (error) {
        console.error(`Error al ${accion} usuario:`, error);
        Swal.fire({
            icon: 'error',
            title: 'Error en la operación',
            text: error.message || 'No se pudo conectar con el servidor',
            theme: 'dark'
        });
    }
};

//definimos la funcion anteriormente declarada
const mostrarDetallesUsuario = (usuario) => {

    //guardamos al usuario seleccionado en una variable
    usarioEditable = usuario;

    //capturamos partes del modal
    const modalFooter = document.getElementById('modalFooter');
    const modalBody = document.getElementById('modalUsuarioBody');
    const modalTitle = document.getElementById('modalUsuarioLabel');

    //Insertamos el titulo del modal
    modalTitle.textContent = `Usuario: ${usuario.nombre} ${usuario.apellido}`;

    //inicializamos variable vacia para poder usarla fuera del bloque if
    let btnEstado = '';

    //Determinamos el color y texto del boton reactivar o activar
    if (usarioEditable.id_estado != 1) {
        btnEstado = `<button type="button" class="btn btn-success" onclick="cambiarEstadoUsuario('${usuario.documento}', 1)">Reactivar</button>`;
    } else {
        btnEstado = `<button type="button" class="btn btn-danger " onclick="cambiarEstadoUsuario('${usuario.documento}', 0)">Desactivar</button>`;
    }

    //insertamos el footer
    modalFooter.innerHTML = `
    ${btnEstado}
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="habilitarEdicion()">
            <i class="bi bi-pencil"></i> Editar Usuario
        </button>
    `;

    //Insertamos los datos del usuario en el body
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Documento:</label>
                <p class="form-control-plaintext text-white">${usuario.documento}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nombre Completo:</label>
                <p class="form-control-plaintext text-white">${usuario.nombre} ${usuario.apellido}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Email:</label>
                <p class="form-control-plaintext text-white">${usuario.email}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Rol:</label>
                <p class="form-control-plaintext text-white">${obtenerRolUsuario(usuario.id_rol)}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Estado:</label>
                <p class="form-control-plaintext">${obtenerEstadoUsuario(usuario.id_estado)}</p>
            </div>
            <div class="col-md-6 mb-3 ">
                <label class="form-label fw-bold">Vigencia:</label>
                <p class="form-control-plaintext text-white">${usuario.vigencia_usuario}</p>
            </div>
        </div>
    `;
};

//funcion para cerrar modal de bootstrap 5
const cerrarModal = () => {
    const modalElement = document.getElementById('modalUsuario');
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
        modal.hide();
    }
};

//función para guardar cambios del usuario
const guardarCambios = () => {

    //Capturamos valores de los inputs
    const emailNuevo = document.getElementById('emailNuevo').value || '';
    const nombreNuevo = document.getElementById('nombreNuevo').value || '';
    const apellidoNuevo = document.getElementById('apellidoNuevo').value || '';
    const rolNuevo = document.getElementById('rolNuevo').value || '';
    const documento = document.getElementById('documentoBuscado').value || '';
    const numero = document.getElementById('numeroNuevo').value || '';
    const nuevaPassword = document.getElementById('GenerarContraseña').checked || '';

    //Asignamos a un objeto para su manejo
    const parametros = {
        'nombre': nombreNuevo,
        'apellido': apellidoNuevo,
        'rol': rolNuevo,
        'documento': documento,
        'generar_password': nuevaPassword,
        'numero': numero,
        'email': emailNuevo
    }
    $.ajax({
        data: parametros,
        url: ENDPOINT_EDITAR,
        type: 'POST',
        dataType: 'json',

        success: function (response) {
            cerrarModal(); //cerramos el modal
            cargarUsuarios() //refrescamos la tabla

            //mostramos el mensaje en una alerta
            Swal.fire({
                icon: 'success',
                title: `${response.mensaje}`,
                showConfirmButton: false,
                theme: 'dark',
                timer: 1000
            })
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en la comunicación con el servidor:", textStatus, errorThrown);

            cerrarModal(); //cerramos el modal
            cargarUsuarios() //refrescamos la tabla

            //Mostramos alerta estetica
            Swal.fire({
                icon: 'error',
                title: '¡Ha ocurrido un error interno!',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,

            });
        }
    });
}

//funcion para editar al usuario
const habilitarEdicion = () => {

    //capturamos el cuerpo y el footer del modal
    const modalBody = document.getElementById('modalUsuarioBody');
    const modalFooter = document.getElementById('modalFooter');

    //Usamos como plantilla el anterior html y cambiamos p por input
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nombre:</label>
                <input type="text" class="form-control glass-input" id="nombreNuevo" value="${usarioEditable.nombre}" maxlength="50">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Apellido:</label>
                <input type="text" class="form-control glass-input" id="apellidoNuevo" value="${usarioEditable.apellido}" maxlength="50">
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Email:</label>
                <input type="email" class="form-control glass-input" id="emailNuevo" value="${usarioEditable.email}" maxlength="100">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Rol:</label>
                <select class="form-select glass-input" id="rolNuevo">
                    <option  class="bg-slate-900" value="1" class"bg-slate-900" ${usarioEditable.id_rol == 1 ? 'selected' : ''}>Administrador</option>
                    <option class="bg-slate-900" value="2"  ${usarioEditable.id_rol == 2 ? 'selected' : ''}>Comisionado</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Numero:</label>
                <input type="number" class="form-control glass-input" id="numeroNuevo" value="${usarioEditable.numero}" maxlength="100">
            </div>

            <input type="hidden" id="documentoBuscado" value="${usarioEditable.documento}">
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Estado:</label>
                <p class="form-control-plaintext">${obtenerEstadoUsuario(usarioEditable.id_estado)}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold d-block">Nueva Contraseña:</label>
                <input type="checkbox" id="GenerarContraseña" class="btn-check" autocomplete="off">
                <label class="btn btn-outline-primary" for="GenerarContraseña">
                    <i class="bi bi-key"></i> Generar Contraseña
                </label>
            </div>
        </div>
    `;

    //insertamos en el footer los boton onclick
    modalFooter.innerHTML = `
        <button type="button" class="btn btn-danger" onclick="mostrarDetallesUsuario(usarioEditable)">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="guardarCambios()">
            <i class="bi bi-check-circle"></i> Guardar Cambios
        </button>
    `;
};

// ─── Paginación visual personalizada ─────────────────────────────────────────
const actualizarPaginacionVisualUsuarios = (table) => {
    if (!table) return;

    const info = table.page.info(); // { page (0-based), pages, ... }
    const paginaActual = info.page;
    const totalPaginas = info.pages;

    const btnPrev = document.getElementById("btnPaginaAnteriorUsuarios");
    const btnNext = document.getElementById("btnPaginaSiguienteUsuarios");
    const contenedor = document.getElementById("pagBotonesUsuarios");

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

//Agregamos un evento que al cargar el DOM ejecute la funcion de cargarUsuarios
document.addEventListener('DOMContentLoaded', () => {
    cargarUsuarios();
});