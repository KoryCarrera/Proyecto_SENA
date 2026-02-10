//Capturamos el btn
var btnRegistrar = document.getElementById("btnRegistrarProceso");

btnRegistrar.addEventListener('click', function registrarProceso() { //le agregamos un evento click al btn

    //Capturamos los datos del proceso a ingresar
    var nombre = document.getElementById("nombre-proceso").value;
    var descripcion = document.getElementById("descripcion").value;

    //Asignamos los datos capturados en un objeto para facilidad de manejo
    var parametros = {
        'nombre-proceso': nombre,
        'descripcion': descripcion
    }

    $.ajax({ //Utilizamos AJAX para la comunicacion

        data: parametros, //Enviamos el objeto
        url: '/registrarProceso', //Especificamos url definida en el router
        type: 'POST', //Definimos metodo http
        dataType: 'json', //Archivo a esperar

        success: function (respuesta) { //Si la request recibió una response esto se ejecutara
            if (respuesta.status === 'ok') {

                alert(respuesta.mensaje); //Mandamos el mensaje del endpoint en una alert

                //Vaciamos los inputs
                document.getElementById("nombre-proceso").value = '';
                document.getElementById("descripcion").value = '';

            } else if (respuesta.status === 'error') { //Si recibimos una response con status error

                alert(respuesta.mensaje); //Imprimimos el mensaje en una alert
            }
        },


        error: function (jqXHR, textStatus, errorThrown) { //En caso de error capturamos en consola
            console.error("Error de conexión:", textStatus, errorThrown);
            alert("Error de conexión con el servidor");
        }
    });
});