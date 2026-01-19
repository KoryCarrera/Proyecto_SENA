const cargarProcesos = async () => {
    
    const cuerpoTabla = document.getElementById("tablaUsuarios");
    

    if (!cuerpoTabla) {
        console.error('No se encuentra  la tabla');
        return; 
    }

    cuerpoTabla.innerHTML = `
        <tr>
            <td colspan="5" class="text-center">
                 Cargando procesos
            </td>
        </tr>
    `;
    
    try {
        const response = await fetch('/listarProcesos');
        const data = await response.json();
        
        if (data.status === 'ok' && data.procesos.length > 0) {
            renderizarTablaProcesos(data.procesos, cuerpoTabla);
        } else {
            // en caso de que no hayan procesos,esto es lo que se muestra
            cuerpoTabla.innerHTML = `
            
                <tr>
                    <td colspan="5" class="text-center">
                        No hay procesos registrados
                    </td>
                </tr>
            `;
        }
        
    } catch (error) {
        console.error('Error:', error);
        //en este se muestra el error en caso de que no se pueda conectar
        cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-danger">
                    Error al cargar procesos
                    <button onclick="cargarProcesos()">Reintentar</button>
                </td>
            </tr>
        `;
    }
};
const renderizarTablaProcesos = (procesos, cuerpoTabla) => {
    //desde aca comenzamos con la tabla de procesos,llenando todo con los procesos desde la bd
    let htmlFilas = '';
    
    // con el foreach hacemos un bucle de recorrido
    procesos.forEach((proceso) => {
        // desde aca estamos creando una fila para cada una de los procesos
        htmlFilas += `
            <tr>
                <td>${proceso.nombre}</td>
                <td>${proceso.fechaRegistro}</td>
                <td>${proceso.fechaDesactivacion}</td>
                <td>${proceso.creador}</td>
                <td>
                    <button class="btn-gestionar" 
                            onclick="desactivarProceso(${proceso.id})">
                        Desactivar
                    </button>
                </td>
            </tr>
        `;
    });
    cuerpoTabla.innerHTML = htmlFilas;
    
    console.log(` Se cargaron ${procesos.length} procesos`);
};
const desactivarProceso = async (idProceso) => {
    // confirmacion de desactivacion
    if (!confirm('¿deseas desactivar este proceso?')) {
        return; 
    }
    
    try {
        const response = await fetch('/desactivarProceso', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: idProceso })
        });
        
        const data = await response.json();
        
        if (data.status === 'ok') {
            alert(' Proceso esta desactivado');
            cargarProcesos();
        } else {
            alert('Error: ' + data.mensaje);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert(' Error al desactivar el proceso');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    console.log(' Página cargada, cargando procesos...');
    cargarProcesos();
});
document.addEventListener('DOMContentLoaded', () => {
    console.log('Configurando sistema...');
    
    
    const botonAbrir = document.getElementById('abrirModal');
    const botonCerrar = document.getElementById('cerrar-modal');
    const botonGuardar = document.getElementById('guardar-modal');
    const modal = document.getElementById('modal');
    const formulario = document.querySelector('.formulario');
    
    if (!botonAbrir || !botonCerrar || !modal || !formulario) {
        console.error('Faltan elementos del modal');
        return;
    }
    
    console.log('Elementos del modal encontrados');
    
    botonAbrir.addEventListener('click', () => {
        console.log(' Abriendo modal...');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });
    
    botonCerrar.addEventListener('click', () => {
        console.log(' Cerrando modal...');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        formulario.reset();
    });
    
    modal.addEventListener('click', (evento) => {
        if (evento.target === modal) {
            console.log(' Cerrando modal (clic fuera)...');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            formulario.reset();
        }
    });
    
    document.addEventListener('keydown', (evento) => {
        if (modal.style.display === 'flex' && evento.key === 'Escape') {
            console.log(' Cerrando modal (tecla ESC)...');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            formulario.reset();
        }
    });
    

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        
        console.log(' Enviando formulario...');
        

        const nombreProceso = document.getElementById('nombre-proceso').value;
        const descripcion = document.getElementById('descripcion').value;
        
        // Verifica los campos se hayan llenado
        if (!nombreProceso.trim()) {
            alert(' Por favor ingresa el nombre del proceso');
            document.getElementById('nombre-proceso').focus();
            return;
        }
        
        if (!descripcion.trim()) {
            alert(' Por favor ingresa la descripción');
            document.getElementById('descripcion').focus();
            return;
        }
        
        botonGuardar.disabled = true;
        botonGuardar.textContent = 'Creando...';
        
        try {
            const response = await fetch(ENDPOINT_CREAR, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nombre: nombreProceso,
                    descripcion: descripcion
                })
            });
            
            if (!response.ok) {
                throw new Error(`Error HTTP ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.status === 'ok') {
                console.log(' Proceso creado');
                alert('Proceso creado exitosamente');
                
                // Cierra el modal cambiando el estilo a none 
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                formulario.reset();
                

                cargarProcesos();
            } else {
                throw new Error(data.mensaje || 'Error al crear');
            }
            
        } catch (error) {
            console.error('Error:', error);
            alert(` Error: ${error.message}`);
        } finally {
            botonGuardar.disabled = false;
            botonGuardar.textContent = 'crear proceso';
        }
    });
    
    console.log('Modal configurado');
    

    console.log('Cargando procesos...');
    cargarProcesos();
});