// ─── Endpoints ───────────────────────────────────────────────────────────────
const ENDPOINT_LISTAR = "/listarProceso";
const ENDPOINT_CREAR = "/registrarProceso";
const ENDPOINT_DESACTIVAR = "/desactivarProceso";
const ENDPOINT_REACTIVAR = "/reactivarProceso";

// ─── Cargar procesos ──────────────────────────────────────────────────────────
const cargarProcesos = async () => {
  const cuerpoTabla = document.getElementById("tablaProcesosBody");

  if (!cuerpoTabla) {
    console.error("No se encuentra el tbody de la tabla de procesos");
    return;
  }

  // Destruir DataTable existente para evitar duplicados
  if ($.fn.DataTable.isDataTable("#tablaProcesos")) {
    $("#tablaProcesos").DataTable().destroy();
  }

  cuerpoTabla.innerHTML = `
    <tr>
      <td colspan="6" class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2 mb-0">Cargando procesos...</p>
      </td>
    </tr>`;

  try {
    const response = await fetch(ENDPOINT_LISTAR);
    const data = await response.json();

    if (data.status === "ok" && data.procesos.length > 0) {
      renderizarTablaProcesos(data.procesos, cuerpoTabla);
    } else {
      cuerpoTabla.innerHTML = `
        <tr>
          <td colspan="6" class="text-center py-4 text-warning">
            <i class="bi bi-exclamation-triangle fs-1"></i>
            <p class="mt-2 mb-0 fw-bold">No hay procesos registrados</p>
          </td>
        </tr>`;
    }
  } catch (error) {
    console.error("Error:", error);
    cuerpoTabla.innerHTML = `
      <tr>
        <td colspan="6" class="text-center py-4 text-danger">
          <p class="fw-bold">Error: ${error.message}</p>
          <button class="btn btn-sm btn-primary mt-2" onclick="cargarProcesos()">Reintentar</button>
        </td>
      </tr>`;
  }
};

// ─── Renderizar tabla ─────────────────────────────────────────────────────────
const renderizarTablaProcesos = (procesos, cuerpoTabla) => {
  let htmlFilas = "";

  procesos.forEach((proceso) => {
    const botonGestion =
      proceso.estado == 1
        ? `<button class="btn-gestionar btn-desactivar bg-red-600 hover:bg-red-500 px-3 py-2 rounded-lg text-white" onclick="desactivarProceso(${proceso.id_proceso})">Desactivar</button>`
        : `<button class="btn-gestionar btn-reactivar bg-indigo-600 hover:bg-indigo-500 px-3 py-2 rounded-lg text-white" onclick="reactivarProceso(${proceso.id_proceso})">Reactivar</button>`;

    htmlFilas += `
      <tr>
        <td>${proceso.nombre_proceso}</td>
        <td>${proceso.descripcion}</td>
        <td>${proceso.fecha_creacion}</td>
        <td>${proceso.documento}</td>
        <td>${proceso.nombre_creador}</td>
        <td>${botonGestion}</td>
      </tr>`;
  });

  cuerpoTabla.innerHTML = htmlFilas;

  // ── Inicializar DataTables con datos reales en el DOM ──────────────────────
  var table = $("#tablaProcesos").DataTable({
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
    },
    dom: "rti", // Sin paginación nativa,para que recuerden,falta la f,la l y la p,
    autoWidth: false,
    drawCallback: function () {
      actualizarPaginacionProcesos(table);
    },
  });

  // ── Buscador personalizado ─────────────────────────────────────────────────
  $("#buscarProcesos")
    .off("keyup")
    .on("keyup", function () {
      table.search(this.value).draw();
    });

  // ── Select de cantidad ─────────────────────────────────────────────────────
  $("#filtroCantidadProcesos")
    .off("change")
    .on("change", function () {
      table.page.len(parseInt($(this).val())).draw();
    });
};

// ─── Paginación visual de procesos ────────────────────────────────────────────
const actualizarPaginacionProcesos = (table) => {
  if (!table) return;

  const info = table.page.info();
  const paginaActual = info.page;
  const totalPaginas = info.pages;

  const btnPrev = document.getElementById("btnProcesoAnterior");
  const btnNext = document.getElementById("btnProcesoSiguiente");
  const contenedor = document.getElementById("pagBotonesProcesos");

  if (!btnPrev || !btnNext || !contenedor) return;

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

  // ── Botones de número ──────────────────────────────────────────────────────
  contenedor.innerHTML = "";
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

  // ── Botón Anterior ─────────────────────────────────────────────────────────
  const nuevoPrev = btnPrev.cloneNode(true);
  btnPrev.parentNode.replaceChild(nuevoPrev, btnPrev);
  nuevoPrev.disabled = paginaActual === 0;
  if (!nuevoPrev.disabled) {
    nuevoPrev.addEventListener("click", () =>
      table.page("previous").draw("page"),
    );
  }

  // ── Botón Siguiente ────────────────────────────────────────────────────────
  const nuevoNext = btnNext.cloneNode(true);
  btnNext.parentNode.replaceChild(nuevoNext, btnNext);
  nuevoNext.disabled = paginaActual >= totalPaginas - 1;
  if (!nuevoNext.disabled) {
    nuevoNext.addEventListener("click", () => table.page("next").draw("page"));
  }
};

// ─── Desactivar proceso ───────────────────────────────────────────────────────
const desactivarProceso = async (id_Proceso) => {
  const { value: motivo, isConfirmed } = await Swal.fire({
    icon: "warning",
    title: "¿Desactivar este proceso?",
    text: "Quedará un registro de esta acción. Escribe el motivo:",
    input: "textarea",
    inputPlaceholder: "Escriba el motivo aquí...",
    showCancelButton: true,
    cancelButtonText: "Cancelar",
    confirmButtonText: "Sí, desactivar",
    confirmButtonColor: "#dc3545", 
    theme: "dark",
    inputValidator: (value) => {
      if (!value) return "¡El motivo es obligatorio!";
    }
  });

  if (!isConfirmed) return;

  try {
    const response = await fetch(ENDPOINT_DESACTIVAR, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: id_Proceso, motivo: motivo }), 
    });
    
    const data = await response.json();

    if (data.status !== "ok") throw new Error(data.mensaje);

    Swal.fire({
      icon: "success",
      title: "Proceso desactivado exitosamente",
      theme: "dark",
      showConfirmButton: false,
      timer: 1500,
    });
    
    cargarProcesos();

  } catch (error) {
    console.error("Error al desactivar:", error);
    Swal.fire({
      icon: "error",
      title: "Error en la operación",
      text: error.message || "Error de conexión con el servidor",
      theme: "dark",
    });
  }
};

// ─── Reactivar proceso ────────────────────────────────────────────────────────
const reactivarProceso = async (id_Proceso) => {
  const { value: motivo, isConfirmed } = await Swal.fire({
    icon: "info",
    title: "¿Reactivar este proceso?",
    text: "Quedará un registro de esta acción. Escribe el motivo:",
    input: "textarea",
    inputPlaceholder: "Escriba el motivo aquí...",
    showCancelButton: true,
    cancelButtonText: "Cancelar",
    confirmButtonText: "Sí, reactivar",
    confirmButtonColor: "#198754",
    theme: "dark",
    inputValidator: (value) => {
      if (!value) return "¡El motivo es obligatorio!";
    }
  });

  if (!isConfirmed) return;

  try {
    const response = await fetch(ENDPOINT_REACTIVAR, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: id_Proceso, motivo: motivo }), 
    });
    
    const data = await response.json();

    if (data.status !== "ok") throw new Error(data.mensaje);

    Swal.fire({
      icon: "success",
      title: "Proceso reactivado exitosamente",
      theme: "dark",
      showConfirmButton: false,
      timer: 1500,
    });
    cargarProcesos();

  } catch (error) {
    console.error("Error al reactivar:", error);
    Swal.fire({
      icon: "error",
      title: "Error en la operación",
      text: error.message || "Error de conexión con el servidor",
      theme: "dark",
    });
  }
};

// ─── DOMContentLoaded ─────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  // Cargar tabla
  cargarProcesos();

  // Modal
  const botonAbrir = document.getElementById("abrirModal");
  const botonCerrar = document.getElementById("cerrar-modal");
  const modal = document.getElementById("modal");
  const formulario = document.querySelector(".formulario");
  const btnRegistrar = document.getElementById("btnRegistrarProceso");

  if (!botonAbrir || !botonCerrar || !modal || !formulario) return;

  botonAbrir.addEventListener("click", () => {
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
  });

  const cerrarModal = () => {
    modal.style.display = "none";
    document.body.style.overflow = "auto";
    formulario.reset();
  };

  botonCerrar.addEventListener("click", cerrarModal);

  modal.addEventListener("click", (e) => {
    if (e.target === modal) cerrarModal();
  });

  document.addEventListener("keydown", (e) => {
    if (modal.style.display === "flex" && e.key === "Escape") cerrarModal();
  });

  // Guardar proceso
  if (btnRegistrar) {
    btnRegistrar.addEventListener("click", async () => {
      const nombreProceso = document
        .getElementById("nombre-proceso")
        .value.trim();
      const descripcion = document.getElementById("descripcion").value.trim();

      if (!nombreProceso) {
        Swal.fire({
          icon: "error",
          title: "Por favor ingresa el nombre del proceso",
          theme: "dark",
          showConfirmButton: false,
          timer: 1200,
        });
        document.getElementById("nombre-proceso").focus();
        return;
      }
      if (!descripcion) {
        Swal.fire({
          icon: "error",
          title: "Por favor ingresa la descripción",
          theme: "dark",
          showConfirmButton: false,
          timer: 1200,
        });
        document.getElementById("descripcion").focus();
        return;
      }

      btnRegistrar.disabled = true;
      btnRegistrar.textContent = "Creando...";

      try {
        const response = await fetch(ENDPOINT_CREAR, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ nombre: nombreProceso, descripcion }),
        });

        if (!response.ok) throw new Error(`Error HTTP ${response.status}`);
        const data = await response.json();

        if (data.status === "ok") {
          Swal.fire({
            icon: "success",
            title: "Proceso creado exitosamente",
            theme: "dark",
            showConfirmButton: false,
            timer: 1000,
          });
          cerrarModal();
          cargarProcesos();
        } else {
          throw new Error(data.mensaje || "Error al crear");
        }
      } catch (error) {
        console.error("Error:", error);
        Swal.fire({
          icon: "error",
          title: "Error al crear el proceso",
          text: error.message,
          theme: "dark",
          showConfirmButton: false,
          timer: 1500,
        });
      } finally {
        btnRegistrar.disabled = false;
        btnRegistrar.textContent = "Crear proceso";
      }
    });
  }
});
