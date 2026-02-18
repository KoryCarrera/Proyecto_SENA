const ENDPOINT = '/graficasComi'; 

Chart.defaults.color = '#ffffff';

// Paleta base
const CHART_COLORS = [
    'rgb(56, 189, 248)',   // 0. Sky Blue
    'rgb(45, 212, 191)',   // 1. Teal/Turquesa
    'rgb(147, 197, 253)',  // 2. Azul Pastel
    'rgb(79, 70, 229)',    // 3. Índigo Original
    'rgb(192, 132, 252)'   // 4. Lavanda suave
];

// --- AQUÍ DEFINES QUÉ COLOR REPRESENTA QUÉ COSA ---
// Si el label del backend coincide con estas llaves, usará ese color.
const COLOR_MAP = {
    // Estados (Pie Chart generalmente)
    'Por atender': CHART_COLORS[0], // Sky Blue
    'Atendido': CHART_COLORS[1],    // Teal
    'No atendido': CHART_COLORS[3], // Índigo
    
    // Tipos de Caso (Polar Area)
    'Denuncia': CHART_COLORS[4],            // Lavanda
    'Solicitud': CHART_COLORS[2],           // Azul Pastel
    'Derecho de Petición': CHART_COLORS[0], // Sky Blue
    'Queja': CHART_COLORS[3],               // Índigo
    'Reclamo': CHART_COLORS[1],             // Teal

    // Procesos (Bar Chart)
    'Ropa de Trabajo': CHART_COLORS[2],
    'SST': CHART_COLORS[1],
    'SSEMI': CHART_COLORS[0],
    'Plan de incentivos': CHART_COLORS[4],
    'Bienestar Social': CHART_COLORS[3]
};

const MONTH_NAMES_ES = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

/**
 * Función auxiliar para obtener color consistente
 */
const getColorForLabel = (label) => {
    // 1. Busca en el mapa fijo
    if (COLOR_MAP[label]) {
        return COLOR_MAP[label];
    }
    // 2. Si no existe (ej: un proceso nuevo), devuelve un color por defecto (el gris o el primero)
    return 'rgb(156, 163, 175)'; // Gris genérico para datos no mapeados
};

/**
 * Función genérica para dibujar gráficos de Chart.js.
 */
const drawChart = (canvasElement, type, labels, data) => {
    if (canvasElement.chart) {
        canvasElement.chart.destroy();
    }
    
    // Lógica para definir colores
    let backgroundColors;
    let borderColors;

    if (type === 'line') {
        // REQUERIMIENTO: Gráfica de línea de un solo color
        // Usamos el primer color de la paleta (Sky Blue) como principal
        backgroundColors = CHART_COLORS[0]; 
        borderColors = CHART_COLORS[0];
    } else {
        // Para otros gráficos, mapeamos cada label a su color específico
        const baseColors = labels.map(label => getColorForLabel(label));
        
        // Ajuste para PolarArea (transparencia)
        backgroundColors = baseColors.map(color => 
            type === 'polarArea' ? color.replace('rgb', 'rgba').replace(')', ', 0.7)') : color
        );
        borderColors = 'black'; // Borde negro para separar segmentos en tortas/barras
    }

    const options = {
        responsive: true,
        plugins: {
            legend: {
                // En Line charts a veces es util ver la leyenda, en polar/pie a veces no. 
                // Lo dejo false como lo tenías, o true si quieres ver qué es cada color.
                display: type !== 'bar' && type !== 'line', 
                position: 'bottom',
                labels: { color: '#ffffff' }
            },
        },
        maintainAspectRatio: false,
        scales: type === 'polarArea' ? { r: { beginAtZero: true, display: false } } : 
                (type === 'bar' || type === 'line') ? { 
    y: { 
        beginAtZero: true,
        ticks: { 
            color: '#ffffff',
            stepSize: 1,               // 🔥 Solo saltos de 1
            precision: 0,              // 🔥 Sin decimales
            callback: function(value) {
                if (Number.isInteger(value)) {
                    return value;      // 🔥 Solo mostrar enteros
                }
            }
        }
    }, 
    x: { 
        ticks: { color: '#ffffff'} 
    } 
} : {}

    };

    canvasElement.chart = new Chart(canvasElement, {
        type: type,
        data: {
            labels: labels,
            datasets: [{
                label: (type === 'pie' || type === 'polarArea' ? 'Cantidad' : 'Casos Registrados'),
                data: data,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1.5,
                // Propiedades específicas de línea
                pointBackgroundColor: type === 'line' ? '#ffffff' : undefined, // Puntos blancos en la línea
                pointBorderColor: type === 'line' ? borderColors : undefined,
                tension: 0.3,
                fill: type === 'line' ? false : true
            }]
        },
        options: options
    });
};

/**
 * Función que gestiona la representación de un gráfico.
 */
const renderChartFromResponse = (canvasId, container, apiResponse, chartId, chartType, chartName) => {
    
    const chartIdCapitalized = chartId.charAt(0).toUpperCase() + chartId.slice(1);
    let labels = apiResponse[`labels${chartIdCapitalized}`]; 
    const data = apiResponse[`data${chartIdCapitalized}`];

    // Convertir etiquetas numéricas de meses a strings (Solo si es mes)
    // OJO: Según tu JSON, 'bar' trae procesos, no meses. 
    // Dejo la validación segura: solo convierte si son números.
    if (chartId === 'bar' && labels && Array.isArray(labels) && labels.length > 0 && typeof labels[0] === 'number') {
        labels = labels.map(monthNum => MONTH_NAMES_ES[monthNum - 1] || `Mes ${monthNum}`);
    }

    const errorMessage = apiResponse.errors?.[chartId]; 

    // Manejo de errores o datos vacíos
    if (errorMessage || !labels || !data || labels.length === 0 || data.length === 0) {
        const errorText = errorMessage 
            ? `⚠️ Error en ${chartName}: ${errorMessage}`
            : `✅ No hay datos de ${chartName} registrados para mostrar.`;
            
        container.innerHTML = `<p class="text-center p-4 text-warning">${errorText}</p>`;
        return;
    }

    // Crear nuevo canvas
    container.innerHTML = ''; 
    const newCanvas = document.createElement('canvas');
    newCanvas.id = canvasId; 
    container.appendChild(newCanvas);
    
    // Dibujar el gráfico
    drawChart(newCanvas, chartType, labels, data);
};

/**
 * Función principal que realiza UNA SOLA petición a la API y distribuye los datos.
 */
const loadAllChartData = async () => {
    
    // Obtener referencias a los contenedores
    const charts = [
        { 
            canvasId: 'barChart',
            container: document.getElementById('barChart')?.parentElement,
            id: 'bar', 
            type: 'line',  // Aquí definiste que 'bar' (del json) se pinte como LINEA
            name: 'Casos por Procesos' // Cambié el nombre según tu JSON (trae Ropa, SST, etc)
        },
        { 
            canvasId: 'pieChart',
            container: document.getElementById('pieChart')?.parentElement,
            id: 'pie', 
            type: 'bar', // Aquí definiste que 'pie' (del json) se pinte como BARRA
            name: 'Casos por Estado' // Cambié el nombre según tu JSON (trae Por atender)
        },
        { 
            canvasId: 'polarChart',
            container: document.getElementById('polarChart')?.parentElement,
            id: 'polar', 
            type: 'polarArea', 
            name: 'Casos por Tipo' 
        }
    ].filter(c => c.container);
    
    console.log('Charts encontrados:', charts.length);
    
    // Mostrar indicador de carga
    charts.forEach(c => {
        c.container.innerHTML = `<p class="text-center p-4 text-muted">Cargando datos de ${c.name}...</p>`;
    });

    try {
        console.log('Haciendo fetch a:', ENDPOINT);
        
        const response = await fetch(ENDPOINT);
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`Error HTTP ${response.status}`);
        }
        
        const apiResponse = await response.json();
        
        console.log('Respuesta completa del servidor:', apiResponse);
        
        if (apiResponse.status !== 'ok' && apiResponse.status !== 'partial_error') {
            throw new Error(apiResponse.mensaje || 'Error del servidor');
        }

        // Renderizar cada gráfico
        charts.forEach(c => {
            console.log(`Renderizando gráfico: ${c.name}`);
            renderChartFromResponse(c.canvasId, c.container, apiResponse, c.id, c.type, c.name);
        });

    } catch (error) {
        console.error("Error crítico:", error);
        
        charts.forEach(c => {
            if(c.container) {
                c.container.innerHTML = `<p class="text-center p-4 text-danger">
                    <strong>Error:</strong> ${error.message}
                </p>`;
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', loadAllChartData);