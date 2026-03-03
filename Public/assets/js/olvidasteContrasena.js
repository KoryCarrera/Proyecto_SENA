const ENDPOINT_OLVIDASTE = ''
const botonOlvidaste = document.getElementById('olvidarContrasena');

botonOlvidaste.addEventListener('click', function (){

    Swal.fire({
        title: '¿Has olvidado tu contraseña? ¡Rellena el formulario!',
        html: `
        <label for="email">Ingrese el correo electronico con el que se registro en el sistema</label>
        <input type="email" id="email" class="swal2-input" placeholder="Correo electrónico" required>
        <br><br>
        <label for="documento">Ingrese el número de documento</label>
        <input type="number" id="documento" class="swal2-input" placeholder="Número de documento" required>
        <br><br>
        <label for="nombre">Ingrese su nombre completo</label>
        <input type="text" id="nombre" placeholder="Nombre completo" class="swal2-input" required>
        <br><br>
        <label for="telefono">Ingrese el número de teléfono</label>
        <input type="number" id="telefono" placeholder="Número de teléfono" class="swal2-input" required>
        `,
        showCancelButton: true,
        theme: 'dark',
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',

        preConfirm: () => {
            const email = document.getElementById('email').value;
            const documento = document.getElementById('documento').value;
            const nombre = document.getElementById('nombre').value;
            const telefono = document.getElementById('telefono').value;

            return {
                'email': email,
                'documento': documento,
                'nombre': nombre,
                'telefono': telefono
            }.then((data) => {
                $.ajax({
                    url: ENDPOINT_OLVIDASTE,
                    method: 'POST',
                    data: data,
                    success: function (response) {
                        if (response.status === 'ok') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Correo enviado con exito',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000,
                                theme: 'dark'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al enviar correo',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000,
                                theme: 'dark'
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en la solicitud',
                            text: 'Ocurrió un error al enviar la solicitud. Por favor, inténtalo de nuevo.',
                            showConfirmButton: false,
                            timer: 2000,
                            theme: 'dark'
                        });
                    }
                })
            })
        }
    })
})