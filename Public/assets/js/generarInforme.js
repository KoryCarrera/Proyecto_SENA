//definimos las urls en constantes para evitar errores de tipeo
const ENDPOINT_CASOS = '/CasosPDF';
const ENDPOINT_USUARIOS = '/UsuariosPDF';
const ENDPOINT_PROCESOS = '/ProcesosPDF';
const ENDPOINT_EXCEL = '';

const generarInforme = document.getElementById("informe");

const formato = document.getElementById("formato");
const tipo = document.getElementById("tipoReporte");
const tituloForm = document.getElementById("tituloForm");
const inputsAocultar = document.querySelectorAll(".relative:not(#excluido)"); //Seleccionamos los elementos con la clase "relative" que no tengan la clase "excluido"

//Insertamos el titulo por defecto
tituloForm.innerHTML = "Datos para tu informe";

tipo.addEventListener('change', function () { //Agregamos un evento de "cambio" en el select
    switch (tipo.value) {
        case "1":
            tituloForm.innerHTML = "Reporte por Casos";
            break;
        case "2":
            tituloForm.innerHTML = "Reporte por Usuarios";
            break;
        case "3":
            tituloForm.innerHTML = "Reporte por Procesos Organizacionales";
            break;
        default:
            tituloForm.innerHTML = "Reporte";
    };
});

formato.addEventListener('change', function () { //Agregamos un evento de "cambio" en el select
    if (this.value === "2") { //Validamos que si la elección es excel
        inputsAocultar.forEach((grupo, index) => { //Recorremos el array que nos devolió el querySelector
            if (index > 0) { //Validamos que haya mas de 0 elementos con esa clase
                grupo.style.display = 'none'; //le cambiamos la propiedad para ocultarlo
            }
        });
        tituloForm.innerHTML = "Reporte general anual en Excel"; //Cambiamos el titulo del formulario
    } else { //si cambia de opcion volvemos a buscar
        inputsAocultar.forEach((grupo) => {
            grupo.style.display = 'flex';
        });
    }
});

generarInforme.addEventListener('click', function () {

    // Validación
    if (formato.value != 2) {
        if (!formato.value) {

            Swal.fire({
                title: '¡Por favor rellena los campos!',
                theme: 'dark',
                icon: 'info',
                showConfirmButton: false,
                timer: 1500,
            });
            return;
        }
    }

    let ENDPOINT;

    switch (tipo.value) {
        case "1":
            ENDPOINT = ENDPOINT_CASOS;
            break;
        case "2":
            ENDPOINT = ENDPOINT_USUARIOS;
            break;
        case "3":
            ENDPOINT = ENDPOINT_PROCESOS;
            break;
        default:
            ENDPOINT = '';
    };

    if (!formato.value) {

        Swal.fire({
            title: '¡Por favor selecciona un formato!',
            theme: 'dark',
            icon: 'info',
            showConfirmButton: false,
            timer: 1500,
        });

    }

    // Creamos un formulario temporal y enviarlo en nueva ventana
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = ENDPOINT;
    form.target = '_blank'; // Abrir en nueva pestaña

    //Metemos los valores en el input del form creado

    //Ponemos el form y lo quitamos rapidamente
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    Swal.fire({
        title: '¡Se ha generado tu informe exitosamente!',
        theme: 'dark',
        icon: 'success',
    });
});