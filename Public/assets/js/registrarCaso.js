var btnRegistrar = document.getElementById("btnRegistrarcaso");

btnRegistrar.addEventListener('click', function registrarCaso() {

    var proceso = document.getElementById("proceso").value;
    
    var estado = document.getElementById("estado").value;
    
    var tipoCaso = document.getElementById("tipoCaso").value;
    
    var descripcion = document.getElementById("descripcion").value;

    var parametros = {
        'proceso': proceso,
        'estado': estado,
        'tipoCaso': tipoCaso,
        'descripcion': descripcion
    }

    console.log("Enviando caso...");

    $.ajax({

        data: parametros,
        url: '/registrarCaso',
        type: 'POST',
        dataType: 'json',

        success: function (respuesta) {
            if (respuesta.status === 'ok') {

                alert(respuesta.mensaje);

                document.getElementById("proceso").value = '';
                document.getElementById("estado").value = '';
                document.getElementById("tipoCaso").value = '';
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
