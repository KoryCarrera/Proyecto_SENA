const ENDPOINT_LOGOUT = '/logout';

//Capturamos el boton de logout
const btnLogout = document.getElementById('logoutButton');

//Capturamos el input oculto del token CSRF
const csrfTokenInput = document.getElementById('csrf_token').value;

btnLogout.addEventListener('click', function(event) {

    console.log('Detecté el click en el botón de logout');
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Quieres cerrar sesión?",
        icon: 'warning',
        theme: 'dark',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Si el usuario confirma, enviamos la solicitud de logout
            $.ajax({
                url: ENDPOINT_LOGOUT,
                method: 'POST',
                data: {
                    logout: 'logout',
                    csrf_token: csrfTokenInput
                },
                success: function(response) {
                    // Redirige al usuario a la página de inicio de sesión o a la página principal
                    location.reload();
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error', 'No se pudo cerrar sesión. Inténtalo de nuevo.', 'error');
                }
            });
        }
    });
});