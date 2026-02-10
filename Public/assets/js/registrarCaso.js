const ENDPOINT_ENVIAR = '/registrarCaso';
const ENDPOINT_OBTENER = '/opcionesRegistro';


function cargarOpciones() {
    $.ajax({
        url: ENDPOINT_OBTENER,
        type: 'GET',
        dataType: 'json',
        success: function (respuesta) {
            if (respuesta.status === 'ok') {
                const d = respuesta.data;

                //Llenar Procesos
                const selectProceso = $('#proceso');
                selectProceso.empty().append('<option selected disabled>Seleccione el proceso</option>');
                d.procesos.forEach(item => {
                    selectProceso.append(`<option value="${item.id_proceso}">${item.nombre}</option>`);
                });

                //Llenar Estados
                const selectEstado = $('#estado');
                selectEstado.empty().append('<option selected disabled>Seleccione el estado</option>');
                d.estadosCaso.forEach(item => {
                    selectEstado.append(`<option value="${item.id_estado}">${item.estado}</option>`);
                });

                //Llenar Tipos de Caso
                const selectTipo = $('#tipoCaso');
                selectTipo.empty().append('<option selected disabled>Seleccione el tipo de caso</option>');
                d.tiposCaso.forEach(item => {
                    selectTipo.append(`<option value="${item.id_tipo_caso}">${item.nombre_caso}</option>`);
                });

            } else {
                console.error("Error al cargar catálogos:", respuesta.mensaje);
            }
        },
        error: function () {
            alert("No se pudieron cargar las opciones del formulario. Verifique su conexión.");
        }
    });
}

//Ejecutar la carga al iniciar la página
$(document).ready(function () {
    cargarOpciones();
});

const btnRegistrar = document.getElementById("btnRegistrarcaso");
btnRegistrar.addEventListener('click', function registrarCaso() {

        btnRegistrar.disabled = true;
        btnRegistrar.textContent = "Enviando...";
        btnRegistrar.style.opacity = "0.6";
        btnRegistrar.style.cursor = "not-allowed";

        const fecha_inicio = document.getElementById("fecha_inicio").value;
        const fecha_cierre = document.getElementById("fecha_cierre").value;
        const proceso = document.getElementById("proceso").value;
        const estado = document.getElementById("estado").value;
        const tipoCaso = document.getElementById("tipoCaso").value;
        const descripcion = document.getElementById("descripcion").value;



        const parametros = {
            'fecha_inicio': fecha_inicio,
            'fecha_cierre': fecha_cierre,
            'proceso': proceso,
            'estado': estado,
            'tipoCaso': tipoCaso,
            'descripcion': descripcion
        }

        $.ajax({
            data: parametros,
            url: ENDPOINT_ENVIAR,
            type: 'POST',
            dataType: 'json',

            success: function (respuesta) {
                if (respuesta.status === 'ok') {
                    alert(respuesta.mensaje);

                    document.getElementById("fecha_inicio").value = '';
                    document.getElementById("fecha_cierre").value = '';
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
