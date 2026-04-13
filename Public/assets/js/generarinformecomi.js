const ENDPOINT_PDF = '/CasosComiPDF'; 
const ENDPOINT_EXCEL = '/generarExcel'; 

const generarInforme = document.getElementById("informe");
const formato = document.getElementById("formato");
const tituloForm = document.getElementById("tituloForm");

if(tituloForm) tituloForm.innerHTML = "Reporte anual, selecciona un formato"; 

if (formato) {
    formato.addEventListener('change', function () { 
        if (this.value === "2") { 
            if(tituloForm) tituloForm.innerHTML = "Reporte general en Excel"; 
        } else { 
            if(tituloForm) tituloForm.innerHTML = "Reporte anual de casos en PDF";
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