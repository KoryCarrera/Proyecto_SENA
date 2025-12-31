const generarInforme = document.getElementById("informe");

generarInforme.addEventListener('click', function() {
    
    const formato = document.getElementById("formato").value;
    const tituloObservacion = document.getElementById("titulo").value;
    const contenidoObservacion = document.getElementById("descripcion").value;
    const conclusiones = document.getElementById("conclusion").value;

    // Validación
    if (!tituloObservacion || !contenidoObservacion || !conclusiones) {
        alert('Por favor completa todos los campos');
        return;
    }

    let ENDPOINT;
    if (formato != 2) {
        ENDPOINT = '../../../app/controllers/reportePDF.php';
    } else {
        ENDPOINT = '../../../app/controllers/reporteEXCEL.php';
    }

    // Crear un formulario temporal y enviarlo en nueva ventana
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = ENDPOINT;
    form.target = '_blank'; // Abrir en nueva pestaña

    // Agregar campos
    const campos = {
        'titulo': tituloObservacion,
        'contenidoObservacion': contenidoObservacion,
        'conclusiones': conclusiones
    };

    for (let key in campos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = campos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    alert('Generando PDF...');
});