<?php
// Cargamos la session
session_start();

// Llamar al archivo necesario para dompdf y otros archivos necesarios para obtener datos
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

// referenciar dompdf
use Dompdf\Dompdf;
use Dompdf\Options;



//se especifica que este usando el metodo post

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //se crea una instancia de la clase baseHelper
    $model = new baseHelper($pdo);

    //se obtienen los datos del usuario
    $documento = $_SESSION['user']['documento'];
    //se define el formato y la descripcion
    $formato = 'PDF';
    $descripcion = 'Reporte Procesos';

    //se crea un array con los datos
    $data = [
        ['value' => $documento, 'type' => PDO::PARAM_STR],
        ['value' => $formato, 'type' => PDO::PARAM_STR],
        ['value' => $descripcion, 'type' => PDO::PARAM_STR]
    ];

    //se consulta la lista de procesos
    $resProcesos = $model->consultObjectHelper('sp_listar_proceso_organizacional');
    //se registra el informe
    $datosInforme = $model->insertOrUpdateData('sp_registrar_informe(?, ?, ?)', $data);

    //se verifica que la consulta y el registro se hayan realizado correctamente
    if ($resProcesos && $datosInforme) {

        $listaCompleta = $resProcesos;
        $totalProcesos = count($listaCompleta);
        $totalActivos = 0;
        $totalInactivos = 0;

        //se recorre la lista de procesos
        foreach ($listaCompleta as $proceso) {
            //se cuenta el numero de procesos activos e inactivos
            if ($proceso['estado'] == 1) {
                $totalActivos++;
            } else {
                $totalInactivos++;
            }
        }
        //se calcula el porcentaje de procesos activos e inactivos
        $porcentajeActivos = ($totalProcesos > 0) ? number_format((($totalActivos / $totalProcesos) * 100), 1) : 0;
        $porcentajeInactivos = ($totalProcesos > 0) ? number_format((($totalInactivos / $totalProcesos) * 100), 1) : 0;

        // --- CONFIGURACIÓN DE GRÁFICAS (QuickChart) ---

        // 1. Gráfica de Torta (Distribución)
        $chartPieConfig = [
            'type' => 'pie',
            'data' => [
                'labels' => ['Activos', 'Inactivos'],
                'datasets' => [[
                    'data' => [$totalActivos, $totalInactivos],
                    'backgroundColor' => ['#39A900', '#dc3545']
                ]]
            ],
            'options' => [
                'plugins' => [
                    'datalabels' => ['color' => '#fff', 'font' => ['weight' => 'bold']]
                ]
            ]
        ];

        // 2. Gráfica de Barras (Comparativa)
        $chartBarConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => ['Activos', 'Inactivos'],
                'datasets' => [[
                    'label' => 'Cantidad de Procesos',
                    'data' => [$totalActivos, $totalInactivos],
                    'backgroundColor' => ['#39A900', '#dc3545']
                ]]
            ],
            'options' => [
                'legend' => ['display' => false],
                'title' => ['display' => true, 'text' => 'Estado de Procesos']
            ]
        ];

        // Función para convertir la gráfica a Base64 y evitar líos de carga
        function getChartBase64($config)
        {
            $url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($config)) . '&w=300&h=200';
            $data = @file_get_contents($url);
            return $data ? 'data:image/png;base64,' . base64_encode($data) : '';
        }

        //se convierte la gráfica a base64
        $chartPieSrc = getChartBase64($chartPieConfig);
        $chartBarSrc = getChartBase64($chartBarConfig);

        // Convertimos el logo
        $logoPath = __DIR__ . '/../../Public/assets/img/logo_sena.png';
        $logoSrc = (file_exists($logoPath)) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        ob_start();
?>
        <!DOCTYPE html>
        <html lang='es'>

        <head>
            <meta charset='UTF-8'>
            <style>
                @page {
                    /* se define el margen */
                    margin: 1.5cm;
                    /* se define el tamaño de la página */
                    size: A4;
                }

                body {
                    /* se define la fuente */
                    font-family: 'Helvetica', sans-serif;
                    /* se define el tamaño de la fuente */
                    font-size: 10pt;
                    /* se define el color de la fuente */
                    color: #333;
                }

                .header {
                    /*borde inferior */
                    border-bottom: 3px solid #39A900;
                    /*padding inferior */
                    padding-bottom: 10px;
                    /*margen inferior */
                    margin-bottom: 20px;
                }

                .report-title {
                    /* texto centrado */
                    text-align: center;
                    /* color de fondo */
                    background-color: #2c3e50;
                    /* color de texto */
                    color: white;
                    /* padding */
                    padding: 10px;
                    /* margen */
                    margin: 20px 0;
                    /* negrita */
                    font-weight: bold;
                }

                /* Layout para las gráficas */
                .charts-table {
                    /* ancho */
                    width: 100%;
                    /* margen inferior */
                    margin-bottom: 20px;
                    /* colapsar bordes */
                    border-collapse: collapse;
                }

                .chart-cell {
                    /* ancho */
                    width: 50%;
                    /* texto centrado */
                    text-align: center;
                    /* padding */
                    padding: 10px;
                    /* borde */
                    border: 1px solid #eee;
                }

                .chart-img {
                    /* ancho */
                    width: 250px;
                    /* alto */
                    height: auto;
                }

                .stats-summary {
                    /* ancho */
                    width: 100%;
                    /* colapsar bordes */
                    border-collapse: collapse;
                    /* margen inferior */
                    margin-bottom: 20px;
                }

                .stat-card {
                    /* borde */
                    border: 1px solid #ddd;
                    /* padding */
                    padding: 15px;
                    /* texto centrado */
                    text-align: center;
                    /* ancho */
                    width: 33.3%;
                }

                .stat-val {
                    /* tamaño de fuente */
                    font-size: 18pt;
                    /* negrita */
                    font-weight: bold;
                    /* color */
                    color: #2c3e50;
                    /* display */
                    display: block;
                }

                .stat-lab {
                    /* tamaño de fuente */
                    font-size: 8pt;
                    /* color */
                    color: #777;
                    /* texto en mayusculas */
                    text-transform: uppercase;
                }

                .data-table {
                    /* ancho */
                    width: 100%;
                    /* colapsar bordes */
                    border-collapse: collapse;
                    /* margen superior */
                    margin-top: 20px;
                }

                .data-table th {
                    /* color de fondo */
                    background-color: #2c3e50;
                    /* color de texto */
                    color: white;
                    /* padding */
                    padding: 8px;
                    /* tamaño de fuente */
                    font-size: 9pt;
                    /* texto alineado a la izquierda */
                    text-align: left;
                }

                .data-table td {
                    /* padding */
                    padding: 7px;
                    /* borde */
                    border: 1px solid #ddd;
                    /* tamaño de fuente */
                    font-size: 8.5pt;
                }

                .status-active {
                    /* color */
                    color: #39A900;
                    /* negrita */
                    font-weight: bold;
                }

                .status-inactive {
                    /* color */
                    color: #dc3545;
                    /* negrita */
                    font-weight: bold;
                }
            </style>
        </head>

        <body>
            <div class='header'>
                <table style="width: 100%;">
                    <tr>
                        <!-- se define el ancho -->
                        <td style="width: 70px;">
                            <?php if ($logoSrc): ?>
                                <img width='70' src='<?php echo $logoSrc; ?>' alt='SENA'>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <!-- tamaño de fuente -->
                            <div style="font-size: 16pt; font-weight: bold; color: #39A900;">SENA</div>
                            <!-- color -->
                            <div style="color: #666;">Reporte de Gestión de Procesos</div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- titulo del reporte -->
            <div class='report-title'>ANÁLISIS ESTRATÉGICO DE PROCESOS</div>

            <table class="stats-summary">
                
                <tr>
                    <td class="stat-card">

                        <span class="stat-val"><?php echo $totalProcesos; ?></span>

                        <span class="stat-lab">Total Procesos</span>
                    </td>
                    <td class="stat-card">
                        <span class="stat-val" style="color: #39A900;"><?php echo $totalActivos; ?></span>
                        <span class="stat-lab">Activos (<?php echo $porcentajeActivos; ?>%)</span>
                    </td>
                    <td class="stat-card">
                        <span class="stat-val" style="color: #dc3545;"><?php echo $totalInactivos; ?></span>
                        <span class="stat-lab">Inactivos (<?php echo $porcentajeInactivos; ?>%)</span>
                    </td>
                </tr>
            </table>
                                <!-- titulo -->
            <div style="background-color: #34495e; color: white; padding: 8px; margin-bottom: 10px; font-weight: bold;">
                1. REPRESENTACIÓN VISUAL
            </div>
                                <!-- tabla -->
            <table class="charts-table">
                <tr>
                    <td class="chart-cell">
                        <!-- titulo -->
                        <p style="font-size: 9pt; margin-bottom: 5px;">Distribución Porcentual</p>
                        <?php if ($chartPieSrc): ?>
                            <img class="chart-img" src="<?php echo $chartPieSrc; ?>" alt="Gráfica circular">
                        <?php else: ?>
                            <p style="color: red;">No se cargó la chimbada de gráfica 1</p>
                        <?php endif; ?>
                    </td>
                    <td class="chart-cell">
                        <!-- titulo -->
                        <p style="font-size: 9pt; margin-bottom: 5px;">Comparativa de Estados</p>
                        <!-- imagen -->
                        <?php if ($chartBarSrc): ?>
                            <!-- imagen -->
                            <img class="chart-img" src="<?php echo $chartBarSrc; ?>" alt="Gráfica de barras">
                        <?php else: ?>
                            <p style="color: red;">No se cargó la chimbada de gráfica 2</p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <div style="background-color: #34495e; color: white; padding: 8px; margin-bottom: 10px; font-weight: bold;">
                2. LISTADO DETALLADO
            </div>

            <table class='data-table'>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Proceso</th>
                        <th>Fecha Creación</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listaCompleta as $p): ?>
                        <tr>
                            <td><?php echo $p['id_proceso']; ?></td>
                            <td><strong><?php echo htmlspecialchars($p['nombre_proceso']); ?></strong></td>
                            <td><?php echo date('d/m/Y', strtotime($p['fecha_creacion'])); ?></td>
                            <td class="<?php echo ($p['estado'] == 1) ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo ($p['estado'] == 1) ? 'ACTIVO' : 'INACTIVO'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </body>

        </html>
<?php
        $html = ob_get_clean();
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        if (ob_get_length()) ob_end_clean();
        $dompdf->stream("Reporte_Procesos_SENA.pdf", ["Attachment" => true]);
        exit;
    }
}
?>