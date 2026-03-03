//configuracion de taiwilind
tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
            colors: {
                slate: {
                    850: '#151e2e',
                }
            },
            animation: {
                'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                'blob': 'blob 7s infinite',
            },
            keyframes: {
                fadeInUp: {
                    '0%': {
                        opacity: '0',
                        transform: 'translateY(20px)'
                    },
                    '100%': {
                        opacity: '1',
                        transform: 'translateY(0)'
                    },
                },
                blob: {
                    '0%': {
                        transform: 'translate(0px, 0px) scale(1)'
                    },
                    '33%': {
                        transform: 'translate(30px, -50px) scale(1.1)'
                    },
                    '66%': {
                        transform: 'translate(-20px, 20px) scale(0.9)'
                    },
                    '100%': {
                        transform: 'translate(0px, 0px) scale(1)'
                    },
                }
            }
        }
    }
}

//Se capturan los inputs
var documento = document.getElementById("documento")
var contraseña = document.getElementById("password")
var csrfToken = document.getElementById("csrf_token")

//Se captura el boton de enviar para darle un evento
var enviar = document.getElementById("ingresar");


enviar.addEventListener('click', function login() { //Se le agrega el evento click y una función

    //Se capturan el valor de los inputs
    const documentUser = documento.value;
    const passUser = contraseña.value;
    const tokenUSer = csrfToken.value;

    //Se asignan a un objeto para manejarlo mas facilmente
    var parametros = {
        'documento': documentUser,
        'password': passUser,
        'csrf_token': tokenUSer
    }
    $.ajax({ //utilizamos AJAX para la request
        data: parametros, //Enviamos en data el objeto
        url: '/login/auth', //Definimos url (usando la del enrutador)
        type: 'POST', //Definimos el metodo http
        dataType: 'json',  //definimos el formato esperado
        success: function redireccion(respuesta) { //Definimos lo que pasa si el evento fue success
            if (respuesta.status === 'ok') {  //verificamos status

                //Mostramos una alerta estetica
                Swal.fire({
                    title: "¡Ingreso permitido!",
                    icon: 'success',
                    theme: 'dark',
                    showConfirmButton: false,
                    timer: 1000
                });

                //Redireccionamos utilizando la ruta proporcionada por el controller
                setTimeout(() => {
                    window.location.href = respuesta.redirect;
                }, 1000);

                //Si el estatus es error mostrará una alerta dando el mensaje
            } else if (respuesta.status === 'error') {

                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: `${respuesta.mensaje}`,
                    theme: 'dark',
                    timer: 1500,
                    showConfirmButton: false,
                });
            }
        },

        //si el evento falla definimos lo que pasara
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en la comunicación con el servidor:", textStatus, errorThrown);

            Swal.fire({
                icon: "error",
                title: "¡Ha ocurrido un error interno!",
                theme: 'dark',
                timer: 1500,
                showConfirmButton: false,
            });
        }
    });
}
);


document.addEventListener('keydown', function (event) {

    if (event.key === 'Enter') {
        //Se capturan el valor de los inputs
        const documentUser = documento.value;
        const passUser = contraseña.value;
        const tokenUSer = csrfToken.value;

        //Se asignan a un objeto para manejarlo mas facilmente
        var parametros = {
            'documento': documentUser,
            'password': passUser,
            'csrf_token': tokenUSer
        }
        $.ajax({ //utilizamos AJAX para la request
            data: parametros, //Enviamos en data el objeto
            url: '/login/auth', //Definimos url (usando la del enrutador)
            type: 'POST', //Definimos el metodo http
            dataType: 'json',  //definimos el formato esperado
            success: function redireccion(respuesta) { //Definimos lo que pasa si el evento fue success
                if (respuesta.status === 'ok') {  //verificamos status

                    //Mostramos una alerta estetica
                    Swal.fire({
                        title: "¡Ingreso permitido!",
                        icon: 'success',
                        theme: 'dark',
                        showConfirmButton: false,
                        timer: 1000
                    });

                    //Redireccionamos utilizando la ruta proporcionada por el controller
                    setTimeout(() => {
                        window.location.href = respuesta.redirect;
                    }, 1000);

                    //Si el estatus es error mostrará una alerta dando el mensaje
                } else if (respuesta.status === 'error') {

                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: `${respuesta.mensaje}`,
                        theme: 'dark',
                        timer: 1500,
                        showConfirmButton: false,
                    });
                }
            },

            //si el evento falla definimos lo que pasara
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("Error en la comunicación con el servidor:", textStatus, errorThrown);

                Swal.fire({
                    icon: "error",
                    title: "¡Ha ocurrido un error interno!",
                    theme: 'dark',
                    timer: 1500,
                    showConfirmButton: false,
                });
            }
        });
    }
})