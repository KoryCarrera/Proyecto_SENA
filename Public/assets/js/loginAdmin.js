console.log('estoy bien referenciado');

var enviar = document.getElementById("ingresar");

enviar.addEventListener('click', function login() {
    var documento = document.getElementById("documento").value;
    var contraseña = document.getElementById("password").value;
    var csrfToken = document.getElementById("csrf_token").value;

    var parametros = {
        'documento': documento,
        'password': contraseña,
        'csrf_token': csrfToken
    }
    console.log("se esta ejecutando");
    $.ajax({
        data: parametros,
        url: '../../app/controllers/loginAdmin.php',
        type: 'POST',
        dataType: 'json',
        success: function redireccion(respuesta) {
            if (respuesta.status === 'ok') {
                window.location.href = respuesta.redirect;
            } else if (respuesta.status === 'error') {
                alert(respuesta.mensaje);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en la comunicación con el servidor:", textStatus, errorThrown);
            alert("Ocurrió un error de conexión.");
        }
});
}
);