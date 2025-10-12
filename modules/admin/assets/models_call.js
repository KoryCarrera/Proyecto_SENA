async function cargarComponente(idElemento, rutaArchivo) {
	const respuesta = await
	fetch(rutaArchivo);
	const html = await respuesta.text();
	
	document.getElementById(idElemento).innerHTML = html;
	}
