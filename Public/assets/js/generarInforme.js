//definimos las urls en constantes para evitar errores de tipeo
const ENDPOINT_CASOS = '/CasosPDF';
const ENDPOINT_USUARIOS = '/UsuariosPDF';
const ENDPOINT_PROCESOS = '/ProcesosPDF';
const ENDPOINT_EXCEL = '/generarExcel';

const generarInforme = document.getElementById("informe");
const formato = document.getElementById("formato");
const tipo = document.getElementById("tipoReporte");
const tituloForm = document.getElementById("tituloForm");
const inputsAocultar = document.querySelectorAll(".relative:not(#excluido)"); 

// NUEVO: Sacamos esta variable al inicio para poder usarla en todo el archivo
const esComisionado = window.location.pathname.includes('Comi');

// NUEVO: Si es comisionado, modificamos las opciones del select dinámicamente
if (esComisionado) {
    // 1. Quitamos la opción de usuarios (value = 2)
    const opcionUsuarios = tipo.querySelector('option[value="2"]');
    if (opcionUsuarios) opcionUsuarios.remove();

    // 2. Quitamos la opción de procesos (value = 3)
    const opcionProcesos = tipo.querySelector('option[value="3"]');
    if (opcionProcesos) opcionProcesos.remove();

    // 3. Agregamos la nueva opción de "Mis casos por proceso" (le daremos el value = 4)
    const nuevaOpcion = document.createElement('option');
    nuevaOpcion.value = "4";
    nuevaOpcion.textContent = "Mis Casos por Proceso Organizacional";
    tipo.appendChild(nuevaOpcion);
}

//Insertamos el titulo por defecto
tituloForm.innerHTML = "Datos para tu informe";

tipo.addEventListener('change', function () { 
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
        case "4": // NUEVO: Título para la nueva opción del comisionado
            tituloForm.innerHTML = "Reporte de Mis Casos por Proceso";
            break;
        default:
            tituloForm.innerHTML = "Reporte";
    };
});

formato.addEventListener('change', function () { 
    if (this.value === "2") { 
        inputsAocultar.forEach((grupo, index) => { 
            if (index > 0) { 
                grupo.style.display = 'none'; 
            }
        });
        tituloForm.innerHTML = "Reporte general anual en Excel"; 
    } else { 
        inputsAocultar.forEach((grupo) => {
            grupo.style.display = 'flex';
        });
        // Disparamos el evento change del tipo para restaurar el título correcto
        tipo.dispatchEvent(new Event('change')); 
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
            if (esComisionado) {
                ENDPOINT = '/CasosComiPDF';
            } else {
                ENDPOINT = '/CasosPDF'; // Administrador
            }
            break;
        case "2":
            ENDPOINT = ENDPOINT_USUARIOS;
            break;
        case "3":
            if (esComisionado) {
                ENDPOINT = '/ProcesosComiPDF';
            } else {
                ENDPOINT = '/ProcesosPDF'; // Administrador
            }
            break;
        case "4": // NUEVO: Endpoint exclusivo para la nueva opción del comisionado
            ENDPOINT = '/MisCasosProcesoComiPDF'; // <-- Cambia esto por la ruta real de tu backend
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
        return; // Añadí un return aquí para que no intente generar el form si falta el formato
    }

    if (formato.value != 2) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = ENDPOINT;
        form.target = '_blank'; 

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        Swal.fire({
            title: '¡Se ha generado tu informe exitosamente!',
            theme: 'dark',
            icon: 'success',
        });
    } else {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = ENDPOINT_EXCEL;
        form.target = '_blank'; 

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        Swal.fire({
            title: '¡Se ha generado tu informe exitosamente!',
            theme: 'dark',
            icon: 'success',
        });
    };
});