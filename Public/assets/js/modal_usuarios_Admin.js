//Configuramos el modal y le damos interactividad (abrir, cerrar, guardar)
document.addEventListener("DOMContentLoaded", () => {
  const botonAbrir = document.getElementById("abrirModalCrear");
  const botonCerrar = document.getElementById("cerrar-modal-crear");
  const botonGuardar = document.getElementById("guardar-modal-crear");
  const modal = document.getElementById("modalCrearUsuario");
  const formulario = document.querySelector(".formulario-crear");

  //inputs del formulario
  const rol = document.getElementById("crearRol");
  const nombre = document.getElementById("crearNombre");
  const apellido = document.getElementById("crearApellido");
  const documento = document.getElementById("crearDocumento");
  const email = document.getElementById("crearEmail");
  const telefono = document.getElementById("crearTelefono");
  

  //Contador de caracteres para los campos del input
  const contNombre = document.getElementById("contearNombre");
  const contApellido = document.getElementById("contearApellido");
  const contDocumento = document.getElementById("contearDocumento");
  const contEmail = document.getElementById("contearEmail");
  const contTelefono = document.getElementById("contearTelefono");

  contNombre.textContent = "0";
  contApellido.textContent = "0";
  contDocumento.textContent = "0";
  contEmail.textContent = "0";

  nombre.addEventListener("input", () => {
    contNombre.textContent = nombre.value.length;
  });

  apellido.addEventListener("input", () => {
    contApellido.textContent = apellido.value.length;
  });

  documento.addEventListener("input", () => {
    contDocumento.textContent = documento.value.length;
  });

  email.addEventListener("input", () => {
    contEmail.textContent = email.value.length;
  });

  telefono.addEventListener("input", () => {
    contTelefono.textContent = telefono.value.length;
  });

  if (!botonAbrir || !botonCerrar || !modal || !formulario) {
    return;
  }

  botonAbrir.addEventListener("click", () => {
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";

    contNombre.textContent = "0";
    contApellido.textContent = "0";
    contDocumento.textContent = "0";
    contEmail.textContent = "0";
    contTelefono.textContent = "0";
  });

  botonCerrar.addEventListener("click", () => {
    modal.style.display = "none";
    document.body.style.overflow = "auto";
    formulario.reset();

    contNombre.textContent = "0";
    contApellido.textContent = "0";
    contDocumento.textContent = "0";
    contEmail.textContent = "0";
    contTelefono.textContent = "0";
  });

  modal.addEventListener("click", (evento) => {
    if (evento.target === modal) {
      modal.style.display = "none";
      document.body.style.overflow = "auto";

      contNombre.textContent = "0";
      contApellido.textContent = "0";
      contDocumento.textContent = "0";
      contEmail.textContent = "0";
      contTelefono.textContent = "0";

      formulario.reset();
    }
  });

  document.addEventListener("keydown", (evento) => {
    if (modal.style.display === "flex" && evento.key === "Escape") {
      modal.style.display = "none";
      document.body.style.overflow = "auto";

      contNombre.textContent = "0";
      contApellido.textContent = "0";
      contDocumento.textContent = "0";
      contEmail.textContent = "0";
      contTelefono.textContent = "0";

      formulario.reset();
    }
  });

  formulario.addEventListener("submit", async (evento) => {
    evento.preventDefault();

    // Verificamos que los campos se hayan llenado
    if (!rol || rol === "") {
      Swal.fire({
        icon: "error",
        title: "Por favor selecciona un rol",
        theme: "dark",
        showConfirmButton: false,
        timer: 1000,
      });
      document.getElementById("crearRol").focus();
      return;
    }

    if (!nombre.value.trim()) {
      Swal.fire({
        icon: "error",
        title: "Por favor ingresa el nombre",
        theme: "dark",
        showConfirmButton: false,
        timer: 1000,
      });
      document.getElementById("crearNombre").focus();
      return;
    }

    if (!apellido.value.trim()) {
      Swal.fire({
        icon: "error",
        title: "Por favor ingresa el apellido",
        theme: "dark",
        showConfirmButton: false,
        timer: 1000,
      });
      document.getElementById("crearApellido").focus();
      return;
    }

    if (!documento.value.trim()) {
      Swal.fire({
        icon: "error",
        title: "Por favor ingresa el documento",
        theme: "dark",
        showConfirmButton: false,
        timer: 1000,
      });
      document.getElementById("crearDocumento").focus();
      return;
    }

    if (!email.value.trim()) {
      Swal.fire({
        icon: "error",
        title: "Por favor ingresa el correo",
        theme: "dark",
        showConfirmButton: false,
        timer: 1000,
      });
      document.getElementById("crearEmail").focus();
      return;
    }

    var parametrosUsuarios = {
      documento: documento.value,
      rol: rol.value,
      nombre: nombre.value,
      apellido: apellido.value,
      email: email.value,
      telefono: telefono.value,
    };

    botonGuardar.disabled = true;
    botonGuardar.textContent = "Creando...";

    try {
      $.ajax({
        data: parametrosUsuarios,
        url: ENDPOINT_INSERTAR,
        type: "POST",
        dataType: "json",

        success: function (data) {
          if (data.status === "ok") {
            console.log(" Usuario creado");
            Swal.fire({
              icon: "success",
              title: `${data.mensaje}`,
              theme: "dark",
              showConfirmButton: false,
              timer: 1000,
            });

            // Cierra el modal cambiando el estilo a none
            modal.style.display = "none";
            document.body.style.overflow = "auto";
            formulario.reset();

            cargarUsuarios();
          } else {
            throw new Error(data.mensaje || "Error al crear el usuario");
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.error(
            "Error en la comunicación con el servidor:",
            textStatus,
            errorThrown,
          );

          Swal.fire({
            icon: "error",
            title: "¡Ha ocurrido un error interno!",
            theme: "dark",
            timer: 1500,
            showConfirmButton: false,
          });
        },
      });
    } catch (error) {
      console.error("Error:", error);
      Swal.fire({
        icon: "error",
        title: "Error al crear el usuario",
        text: `Error: ${error.message}`,
        theme: "dark",
        showConfirmButton: false,
        timer: 1000,
      });
    } finally {
      botonGuardar.disabled = false;
      botonGuardar.textContent = "Crear Usuario";
    }

    contNombre.textContent = "0";
    contApellido.textContent = "0";
    contDocumento.textContent = "0";
    contEmail.textContent = "0";
    
  });
});
