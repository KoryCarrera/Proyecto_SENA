var btnRegistrar = document.getElementById("btnRegistrarcaso");

btnRegistrar.addEventListener('click', function registrarCaso() {


    btnRegistrar.disabled = true;
    btnRegistrar.textContent = "Enviando...";
    btnRegistrar.style.opacity = "0.6";
    btnRegistrar.style.cursor = "not-allowed";

    var fecha_inicio = document.getElementById("fecha_inicio").value;

    var proceso = document.getElementById("proceso").value;

    var estado = document.getElementById("estado").value;

    var tipoCaso = document.getElementById("tipoCaso").value;

    var descripcion = document.getElementById("descripcion").value;

    var parametros = {
        'fecha_inicio': fecha_inicio,
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

                document.getElementById("fecha_inicio").value = '';
                document.getElementById("proceso").value = '';
                document.getElementById("estado").value = '';
                document.getElementById("tipoCaso").value = '';
                document.getElementById("descripcion").value = '';

                setTimeout(function () {
                    btnRegistrar.disabled = false;
                    btnRegistrar.textContent = "ENVIAR";
                    btnRegistrar.style.opacity = "1";
                    btnRegistrar.style.cursor = "pointer";
                }, 2000);

            } else if (respuesta.status === 'error') {

                alert(respuesta.mensaje);

                btnRegistrar.disabled = false;
                btnRegistrar.textContent = "ENVIAR";
                btnRegistrar.style.opacity = "1";
                btnRegistrar.style.cursor = "pointer";
            }
        },


        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error de conexión:", textStatus, errorThrown);
            alert("Error de conexión con el servidor");

            btnRegistrar.disabled = false;
            btnRegistrar.textContent = "ENVIAR";
            btnRegistrar.style.opacity = "1";
            btnRegistrar.style.cursor = "pointer";
        }
    });
});
