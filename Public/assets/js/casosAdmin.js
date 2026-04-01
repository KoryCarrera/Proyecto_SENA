//Definimos los endpoints en constantes para facilidad
const ENDPOINT_LISTAR = "/listarCasos";
const ENDPOINT_OBTENER = "/modalCasoAdmin";
const ENDPOINT_SEGUIMIENTOS = "/listarSeguimientos";
const ENDPOINT_GESTIONAR = '/gestionarCaso';
const ENDPOINT_COMISIONADOS = '/listarComisionados';

//Mandar mensaje de error de dataTable a la consola
$.fn.dataTable.ext.errMode = 'none';

//Definimos una función Async de cargar casos
const cargarCasos = async () => {
  //capturamos el cuerpo de la tabla
  const cuerpoTabla = document.getElementById("tablaCasos");

  //validamos que exista
  if (!cuerpoTabla) {
    console.error("!No se encontró el cuerpo de la tabla¡");
    return;
  }

  //Insertamos el "cargando..." mientras nos llegan datos
  cuerpoTabla.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 mb-0">Cargando casos...</p>
            </td>
        </tr>
    `;

  try {
    //Hacemos fetch al endpoint para obtener los datos
    const response = await fetch(ENDPOINT_LISTAR);

    //Validamos el status devuelto por el endpoint
    if (!response.ok) {
      throw new Error(
        `Error HTTP ${response.status}: No se pudo conectar con el servidor`,
      );
    }

    //Transformamos a JSON
    const data = await response.json();

    //Personalizacion de errores
    if (data.status !== "ok") {
      throw new Error(data.mensaje || "Error desconocido");
    }

    //validamos datos, tipo y longitud
    if (!data.casos || !Array.isArray(data.casos) || data.casos.length === 0) {
      cuerpoTabla.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4 text-warning">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                        <p class="mt-2 mb-0 fw-bold">No hay casos registrados</p>
                    </td>
                </tr>
            `;
      return;
    }

    //declaramos una función que definiremos mas adelante
    renderizarTablaCasos(data.casos, cuerpoTabla);

    //personalizamos captacion de error catch
  } catch (error) {
    console.error("Error al cargar los casos:", error);
    cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-danger">
                    <p class="fw-bold">Error: ${error.message}</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="cargarCasos()">
                        Reintentar
                    </button>
                </td>
            </tr>
        `;
  }
};

//definimos la funcion previamente declarada
const renderizarTablaCasos = (casos, cuerpoTabla) => {
  let htmlFilas = ""; //Inicializamos la variable vacia para guardar el html

  casos.forEach((caso) => {
    //Capturamos la fecha de inicio
    const fechaInicio = caso.fecha_inicio
      ? formatearFecha(caso.fecha_inicio) //Fecha estetica
      : "No registrada";

    //capturamos fecha de cierre del argumento
    const fechaCierre = caso.fecha_cierre
      ? formatearFecha(caso.fecha_cierre) //estilizamos la fecha recibida
      : '<span class="badge bg-warning text-dark">Pendiente</span>';

    //Declaramos variable que definiremos mas adelante
    const estadoBadge = obtenerBadgeEstado(caso.estado);

    //insertamos cada iteracion en html
    htmlFilas += `
            <tr>
                <th scope="row">${caso.id_caso}</th>
                <td>${fechaInicio}</td>
                <td>${caso.tipo_caso || "N/A"}</td>
                <td>${fechaCierre}</td>
                <td>${estadoBadge}</td>
                <td>${caso.proceso || "N/A"}</td>
                <td>${caso.comisionado}</td>
                <td>
                    <button class="btn-table bg-red-600 hover:bg-red-500 mr-2 rounded-lg px-3 py-2" onclick="modalReasignar(${caso.id_caso})">
                        <i class="bi bi-repeat text-white"></i> 
                    </button>
                </td>
                <td>
                    <button class="btn-table bg-indigo-600 hover:bg-indigo-500 mr-2 rounded-lg px-3 py-2" onclick="supervisarCaso(${caso.id_caso})">
                        <i class="bi bi-eye text-white"></i> 
                    </button>
                </td>
              
            </tr>
        `;
  });

  cuerpoTabla.innerHTML = htmlFilas; //insertamos en el cuerpo de la tabla

  // Inicializamos DataTables despues de que los datos estén en el DOM
  var table = $("#tablaCaso").DataTable({
    destroy: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50],
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
    },
    dom: "rti",
    drawCallback: function () {
      actualizarPaginacionVisual(table);
    },
  });

  $("#buscarAdmin").on("keyup", function () {
    table.search(this.value).draw();
  });

  // El select de cantidad debe estar aquí dentro donde 'table' existe
  $("#filtroCantidad")
    .off("change")
    .on("change", function () {
      const valor = parseInt($(this).val());
      table.page.len(valor).draw();
    });
};

const formatearFecha = (fecha) => {
  if (!fecha) return "N/A"; //estilizamos el NULL

  //estilizamos fecha
  const date = new Date(fecha);
  const dia = String(date.getDate()).padStart(2, "0");
  const mes = String(date.getMonth() + 1).padStart(2, "0");
  const anio = date.getFullYear();

  //devolvemos fecha estilizada
  return `${dia}/${mes}/${anio}`;
};

//funcion para estilizar estado segun tipo
const obtenerBadgeEstado = (estado) => {
  const estadoLower = (estado || "").toLowerCase();

  //Usamos switch para cada caso
  //utilizamos clases de bootstrap para estilizar con colores
  switch (estadoLower) {
    case "por atender":
      return `<span class="badge bg-primary">${estado}</span>`;
    case "no atendido":
      return `<span class="badge bg-danger">${estado}</span>`;
    case "atendido":
      return `<span class="badge bg-success">${estado}</span>`;
    default:
      return `<span class="badge bg-secondary">${estado}</span>`;
  }
};

//Funcion para insertar datos en la tabla de seguimientos del modal
function llenarTablaSeguimientos(id_caso) {
  const tablaBody = document.getElementById("tablaSeguimientosBody");

  //Insertamos el cargando
  tablaBody.innerHTML = `
<tr class="bg-transparent">
        <td colspan="4" class="text-center py-4 text-slate-500 italic bg-transparent border-0">
            <span class="spinner-border spinner-border-sm me-2"></span> Cargando historial...
        </td>
    </tr>
    `;

  //Hacemos la request para obtener los seguimientos del caso

  $.ajax({
    url: ENDPOINT_SEGUIMIENTOS,
    method: "POST",
    data: { idcaso: id_caso },
    success: function (response) {
      //validamos que el status sea ok y que existan seguimientos
      if (
        response.status === "ok" &&
        response.seguimientos &&
        response.seguimientos.length > 0
      ) {
        //limpiamos la tabla
        tablaBody.innerHTML = "";

        //insertamos los seguimientos en la tabla
        response.seguimientos.forEach((seg) => {
          tablaBody.innerHTML += `
                    <tr class="border-bottom border-slate-700/30 bg-transparent">
                        <td class="bg-transparent text-indigo-300 fw-bold">${seg.id_seguimiento}</td>
                        <td class="bg-transparent text-slate-200">${formatearFecha(seg.fecha_seguimiento)}</td>
                        <td class="bg-transparent text-slate-200">${seg.usuario}</td>
                        <td class="bg-transparent text-slate-400" style="white-space: pre-wrap;">${seg.observacion}</td>
                    </tr>
                    `;
        });
      } else {
        tablaBody.innerHTML = `
                <tr class="bg-transparent">
                    <td colspan="4" class="text-center py-4 text-slate-500 bg-transparent">${response.mensaje}.</td>
                </tr>`;
      }

      if (response.status === "error") {
        tablaBody.innerHTML = `
            <tr class="bg-transparent">
                    <td colspan="4" class="text-center py-4 text-warning bg-transparent">${response.mensaje}.</td>
                </tr>`;
      }
    },
    error: function (xhr, status, error) {
      console.error("Error al cargar los seguimientos:", error);
      tablaBody.innerHTML = `
            <tr class="bg-transparent">
                <td colspan="4" class="text-center py-4 text-danger bg-transparent font-bold">Error de conexión al servidor</td>
            </tr>`;
    },
  });
}

//funcion para supervisar claso
const supervisarCaso = async (idCaso) => {
  //capturamos body del modal
  const modalElement = document.getElementById("modalCaso");
  //inicializamos clase de bootstrap
  const modal = new bootstrap.Modal(modalElement);

  //mostramos el cargando
  document.getElementById("modalCasoBody").innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3">Cargando detalles del caso...</p>
        </div>
    `;

  modal.show(); //Renderizamos

  try {
    //Hacemos fetch al endpoint donde devolverá el caso a supervisar
    const response = await fetch(ENDPOINT_OBTENER, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id_caso=${idCaso}`,
    });

    //personalizacion de errores
    if (!response.ok) {
      throw new Error(`Error HTTP ${response.status}`);
    }

    //transformamos a json la response
    const data = await response.json();

    //Validamos que el status de la response ok
    if (data.status === "ok" && data.caso) {
      //mostramos los detalles del caso con una función que declararemos más adelante
      mostrarDetallesCaso(data.caso);
    } else {
      //Mandamos un error personalizado
      throw new Error(data.mensaje || "No se pudo obtener el caso");
    }
    //capturamos errores
  } catch (error) {
    document.getElementById("modalCasoBody").innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
  }
};

const cerrarModal = () => {
  const modalElement = document.getElementById('modalCaso');
  const modal = bootstrap.Modal.getInstance(modalElement);
  if (modal) {
    modal.hide();
  }
};

const modalReasignar = async (idCaso) => {

  //Capturamos el modal, reutilizando el modal de supervisar caso
  const modalElement = document.getElementById('modalCaso');
  //capturamos partes del modal
  const modalBody = document.getElementById("modalCasoBody");
  const modalTitle = document.getElementById("modalCasoLabel");
  const modalFooter = document.getElementById("modalFooter");

  //Inicializamos la clase de bootstrap para el modal
  const modal = new bootstrap.Modal(modalElement);

  //Mostramos el cargando
  document.getElementById("modalCasoBody").innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3">Cargando detalles del caso...</p>
        </div>
    `;

  modal.show(); //Renderizamos

  //Insertamos el texto del titulo
  modalTitle.innerText = `Reasignar caso #${idCaso} (Operación sensible)`

  //Recorreremos los usuarios 
  let optionsComi = '';

  modalFooter.innerHTML = `
    <button type="button"
      class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors text-sm font-medium"
      data-bs-dismiss="modal">Cerrar</button>
    <button type="button" class="btn btn-success" onclick="reasignarCaso(${idCaso})">
      <i class="bi bi-check-circle"></i> Reasignar
    </button>
    `

  try {
    const res = await fetch(ENDPOINT_COMISIONADOS);
    const comisionados = await res.json()

    let optionsComi = '';
    comisionados.data.forEach((comi) => {
      // Las opciones heredarán el color del CSS
      optionsComi += `<option value="${comi.documento}">${comi.comisionado} - (${comi.total_casos})</option>`
    })

    // Insertamos el body con los inputs limpios usando glass-input
    modalBody.innerHTML = `
<div class="row">
  <div class="col-md-12 mb-3">
    <label class="form-label fw-bold text-slate-300">Elegir usuario al cual se va reasignar:</label>
    <select name="newComi" id="nuevoComisionado" class="form-select glass-input">
      <option value="" selected disabled>Seleccione un comisionado</option>
      ${optionsComi}
    </select>
  </div>
  
  <div class="col-md-12 mb-3">
    <textarea class="form-control glass-input" id="motivo" rows="3" 
    placeholder="Describa brevemente por qué se cambia el responsable (quedará en el historial del caso)"></textarea>
  </div>
</div>`

  } catch (err) {
    console.error(err);

    modalBody.innerHTML = `
      <div class="alert alert-danger m-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        No se pudieron cargar los comisionados. Por favor, intente más tarde.
      </div>`;
  }
};

const reasignarCaso = async (idCaso) => {
  const nuevoComi = document.getElementById("nuevoComisionado");
  const motivo = document.getElementById("motivo");
  const body = document.getElementById("modalCasoBody");

  //validamos que nada venga vacio
  if (!nuevoComi.value || !motivo.value) {
    body.innerHTML += `<div class="alert alert-danger m-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        Para reasignar, todos los datos son necesarios.
      </div>`
    return;
  };

  //armamos el objeto para enviar
  const params = {
    'idCaso': idCaso,
    'documentoNuevo': nuevoComi.value,
    'motivo': motivo.value,
  }

  //Enviamos mediante ajax
  $.ajax({
    data: params,
    url: ENDPOINT_GESTIONAR,
    type: 'POST',
    dataType: 'json',


    success: function (response) {
      cerrarModal();
      cargarCasos();

      if (response.status == 'error') {
        //se muestra una alerta estetica de error
        Swal.fire({
          icon: 'error',
          title: `${response.mensaje}`,
          showConfirmButton: false,
          theme: 'dark',
          timer: 1000
        });
        return;
      };

      Swal.fire({
        icon: 'success',
        title: 'Usuario reactivado con exito',
        theme: 'dark',
        showConfirmButton: false,
        timer: 1000,
      });
    },

    error: function (jqXHR, textStatus, errorThrown) {
      console.error("Error en la comunicación con el servidor:", textStatus, errorThrown);
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'Ocurrió un error al intentar reactivar al usuario.',
        theme: 'dark'
      });
    }
  })
}

//definimos la función previamente declarada
const mostrarDetallesCaso = (caso) => {
  //capturamos partes del modal
  const modalBody = document.getElementById("modalCasoBody");
  const modalTitle = document.getElementById("modalCasoLabel");

  //definimos el titulo
  modalTitle.textContent = `Caso #${caso.id_caso} - ${caso.tipo_caso}`;

  //insertamos el html con los datos del caso
  modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">ID del Caso:</label>
                <p class="form-control-plaintext">${caso.id_caso}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tipo de Caso:</label>
                <p class="form-control-plaintext">${caso.tipo_caso || "N/A"}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Fecha de Inicio:</label>
                <p class="form-control-plaintext">${formatearFecha(caso.fecha_inicio)}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Fecha de Cierre:</label>
                <p class="form-control-plaintext">${caso.fecha_cierre ? formatearFecha(caso.fecha_cierre) : '<span class="badge bg-warning text-dark">Pendiente</span>'}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Estado:</label>
                <p class="form-control-plaintext">${obtenerBadgeEstado(caso.estado)}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Proceso:</label>
                <p class="form-control-plaintext">${caso.proceso || "N/A"}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Comisionado Encargado:</label>
                <p class="form-control-plaintext">${caso.comisionado || "Sin asignar"}</p>
            </div>
        </div>

<div class="row mb-4">
            <div class="col-12">
            <button type="button" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-indigo-400 bg-indigo-900/20 border border-indigo-500/30 rounded-lg hover:bg-indigo-500/20 transition-colors mb-4" id="btnMostrar">
                <i class="bi bi-table text-lg"></i> Mostrar tabla de seguimiento
            </button>

<div id="tablaSeguimientosContainer" class="bg-slate-800/40 rounded-lg p-3 border border-slate-700/50 w-100" style="display: none;">
    <label class="fw-bold text-indigo-400 uppercase mb-2 d-block" style="font-size: 0.75rem; letter-spacing: 0.05em;">
        <i class="bi bi-clock-history me-1"></i> Historial de Seguimientos
    </label>
    <div class="table-responsive w-100" style="max-height: 250px; overflow-y: auto;">
        <table class="table table-dark table-borderless align-middle mb-0 bg-transparent w-100">
            <thead class="text-slate-400 border-bottom border-slate-700 bg-transparent" style="font-size: 0.7rem;">
                <tr>
                    <th class="pb-2 bg-transparent">ID</th>
                    <th class="pb-2 bg-transparent">FECHA</th>
                    <th class="pb-2 bg-transparent">USUARIO</th>
                    <th class="pb-2 bg-transparent">OBSERVACIÓN</th>
                </tr>
            </thead>
            <tbody id="tablaSeguimientosBody" class="text-slate-300 bg-transparent" style="font-size: 0.85rem;">
                </tbody>
        </table>
    </div>
</div>
        
        ${caso.descripcion
      ? `
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Descripción:</label>
                <p class="form-control-plaintext">${caso.descripcion}</p>
            </div>
        </div>
        `
      : ""
    }
    `;

  //Capturamos el boton para mostrar el historial de seguimientos
  const btnSeguimientos = document.getElementById("btnMostrar");

  //capturamos el contenedor de la tabla de seguimientos
  const tablaSeguimientosContainer = document.getElementById(
    "tablaSeguimientosContainer",
  );

  btnSeguimientos.addEventListener("click", () => {
    if (tablaSeguimientosContainer.style.display === "none") {
      tablaSeguimientosContainer.style.display = "block";
      btnSeguimientos.innerHTML = `<i class="bi bi-eye-slash-fill me-1"></i> Ocultar Historial de Seguimientos`;
    } else {
      tablaSeguimientosContainer.style.display = "none";
      btnSeguimientos.innerHTML = `<i class="bi bi-table"></i> Mostrar tabla de seguimiento`;
    }

    llenarTablaSeguimientos(caso.id_caso);
  });
};

// ─── Paginación visual personalizada ─────────────────────────────────────────
const actualizarPaginacionVisual = (table) => {
  if (!table) return;

  const info = table.page.info(); // { page (0-based), pages, ... }
  const paginaActual = info.page;
  const totalPaginas = info.pages;

  const btnPrev = document.getElementById("btnPaginaAnterior");
  const btnNext = document.getElementById("btnPaginaSiguiente");
  const contenedor = document.getElementById("pagBotones");

  if (!btnPrev || !btnNext || !contenedor) return;

  // ── Estilos de los botones de número ──────────────────────────────────────
  const claseNormal = [
    "w-9",
    "h-9",
    "flex",
    "justify-center",
    "items-center",
    "text-white/70",
    "hover:text-white",
    "hover:bg-blue-600/80",
    "text-sm",
    "rounded-lg",
    "transition-colors",
    "cursor-pointer",
    "font-medium",
  ].join(" ");

  const claseActiva = [
    "w-9",
    "h-9",
    "flex",
    "justify-center",
    "items-center",
    "bg-blue-600",
    "text-white",
    "text-sm",
    "rounded-lg",
    "font-semibold",
    "shadow",
    "shadow-blue-500/40",
    "cursor-default",
  ].join(" ");

  // ── Generar botones de número según páginas reales ─────────────────────────
  contenedor.innerHTML = "";

  // Ventana deslizante: máx 5 páginas visibles a la vez
  const ventana = 5;
  let desde = Math.max(0, paginaActual - Math.floor(ventana / 2));
  const hasta = Math.min(totalPaginas, desde + ventana);
  if (hasta - desde < ventana) desde = Math.max(0, hasta - ventana);

  for (let i = desde; i < hasta; i++) {
    const btn = document.createElement("button");
    btn.type = "button";
    btn.textContent = String(i + 1);
    btn.className = i === paginaActual ? claseActiva : claseNormal;
    if (i === paginaActual) {
      btn.setAttribute("aria-current", "page");
    } else {
      btn.addEventListener("click", () => table.page(i).draw("page"));
    }
    contenedor.appendChild(btn);
  }

  // ── Botón Anterior ────────────────────────────────────────────────────────
  // Clonar para limpiar listeners previos
  const nuevoPrev = btnPrev.cloneNode(true);
  btnPrev.parentNode.replaceChild(nuevoPrev, btnPrev);

  if (paginaActual === 0) {
    nuevoPrev.disabled = true;
  } else {
    nuevoPrev.disabled = false;
    nuevoPrev.addEventListener("click", () =>
      table.page("previous").draw("page"),
    );
  }

  // ── Botón Siguiente ───────────────────────────────────────────────────────
  const nuevoNext = btnNext.cloneNode(true);
  btnNext.parentNode.replaceChild(nuevoNext, btnNext);

  if (paginaActual >= totalPaginas - 1) {
    nuevoNext.disabled = true;
  } else {
    nuevoNext.disabled = false;
    nuevoNext.addEventListener("click", () => table.page("next").draw("page"));
  }
};
// ─────────────────────────────────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", () => {
  cargarCasos(); // Los datos llegan → renderizarTablaCasos → DataTables se inicia allí
});
