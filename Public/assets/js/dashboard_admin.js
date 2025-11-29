// 🔴 CONSTANTES
// Endpoint para el gráfico de Torta (ej. Casos por Comisionado)
const API_PIE_ENDPOINT = '../../../app/controllers/dashboardAdmin.php'; 
// Endpoint para el gráfico Polar (ruta especificada por el usuario)
const API_POLAR_ENDPOINT = '../../../app/controllers/dashboardAdmin.php'; 

// Colores definidos para el uso consistente en los gráficos
const CHART_COLORS = [
    'rgb(79, 70, 229)',    
    'rgb(239, 68, 68)',    
    'rgb(245, 158, 11)',   
    'rgb(16, 185, 129)',   
    'rgb(54, 162, 235)'    
];

/**
 * Función genérica para dibujar gráficos de Chart.js.
 */
const drawChart = (canvasElement, type, labels, data) => {
    // Definición de opciones específicas por tipo de gráfico
    const options = {
        responsive: true,
        maintainAspectRatio: false,
        scales: type === 'polarArea' ? { r: { beginAtZero: true, display: false } } : 
                type === 'bar' ? { y: { beginAtZero: true } } : {}
    };

    new Chart(canvasElement, {
        type: type,
        data: {
            labels: labels,
            datasets: [{
                label: (type === 'pie' || type === 'polarArea' ? 'Cantidad' : 'Casos Registrados'),
                data: data,
                backgroundColor: CHART_COLORS.slice(0, labels.length).map(color => 
                    type === 'polarArea' ? `${color.substring(0, color.length - 1)}, 0.7)` : color 
                ),
                borderColor: 'black', // Color para los bordes
                borderWidth: 1.5
            }]
        },
        options: options
    });
};

/**
 * Función asíncrona genérica para hacer la llamada AJAX (Fetch) y obtener datos para cualquier gráfico.
 */
const loadChartData = async (chartElement, endpoint, chartType) => {
    const container = chartElement.parentElement;
    
    // 1. Mostrar indicador de carga
    container.innerHTML = '<p class="text-gray-500 text-center p-8">Cargando datos desde la API...</p>'; 

    try {
        // 2. Realizar la petición GET
        const response = await fetch(endpoint);
        
        if (!response.ok) {
            // Verifica el código HTTP (404, 500, etc.)
            throw new Error(`Error HTTP: ${response.status}. Verifica el endpoint: ${endpoint}`);
        }
        
        // 3. Procesar el JSON que viene del backend
        const apiResponse = await response.json(); 
        
        // 4. VERIFICAR EL ESTADO (status: 'ok')
        if (apiResponse.status !== 'ok') {
            throw new Error(apiResponse.message || 'La respuesta del servidor no fue exitosa (status: error).');
        }

        // 5. DISTRIBUIR LOS DATOS y DIBUJAR EL GRÁFICO
        // Usa las claves genéricas ('labels', 'data') o las específicas ('labelsPolar', 'conteoPolar')
        const labels = apiResponse.labelsPolar || apiResponse.labels;
        const data = apiResponse.conteoPolar || apiResponse.data;

        if (!labels || !data) {
             throw new Error("El JSON del controlador no contiene las claves de datos esperadas.");
        }
        
        // Limpiar indicador de carga y re-adjuntar el canvas
        container.innerHTML = '';
        container.appendChild(chartElement);

        drawChart(chartElement, chartType, labels, data);

    } catch (error) {
        // 6. Manejar y mostrar el error si algo falla
        console.error("Error al cargar datos del gráfico:", error);
        container.innerHTML = `<p class="text-red-500 text-center p-8 text-sm">Error de API: ${error.message}</p>`;
    }
};

/**
 * Inicializa todos los gráficos.
 */
const initializeCharts = () => {
    const chartBar = document.getElementById("barChart");
    const chartPie = document.getElementById("pieChart");
    const chartPolar = document.getElementById("polarChart");

    // --- Gráfico Polar (DINÁMICO) ---
    if (chartPolar) {
        loadChartData(chartPolar, API_POLAR_ENDPOINT, 'polarArea');
    }

    // --- Gráfico Pie (DINÁMICO) ---
    if (chartPie) {
        loadChartData(chartPie, API_PIE_ENDPOINT, 'pie');
    }
    
    // --- Gráfico de Barras (Estático) ---
    if (chartBar) {
        drawChart(chartBar, 'bar', 
            ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            [12, 19, 3, 5, 2, 4, 12, 28, 12, 32, 12, 4]);
    }
};

// Asegurar que la inicialización ocurra solo después de que el DOM esté listo
document.addEventListener('DOMContentLoaded', initializeCharts);
