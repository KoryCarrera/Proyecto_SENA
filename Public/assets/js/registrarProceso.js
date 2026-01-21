var btnRegistrar = document.getElementById("btnRegistrarProceso");

btnRegistrar.addEventListener('click', function registrarProceso() {

    var nombre = document.getElementById("nombre-proceso").value;
    var descripcion = document.getElementById("descripcion").value;

    var parametros = {
        'nombre-proceso': nombre,
        'descripcion': descripcion
    }

    console.log("Enviando proceso...");

    $.ajax({

        data: parametros,
        url: '/registrarProceso',
        type: 'POST',
        dataType: 'json',

        success: function (respuesta) {
            if (respuesta.status === 'ok') {

                alert(respuesta.mensaje);

                document.getElementById("nombre-proceso").value = '';
                document.getElementById("descripcion").value = '';

            } else if (respuesta.status === 'error') {

                alert(respuesta.mensaje);
            }
        },


        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error de conexión:", textStatus, errorThrown);
            alert("Error de conexión con el servidor");
        }
    });
});