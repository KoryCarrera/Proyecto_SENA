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
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        responsive: true,
        ordering: false,
        destroy: true,
        paging: true,
        info: true
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

document.addEventListener('DOMContentLoaded', function () {
  cargarNotificaciones(ENDPOINT);

  setInterval(() => {
    cargarNotificaciones(ENDPOINT);
  }, 120000);
});