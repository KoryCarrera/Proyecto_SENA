const ENDPOINT_ENVIAR = '/registrarCaso';
const ENDPOINT_OBTENER = '/opcionesRegistro';

// ============================================
// CARGAR OPCIONES (Procesos y Tipos de Caso)
// ============================================
function cargarOpciones() {
    $.ajax({
        url: ENDPOINT_OBTENER,
        type: 'GET',
        dataType: 'json',
        success: function (respuesta) {
            if (respuesta.status === 'ok') {
                const d = respuesta.data;

                // Llenar Procesos
                const selectProceso = $('#proceso');
                selectProceso.empty().append('<option selected disabled>Seleccione el proceso</option>');
                d.procesos.forEach(item => {
                    selectProceso.append(`<option value="${item.id_proceso}">${item.nombre}</option>`);
                });

                // Llenar Tipos de Caso
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

// EJECUTAR AL CARGAR LA PÁGINA
$(document).ready(function () {
    cargarOpciones();
});

// CONTADOR DE CARACTERES DE LA DESCRIPCIÓN
document.addEventListener("DOMContentLoaded", function () {
    const descripcion = document.getElementById('descripcion');
    const contador = document.getElementById('contadorCaracteres');

    if (descripcion && contador) {
        descripcion.addEventListener('input', function () {
            contador.textContent = this.value.length;

            // Cambiar color según la cantidad de caracteres
            if (this.value.length > 1900) {
                contador.style.color = '#dc3545'; // Rojo
            } else if (this.value.length > 1700) {
                contador.style.color = '#ffc107'; // Amarillo
            } else {
                contador.style.color = '#6c757d'; // Gris
            }
        });
    }
});

// ============================================
// MANEJO DE ARCHIVOS ADJUNTOS
// ============================================
const inputArchivos = document.getElementById('archivos');
const vistaArchivos = document.getElementById('vistaArchivos');
let archivosSeleccionados = [];

if (inputArchivos) {
    inputArchivos.addEventListener('change', function (e) {
        const archivos = Array.from(e.target.files);

        // Validación de la cantidad de archivos (máximo 3)
        if (archivos.length > 3) {
            alert('Solo puede subir 3 archivos como máximo');
            this.value = '';
            return;
        }

        // Validación del tamaño del archivo (máximo 10MB)
        const MAX_SIZE = 10 * 1024 * 1024; // 10 MB
        const archivosGrandes = archivos.filter(f => f.size > MAX_SIZE);

        if (archivosGrandes.length > 0) {
            alert(`Los siguientes archivos superan el límite de 10 MB:\n${archivosGrandes.map(f => f.name).join('\n')}`);
            this.value = '';
            return;
        }

        // Guardar archivos y mostrar preview
        archivosSeleccionados = archivos;
        mostrarPreviewArchivos(archivos);
    });
}

// ============================================
// MOSTRAR PREVIEW DE ARCHIVOS SELECCIONADOS
// ============================================
function mostrarPreviewArchivos(archivos) {
    if (!vistaArchivos) return;

    vistaArchivos.innerHTML = '';

    archivos.forEach((archivo, index) => {
        const div = document.createElement('div');
        div.className = 'archivo-preview';

        // Icono según el tipo de archivo
        let icono = 'bi-file-earmark';
        if (archivo.type.startsWith('image/')) {
            icono = 'bi-file-image';
        } else if (archivo.type.startsWith('video/')) {
            icono = 'bi-file-play';
        } else if (archivo.type.includes('pdf')) {
            icono = 'bi-file-pdf';
        } else if (archivo.type.includes('word')) {
            icono = 'bi-file-word';
        } else if (archivo.type.includes('excel') || archivo.type.includes('spreadsheet')) {
            icono = 'bi-file-excel';
        }

        div.innerHTML = `
            <i class="bi ${icono}"></i>
            <div>
                <div class="archivo-nombre" title="${archivo.name}">${archivo.name}</div>
                <div class="archivo-size">${formatBytes(archivo.size)}</div>
            </div>
            <button type="button" class="btn-eliminar-archivo" onclick="eliminarArchivo(${index})">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        `;

        vistaArchivos.appendChild(div);
    });
}

// ELIMINAR ARCHIVO DE LA SELECCIÓN
function eliminarArchivo(index) {
    const dt = new DataTransfer();
    const archivos = Array.from(inputArchivos.files);

    archivos.forEach((archivo, i) => {
        if (i !== index) dt.items.add(archivo);
    });

    inputArchivos.files = dt.files;
    archivosSeleccionados = Array.from(dt.files);
    mostrarPreviewArchivos(archivosSeleccionados);
}

// FORMATEAR BYTES A KB, MB
function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// ENVIAR FORMULARIO CON VALIDACIONES
const btnRegistrar = document.getElementById("btnRegistrarcaso");

if (btnRegistrar) {
    btnRegistrar.addEventListener('click', function () {

        //CAPTURAR VALORES
        const nombreCaso = document.getElementById("nombreCaso").value.trim();
        const proceso = document.getElementById("proceso").value;
        const tipoCaso = document.getElementById("tipoCaso").value;
        const descripcion = document.getElementById("descripcion").value.trim();

        //VALIDACIONES
        if (!nombreCaso) {
            alert('El nombre del caso es obligatorio');
            return;
        }

        if (!proceso) {
            alert('Debe seleccionar un proceso');
            return;
        }

        if (!tipoCaso) {
            alert('Debe seleccionar un tipo de caso');
            return;
        }

        if (!descripcion) {
            alert('La descripción es obligatoria');
            return;
        }

        //DESHABILITAR BOTÓN
        btnRegistrar.disabled = true;
        btnRegistrar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
        btnRegistrar.style.opacity = "0.6";
        btnRegistrar.style.cursor = "not-allowed";

        //CREAR FORMDATA
        const formData = new FormData();
        formData.append('nombreCaso', nombreCaso);
        formData.append('proceso', proceso);
        formData.append('tipoCaso', tipoCaso);
        formData.append('descripcion', descripcion);

        // Agregar archivos si hay
        const archivos = document.getElementById('archivos').files;
        for (let i = 0; i < archivos.length; i++) {
            formData.append('archivos[]', archivos[i]);
        }

        //ENVIAR CON FETCH
        fetch(ENDPOINT_ENVIAR, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(respuesta => {
                if (respuesta.status === 'ok') {

                    //Alerta estetica
                    Swal.fire({
                        icon: 'success',
                        title: 'Caso registrado exitosamente',
                        theme: 'dark',
                        showConfirmButton: false,
                        timer: 1000
                    });

                    // Limpiar formulario
                    document.getElementById("nombreCaso").value = '';
                    document.getElementById("proceso").value = '';
                    document.getElementById("tipoCaso").value = '';
                    document.getElementById("descripcion").value = '';
                    document.getElementById("archivos").value = '';
                    vistaArchivos.innerHTML = '';
                    document.getElementById("contadorCaracteres").textContent = '0';

                    // Recargar opciones de los selects
                    cargarOpciones();

                    setTimeout(() => {
                        restaurarBoton();
                    }, 1500);

                } else {

                    //Mostrar alerta estetica

                    Swal.fire({
                        icon: 'error',
                        title: 'Error al registrar el caso',
                        theme: 'dark',
                        showConfirmButton: false,
                        timer: 1000,
                    })

                    setTimeout(() => {
                        restaurarBoton();
                    }, 1500);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión con el servidor',
                    theme: 'dark',
                    showConfirmButton: false,
                    timer: 1000,
                });
                restaurarBoton();
            });
    });
}

//RESTAURAR ESTADO DEL BOTÓN
function restaurarBoton() {
    if (btnRegistrar) {
        btnRegistrar.disabled = false;
        btnRegistrar.innerHTML = '<i class="bi bi-send-fill"></i> ENVIAR REGISTRO';
        btnRegistrar.style.opacity = "1";
        btnRegistrar.style.cursor = "pointer";
    }
}