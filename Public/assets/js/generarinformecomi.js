// =========================================================
// SCRIPT DE INFORMES EXCLUSIVO PARA EL COMISIONADO
// =========================================================

const ENDPOINT_PDF = '/CasosComiPDF'; 
const ENDPOINT_EXCEL = '/generarExcel'; 

const generarInforme = document.getElementById("informe");
const formato = document.getElementById("formato");
const tituloForm = document.getElementById("tituloForm");

// ---------------------------------------------------------
// 1. LÓGICA DE AUTODESCARGA AL CARGAR LA PÁGINA
// ---------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    
    if(tituloForm) tituloForm.innerHTML = "Generar reporte manualmente";

    // Alerta y autodescarga después de 0.8 segundos
    setTimeout(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Generando tu informe...',
                text: 'Tu reporte completo se está descargando automáticamente.',
                theme: 'dark',
                icon: 'info',
                timer: 3000,
                showConfirmButton: false
            });
        }

        // Enviamos la petición oculta para descargar el PDF automáticamente
        const formAuto = document.createElement('form');
        formAuto.method = 'POST';
        formAuto.action = ENDPOINT_PDF; 
        
        document.body.appendChild(formAuto);
        formAuto.submit();
        document.body.removeChild(formAuto);

    }, 800); 
});

// ---------------------------------------------------------
// 2. LÓGICA DE INTERFAZ MANUAL (Botón de Descargar)
// ---------------------------------------------------------

if (formato) {
    formato.addEventListener('change', function () { 
        if (this.value === "2") { 
            if(tituloForm) tituloForm.innerHTML = "Reporte general en Excel"; 
        } else { 
            if(tituloForm) tituloForm.innerHTML = "Generar reporte manualmente";
        }
    });
}

if (generarInforme) {
    generarInforme.addEventListener('click', function () {
        
        // Validamos que haya seleccionado PDF o Excel
        if (!formato.value || formato.value === "Selecione el tipo de archivo") {
            Swal.fire({
                title: '¡Por favor selecciona un formato!',
                theme: 'dark',
                icon: 'warning',
                showConfirmButton: false,
                timer: 1500,
            });
            return;
        }

        // Si eligió 2 va a Excel, sino va a PDF
        let ENDPOINT = (formato.value == 2) ? ENDPOINT_EXCEL : ENDPOINT_PDF;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = ENDPOINT;
        form.target = '_blank'; // Se abre en otra pestaña porque fue un clic manual

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        Swal.fire({
            title: '¡Se ha generado tu informe exitosamente!',
            theme: 'dark',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
        });
    });
}