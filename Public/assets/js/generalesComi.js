//Definimos el endpoint en una constante para facilidad
const ENDPOINT_DATOS = '/estadisticasGenerales';

//Capturamos los h2 donde mostraremos los datos
const total = document.getElementById('total');
const denuncia = document.getElementById('denuncia');
const solicitudes = document.getElementById('solicitud');
const tutelas = document.getElementById('tutela');
const atendidos = document.getElementById('atendido');
const porAtender = document.getElementById('porAtender');
const noAtendidos = document.getElementById('noAtendidos')

//asignamos a cargando mientras llega el ajax
total.textContent = `Cargando...`;
denuncia.textContent = `Cargando...`;
solicitudes.textContent = `Cargando...`;
tutelas.textContent = `Cargando...`;
atendidos.textContent = `Cargando...`;
porAtender.textContent = `Cargando...`;

//Agregamos el evento para que cuando cargue todo el dom ejecutar la funcion anonima
document.addEventListener('DOMContentLoaded', function () {

    //utilizamos ajax para pedir los datos que necesitamos
    $.ajax({
        url: ENDPOINT_DATOS, //url donde haremos la peticion
        type: 'GET', //Protocolo http que usaremos
        dataType: 'json', //Tipo de respuesta
        success: function (respuesta) {
            //Se valida que el html necesitado exista
            if (!total || !denuncia || !solicitudes || !tutelas || !atendidos || !porAtender) {
                Swal.fire({
                    icon: 'error',
                    title: '¡No se han encontrado espacios para los datos!',
                    text: '¡Recarga la pagina!',
                    showConfirmButton: false,
                    timer: 1500,
                    theme: 'dark',
                });
                return;
            };

            //Se valida que la respuesta no haya sido vacia
            if (!respuesta || respuesta.length < 0) {
                total.textContent = `0`;
                denuncia.textContent = `0`;
                solicitudes.textContent = `0`;
                tutelas.textContent = `0`;
                atendidos.textContent = `0`;
                porAtender.textContent = `0`;
                return;
            };

            //Validamos que el backend no haya devuelto error
            if (respuesta.status == 'error') {
                Swal.fire({
                    icon: 'error',
                    title: '¡Ha ocurrido un error!',
                    text: 'No se ha podido conseguir los datos',
                    showConfirmButton: false,
                    timer: 1500,
                    theme: 'dark',
                });
                return;
            }

            //despues de las validaciones insertamos los datos
            total.textContent = `${respuesta.total}`;
            denuncia.textContent = `${respuesta.denuncias}`;
            solicitudes.textContent = `${respuesta.solicitudes}`;
            tutelas.textContent = `${respuesta.tutelas}`;
            atendidos.textContent = `${respuesta.atendidos}`;
            porAtender.textContent = `${respuesta.porAtender}`;
            noAtendidos.textContent = `${respuesta.noAtendidos ?? 0}`;


        },
        //en caso de error
        error: function (jqXHR, textStatus, errorThrown) {
            //Enviamos el error a consola para su manejo
            console.error("Error en la comunicación con el servidor:", textStatus, errorThrown);

            //Enviamos una alerta para validar el error
            Swal.fire({
                icon: "error",
                title: "¡Ha ocurrido un error interno!",
                theme: 'dark',
                timer: 1500,
                showConfirmButton: false,
            });

            //Insertamos la palabra error
            total.textContent = `¡Error!`;
            denuncia.textContent = `¡Error!`;
            solicitudes.textContent = `¡Error!`;
            tutelas.textContent = `¡Error!`;
            atendidos.textContent = `¡Error!`;
            porAtender.textContent = `¡Error!`;
            noAtendidos.textContent = `¡Error!`;
            return;
        }
    });
});
