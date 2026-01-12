const ENDPOINT = '/graficasAdmin'; 

const CHART_COLORS = [
    'rgb(79, 70, 229)',     
    'rgb(239, 68, 68)',     
    'rgb(245, 158, 11)',   
    'rgb(16, 185, 129)',    
    'rgb(54, 162, 235)'     
];

const MONTH_NAMES_ES = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

/**
 * Función genérica para dibujar gráficos de Chart.js.
 */
const drawChart = (canvasElement, type, labels, data) => {
    if (canvasElement.chart) {
        canvasElement.chart.destroy();
    }
    
    const options = {
        responsive: true,
        maintainAspectRatio: false,
        scales: type === 'polarArea' ? { r: { beginAtZero: true, display: false } } : 
                type === 'bar' ? { y: { beginAtZero: true } } : {}
    };

    canvasElement.chart = new Chart(canvasElement, {
        type: type,
        data: {
            labels: labels,
            datasets: [{
                label: (type === 'pie' || type === 'polarArea' ? 'Cantidad' : 'Casos Registrados'),
                data: data,
                backgroundColor: CHART_COLORS.slice(0, labels.length).map(color => 
                    type === 'polarArea' ? `${color.substring(0, color.length - 1)}, 0.7)` : color 
                ),
                borderColor: 'black', 
                borderWidth: 1.5
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

    // Convertir etiquetas numéricas de meses a strings
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
    
    // Obtener referencias a los contenedores (NO a los canvas)
    const charts = [
        { 
            canvasId: 'barChart',
            container: document.getElementById('barChart')?.parentElement,
            id: 'bar', 
            type: 'bar', 
            name: 'Casos por Mes' 
        },
        { 
            canvasId: 'pieChart',
            container: document.getElementById('pieChart')?.parentElement,
            id: 'pie', 
            type: 'pie', 
            name: 'Casos por Comisionado' 
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