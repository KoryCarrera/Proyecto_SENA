//Configuramos el modal y le damos interactividad (abrir, cerrar, guardar)
document.addEventListener('DOMContentLoaded', () => {

    const botonAbrir = document.getElementById('abrirModalCrear');
    const botonCerrar = document.getElementById('cerrar-modal-crear');
    const botonGuardar = document.getElementById('guardar-modal-crear');
    const modal = document.getElementById('modalCrearUsuario');
    const formulario = document.querySelector('.formulario-crear');

    if (!botonAbrir || !botonCerrar || !modal || !formulario) {
        return;
    }

    console.log('Elementos del modal crear usuario encontrados');

    botonAbrir.addEventListener('click', () => {
        console.log(' Abriendo modal crear usuario...');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });

    botonCerrar.addEventListener('click', () => {
        console.log(' Cerrando modal crear usuario...');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        formulario.reset();
    });

    modal.addEventListener('click', (evento) => {
        if (evento.target === modal) {
            console.log(' Cerrando modal crear usuario (clic fuera)...');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            formulario.reset();
        }
    });

    document.addEventListener('keydown', (evento) => {
        if (modal.style.display === 'flex' && evento.key === 'Escape') {
            console.log(' Cerrando modal crear usuario (tecla ESC)...');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            formulario.reset();
        }
    });

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const rol = document.getElementById('crearRol').value;
        const nombre = document.getElementById('crearNombre').value;
        const apellido = document.getElementById('crearApellido').value;
        const documento = document.getElementById('crearDocumento').value;
        const email = document.getElementById('crearEmail').value;
        const contrasena = document.getElementById('crearContrasena').value;

        // Verificamos que los campos se hayan llenado
        if (!rol || rol === '') {
            Swal.fire({
                icon: 'error',
                title: 'Por favor selecciona un rol',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
            document.getElementById('crearRol').focus();
            return;
        }

        if (!nombre.trim()) {
            Swal.fire({
                icon: 'error',
                title: 'Por favor ingresa el nombre',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
            document.getElementById('crearNombre').focus();
            return;
        }

        if (!apellido.trim()) {
            Swal.fire({
                icon: 'error',
                title: 'Por favor ingresa el apellido',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
            document.getElementById('crearApellido').focus();
            return;
        }

        if (!documento.trim()) {
            Swal.fire({
                icon: 'error',
                title: 'Por favor ingresa el documento',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
            document.getElementById('crearDocumento').focus();
            return;
        }

        if (!email.trim()) {
            Swal.fire({
                icon: 'error',
                title: 'Por favor ingresa el correo',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
            document.getElementById('crearEmail').focus();
            return;
        }

        if (!contrasena.trim()) {
            Swal.fire({
                icon: 'error',
                title: 'Por favor ingresa la contraseña',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
            document.getElementById('crearContrasena').focus();
            return;
        }

        var parametrosUsuarios = {
            'documento': documento,
            'rol': rol,
            'nombre': nombre,
            'apellido': apellido,
            'email': email,
            'contrasena': contrasena
        }

        botonGuardar.disabled = true;
        botonGuardar.textContent = 'Creando...';

        try {

            $.ajax({
                data: parametrosUsuarios,
                url: ENDPOINT_INSERTAR,
                type: 'POST',
                dataType: 'json',

                success: function (data) {

                    if (data.status === 'ok') {
                        console.log(' Usuario creado');
                        Swal.fire({
                            icon: 'success',
                            title: `${data.mensaje}`,
                            theme: 'dark',
                            showConfirmButton: false,
                            timer: 1000,
                        });

                        // Cierra el modal cambiando el estilo a none
                        modal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                        formulario.reset();

                        cargarUsuarios();
                    } else {
                        throw new Error(data.mensaje || 'Error al crear el usuario');
                    }
                }, error: function (jqXHR, textStatus, errorThrown) {
                    console.error("Error en la comunicación con el servidor:", textStatus, errorThrown);

                    Swal.fire({
                        icon: "error",
                        title: "¡Ha ocurrido un error interno!",
                        theme: 'dark',
                        timer: 1500,
                        showConfirmButton: false,
                    });
                }
            });
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error al crear el usuario',
                text: `Error: ${error.message}`,
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
        } finally {
            botonGuardar.disabled = false;
            botonGuardar.textContent = 'Crear Usuario';
        }
    });
});
