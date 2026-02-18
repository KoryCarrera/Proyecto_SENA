const ENDPOINT = '/graficasAdmin';

Chart.defaults.color = '#ffffff';

// Paleta base
const CHART_COLORS = [
    'rgb(56, 189, 248)',   // Sky Blue
    'rgb(45, 212, 191)',   // Teal/Turquesa
    'rgb(147, 197, 253)',  // Azul Pastel
    'rgb(79, 70, 229)',    // Índigo
    'rgb(192, 132, 252)'   // Lavanda suave
];

// Mapa de colores para etiquetas conocidas (casos de uso)
const COLOR_MAP = {
    // Tipos de Caso (Polar Area)
    'Denuncia': CHART_COLORS[4],
    'Solicitud': CHART_COLORS[2],
    'Derecho de Petición': CHART_COLORS[0],
    'Queja': CHART_COLORS[3],
    'Reclamo': CHART_COLORS[1],

    // Comisionados (Pie Chart) – puedes agregar nombres específicos si lo deseas
    // Si no están, se asignará un color por índice automáticamente
};

const MONTH_NAMES_ES = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

/**
 * Dibuja un gráfico con Chart.js aplicando la lógica de colores mejorada.
 */
const drawChart = (canvasElement, type, labels, data) => {
    if (canvasElement.chart) {
        canvasElement.chart.destroy();
    }

    let backgroundColors;
    let borderColors;

    if (type === 'line') {
        // Línea: un solo color (Sky Blue)
        backgroundColors = CHART_COLORS[0];
        borderColors = CHART_COLORS[0];
    } else {
        // Para otros gráficos: asignar color según etiqueta (mapeado o cíclico)
        const baseColors = labels.map((label, index) =>
            COLOR_MAP[label] || CHART_COLORS[index % CHART_COLORS.length]
        );

        // PolarArea: añadir transparencia
        backgroundColors = type === 'polarArea'
            ? baseColors.map(color => color.replace('rgb', 'rgba').replace(')', ', 0.7)'))
            : baseColors;

        borderColors = 'black'; // Borde negro para segmentos
    }

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                // Mostrar leyenda solo en gráficos circulares/polares (no en barras/líneas)
                display: type !== 'bar' && type !== 'line',
                position: 'bottom',
                labels: { color: '#ffffff' }
            }
        },
        scales:
            type === 'polarArea'
                ? { r: { beginAtZero: true, display: false } }
                : type === 'bar' || type === 'line'
                ? {
                      y: {
                          beginAtZero: true,
                          ticks: {
                              color: '#ffffff',
                              stepSize: 1,       // Saltos de 1 en 1
                              precision: 0,      // Sin decimales
                              callback: function (value) {
                                  if (Number.isInteger(value)) return value;
                              }
                          }
                      },
                      x: { ticks: { color: '#ffffff' } }
                  }
                : {}
    };

    canvasElement.chart = new Chart(canvasElement, {
        type: type,
        data: {
            labels: labels,
            datasets: [{
                label: type === 'doughnut' || type === 'polarArea' ? 'Cantidad' : 'Casos Registrados',
                data: data,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1.5,
                // Propiedades específicas de línea
                pointBackgroundColor: type === 'line' ? '#ffffff' : undefined,
                pointBorderColor: type === 'line' ? borderColors : undefined,
                tension: 0.3,
                fill: type === 'line' ? false : true
            }]
        },
        options: options
    });
};

/**
 * Renderiza un gráfico a partir de la respuesta de la API.
 */
const renderChartFromResponse = (canvasId, container, apiResponse, chartId, chartType, chartName) => {
    const chartIdCapitalized = chartId.charAt(0).toUpperCase() + chartId.slice(1);
    let labels = apiResponse[`labels${chartIdCapitalized}`];
    const data = apiResponse[`data${chartIdCapitalized}`];

    // Convertir etiquetas numéricas de meses a nombres (solo para barras)
    if (chartId === 'bar' && labels && Array.isArray(labels) && labels.length > 0 && typeof labels[0] === 'number') {
        labels = labels.map(monthNum => MONTH_NAMES_ES[monthNum - 1] || `Mes ${monthNum}`);
    }

    const errorMessage = apiResponse.errors?.[chartId];

    if (errorMessage || !labels || !data || labels.length === 0 || data.length === 0) {
        const errorText = errorMessage
            ? `⚠️ Error en ${chartName}: ${errorMessage}`
            : `✅ No hay datos de ${chartName} registrados para mostrar.`;
        container.innerHTML = `<p class="text-center p-4 text-warning">${errorText}</p>`;
        return;
    }

    container.innerHTML = '';
    const newCanvas = document.createElement('canvas');
    newCanvas.id = canvasId;
    container.appendChild(newCanvas);
    drawChart(newCanvas, chartType, labels, data);
};

/**
 * Función principal: una sola petición a la API y renderizado de todos los gráficos.
 */
const loadAllChartData = async () => {
    const charts = [
        {
            canvasId: 'barChart',
            container: document.getElementById('barChart')?.parentElement,
            id: 'bar',
            type: 'line',       // Los datos de 'bar' (meses) se muestran como línea
            name: 'Casos por Mes'
        },
        {
            canvasId: 'pieChart',
            container: document.getElementById('pieChart')?.parentElement,
            id: 'pie',
            type: 'bar',         // Los datos de 'pie' (comisionados) se muestran como barras
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

    // Mostrar cargadores
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

        charts.forEach(c => {
            console.log(`Renderizando gráfico: ${c.name}`);
            renderChartFromResponse(c.canvasId, c.container, apiResponse, c.id, c.type, c.name);
        });

    } catch (error) {
        console.error("Error crítico:", error);
        charts.forEach(c => {
            if (c.container) {
                c.container.innerHTML = `<p class="text-center p-4 text-danger">
                    <strong>Error:</strong> ${error.message}
                </p>`;
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', loadAllChartData);