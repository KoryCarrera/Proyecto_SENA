const generarInforme = document.getElementById("informe");

    const formato = document.getElementById("formato");
    const tituloObservacion = document.getElementById("titulo");
    const contenidoObservacion = document.getElementById("descripcion");
    const conclusiones = document.getElementById("conclusion");
    const inputsAocultar = document.querySelectorAll(".input-group.mb-4.custom-input-group");

    formato.addEventListener('change', function () { //Agregamos un evento de "cambio" en el select
        if (this.value === "2") { //Validamos que si la elección es excel
            inputsAocultar.forEach((grupo, index) => { //Recorremos el array que nos devolió el querySelector
                if (index > 0) { //Validamos que haya mas de 0 elementos con esa clase
                    grupo.style.display = 'none'; //le cambiamos la propiedad para ocultarlo
                }
            });
        } else { //si cambia de opcion volvemos a buscar
            inputsAocultar.forEach((grupo) => {
                grupo.style.display = 'flex';
            });
        }
    });

generarInforme.addEventListener('click', function() {
    
    // Validación
    if (!tituloObservacion || !contenidoObservacion || !conclusiones) {
        alert('Por favor completa todos los campos');
        return;
    }

    let ENDPOINT;
    if (formato.value !== "2") {
        ENDPOINT = '/generarPDF';
    } else {
        ENDPOINT = '/generarExcel';
    }

    // Creamos un formulario temporal y enviarlo en nueva ventana
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = ENDPOINT;
    form.target = '_blank'; // Abrir en nueva pestaña

    // Agregamos campos
    const campos = {
        'titulo': tituloObservacion.value,
        'contenidoObservacion': contenidoObservacion.value,
        'conclusiones': conclusiones.value
    };
    //Metemos los valores en el input del form creado
    for (let key in campos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = campos[key];
        form.appendChild(input);
    }
    //Ponemos el form y lo quitamos rapidamente
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    alert('Generando Reporte...');
});