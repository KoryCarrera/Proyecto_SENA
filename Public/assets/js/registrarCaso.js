pdfjsLib.GlobalWorkerOptions.workerSrc = 'assets/js/pdf.worker.min.js';

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
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudieron cargar las opciones del formulario. Verifique su conexión.',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
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
const formulario    = document.getElementById('tuFormulario');
let archivosSeleccionados = [];

if (inputArchivos) {
    inputArchivos.addEventListener('change', async function(e) {
        const archivos = Array.from(e.target.files);

        // Validación cantidad
        if (archivos.length > 3) {
            Swal.fire({ icon: 'error', title: 'Solo puede subir 3 archivos como máximo', theme: 'dark', showConfirmButton: false, timer: 1500 });
            this.value = '';
            return;
        }

        // Validación tamaño
        const MAX_SIZE = 10 * 1024 * 1024;
        const archivosGrandes = archivos.filter(f => f.size > MAX_SIZE);
        if (archivosGrandes.length > 0) {
            Swal.fire({ icon: 'error', title: 'Algunos archivos superan 10MB', theme: 'dark', showConfirmButton: false, timer: 1500 });
            this.value = '';
            return;
        }

        // 1️⃣ Guardar referencia y previsualizar
        archivosSeleccionados = archivos;
        await mostrarPreviewArchivos(archivos);
    });
}

// PREVISUALIZACIÓN 

async function mostrarPreviewArchivos(archivos) {
    vistaArchivos.innerHTML = '';

    for (const file of archivos) {
        const card = document.createElement('div');
        card.classList.add('archivo-card');

        if (file.type.startsWith('image/')) {
            // Imagen: URL temporal en memoria, no sale del navegador
            const url = URL.createObjectURL(file);
            card.innerHTML = `<img src="${url}" alt="${file.name}" class="w-full h-full object-cover rounded-lg">`;

        } else if (file.type === 'application/pdf' || file.name.endsWith('.pdf')) {
            // PDF: renderiza la primera página en un canvas
            const buf  = await file.arrayBuffer();
            const pdf  = await pdfjsLib.getDocument({ data: buf }).promise;
            const page = await pdf.getPage(1);
            const viewport = page.getViewport({ scale: 1 });
            const canvas   = document.createElement('canvas');
            canvas.width   = viewport.width;
            canvas.height  = viewport.height;
            await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
            card.appendChild(canvas);

        } else if (file.name.endsWith('.docx')) {
            // DOCX: extrae el HTML del documento
            const buf    = await file.arrayBuffer();
            const result = await mammoth.convertToHtml({ arrayBuffer: buf });
            const div    = document.createElement('div');
            div.innerHTML = result.value;
            card.appendChild(div);

        } else {
            card.innerHTML = `<span>${file.name}</span>`;
        }

        vistaArchivos.appendChild(card);
    }
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
        const radicadoSena = document.getElementById('radicado').value.trim();
        const proceso = document.getElementById("proceso").value;
        const tipoCaso = document.getElementById("tipoCaso").value;
        const descripcion = document.getElementById("descripcion").value.trim();

        //VALIDACIONES
        if (!nombreCaso) {
            Swal.fire({
                icon: 'error',
                title: 'El nombre del caso es obligatorio',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
            return;
        }

        if (!proceso) {
            Swal.fire({
                icon: 'error',
                title: 'Debe seleccionar un proceso',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
            return;
        }

        if (!tipoCaso) {
            Swal.fire({
                icon: 'error',
                title: 'Debe seleccionar un tipo de caso',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
            return;
        }

        if (!descripcion) {
            Swal.fire({
                icon: 'error',
                title: 'La descripción es obligatoria',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1000,
            });
            return;
        };

        //DESHABILITAR BOTÓN
        btnRegistrar.disabled = true;
        btnRegistrar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
        btnRegistrar.style.opacity = "0.6";
        btnRegistrar.style.cursor = "not-allowed";

        //CREAR FORMDATA
        const formData = new FormData();
        formData.append('nombreCaso', nombreCaso);
        formData.append('radicadoSena', radicadoSena);
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
                    document.getElementById("radicado").value = '';
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