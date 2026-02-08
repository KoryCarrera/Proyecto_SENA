//Se captura el boton de enviar para darle un evento
var enviar = document.getElementById("ingresar");

enviar.addEventListener('click', function login() { //Se le agrega el evento click y una función
    //Se capturan los inputs del usuario
    var documento = document.getElementById("documento").value;
    var contraseña = document.getElementById("password").value;
    var csrfToken = document.getElementById("csrf_token").value;

    //Se asignan a un objeto para manejarlo mas facilmente
    var parametros = {
        'documento': documento,
        'password': contraseña,
        'csrf_token': csrfToken
    }
    $.ajax({ //utilizamos AJAX para la request
        data: parametros, //Enviamos en data el objeto
        url: '/loginAdmin/auth', //Definimos url (usando la del enrutador)
        type: 'POST', //Definimos el metodo http
        dataType: 'json',  //definimos el formato esperado
        success: function redireccion(respuesta) { //Definimos lo que pasa si el evento fue success
            if (respuesta.status === 'ok') {  //verificamos status

                //Redireccionamos utilizando la ruta proporcionada por el controller
                window.location.href = respuesta.redirect;

            } else if (respuesta.status === 'error') {

                //en caso de status error enviamos el mensaje recibido
                alert(respuesta.mensaje);
            }
        },

        //si el evento falla definimos lo que pasara
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en la comunicación con el servidor:", textStatus, errorThrown);
            alert("Ocurrió un error de conexión.");
        }
});
}
);