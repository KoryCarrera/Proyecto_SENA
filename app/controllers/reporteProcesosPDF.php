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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $model = new baseHelper($pdo);
    $documento = $_SESSION['user']['documento'];
    $formato = 'PDF';
    $descripcion = 'Reporte Procesos';

    $data = [
        ['value' => $documento, 'type' => PDO::PARAM_STR],
        ['value' => $formato, 'type' => PDO::PARAM_STR],
        ['value' => $descripcion, 'type' => PDO::PARAM_STR]
    ];

    $resProcesos = $model->consultObjectHelper('sp_listar_proceso_organizacional');
    $datosInforme = $model->insertOrUpdateData('sp_registrar_informe(?, ?, ?)', $data);

    if ($resProcesos && $datosInforme) {

        $listaCompleta = $resProcesos;
        $totalProcesos = count($listaCompleta);
        $totalActivos = 0;
        $totalInactivos = 0;

        foreach ($listaCompleta as $proceso) {
            if ($proceso['estado'] == 1) {
                $totalActivos++;
            } else {
                $totalInactivos++;
            }
        }

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
                    margin: 1.5cm;
                    size: A4;
                }

                body {
                    font-family: 'Helvetica', sans-serif;
                    font-size: 10pt;
                    color: #333;
                }

                .header {
                    border-bottom: 3px solid #39A900;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }

                .report-title {
                    text-align: center;
                    background-color: #2c3e50;
                    color: white;
                    padding: 10px;
                    margin: 20px 0;
                    font-weight: bold;
                }

                /* Layout para las gráficas */
                .charts-table {
                    width: 100%;
                    margin-bottom: 20px;
                    border-collapse: collapse;
                }

                .chart-cell {
                    width: 50%;
                    text-align: center;
                    padding: 10px;
                    border: 1px solid #eee;
                }

                .chart-img {
                    width: 250px;
                    height: auto;
                }

                .stats-summary {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }

                .stat-card {
                    border: 1px solid #ddd;
                    padding: 15px;
                    text-align: center;
                    width: 33.3%;
                }

                .stat-val {
                    font-size: 18pt;
                    font-weight: bold;
                    color: #2c3e50;
                    display: block;
                }

                .stat-lab {
                    font-size: 8pt;
                    color: #777;
                    text-transform: uppercase;
                }

                .data-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }

                .data-table th {
                    background-color: #2c3e50;
                    color: white;
                    padding: 8px;
                    font-size: 9pt;
                    text-align: left;
                }

                .data-table td {
                    padding: 7px;
                    border: 1px solid #ddd;
                    font-size: 8.5pt;
                }

                .status-active {
                    color: #39A900;
                    font-weight: bold;
                }

                .status-inactive {
                    color: #dc3545;
                    font-weight: bold;
                }
            </style>
        </head>

        <body>
            <div class='header'>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 70px;">
                            <?php if ($logoSrc): ?>
                                <img width='70' src='<?php echo $logoSrc; ?>' alt='SENA'>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <div style="font-size: 16pt; font-weight: bold; color: #39A900;">SENA</div>
                            <div style="color: #666;">Reporte de Gestión de Procesos</div>
                        </td>
                    </tr>
                </table>
            </div>

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

            <div style="background-color: #34495e; color: white; padding: 8px; margin-bottom: 10px; font-weight: bold;">
                1. REPRESENTACIÓN VISUAL
            </div>

            <table class="charts-table">
                <tr>
                    <td class="chart-cell">
                        <p style="font-size: 9pt; margin-bottom: 5px;">Distribución Porcentual</p>
                        <?php if ($chartPieSrc): ?>
                            <img class="chart-img" src="<?php echo $chartPieSrc; ?>" alt="Gráfica circular">
                        <?php else: ?>
                            <p style="color: red;">No se cargó la chimbada de gráfica 1</p>
                        <?php endif; ?>
                    </td>
                    <td class="chart-cell">
                        <p style="font-size: 9pt; margin-bottom: 5px;">Comparativa de Estados</p>
                        <?php if ($chartBarSrc): ?>
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