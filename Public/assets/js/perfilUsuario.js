const ENDPOINT_EDITAR = "/ConfiUsuario";

//Capturamos los valores de los campos del formulario
const nuevoNombre = document.getElementById("nuevoNombre");
const nuevoApellido = document.getElementById("nuevoApellido");
const nuevoEmail = document.getElementById("nuevoEmail");
const numeroNuevo = document.getElementById("numeroNuevo");
const contrasenaNueva = document.getElementById("contrasenaNueva");
const confirmarContrasena = document.getElementById("confirmarContrasena");
const infoImportante = document.getElementById("infoImportante");

//capturamos el botón de guardar cambios
const btnGuardarCambios = document.getElementById("btnActualizar");

//Insertamos el contenido por defecto de la infoImportante para dar claridad al usuario

if (infoImportante) {
    infoImportante.textContent = "Tenga en cuenta que cualquier cambio realizado en su perfil será reportado al administrador, incluyendo un registro de su información antigua y la nueva. Además, por tratarse de información sensible, la modificación de su contraseña requerirá la aprobación previa de la administración antes de hacerse efectiva.";
}

btnGuardarCambios.addEventListener('click', function (event) {

    //Prevenimso el recargado de la pagina
    event.preventDefault();

    //validamos que se ingrese al menos un campo para actualizar
    if (!nuevoNombre.value && !nuevoApellido.value && !nuevoEmail.value && !numeroNuevo.value && !contrasenaNueva.value && !confirmarContrasena.value) {

        Swal.fire({
            icon: 'warning',
            title: 'Campos Vacíos',
            text: 'Por favor, ingresa al menos un campo para actualizar tu perfil.',
            showConfirmButton: false,
            timer: 2000,
            theme: 'dark'
        });
        return; // Detenemos la ejecución si no se ingresó ningún campo
    };

    //validamos que se haya tratado de cambiar la contraseña, y que ambas contraseñas coincidan

    if (contrasenaNueva.value && !confirmarContrasena.value) {
        Swal.fire({
            icon: 'warning',
            title: 'Confirmar Contraseña',
            text: 'Por favor, confirma tu nueva contraseña.',
            showConfirmButton: false,
            timer: 2000,
            theme: 'dark'
        });
        return; // Detenemos la ejecución si no se ingresó la confirmación de contraseña
    };

    if (contrasenaNueva.value && confirmarContrasena.value && contrasenaNueva.value !== confirmarContrasena.value) {
        Swal.fire({
            icon: 'error',
            title: 'Contraseñas No Coinciden',
            text: 'La nueva contraseña y la confirmación no coinciden. Por favor, verifica e intenta nuevamente.',
            showConfirmButton: false,
            timer: 2000,
            theme: 'dark'
        });
        contrasenaNueva.value = "";
        confirmarContrasena.value = "";

        return; // Detenemos la ejecución si las contraseñas no coinciden

    };

    if (contrasenaNueva.value && contrasenaNueva.value.length < 6) {
        Swal.fire({
            icon: 'error',
            title: 'Contraseña Demasiado Corta',
            text: 'La contraseña debe tener al menos 6 caracteres.',
            showConfirmButton: false,
            timer: 2000,
            theme: 'dark'
        });
        contrasenaNueva.value = "";
        confirmarContrasena.value = "";
        return;
    }

    //Si se pasa la validaciones se pide una confirmacion de contraseña para proceder con la actualización
    if (nuevoNombre.value || nuevoApellido.value || nuevoEmail.value || numeroNuevo.value || contrasenaNueva.value) {

        Swal.fire({
            title: 'Verificación de identidad',
            text: 'Por favor, ingresa tu contraseña actual para confirmar los cambios en tu perfil.',
            input: 'password',
            inputAttributes: {
                autocapitalize: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            theme: 'dark',
            preConfirm: (password) => {
                if (!password) {
                    Swal.showValidationMessage('Por favor, ingresa tu contraseña actual para confirmar los cambios.');
                }
                return password;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                //Enviamos los datos nuevos y la contraseña dada por el usuario para validar su identidad, el back lo validará y responderá si la actualización fue exitosa o no

                const datosActualizados = {
                    'nombre': nuevoNombre.value,
                    'apellido': nuevoApellido.value,
                    'email': nuevoEmail.value,
                    'numero': numeroNuevo.value,
                    'contrasena': contrasenaNueva.value,
                    'password_actual': result.value
                };

                try {
                    $.ajax({
                        data: datosActualizados,
                        url: ENDPOINT_EDITAR,
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'ok') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Perfil Actualizado',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 2000,
                                    theme: 'dark'
                                });

                                infoImportante.textContent = "Tu perfil ha sido actualizado exitosamente. Recuerda que cualquier cambio en tu información será registrado y reportado al administrador para garantizar la seguridad de tu cuenta. Si has cambiado tu contraseña, esta modificación requerirá la aprobación previa de la administración antes de hacerse efectiva, por lo que te recomendamos estar atento a cualquier comunicación relacionada con este proceso.";

                                //Limpiamos los campos del formulario
                                nuevoNombre.value = "";
                                nuevoApellido.value = "";
                                nuevoEmail.value = "";
                                numeroNuevo.value = "";
                                contrasenaNueva.value = "";
                                confirmarContrasena.value = "";
                            };

                            if (response.status === 'error') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error al Actualizar',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 2000,
                                    theme: 'dark'
                                });

                                //Limpiamos los campos del formulario
                                nuevoNombre.value = "";
                                nuevoApellido.value = "";
                                nuevoEmail.value = "";
                                numeroNuevo.value = "";
                                contrasenaNueva.value = "";
                                confirmarContrasena.value = "";
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

                            //Limpiamos los campos del formulario
                            nuevoNombre.value = "";
                            nuevoApellido.value = "";
                            nuevoEmail.value = "";
                            numeroNuevo.value = "";
                            contrasenaNueva.value = "";
                            confirmarContrasena.value = "";
                        }
                    });
                } catch (error) {
                    console.error("Error al enviar la solicitud:", error);

                    Swal.fire({
                        icon: "error",
                        title: "¡Ha ocurrido un error interno!",
                        theme: 'dark',
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    //Limpiamos los campos del formulario
                    nuevoNombre.value = "";
                    nuevoApellido.value = "";
                    nuevoEmail.value = "";
                    numeroNuevo.value = "";
                    contrasenaNueva.value = "";
                    confirmarContrasena.value = "";
                }
            }
        });
    }
});

//vamos a crear la funcion para que cuando se active el checkbox se pueda activar el 2fa

// ── Activar/Desactivar 2FA ─────────────────────────
const activar2FA = document.getElementById('activar2FA');

if (activar2FA) {
    activar2FA.addEventListener('change', function () {
        const formData = new FormData();
        formData.append('estado_2fa', this.checked ? 1 : 0);

        fetch('/actualizar2FA', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                Swal.fire({
                    icon: 'success',
                    title: this.checked ? '2FA Activado' : '2FA Desactivado',
                    theme: 'dark',
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                this.checked = !this.checked; // Revertir visualmente el botón
                Swal.fire({
                    icon: 'error',
                    title: 'Error al actualizar',
                    text: data.mensaje || 'Ocurrió un error inesperado',
                    theme: 'dark',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        })
        .catch(error => {
            this.checked = !this.checked; // Revertir visualmente el botón
            console.error('Error de fetch:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'Hubo un problema al comunicarse con el servidor',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1500
            });
        });
    });
}
