// ============================================
// CONFIGURACIÓN GLOBAL
// ============================================
pdfjsLib.GlobalWorkerOptions.workerSrc = 'assets/js/pdf.worker.min.js';

const ENDPOINT_ENVIAR  = '/registrarCaso';
const ENDPOINT_OBTENER = '/opcionesRegistro';

const MAX_ARCHIVOS    = 3;
const MAX_BYTES_TOTAL = 10 * 1024 * 1024; // 10 MB

/**
 * Única fuente de verdad del estado de archivos.
 * NUNCA se lee de inputArchivos.files directamente después de la selección.
 */
let archivosSeleccionados = [];

// ============================================
// CATÁLOGOS (jQuery — se mantiene tu patrón)
// ============================================
function cargarOpciones() {
    $.ajax({
        url: ENDPOINT_OBTENER,
        type: 'GET',
        dataType: 'json',
        success(respuesta) {
            if (respuesta.status !== 'ok') {
                console.error('Error al cargar catálogos:', respuesta.mensaje);
                return;
            }
            const { procesos, tiposCaso } = respuesta.data;

            $('#proceso')
                .empty()
                .append('<option selected disabled>Seleccione el proceso</option>');
            procesos.forEach(({ id_proceso, nombre }) =>
                $('#proceso').append(`<option value="${id_proceso}">${nombre}</option>`)
            );

            $('#tipoCaso')
                .empty()
                .append('<option selected disabled>Seleccione el tipo de caso</option>');
            tiposCaso.forEach(({ id_tipo_caso, nombre_caso }) =>
                $('#tipoCaso').append(`<option value="${id_tipo_caso}">${nombre_caso}</option>`)
            );
        },
        error() {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudieron cargar las opciones del formulario.',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1500,
            });
        },
    });
}

$(document).ready(() => cargarOpciones());

// ============================================
// CONTADOR DE CARACTERES
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    const descripcion = document.getElementById('descripcion');
    const contador    = document.getElementById('contadorCaracteres');

    if (!descripcion || !contador) return;

    descripcion.addEventListener('input', function () {
        const len = this.value.length;
        contador.textContent = len;
        contador.style.color =
            len > 1900 ? '#dc3545' :
            len > 1700 ? '#ffc107' :
                         '#6c757d';
    });
});

// ============================================
// MANEJO DE ARCHIVOS — LISTENER DEL INPUT
// ============================================
const inputArchivos = document.getElementById('archivos');
const vistaArchivos = document.getElementById('vistaArchivos');

if (inputArchivos) {
    inputArchivos.addEventListener('change', async function (e) {
        const nuevos = Array.from(e.target.files);

        // Limpiar siempre el input para permitir reseleccionar el mismo archivo
        this.value = '';

        if (nuevos.length === 0) return;

        // ── Validación 1: Espacio disponible por cantidad ──────────────────
        const espacio = MAX_ARCHIVOS - archivosSeleccionados.length;

        if (espacio <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Límite de archivos alcanzado',
                text: `Ya tienes ${MAX_ARCHIVOS} archivos. Elimina uno antes de agregar otro.`,
                theme: 'dark',
                showConfirmButton: false,
                timer: 2000,
            });
            return;
        }

        const candidatos        = nuevos.slice(0, espacio);
        const rechazadosCantidad = nuevos.length - candidatos.length;

        // ── Validación 2: Peso total acumulado ────────────────────────────
        const pesoActual = archivosSeleccionados.reduce((acc, f) => acc + f.size, 0);
        const validos    = [];
        const rechazadosPeso = [];
        let pesoValidado = 0;

        for (const archivo of candidatos) {
            if (pesoActual + pesoValidado + archivo.size > MAX_BYTES_TOTAL) {
                rechazadosPeso.push(archivo);
            } else {
                validos.push(archivo);
                pesoValidado += archivo.size;
            }
        }

        // ── Notificaciones de rechazo ─────────────────────────────────────
        if (rechazadosCantidad > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Archivos ignorados por cantidad',
                text: `Solo podías agregar ${espacio} archivo(s) más. Se ignoraron ${rechazadosCantidad}.`,
                theme: 'dark',
                showConfirmButton: false,
                timer: 2500,
            });
        }

        if (rechazadosPeso.length > 0) {
            const lista = rechazadosPeso.map(f => `• ${f.name} (${formatBytes(f.size)})`).join('\n');
            Swal.fire({
                icon: 'error',
                title: 'Peso total superado (10 MB)',
                text: `Los siguientes archivos fueron rechazados:\n\n${lista}`,
                theme: 'dark',
            });
        }

        if (validos.length === 0) return;

        archivosSeleccionados = [...archivosSeleccionados, ...validos];
        await renderizarLista();
    });
}

// ============================================
// ELIMINAR ARCHIVO POR ÍNDICE
// ============================================
function eliminarArchivo(index) {
    archivosSeleccionados.splice(index, 1);
    renderizarLista();
}

// ============================================
// RENDERIZADO DE LA LISTA
// ============================================
async function renderizarLista() {
    vistaArchivos.innerHTML = '';

    if (archivosSeleccionados.length === 0) return;

    // Encabezado de contador y peso total
    const pesoTotal = archivosSeleccionados.reduce((acc, f) => acc + f.size, 0);
    const header    = document.createElement('p');
    header.className   = 'archivos-header text-muted small mb-2';
    header.textContent = `${archivosSeleccionados.length} / ${MAX_ARCHIVOS} archivos · ${formatBytes(pesoTotal)} / 10 MB`;
    vistaArchivos.appendChild(header);

    for (let i = 0; i < archivosSeleccionados.length; i++) {
        const file = archivosSeleccionados[i];
        const card = document.createElement('div');
        card.classList.add('archivo-card', 'position-relative');

        // Botón eliminar
        const btnX = document.createElement('button');
        btnX.type       = 'button';
        btnX.className  = 'archivo-btn-eliminar';
        btnX.setAttribute('aria-label', `Eliminar ${file.name}`);
        btnX.innerHTML  = '&times;';
        btnX.addEventListener('click', () => eliminarArchivo(i));

        // Previsualización
        const preview = document.createElement('div');
        preview.className = 'archivo-preview';
        await generarPreview(file, preview);

        // Pie con nombre y peso
        const info = document.createElement('div');
        info.className = 'archivo-info d-flex justify-content-between small';
        info.innerHTML = `
            <span class="archivo-nombre text-truncate" title="${file.name}">${file.name}</span>
            <span class="archivo-peso text-muted ms-2 flex-shrink-0">${formatBytes(file.size)}</span>
        `;

        card.appendChild(btnX);
        card.appendChild(preview);
        card.appendChild(info);
        vistaArchivos.appendChild(card);
    }
}

// ============================================
// GENERADOR DE PREVIEW POR TIPO
// ============================================
async function generarPreview(file, container) {
    try {
        if (file.type.startsWith('image/')) {
            const url = URL.createObjectURL(file);
            const img = document.createElement('img');
            img.src       = url;
            img.alt       = file.name;
            img.className = 'w-100 h-100 object-fit-cover rounded';
            container.appendChild(img);

        } else if (file.type === 'application/pdf' || file.name.endsWith('.pdf')) {
            const buf      = await file.arrayBuffer();
            const pdf      = await pdfjsLib.getDocument({ data: buf }).promise;
            const page     = await pdf.getPage(1);
            const viewport = page.getViewport({ scale: 1 });
            const canvas   = document.createElement('canvas');
            canvas.width   = viewport.width;
            canvas.height  = viewport.height;
            await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
            container.appendChild(canvas);

        } else if (file.name.endsWith('.docx')) {
            const buf    = await file.arrayBuffer();
            const result = await mammoth.convertToHtml({ arrayBuffer: buf });
            const div    = document.createElement('div');
            div.className = 'docx-preview overflow-auto';
            div.innerHTML = result.value;
            container.appendChild(div);

        } else {
            container.innerHTML = `<span class="text-muted">📎 ${file.name}</span>`;
        }
    } catch {
        container.innerHTML = `<span class="text-muted">No se pudo previsualizar: ${file.name}</span>`;
    }
}

// ============================================
// UTILIDAD: FORMATEAR BYTES
// ============================================
function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k     = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i     = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

// ============================================
// ENVÍO DEL FORMULARIO
// ============================================
const btnRegistrar = document.getElementById('btnRegistrarcaso');

if (btnRegistrar) {
    btnRegistrar.addEventListener('click', async function () {

        // ── Captura de campos ─────────────────────────────────────────────
        const nombreCaso   = document.getElementById('nombreCaso').value.trim();
        const radicadoSena = document.getElementById('radicado').value.trim();
        const proceso      = document.getElementById('proceso').value;
        const tipoCaso     = document.getElementById('tipoCaso').value;
        const descripcion  = document.getElementById('descripcion').value.trim();

        // ── Validaciones de formulario ────────────────────────────────────
        const validaciones = [
            { condicion: !nombreCaso,  titulo: 'El nombre del caso es obligatorio' },
            { condicion: !proceso,     titulo: 'Debe seleccionar un proceso'        },
            { condicion: !tipoCaso,    titulo: 'Debe seleccionar un tipo de caso'   },
            { condicion: !descripcion, titulo: 'La descripción es obligatoria'      },
        ];

        for (const { condicion, titulo } of validaciones) {
            if (condicion) {
                Swal.fire({ icon: 'error', title: titulo, theme: 'dark', showConfirmButton: false, timer: 1000 });
                return;
            }
        }

        // ── Bloquear botón ────────────────────────────────────────────────
        bloquearBoton();

        // ── Armar FormData ────────────────────────────────────────────────
        const formData = new FormData();
        formData.append('nombreCaso',   nombreCaso);
        formData.append('radicadoSena', radicadoSena);
        formData.append('proceso',      proceso);
        formData.append('tipoCaso',     tipoCaso);
        formData.append('descripcion',  descripcion);

        // Los archivos vienen del array de estado, NO del input
        archivosSeleccionados.forEach(archivo => {
            formData.append('archivos[]', archivo);
        });

        // ── Petición al servidor ──────────────────────────────────────────
        try {
            const response = await fetch(ENDPOINT_ENVIAR, { method: 'POST', body: formData });

            // El servidor puede devolver 400 con JSON de error
            const respuesta = await response.json();

            if (!response.ok || respuesta.status !== 'ok') {
                throw new Error(respuesta.mensaje || 'Error desconocido del servidor');
            }

            Swal.fire({
                icon: 'success',
                title: 'Caso registrado exitosamente',
                theme: 'dark',
                showConfirmButton: false,
                timer: 1500,
            });

            limpiarFormulario();
            cargarOpciones();

        } catch (error) {
            console.error('Error al registrar:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error al registrar el caso',
                text: error.message || 'Verifique su conexión e intente nuevamente.',
                theme: 'dark',
                showConfirmButton: false,
                timer: 2000,
            });
        } finally {
            setTimeout(restaurarBoton, 1500);
        }
    });
}

// ============================================
// HELPERS DEL BOTÓN Y FORMULARIO
// ============================================
function bloquearBoton() {
    if (!btnRegistrar) return;
    btnRegistrar.disabled     = true;
    btnRegistrar.innerHTML    = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
    btnRegistrar.style.opacity = '0.6';
    btnRegistrar.style.cursor  = 'not-allowed';
}

function restaurarBoton() {
    if (!btnRegistrar) return;
    btnRegistrar.disabled      = false;
    btnRegistrar.innerHTML     = '<i class="bi bi-send-fill"></i> ENVIAR REGISTRO';
    btnRegistrar.style.opacity = '1';
    btnRegistrar.style.cursor  = 'pointer';
}

function limpiarFormulario() {
    ['nombreCaso', 'radicado', 'proceso', 'tipoCaso', 'descripcion', 'archivos']
        .forEach(id => { document.getElementById(id).value = ''; });

    document.getElementById('contadorCaracteres').textContent = '0';
    vistaArchivos.innerHTML = '';
    archivosSeleccionados   = [];  // Resetear estado de archivos
}