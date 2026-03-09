
const ENDPOINT = '/graficasAdmin';
const ENDPOINT_SEMANA = '/graficasAdmin/Semana';
const ENDPOINT_MES = '/graficasAdmin/Mes';

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

const USER_COLORS_ARRAY = [
    CHART_COLORS.yellow,
    CHART_COLORS.mint,
    CHART_COLORS.lavender,
    CHART_COLORS.peach,
    CHART_COLORS.slate,
    CHART_COLORS.rose_light,
    CHART_COLORS.cyan_dark,
    CHART_COLORS.green,
    CHART_COLORS.stone
];


const COLOR_MAP = {
    // Tipos de casos
    'Denuncia': CHART_COLORS.pink,
    'Solicitud': CHART_COLORS.blue,
    'Derecho de Petición': CHART_COLORS.indigo,
    'Acción de Tutela': CHART_COLORS.purple,

    // Meses
    'Enero': CHART_COLORS.cyan,
    'Febrero': CHART_COLORS.rose,
    'Marzo': CHART_COLORS.amber_dark,
    'Abril': CHART_COLORS.lime,
    'Mayo': CHART_COLORS.fuchsia,
    'Junio': CHART_COLORS.sky,
    'Julio': CHART_COLORS.orange,
    'Agosto': CHART_COLORS.teal,
    'Septiembre': CHART_COLORS.violet,
    'Octubre': CHART_COLORS.red,
    'Noviembre': CHART_COLORS.emerald_dark,
    'Diciembre': CHART_COLORS.indigo_light
};

const chartContainers = {
    bar: null,
    line: null,
    polar: null
};

const dynamicColorMap = {};
let colorIndex = 0;

const drawChart = (canvasElement, type, labels, data) => {
    if (canvasElement.chart) {
        canvasElement.chart.destroy();
    }

    let backgroundColors;
    let borderColors;
    let pointBgColors;
    let pointBorderColors;

    const mappedColors = labels.map(label => {
        if (COLOR_MAP[label]) return COLOR_MAP[label];
        if (!dynamicColorMap[label]) {
            dynamicColorMap[label] = USER_COLORS_ARRAY[colorIndex % USER_COLORS_ARRAY.length];
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
        dataset.pointBorderWidth = 2;
        dataset.pointBackgroundColor = pointBgColors;
        dataset.pointBorderColor = pointBorderColors;
        dataset.tension = 0.3;
        dataset.fill = true;
    } else if (type === 'bar') {
        dataset.borderRadius = 6;
    }

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: type !== 'bar' && type !== 'line',
                position: 'bottom',
                labels: { color: '#ffffff', font: { size: 18, weight: 'bold' } }
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
                                font: {
                                    size: 14,
                                    weight: 'bold',
                                },
                                stepSize: 1,
                                precision: 0,
                                callback: value => Number.isInteger(value) ? value : null
                            }
                        },
                        x: {
                            ticks: {
                                color: '#ffffff',
                                font: {
                                    size: 14,
                                    weight: 'bold',
                                },
                            }
                        }
                    }
                    : {}
    };

    canvasElement.chart = new Chart(canvasElement, {
        type: type,
        data: { labels: labels, datasets: [dataset] },
        options: options
    });
};


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


const loadAllChartData = async (urlFetch) => {
    const charts = [
        {
            canvasId: 'barChart',
            container: chartContainers.line,
            id: 'bar',
            type: 'line',
            name: 'Casos por Mes'
        },
        {
            canvasId: 'pieChart',
            container: chartContainers.bar,
            id: 'pie',
            type: 'bar',
            name: 'Casos por Comisionado'
        },
        {
            canvasId: 'polarChart',
            container: chartContainers.polar,
            id: 'polar',
            type: 'polarArea',
            name: 'Casos por Tipo'
        }
    ].filter(c => c.container);

    // Mostrar cargadores
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

const actualizarTextos = (periodo) => {
    const textos = {
        semana: {
            linea: {
                t: 'Estadística semanal de casos ingresados',
                c: `Muestra la cantidad de casos ingresados en el sistema durante la semana actual del año ${anioActual} en una gráfica de líneas.`
            },
            polar: {
                t: 'Estadística semanal de casos por tipo',
                c: `Presenta la distribución de casos por tipo durante la semana actual del año ${anioActual} en una gráfica polar.`
            },
            bar: {
                t: 'Estadística semanal de casos asignados por comisionado',
                c: `Muestra la cantidad de casos asignados por cada comisionado activo durante la semana actual del año ${anioActual} en una gráfica de barras.`
            }
        },
        mes: {
            linea: {
                t: `Estadística de ${mesActual} de ${anioActual} - Casos ingresados`,
                c: `Muestra la cantidad de casos ingresados en el sistema durante ${mesActual} de ${anioActual} en una gráfica de líneas.`
            },
            polar: {
                t: `Estadística de ${mesActual} de ${anioActual} - Casos por tipo`,
                c: `Presenta la distribución de casos por tipo durante ${mesActual} de ${anioActual} en una gráfica polar.`
            },
            bar: {
                t: `Estadística de ${mesActual} de ${anioActual} - Casos por comisionado`,
                c: `Muestra la cantidad de casos asignados por cada comisionado activo durante ${mesActual} de ${anioActual} en una gráfica de barras.`
            }
        },
        anual: {
            linea: {
                t: 'Estadística anual de casos ingresados',
                c: `Muestra la cantidad de casos ingresados en el sistema en una gráfica de líneas correspondiente al año ${anioActual}.`
            },
            polar: {
                t: 'Estadística anual de casos por tipo',
                c: `Presenta la distribución de casos por tipo en una gráfica polar correspondiente al año ${anioActual}.`
            },
            bar: {
                t: 'Estadística anual de casos asignados por comisionado',
                c: `Muestra la cantidad de casos asignados por cada comisionado activo en una gráfica de barras correspondiente al año ${anioActual}.`
            }
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
    chartContainers.bar = document.getElementById('pieChart')?.parentElement
    chartContainers.line = document.getElementById('barChart')?.parentElement,
    chartContainers.polar = document.getElementById('polarChart')?.parentElement;

    actualizarTextos('anual');
    loadAllChartData(ENDPOINT);

})

selectGraficas.addEventListener('change', function () {
    const periodo = this.value;

    actualizarTextos(periodo);

    let endpoint = ENDPOINT;
    if (periodo === 'semana') endpoint = ENDPOINT_SEMANA;
    if (periodo === 'mes') endpoint = ENDPOINT_MES;

    loadAllChartData(endpoint);
});


document.addEventListener('DOMContentLoaded', () => {
    actualizarTextos('anual');
    loadAllChartData(ENDPOINT);
});