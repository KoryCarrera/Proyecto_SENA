const ENDPOINT_LISTAR = '/listarProceso';
const ENDPOINT_CREAR = '/registrarProceso';
const ENDPOINT_DESACTIVAR = '/desactivarProceso';
const ENDPOINT_REACTIVAR = '/reactivarProceso';

const cargarProcesos = async () => {
    
    const cuerpoTabla = document.getElementById("tablaProcesos");
    

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
        const response = await fetch(ENDPOINT_LISTAR);
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
    let htmlFilas = '';
    
    procesos.forEach((proceso) => {
        // ✅ Determinar qué botón mostrar según el estado
        const botonGestion = proceso.estado == 1 
            ? `<button class="btn-gestionar btn-desactivar" 
                       onclick="desactivarProceso(${proceso.id_proceso})">
                   Desactivar
               </button>`
            : `<button class="btn-gestionar btn-reactivar" 
                       onclick="reactivarProceso(${proceso.id_proceso})">
                   Reactivar
               </button>`;
        
        htmlFilas += `
            <tr>
                <td>${proceso.nombre_proceso}</td>
                <td>${proceso.descripcion}</td>
                <td>${proceso.fecha_creacion}</td>
                <td>${proceso.documento}</td>
                <td>${proceso.nombre_creador}</td>
                <td>${botonGestion}</td>
            </tr>
        `;
    });
    
    cuerpoTabla.innerHTML = htmlFilas;
    console.log(`Se cargaron ${procesos.length} procesos`);
};

const desactivarProceso = async (id_Proceso) => {
    // confirmacion de desactivacion
    if (!confirm('¿deseas desactivar este proceso?')) {
        return; 
    }
    
    try {
        const response = await fetch(ENDPOINT_DESACTIVAR, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id_Proceso })
        });
        
        const data = await response.json();
        
        if (data.status === 'ok') {
            alert('Proceso ha sido desactivado');
            cargarProcesos();
        } else {
            alert('Error: ' + data.mensaje);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert(' Error al desactivar el proceso');
    }
};

const reactivarProceso = async (id_Proceso) => {
    if (!confirm('¿Deseas reactivar este proceso?')) {
        return; 
    }
    
    try {
        const response = await fetch(ENDPOINT_REACTIVAR, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id_Proceso })
        });
        
        const data = await response.json();
        
        if (data.status === 'ok') {
            alert('Proceso reactivado exitosamente');
            cargarProcesos();
        } else {
            alert('Error: ' + data.mensaje);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Error al reactivar el proceso');
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