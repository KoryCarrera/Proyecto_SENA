const ENDPOINT = '../../../app/controllers/dashboardComi.php'; 

const CHART_COLORS = [
    'rgb(79, 70, 229)',    
    'rgb(239, 68, 68)',    
    'rgb(245, 158, 11)',  
    'rgb(16, 185, 129)',   
    'rgb(54, 162, 235)'    
];

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

const renderChartFromResponse = (chartElement, apiResponse, chartId, chartType, chartName) => {
    const container = chartElement.parentElement;
    
    if (!container) return; 

    const chartIdCapitalized = chartId.charAt(0).toUpperCase() + chartId.slice(1);
    const labelsKey = `labels${chartIdCapitalized}`;
    const dataKey = `data${chartIdCapitalized}`;
    
    const labels = apiResponse[labelsKey];
    const data = apiResponse[dataKey];

    const errorMessage = apiResponse.errors?.[chartId]; 

    if (errorMessage || !labels || !data || labels.length === 0) {
        const errorText = errorMessage 
            ? `⚠️ Error en ${chartName}: ${errorMessage}`
            : `✅ No hay datos de ${chartName} registrados para mostrar.`;
            
        container.innerHTML = `<p class="text-indigo-600 text-center p-8 font-semibold text-sm">
            ${errorText}
        </p>`;
        return;
    }

    if (!container.contains(chartElement)) {
        container.innerHTML = '';
        container.appendChild(chartElement);
    }
    
    drawChart(chartElement, chartType, labels, data);
};

const loadAllChartData = async () => {
    
    const chartsToLoad = [
        { element: document.getElementById("barChart"), id: 'bar', type: 'bar', name: 'Casos por Mes' },
        { element: document.getElementById("pieChart"), id: 'pie', type: 'pie', name: 'Casos por Comisionado' },
        { element: document.getElementById("polarChart"), id: 'polar', type: 'polarArea', name: 'Casos por Tipo' }
    ].filter(c => c.element);
    
    chartsToLoad.forEach(({ element, name }) => {
        const container = element.parentElement;
        if(container) {
            container.innerHTML = `<p class="text-gray-500 text-center p-8">Cargando datos de ${name}...</p>`;
            container.originalCanvas = element; 
        }
    });

    try {

        const response = await fetch(ENDPOINT);
        
        if (!response.ok) {
            throw new Error(`Error HTTP ${response.status}: Verifica la ruta del controlador PHP.`);
        }
        
        const apiResponse = await response.json(); 
        
        if (apiResponse.status !== 'ok' && apiResponse.status !== 'partial_error') {
            throw new Error(apiResponse.mensaje || 'Fallo crítico del servidor al obtener todos los datos.');
        }

        chartsToLoad.forEach(c => {
            const originalCanvas = c.element.parentElement.originalCanvas; 
            if (originalCanvas) {
                renderChartFromResponse(originalCanvas, apiResponse, c.id, c.type, c.name);
            }
        });

    } catch (error) {

        console.error("Error crítico de la API:", error);
        
        chartsToLoad.forEach(c => {
            const container = c.element.parentElement;
            if(container) {
                 container.innerHTML = `<p class="text-red-600 text-center p-8 font-bold text-sm">
                    Error de Conexión: ${error.message}. Asegúrate de que XAMPP y MySQL estén activos.
                </p>`;
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', loadAllChartData);