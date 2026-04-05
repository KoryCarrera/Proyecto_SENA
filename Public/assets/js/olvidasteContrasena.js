const ENDPOINT_OLVIDASTE = '/olvidastePass';
const botonOlvidaste = document.getElementById('olvidarContrasena');

botonOlvidaste.addEventListener('click', function () {
    Swal.fire({
        title: '¿Has olvidado tu contraseña?',
        html: `
            <div style="text-align: left; font-size: 14px; margin-top: 10px;">
                <label for="swal-email" style="color: #cbd5e1; display: block; margin-bottom: 5px;">Correo electrónico</label>
                <input type="email" id="swal-email" class="swal2-input" style="width: 85%; margin: 0 auto 15px; display: block;" placeholder="ejemplo@correo.com" required>
                
                <label for="swal-documento" style="color: #cbd5e1; display: block; margin-bottom: 5px;">Número de documento</label>
                <input type="number" id="swal-documento" class="swal2-input" style="width: 85%; margin: 0 auto 15px; display: block;" placeholder="Ej: 1000000000" required>
                
                <label for="swal-nombre" style="color: #cbd5e1; display: block; margin-bottom: 5px;">Primer nombre</label>
                <input type="text" id="swal-nombre" class="swal2-input" style="width: 85%; margin: 0 auto 15px; display: block;" placeholder="Tu primer nombre" required>
                
                <label for="swal-telefono" style="color: #cbd5e1; display: block; margin-bottom: 5px;">Número de teléfono</label>
                <input type="number" id="swal-telefono" class="swal2-input" style="width: 85%; margin: 0 auto; display: block;" placeholder="Tu celular" required>
            </div>
        `,
        background: '#1e293b', // Color slate-800
        color: '#ffffff',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6366f1', // Color indigo-500
        cancelButtonColor: '#ef4444',  // Color red-500
        showLoaderOnConfirm: true,     // Activa el spinner de carga
        
        // El preConfirm es perfecto para capturar los datos y hacer la validación
        preConfirm: () => {
            const email = document.getElementById('swal-email').value;
            const documento = document.getElementById('swal-documento').value;
            const nombre = document.getElementById('swal-nombre').value;
            const telefono = document.getElementById('swal-telefono').value;

            // 1. Validación básica en el frontend
            if (!email || !documento || !nombre || !telefono) {
                Swal.showValidationMessage('Todos los campos son obligatorios');
                return false; // Detiene el proceso
            }

            // 2. Retornamos una Promesa con el AJAX
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: ENDPOINT_OLVIDASTE,
                    method: 'POST',
                    data: {
                        email: email,
                        documento: documento,
                        nombre: nombre,
                        telefono: telefono
                    },
                    dataType: 'json',
                    success: function (response) {
                        // Verificamos si el backend respondió con 'ok' o 'success'
                        if (response.status === 'ok' || response.status === 'success') {
                            resolve(response); // Pasamos la respuesta al .then()
                        } else {
                            // Si los datos no coinciden, mostramos el error sin cerrar el modal
                            Swal.showValidationMessage(response.mensaje || response.message || 'Datos incorrectos');
                            resolve(false); 
                        }
                    },
                    error: function () {
                        Swal.showValidationMessage('Error de conexión con el servidor.');
                        resolve(false);
                    }
                });
            });
        },
        // Evitamos que el usuario cierre el modal haciendo clic afuera mientras carga
        allowOutsideClick: () => !Swal.isLoading() 
        
    }).then((result) => {
        // 3. Este .then() se ejecuta solo si el preConfirm hizo el resolve(response) exitosamente
        if (result.isConfirmed && result.value) {
            Swal.fire({
                icon: 'success',
                title: '¡Correo enviado con éxito!',
                text: result.value.mensaje || result.value.message || 'Revisa tu bandeja de entrada.',
                background: '#1e293b',
                color: '#ffffff',
                confirmButtonColor: '#6366f1',
                timer: 3000,
                showConfirmButton: false
            });
        }
    });
});