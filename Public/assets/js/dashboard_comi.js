const ENDPOINT = '/graficasComi';
const ENDPOINT_SEMANA = '/graficasComi/Semana';
const ENDPOINT_MES = '/graficasComi/Mes';

Chart.defaults.color = '#ffffff';

// Paleta base ampliada (Estilo Landing Page / Glassmorphism)
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

    // Nuevos colores añadidos
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

// Array de colores para las iteraciones automáticas
const COLORS_ARRAY = Object.values(CHART_COLORS);

// Mapa dinámico para asignar colores únicos y consistentes
const dynamicColorMap = {};
let colorIndex = 0;

// Mapa de colores para etiquetas específicas conocidas
const COLOR_MAP = {
    // Estados 
    'Por atender': CHART_COLORS.amber,
    'Atendido': CHART_COLORS.emerald,
    'No atendido': CHART_COLORS.red,

    // Tipos de Caso (Polar Area)
    'Denuncia': CHART_COLORS.pink,
    'Solicitud': CHART_COLORS.blue,
    'Derecho de Petición': CHART_COLORS.indigo,
    'Acción de Tutela': CHART_COLORS.purple,
    
    // Procesos (Bar Chart)
    'Ropa de Trabajo': CHART_COLORS.cyan,
    'SST': CHART_COLORS.teal,
    'SSEMI': CHART_COLORS.sky,
    'Plan de incentivos': CHART_COLORS.violet,
    'Bienestar Social': CHART_COLORS.indigo
};

const MONTH_NAMES_ES = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

const WEEK_NAME_ES = [
    'Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5'
];

/**
 * Función genérica para dibujar gráficos de Chart.js.
 */
const drawChart = (canvasElement, type, labels, data) => {
    if (canvasElement.chart) {
        canvasElement.chart.destroy();
    }

    let backgroundColors;
    let borderColors;
    let pointBgColors;
    let pointBorderColors;

    // Asignar color único a cada etiqueta mapeada o auto-generada
    const mappedColors = labels.map((label, index) => {
        if (COLOR_MAP[label]) return COLOR_MAP[label];

        // Si no está mapeado explícitamente, asignarle un color de la lista genérica
        if (!dynamicColorMap[label]) {
            dynamicColorMap[label] = COLORS_ARRAY[colorIndex % COLORS_ARRAY.length];
            colorIndex++;
        }
        return dynamicColorMap[label];
    });

    if (type === 'line') {
        backgroundColors = CHART_COLORS.blue.bg; // Fondo general
        borderColors = CHART_COLORS.blue.border; // La linea en si
        // Colores específicos paralos puntos de la gráfica
        pointBgColors = mappedColors.map(c => c.bg);
        pointBorderColors = mappedColors.map(c => c.border);
    } else {
        // Barras y Polares
        backgroundColors = mappedColors.map(c => c.bg);
        borderColors = mappedColors.map(c => c.border);
    }

    // Construcción del dataset genereal
    const dataset = {
        label: (type === 'pie' || type === 'polarArea' ? 'Cantidad' : 'Casos Registrados'),
        data: data,
        backgroundColor: backgroundColors,
        borderColor: borderColors,
        borderWidth: 1.5
    };

    // Configuración específica para línea
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
            if (c.container) {
                c.container.innerHTML = `<p class="text-center p-4 text-danger">
                    <strong>Error:</strong> ${error.message}
                </p>`;
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', loadAllChartData);

//capturamos el select de seleccion de estadisticas

const selectGraficas = document.getElementById('selectGraficas');

selectGraficas.addEventListener('change', function () {
    const valorSeleccionado = this.value;

    if (valorSeleccionado === 'semana') {
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

                const response = await fetch(ENDPOINT_SEMANA);

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
                    if (c.container) {
                        c.container.innerHTML = `<p class="text-center p-4 text-danger">
                    <strong>Error:</strong> ${error.message}
                </p>`;
                    }
                });
            }
        };

        loadAllChartData();
    };

    if (valorSeleccionado === 'mes') {
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

                const response = await fetch(ENDPOINT_MES);

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
                    if (c.container) {
                        c.container.innerHTML = `<p class="text-center p-4 text-danger">
                    <strong>Error:</strong> ${error.message}
                </p>`;
                    }
                });
            }
        };

        loadAllChartData();
    };

    if (valorSeleccionado === 'anual') {
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

                const response = await fetch(ENDPOINT);

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
                    if (c.container) {
                        c.container.innerHTML = `<p class="text-center p-4 text-danger">
                    <strong>Error:</strong> ${error.message}
                </p>`;
                    }
                });
            }
        };

        loadAllChartData();
    };
});