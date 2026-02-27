const ENDPOINT = '';

const containerNotificaciones = document.getElementById('containerNotis');

if (!containerNotificaciones) {

  Swal.fire({
    icon: "error",
    title: "¡Ha ocurrido un error al intentar acceder al DOM!",
    theme: 'dark',
    timer: 1500,
    showConfirmButton: false,
  });
};

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

    containerNotificaciones.innerHTML =
      `
      <li class="notis">
        <div class="flex gap-4">
          <div class="shrink-0 pt-1">
            <i class="bi bi-info-circle-fill text-indigo-400"></i>
          </div>
          <div>
            <p>Cargando...</p>
            <p class="text-xs text-slate-400 mt-2">Espere porfavor</p>
          </div>
        </div>
      </li>
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
    };

    const respuesta = await fetchToAPI.json();

    if (respuesta.status === 'error') {
      containerNotificaciones.innerHTML = '';
      containerNotificaciones.innerHTML =
        `
        <li class="notis">
          <div class="flex gap-4">
            <div class="shrink-0 pt-1">
              <i class="bi bi-info-circle-fill text-indigo-400"></i>
              <i class="bi text-indigo-400">¡Error!</i>
            </div>
          <div>
            <p>Ha ocurrido un error al cargar las notificaciones ${respuesta.mensaje}</p>
            <p class="text-xs text-slate-400 mt-2">Verifique y recarge la pagina</p>
          </div>
        </div>
      </li>
      `;
      return;
    };

    if (!Array.isArray(respuesta.notificaciones)) {
      containerNotificaciones.innerHTML =
        `
        <li class="notis">
          <div class="flex gap-4">
            <div class="shrink-0 pt-1">
              <i class="bi bi-info-circle-fill text-indigo-400"></i>
              <i class="bi text-indigo-400">¡Error!</i>
            </div>
          <div>
            <p>Ha ocurrido un error al cargar las notificaciones</p>
            <p class="text-xs text-slate-400 mt-2">Verifique y recarge la pagina</p>
          </div>
        </div>
      </li>
      `;

      return;
    }

    if (respuesta.status === 'ok') {

      containerNotificaciones.innerHTML = '';

      respuesta.notificaciones.forEach(data => {

        containerNotificaciones.innerHTML +=
          `
      <li class="notis">
        <div class="flex gap-4">
          <div class="shrink-0 pt-1">
            <i class="bi bi-info-circle-fill text-indigo-400"></i>
            <i class="bi text-indigo-400">${data.id}</i>
          </div>
          <div>
            <p>${data.descripción}</p>
            <p class="text-xs text-slate-400 mt-2">${formatearFecha(data.fecha)}</p>
          </div>
        </div>
      </li>
      `;
      });
    }
  } catch (err) {
    console.error(err);

    containerNotificaciones.innerHTML = '';

    containerNotificaciones.innerHTML =
      `
        <li class="notis">
          <div class="flex gap-4">
            <div class="shrink-0 pt-1">
              <i class="bi bi-info-circle-fill text-indigo-400"></i>
              <i class="bi text-indigo-400">¡Error!</i>
            </div>
          <div>
            <p>Ha ocurrido un error al cargar las notificaciones</p>
            <p class="text-xs text-slate-400 mt-2">Verifique y recarge la pagina</p>
          </div>
        </div>
      </li>
      `;
  };
};

document.addEventListener('DOMContentLoaded', function () {

  cargarNotificaciones(ENDPOINT);

  setInterval(() => {
    cargarNotificaciones(ENDPOINT);
  }, 120000)
})