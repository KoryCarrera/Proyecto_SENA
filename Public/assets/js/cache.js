// Detectar si la página se cargó desde el caché del navegador
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        // La página se cargó desde caché, forzar recarga
        window.location.reload();
    }
});