const ENDPOINT = '/listarNotiAdmin';

const containerNotificaciones = document.getElementById('containerNotis');
let dtInstance = null;

if (!containerNotificaciones) {
  Swal.fire({
    icon: "error",
    title: "¡Ha ocurrido un error al intentar acceder al DOM!",
    theme: 'dark',
    timer: 1500,
    showConfirmButton: false,
  });
}

const formatearFecha = (fecha) => {
  if (!fecha) return 'N/A';
  const date = new Date(fecha);
  const dia = String(date.getDate()).padStart(2, '0');
  const mes = String(date.getMonth() + 1).padStart(2, '0');
  const anio = date.getFullYear();
  return `${dia}/${mes}/${anio} `;
};

async function cargarNotificaciones(url) {
  try {
    const tbody = containerNotificaciones.querySelector('tbody');

    if (dtInstance) {
      dtInstance.destroy();
      dtInstance = null;
    }

    tbody.innerHTML =
      `
      <tr style="background:transparent;">
        <td style="border:none; padding:8px 0;">
          <div class="notis flex gap-4">
            <div class="shrink-0 pt-1">
              <i class="bi bi-info-circle-fill text-indigo-400"></i>
            </div>
            <div>
              <p>Cargando...</p>
              <p class="text-xs text-slate-400 mt-2">Espere porfavor</p>
            </div>
          </div>
        </td>
      </tr>
      `;

    const fetchToAPI = await fetch(url);

    if (!fetchToAPI.ok) {
      Swal.fire({
        icon: "error",
        title: "¡Ha ocurrido un error al intentar hacer fetch!",
        theme: 'dark',
        timer: 2000,
        showConfirmButton: false,
      });

      return false;
    }

    const respuesta = await fetchToAPI.json();

    if (respuesta.status === 'error') {
      tbody.innerHTML =
        `
        <tr style="background:transparent;">
          <td style="border:none; padding:8px 0;">
            <div class="notis flex gap-4">
              <div class="shrink-0 pt-1">
                <i class="bi bi-info-circle-fill text-indigo-400"></i>
                <i class="bi text-indigo-400">¡Error!</i>
              </div>
              <div>
                <p>Ha ocurrido un error al cargar las notificaciones ${respuesta.mensaje}</p>
                <p class="text-xs text-slate-400 mt-2">Verifique y recarge la pagina</p>
              </div>
            </div>
          </td>
        </tr>
        `;
      return;
    }

    if (!Array.isArray(respuesta.notificaciones)) {
      tbody.innerHTML =
        `
        <tr style="background:transparent;">
          <td style="border:none; padding:8px 0;">
            <div class="notis flex gap-4">
              <div class="shrink-0 pt-1">
                <i class="bi bi-info-circle-fill text-indigo-400"></i>
                <i class="bi text-indigo-400">¡Error!</i>
              </div>
              <div>
                <p>Ha ocurrido un error al cargar las notificaciones</p>
                <p class="text-xs text-slate-400 mt-2">Verifique y recarge la pagina</p>
              </div>
            </div>
          </td>
        </tr>
        `;
      return;
    }

    if (respuesta.status === 'ok') {
      tbody.innerHTML = '';
      let i = 0;
      respuesta.notificaciones.forEach(data => {
        i++;
        tbody.innerHTML +=
          `
        <tr style="background:transparent;">
          <td style="border:none; padding:10px 0;">
            <div class="notis flex gap-4">
              <div class="shrink-0 pt-1">
                <i class="bi bi-info-circle-fill text-indigo-400"></i>
                <i class="bi text-indigo-400">${i}</i>
              </div>
              <div>
                <p>${data.descripción}</p>
                <p class="text-xs text-slate-400 mt-2">${formatearFecha(data.fecha)}</p>
              </div>
            </div>
          </td>
        </tr>
        `;
      });

      dtInstance = $('#containerNotis').DataTable({
        language: { url: "https://cdn.datatables.net/1.13.6/i18n/es-ES.json" },
        responsive: true,
        ordering: false,
        destroy: true,
        paging: true,
        info: true,
        autoWidth: false,
        pageLength: 10,
        dom: "rti",
        drawCallback: function () {
          actualizarPaginacionVisual(this.api());
        },
      });

      // Búsqueda personalizada
      $("#buscarAdmin").off("keyup").on("keyup", function () {
        dtInstance.search(this.value).draw();
      });

      // Filtro de cantidad
      $("#filtroCantidadAdmin").off("change").on("change", function () {
        const valor = parseInt($(this).val());
        dtInstance.page.len(valor).draw();
      });

      // Aseguramos que dataTables no le quite el fondo transparente.
      $('#containerNotis').css('background', 'transparent');
    }
  } catch (err) {
    console.error(err);
    const tbody = containerNotificaciones.querySelector('tbody');
    tbody.innerHTML =
      `
      <tr style="background:transparent;">
        <td style="border:none; padding:8px 0;">
          <div class="notis flex gap-4">
            <div class="shrink-0 pt-1">
              <i class="bi bi-info-circle-fill text-indigo-400"></i>
              <i class="bi text-indigo-400">¡Error!</i>
            </div>
            <div>
              <p>Ha ocurrido un error al cargar las notificaciones</p>
              <p class="text-xs text-slate-400 mt-2">Verifique y recarge la pagina</p>
            </div>
          </div>
        </td>
      </tr>
      `;
  }
}

// ─── Paginación visual personalizada ─────────────────────────────────────────
const actualizarPaginacionVisual = (table) => {
  if (!table) return;

  const info = table.page.info();
  const paginaActual = info.page;
  const totalPaginas = info.pages;

  const btnPrev = document.getElementById("btnPaginaAnteriorNotis");
  const btnNext = document.getElementById("btnPaginaSiguienteNotis");
  const contenedor = document.getElementById("pagBotonesNotis");

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

document.addEventListener('DOMContentLoaded', function () {
  cargarNotificaciones(ENDPOINT);

  setInterval(() => {
    cargarNotificaciones(ENDPOINT);
  }, 120000);
});