
const ENDPOINT_ACTUALIZAR = '/actualizarPass';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formResetPassword');
    const btnActualizar = document.getElementById('btnActualizar');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const token = form.querySelector('input[name="token"]').value;
        const nuevaContrasena = document.getElementById('password').value;
        const confirmacionContrasena = document.getElementById('confirm_password').value;

        if (!nuevaContrasena || !confirmacionContrasena) {
            return mostrarAlerta('warning', '¡Campos incompletos!', 'Todos los campos son obligatorios.');
        }

        if (nuevaContrasena !== confirmacionContrasena) {
            return mostrarAlerta('error', 'Error', 'Las contraseñas no coinciden. Revisa bien.');
        }

        if (nuevaContrasena.length < 8) {
            return mostrarAlerta('warning', 'Contraseña débil', 'La contraseña debe tener al menos 8 caracteres.');
        }

        const originalBtnText = btnActualizar.innerHTML;
        btnActualizar.innerHTML = '<i class="bi bi-arrow-repeat animate-spin inline-block"></i> Actualizando...';
        btnActualizar.disabled = true;
        btnActualizar.classList.add('opacity-70', 'cursor-not-allowed');

        try {
            const formData = new FormData();
            formData.append('token', token);
            formData.append('nuevaContrasena', nuevaContrasena);
            formData.append('confirmacionContrasena', confirmacionContrasena);
            // 6. Petición al Backend con Fetch
            const response = await fetch(ENDPOINT_ACTUALIZAR, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            // 7. Evaluamos la respuesta del backend
            if (data.status === 'ok') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Exito!',
                    text: data.mensaje || 'Contraseña actualizada correctamente.',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '/login';
                    }
                });
            } else {
                // Si el backend tiró un error (ej: token expirado)
                mostrarAlerta('error', 'Algo salió mal', data.mensaje);
            }

        } catch (error) {
            console.error('Error en la petición:', error);
            mostrarAlerta('error', 'Error de conexión', 'No se pudo comunicar con el servidor.');
        } finally {
            // Restauramos el botón a su estado normal si hay error
            btnActualizar.innerHTML = originalBtnText;
            btnActualizar.disabled = false;
            btnActualizar.classList.remove('opacity-70', 'cursor-not-allowed');
        }
    });

    function mostrarAlerta(icono, titulo, texto) {
        Swal.fire({
            icon: icono,
            title: titulo,
            text: texto,
        });
    }
});