// ── Lógica de los campos OTP ───────────────────────────
const otpInputs = Array.from(document.querySelectorAll('.otp-input'));
const codigoHidden = document.getElementById('codigo_2fa');

function actualizarCodigo() {
    codigoHidden.value = otpInputs.map(i => i.value).join('');
}

otpInputs.forEach((input, index) => {

    // Solo permite números y avanza al siguiente campo
    input.addEventListener('input', (e) => {
        const val = e.target.value.replace(/[^A-Za-z0-9]/g, '');
        e.target.value = val ? val[0] : '';
        e.target.classList.toggle('filled', !!val);
        if (val && index < otpInputs.length - 1) otpInputs[index + 1].focus();
        actualizarCodigo();
    });

    // Retroceso: limpia el campo anterior
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && index > 0) {
            otpInputs[index - 1].value = '';
            otpInputs[index - 1].classList.remove('filled');
            otpInputs[index - 1].focus();
            actualizarCodigo();
        }
    });

    // Pegar código completo distribuye en los 6 campos
    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const paste = e.clipboardData.getData('text').replace(/[^A-Za-z0-9]/g, '').slice(0, 6);
        paste.split('').forEach((char, i) => {
            if (otpInputs[i]) {
                otpInputs[i].value = char;
                otpInputs[i].classList.add('filled');
            }
        });
        actualizarCodigo();
        const nextEmpty = otpInputs.find(inp => !inp.value);
        (nextEmpty || otpInputs[5]).focus();
    });
});

// Foco automático al primer campo
window.addEventListener('DOMContentLoaded', () => otpInputs[0].focus());

// ── Validación antes de enviar ─────────────────────────
document.getElementById('form2FA').addEventListener('submit', (e) => {
    actualizarCodigo();
    if (codigoHidden.value.length < 6) {
        e.preventDefault();
        mostrarError('Por favor ingresa los 6 dígitos del código.');
        otpInputs.find(i => !i.value)?.focus();
    }
});

function mostrarError(msg) {
    const alerta = document.getElementById('alertaError');
    document.getElementById('mensajeError').textContent = msg;
    alerta.classList.add('visible');
}

// ── Countdown para reenviar (30 s) ─────────────────────
let timeLeft = 30;
const countdownEl = document.getElementById('countdown');
const countdownText = document.getElementById('countdownText');
const btnReenviar = document.getElementById('btnReenviar');

function startCountdown(seconds) {
    timeLeft = seconds;
    btnReenviar.disabled = true;
    countdownEl.textContent = timeLeft;
    countdownText.textContent = '(en ' + timeLeft + 's)';

    const t = setInterval(() => {
        timeLeft--;
        countdownEl.textContent = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(t);
            btnReenviar.disabled = false;
            countdownText.textContent = '';
        }
    }, 1000);
}

// Inicia el countdown al cargar
startCountdown(30);

// Reenvía el código por AJAX
btnReenviar.addEventListener('click', () => {

    const data = new FormData();
    data.append('solicitud', true);

    fetch('/reenviar2FA', {
        method: 'POST',
        body: data,
    })
        .then(res => res.json())
        .then(data => {
            if (data.status == 'error') {
                Swal.fire({
                    icon: 'error',
                    title: '¡Ha ocurrido un error al validarte!',
                    text: `${data.mensaje}`,
                    theme: 'dark',
                    showConfirmButton: false,
                    timer: 1500,
                });
            };

            if (data.status == 'ok') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Se te ha enviado un nuevo codigo!',
                    theme: 'dark',
                    showConfirmButton: false,
                    timer: 1500,
                });
            }
        })
});

// Enviar boton para autentificarlo
const btnVerificar = document.getElementById('btnVerificar');

btnVerificar.addEventListener('click', function (e) {

    const formData = new FormData();
    formData.append('codigo', codigoHidden.value);
    // Prevenir recarga de pagina al enviar
    e.preventDefault();

    // Enviar codigo al archivo de autentificacion
    fetch('/auth2FA', {
        method: 'POST',
        body: formData,
    })
        .then(res => res.json())
        .then(data => {
            if (data.status == 'error') {
                Swal.fire({
                    icon: 'error',
                    title: '¡Ha ocurrido un error al validarte!',
                    text: `${data.mensaje}`,
                    theme: 'dark',
                    showConfirmButton: false,
                    timer: 1500,
                });
            };

            if (data.status == 'ok') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Te has validado con exito!',
                    theme: 'dark',
                    showConfirmButton: false,
                    timer: 1500,
                });

                setTimeout(() => {
                    window.location.href = data.redirect
                }, 1500);
            }


        })
}); // Cerramos btnVerificar.addEventListener

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
