let timeout;
const tiempoEspera = 900000; // 15 minutos en milisegundos

//Aislamos la lógica en una función para poder reutilizarla
const reiniciarTemporizador = () => {
    clearTimeout(timeout);
    
    timeout = setTimeout(() => {
        location.reload();
    }, tiempoEspera);
};

//Iniciamos el contador apenas carga la página por si el usuario no hace nada
reiniciarTemporizador();

//Escuchamos el mouse, pero también otros eventos clave de actividad
document.addEventListener('mousemove', reiniciarTemporizador);
document.addEventListener('keydown', reiniciarTemporizador); // Si escribe o usa flechas
document.addEventListener('click', reiniciarTemporizador);   // Si hace clic
document.addEventListener('scroll', reiniciarTemporizador);  // Si usa la rueda del mouse