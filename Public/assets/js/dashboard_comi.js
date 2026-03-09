const ENDPOINT = '/graficasComi';
const ENDPOINT_SEMANA = '/graficasComi/Semana';
const ENDPOINT_MES = '/graficasComi/Mes';

Chart.defaults.color = '#ffffff';

const tituloLinea = document.getElementById('tituloLineas');
const tituloPolar = document.getElementById('tituloPolar');
const tituloBar = document.getElementById('titulobar');
const contextoLinea = document.getElementById('contextoLinea');
const contextoPolar = document.getElementById('contextoPolar');
const contextoBar = document.getElementById('contextoBar');
const selectGraficas = document.getElementById('selectGraficas');


const fecha = new Date();
const anioActual = fecha.getFullYear();
const MONTH_NAMES_ES = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];
const mesActual = MONTH_NAMES_ES[fecha.getMonth()];

const WEEK_NAME_ES = [
    'Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5'
];


const CHART_COLORS = {
    indigo: { bg: 'rgba(99, 102, 241, 0.4)', border: '#6366f1' },
    purple: { bg: 'rgba(168, 85, 247, 0.4)', border: '#a855f7' },
    pink: { bg: 'rgba(236, 72, 153, 0.4)', border: '#ec4899' },
    blue: { bg: 'rgba(56, 189, 248, 0.4)', border: '#38bdf8' },
    emerald: { bg: 'rgba(16, 185, 129, 0.4)', border: '#10b981' },
    orange: { bg: 'rgba(249, 115, 22, 0.4)', border: '#f97316' },
    teal: { bg: 'rgba(20, 184, 166, 0.4)', border: '#14b8a6' },
    rose: { bg: 'rgba(244, 63, 94, 0.4)', border: '#f43f5e' },
    amber: { bg: 'rgba(245, 158, 11, 0.4)', border: '#f59e0b' },
    cyan: { bg: 'rgba(6, 182, 212, 0.4)', border: '#06b6d4' },
    violet: { bg: 'rgba(139, 92, 246, 0.4)', border: '#8b5cf6' },
    lime: { bg: 'rgba(132, 204, 22, 0.4)', border: '#84cc16' },
    red: { bg: 'rgba(239, 68, 68, 0.4)', border: '#ef4444' },
    fuchsia: { bg: 'rgba(217, 70, 239, 0.4)', border: '#d946ef' },
    sky: { bg: 'rgba(14, 165, 233, 0.4)', border: '#0ea5e9' },
    yellow: { bg: 'rgba(234, 179, 8, 0.4)', border: '#eab308' },
    green: { bg: 'rgba(34, 197, 94, 0.4)', border: '#22c55e' },
    slate: { bg: 'rgba(100, 116, 139, 0.4)', border: '#64748b' },
    stone: { bg: 'rgba(120, 113, 108, 0.4)', border: '#78716c' },
    emerald_dark: { bg: 'rgba(4, 120, 87, 0.4)', border: '#047857' },
    indigo_light: { bg: 'rgba(129, 140, 248, 0.4)', border: '#818cf8' },
    rose_light: { bg: 'rgba(251, 113, 133, 0.4)', border: '#fb7185' },
    cyan_dark: { bg: 'rgba(14, 116, 144, 0.4)', border: '#0e7490' },
    amber_dark: { bg: 'rgba(180, 83, 9, 0.4)', border: '#b45309' },
    mint: { bg: 'rgba(52, 211, 153, 0.4)', border: '#34d399' },
    peach: { bg: 'rgba(251, 146, 60, 0.4)', border: '#fb923c' },
    lavender: { bg: 'rgba(192, 132, 252, 0.4)', border: '#c084fc' }
};

const COLORS_ARRAY = Object.values(CHART_COLORS);
const dynamicColorMap = {};
let colorIndex = 0;

const COLOR_MAP = {
    'Por atender': CHART_COLORS.amber,
    'Atendido': CHART_COLORS.emerald,
    'No atendido': CHART_COLORS.red,
    'Denuncia': CHART_COLORS.pink,
    'Solicitud': CHART_COLORS.blue,
    'Derecho de Petición': CHART_COLORS.indigo,
    'Acción de Tutela': CHART_COLORS.purple,
    'Ropa de Trabajo': CHART_COLORS.cyan,
    'SST': CHART_COLORS.teal,
    'SSEMI': CHART_COLORS.sky,
    'Plan de incentivos': CHART_COLORS.violet,
    'Bienestar Social': CHART_COLORS.indigo
};

const chartContainers = {
    bar: null,
    pie: null,
    polar: null
};

//Función genérica para dibujar gráficos de Chart.js.
const drawChart = (canvasElement, type, labels, data) => {
    if (canvasElement.chart) {
        canvasElement.chart.destroy();
    }

    let backgroundColors;
    let borderColors;
    let pointBgColors;
    let pointBorderColors;

    const mappedColors = labels.map((label) => {
        if (COLOR_MAP[label]) return COLOR_MAP[label];

        if (!dynamicColorMap[label]) {
            dynamicColorMap[label] = COLORS_ARRAY[colorIndex % COLORS_ARRAY.length];
            colorIndex++;
        }
        return dynamicColorMap[label];
    });

    if (type === 'line') {
        backgroundColors = CHART_COLORS.blue.bg;
        borderColors = CHART_COLORS.blue.border;
        pointBgColors = mappedColors.map(c => c.bg);
        pointBorderColors = mappedColors.map(c => c.border);
    } else {
        backgroundColors = mappedColors.map(c => c.bg);
        borderColors = mappedColors.map(c => c.border);
    }

    const dataset = {
        label: (type === 'pie' || type === 'polarArea' ? 'Cantidad' : 'Casos Registrados'),
        data: data,
        backgroundColor: backgroundColors,
        borderColor: borderColors,
        borderWidth: 1.5
    };

    if (type === 'line') {
        dataset.pointStyle = 'circle';
        dataset.pointRadius = 5;
        dataset.pointHoverRadius = 8;
        dataset.pointBackgroundColor = pointBgColors;
        dataset.pointBorderColor = pointBorderColors;
        dataset.pointBorderWidth = 2;
        dataset.tension = 0.3;
        dataset.fill = true;
    } else if (type === 'bar') {
        dataset.borderRadius = 6;
    }

    const options = {
        responsive: true,
        plugins: {
            legend: {
                display: type !== 'bar' && type !== 'line',
                position: 'bottom',
                labels: {
                    color: '#ffffff', font: {
                        size: 18,
                        weight: 'bold'
                    }
                }
            },
        },
        maintainAspectRatio: false,
        scales: type === 'polarArea' ? { r: { beginAtZero: true, display: false } } :
            (type === 'bar' || type === 'line') ? {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#ffffff',
                        font: {
                            size: 14,
                            weight: 'bold',
                        },
                        stepSize: 1,
                        precision: 0,
                        callback: function (value) {
                            if (Number.isInteger(value)) return value;
                        }
                    }
                },
                x: { ticks: { color: '#ffffff' } }
            } : {}
    };

    canvasElement.chart = new Chart(canvasElement, {
        type: type,
        data: { labels: labels, datasets: [dataset] },
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

//Función principal PARAMETRIZADA (Realiza UNA SOLA petición a la API)

const loadAllChartData = async (urlFetch) => {
    const charts = [
        { canvasId: 'barChart', container: chartContainers.bar, id: 'bar', type: 'line', name: 'Casos por Procesos' },
        { canvasId: 'pieChart', container: chartContainers.pie, id: 'pie', type: 'bar', name: 'Casos por Estado' },
        { canvasId: 'polarChart', container: chartContainers.polar, id: 'polar', type: 'polarArea', name: 'Casos por Tipo' }
    ].filter(c => c.container);

    charts.forEach(c => {
        c.container.innerHTML = `<p class="text-center p-4 text-muted">Cargando datos de ${c.name}...</p>`;
    });

    try {
        const response = await fetch(urlFetch);
        if (!response.ok) throw new Error(`Error HTTP ${response.status}`);

        const apiResponse = await response.json();
        if (apiResponse.status !== 'ok' && apiResponse.status !== 'partial_error') {
            throw new Error(apiResponse.mensaje || 'Error del servidor');
        }

        charts.forEach(c => {
            renderChartFromResponse(c.canvasId, c.container, apiResponse, c.id, c.type, c.name);
        });

    } catch (error) {
        charts.forEach(c => {
            if (c.container) {
                c.container.innerHTML = `<p class="text-center p-4 text-danger"><strong>Error:</strong> ${error.message}</p>`;
            }
        });
    }
};

/**
 * Diccionario dinámico para actualizar los textos de la interfaz
 */
const actualizarTextos = (periodo) => {
    const textos = {
        semana: {
            linea: { t: `Estadística de la semana actual en casos por proceso`, c: `Casos asignados a cada proceso registrados en la semana actual.` },
            polar: { t: `Estadística de la semana en casos por tipo`, c: `Distribución de casos por tipo en la semana actual.` },
            bar: { t: `Estadística de la semana en casos por estado`, c: `Casos clasificados por estado (atendido, por atender, no atendido).` }
        },
        mes: {
            linea: { t: `Estadística de ${mesActual} de ${anioActual} - Casos por proceso`, c: `Casos asignados a cada proceso durante ${mesActual} de ${anioActual}.` },
            polar: { t: `Estadística de ${mesActual} de ${anioActual} - Casos por tipo`, c: `Distribución de casos por tipo en ${mesActual} de ${anioActual}.` },
            bar: { t: `Estadística de ${mesActual} de ${anioActual} - Casos por estado`, c: `Casos por estado en ${mesActual} de ${anioActual}.` }
        },
        anual: {
            linea: { t: 'Estadística anual de casos por proceso organizacional', c: `Cantidad de casos asignados a cada proceso durante el año ${anioActual}.` },
            polar: { t: 'Estadística anual de casos por tipo', c: `Distribución de casos por tipo registrados en el año ${anioActual}.` },
            bar: { t: 'Estadística anual de casos por estado', c: `Casos clasificados por estado en el año ${anioActual}.` }
        }
    };

    const config = textos[periodo];
    if (config) {
        tituloLinea.innerText = config.linea.t;
        contextoLinea.innerText = config.linea.c;
        tituloPolar.innerText = config.polar.t;
        contextoPolar.innerText = config.polar.c;
        tituloBar.innerText = config.bar.t;
        contextoBar.innerText = config.bar.c;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    
    chartContainers.bar = document.getElementById('barChart')?.parentElement
    chartContainers.pie = document.getElementById('pieChart')?.parentElement
    chartContainers.polar = document.getElementById('polarChart')?.parentElement;

    actualizarTextos('anual');
    loadAllChartData(ENDPOINT);
})
selectGraficas.addEventListener('change', function () {
    const valorSeleccionado = this.value;

    actualizarTextos(valorSeleccionado);

    let endpointFetch = ENDPOINT;
    if (valorSeleccionado === 'semana') endpointFetch = ENDPOINT_SEMANA;
    if (valorSeleccionado === 'mes') endpointFetch = ENDPOINT_MES;

    loadAllChartData(endpointFetch);
});

document.addEventListener('DOMContentLoaded', () => {
    actualizarTextos('anual');
    loadAllChartData(ENDPOINT);
});