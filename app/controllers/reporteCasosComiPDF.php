<?php
//Debug
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

//Cargamos la session
session_start();

//Llamar al archivo necesario para dompdf y otros archivos necesarios para obtener datos
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/baseHelper.php";

//referenciar dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

//Recibimos los datos del front
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // se crea una instancia del modelo de casos
    $model = new baseHelper($pdo);
    // se obtiene el documento del usuario
    $documento = $_SESSION['user']['documento'];
    // se define el formato
    $formato = 'PDF';
    // se define la descripcion
    $descripcion = 'Reporte Mis Casos PQRSD';

    // se crea un array con los datos del informe
    $data = [
        ['value' => $documento, 'type' => PDO::PARAM_STR],
        ['value' => $formato, 'type' => PDO::PARAM_STR],
        ['value' => $descripcion, 'type' => PDO::PARAM_STR]
    ];

    // Array con el parámetro del documento para inyectar en los SP
    $paramComisionado = [
        ['value' => $documento, 'type' => PDO::PARAM_STR]
    ];

    // 1. Llamamos al SP para las estadísticas de estado
    $estadosRaw = $model->consultObjectWithParams('sp_casos_por_comisionado_doc(?)', $paramComisionado);
    
    // 2. Llamamos al SP para el listado de casos
    $casosListados = $model->consultObjectWithParams('sp_listar_casos_comisionado_doc(?)', $paramComisionado);

    // 3. Llamamos al SP para las estadísticas por PROCESO ORGANIZACIONAL (El que acabamos de ajustar en SQL)
    $estadisticasProcesos = $model->consultObjectWithParams('sp_estadisticas_procesos_comisionado(?)', $paramComisionado);

    // se registra el informe
    $datosInforme = $model->insertOrUpdateData('sp_registrar_informe(?, ?, ?)', $data);

    //Validamos el retorno de datos
    if ($estadosRaw !== false && is_array($casosListados) && $datosInforme) {

        $est = [];
        $casos = [];
        // Protegemos el gran total por si el comisionado aún no tiene casos asignados
        $granTotal = isset($estadosRaw[0]['gran_total']) ? $estadosRaw[0]['gran_total'] : 0;

        // se recorre el array de estados
        foreach ($estadosRaw as $temp) {
            $est[] = $temp['nombre_estado'];
            $casos[] = (int) $temp['total_casos'];
        }

        // se crea un array con los estados
        $estados = [
            'estado' => $est,
            'casos' => $casos,
            'total' => $granTotal
        ];

        //Se buscará el indice donde estan los estados para luego usar ese indice para mostrar el total
        $indiceAtendidos = array_search('Atendido', $estados['estado']);
        $indicePorAtender = array_search('Por atender', $estados['estado']);
        $indiceNoAtendido = array_search('No atendido', $estados['estado']);

        //Se limita unicamente a los ultimos 10 casos
        $casosListados = array_slice($casosListados, 0, 10);

        // se obtiene el total de casos
        $totalAtendidos = ($indiceAtendidos !== false) ? $estados['casos'][$indiceAtendidos] : 0;
        $totalPorAtender = ($indicePorAtender !== false) ? $estados['casos'][$indicePorAtender] : 0;
        $totalNoAtendido = ($indiceNoAtendido !== false) ? $estados['casos'][$indiceNoAtendido] : 0;

        //Conseguimos el porcentaje
        $porcentajeAtendidos = ($totalAtendidos > 0 && $estados['total'] > 0) ? number_format((($totalAtendidos / $estados['total']) * 100), 1) : 0;
        $porcentajePorAtender = ($totalPorAtender > 0 && $estados['total'] > 0) ? number_format((($totalPorAtender / $estados['total']) * 100), 1) : 0;
        $porcentajeNoAtendidos = ($totalNoAtendido > 0 && $estados['total'] > 0) ? number_format((($totalNoAtendido / $estados['total']) * 100), 1) : 0;

        // se obtiene la ruta del logo
        $logoPath = __DIR__ . '/../../Public/assets/img/logo_sena.png';
        $logoSrc = (file_exists($logoPath)) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        // --- GRÁFICA DE ESTADOS (DOUGHNUT) ---
        $chartCasosConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => ['Atendidos', 'Por Atender', 'No Atendidos'],
                'datasets' => [[
                    'data' => [$totalAtendidos, $totalPorAtender, $totalNoAtendido],
                    'backgroundColor' => ['#27ae60', '#f39c12', '#e74c3c'] 
                ]]
            ],
            'options' => [
                'plugins' => ['datalabels' => ['color' => '#fff', 'font' => ['weight' => 'bold']]]
            ]
        ];
        $chartCasosUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartCasosConfig)) . "&w=400&h=250";
        $chartCasosData = @file_get_contents($chartCasosUrl);
        $chartCasosSrc = $chartCasosData ? 'data:image/png;base64,' . base64_encode($chartCasosData) : '';
        
        // --- GRÁFICA DE PROCESOS (BAR) ---
        $labelsProcesos = [];
        $datosProcesos = [];
        if (is_array($estadisticasProcesos)) {
            foreach ($estadisticasProcesos as $ep) {
                // Acortamos el texto si es muy largo para que no dañe la gráfica (máximo 17 caracteres)
                $nombreProc = strlen($ep['proceso']) > 20 ? substr($ep['proceso'], 0, 17) . '...' : $ep['proceso'];
                $labelsProcesos[] = $nombreProc;
                $datosProcesos[] = (int)$ep['total_casos'];
            }
        }

        $chartProcesosSrc = '';
        if (!empty($labelsProcesos)) {
            $chartProcesosConfig = [
                'type' => 'bar',
                'data' => [
                    'labels' => $labelsProcesos,
                    'datasets' => [[
                        'label' => 'Casos Asignados',
                        'data' => $datosProcesos,
                        'backgroundColor' => '#2980b9' 
                    ]]
                ],
                'options' => [
                    'plugins' => ['datalabels' => ['color' => '#fff', 'font' => ['weight' => 'bold'], 'align' => 'bottom']],
                    'scales' => ['yAxes' => [['ticks' => ['beginAtZero' => true, 'stepSize' => 1]]]] // stepSize 1 para que cuente de 1 en 1
                ]
            ];
            $chartProcesosUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartProcesosConfig)) . "&w=500&h=250";
            $chartProcesosData = @file_get_contents($chartProcesosUrl);
            $chartProcesosSrc = $chartProcesosData ? 'data:image/png;base64,' . base64_encode($chartProcesosData) : '';
        }

        // se inicia el buffer
        ob_start();
?>
        <!DOCTYPE html>
        <html lang='es'>

        <head>
            <meta charset='UTF-8'>
            <title>Reporte Mis Casos PQRSD</title>
            <style>
                @page { margin: 1.5cm 1cm; size: A4; }
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #333; line-height: 1.5; padding: 1cm; }
                .header { width: 100%; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; margin-bottom: 25px; }
                .header-table { width: 100%; border-collapse: collapse; }
                .logo-cell { width: 20%; vertical-align: top; }
                .company-info-cell { width: 80%; text-align: right; vertical-align: top; }
                .company-name { font-size: 16pt; font-weight: bold; color: #2c3e50; margin-bottom: 8px; }
                .company-details { font-size: 8.5pt; color: #666; line-height: 1.6; }
                .report-title { text-align: center; background-color: #2c3e50; color: white; padding: 12px 10px; margin: 15px 0; font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
                .report-info-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; border: 1px solid #ddd; }
                .report-info-table td { padding: 8px 12px; border-bottom: 1px solid #eee; }
                .report-info-table .info-label { width: 30%; background-color: #f8f9fa; font-weight: bold; color: #2c3e50; border-right: 1px solid #ddd; }
                .report-info-table .info-value { width: 70%; }
                .stats-table { width: 100%; border-collapse: separate; border-spacing: 0 15px; margin-bottom: 15px; }
                .stat-box-td { width: 48%; border: 2px solid #2c3e50; padding: 10px 10px; text-align: center; background-color: white; vertical-align: middle; }
                .stat-gap-td { width: 4%; background-color: transparent; border: none; }
                .stat-number { font-size: 26pt; font-weight: bold; color: #2c3e50; margin-bottom: 5px; line-height: 1; display: block; }
                .stat-label { font-size: 9pt; color: #666; text-transform: uppercase; font-weight: bold; line-height: 1.3; display: block; }
                .section-title { background-color: #34495e; color: white; padding: 12px 15px; margin-top: 30px; margin-bottom: 18px; font-size: 12pt; font-weight: bold; }
                .content-box { border: 1px solid #ddd; padding: 18px; background-color: #fafafa; margin-bottom: 25px; text-align: justify; line-height: 1.7; }
                .data-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
                .data-table th { background-color: #2c3e50; color: white; padding: 12px 8px; text-align: left; font-weight: bold; border: 1px solid #2c3e50; font-size: 9.5pt; }
                .data-table td { padding: 10px 8px; border: 1px solid #ddd; background-color: white; font-size: 9pt; vertical-align: middle; }
                .data-table tr:nth-child(even) td { background-color: #f8f9fa; }
                .status-badge { padding: 5px 8px; border-radius: 3px; font-size: 8pt; font-weight: bold; display: inline-block; text-align: center; width: 100%; }
                .status-pendiente { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
                .status-resuelto { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
                .status-peligro { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
                .signature-table { width: 100%; margin-top: 80px; border-collapse: collapse; }
                .sig-cell { width: 45%; text-align: center; vertical-align: top; }
                .sig-gap { width: 10%; }
                .signature-line { border-top: 2px solid #333; margin-top: 10px; padding-top: 10px; font-weight: bold; font-size: 10pt; }
                .signature-role { font-size: 9pt; color: #666; margin-top: 5px; }
                img { max-width: 100%; height: auto; page-break-inside: avoid; }
            </style>
        </head>

        <body>
            <div class='header'>
                <table class='header-table'>
                    <tr>
                        <td class='logo-cell'>
                            <div style='width: 70px; height: 70px; background-color: #2c3e50; color: white; text-align: center; line-height: 70px; font-size: 20pt; font-weight: bold;'>
                                <?php if ($logoSrc): ?>
                                    <img width='70px' height="70px" src='<?php echo $logoSrc; ?>' alt='Logo SENA'>
                                <?php else: ?>
                                    SENA
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class='company-info-cell'>
                            <div class='company-name'>SENA</div>
                            <div class='company-details'>
                                Email: <?php echo htmlspecialchars($_SESSION['user']['email']); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class='report-title'>Reporte de Mis Casos PQRSD</div>

            <table class='report-info-table'>
                <tr>
                    <td class='info-label'>Fecha de Generación:</td>
                    <td class='info-value'><?php echo htmlspecialchars($datosInforme['fecha_registro']); ?></td>
                </tr>
                <tr>
                    <td class='info-label'>Comisionado Responsable:</td>
                    <td class='info-value'><?php echo htmlspecialchars($_SESSION['user']['username']); ?></td>
                </tr>
                <tr>
                    <td class='info-label'>Código de Reporte:</td>
                    <td class='info-value'><?php echo htmlspecialchars($datosInforme['id_generado']); ?></td>
                </tr>
            </table>

            <table class='stats-table'>
                <tr>
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $estados['total']; ?></span>
                        <span class='stat-label'>Total de Mis Casos</span>
                    </td>
                    <td class='stat-gap-td'>&nbsp;</td>
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalAtendidos ?></span>
                        <span class='stat-label'>Casos Atendidos</span>
                    </td>
                </tr>
                <tr style='height: 15px;'><td colspan='3'></td></tr>
                <tr>
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalPorAtender ?></span>
                        <span class='stat-label'>Por atender</span>
                    </td>
                    <td class='stat-gap-td'>&nbsp;</td>
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalNoAtendido ?></span>
                        <span class='stat-label'>No Atendidos</span>
                    </td>
                </tr>
            </table>

            <div class='section-title'>1. RESUMEN EJECUTIVO</div>
            <div class='content-box'>
                Durante el ciclo analizado, se registró un volumen global de <strong><?php echo $estados['total']; ?></strong> casos de PQRSD asignados a su perfil en el transcurso del año <strong><?php echo date('Y'); ?></strong>.
                De este total, el <strong><?php echo $porcentajeAtendidos; ?>%</strong> corresponde a solicitudes que ya han sido <strong>atendidas</strong> formalmente.
                Por otro lado, se identifica que un <strong><?php echo $porcentajePorAtender; ?>%</strong> de los casos se encuentra actualmente en estado <strong>por atender</strong>,
                mientras que el <strong><?php echo $porcentajeNoAtendidos; ?>%</strong> restante se clasifica bajo el estatus de <strong>no atendido</strong>.
            </div>

            <div style="page-break-inside: avoid;">
                <div class='section-title'>2. ANÁLISIS VISUAL DE MIS PQRSD</div>
                <div style="text-align: center; margin-bottom: 25px;">
                    <?php if ($chartCasosSrc): ?>
                        <img src="<?php echo $chartCasosSrc; ?>" style="width: 300px; height: auto;" alt="Gráfica de Casos">
                    <?php else: ?>
                        <p style="color: red;">No se pudo generar la gráfica de estados.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div style="page-break-inside: avoid;">
                <div class='section-title'>3. ESTADÍSTICAS POR PROCESO ORGANIZACIONAL</div>
                
                <table class='data-table'>
                    <thead>
                        <tr>
                            <th style='width: 70%;'>Proceso Organizacional</th>
                            <th style='width: 30%; text-align: center;'>Total Casos Asignados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($estadisticasProcesos)): ?>
                            <tr><td colspan="2" style="text-align:center; padding: 20px;">No hay procesos asignados a este comisionado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($estadisticasProcesos as $ep): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ep['proceso']); ?></td>
                                    <td style="text-align: center; font-weight: bold; font-size: 11pt;"><?php echo $ep['total_casos']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($chartProcesosSrc): ?>
                <div style="text-align: center; margin-top: 15px;">
                    <img src="<?php echo $chartProcesosSrc; ?>" style="width: 450px; height: auto;" alt="Gráfica de Procesos">
                </div>
                <?php endif; ?>
            </div>

            <div class='section-title'>4. ÚLTIMOS CASOS REGISTRADOS</div>
            <table class='data-table'>
                <thead>
                    <tr>
                        <th style='width: 10%;'>ID</th>
                        <th style='width: 18%;'>Fecha Registro</th>
                        <th style='width: 20%;'>Tipo</th>
                        <th style='width: 18%;'>Fecha Respuesta</th>
                        <th style='width: 16%;'>Estado</th>
                        <th style='width: 18%;'>Encargado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($casosListados)): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 20px;">No tienes casos PQRSD registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($casosListados as $temp): ?>
                            <?php
                            $estadoStr = strtolower(trim($temp['estado']));
                            $claseBadge = 'status-pendiente'; 

                            if ($estadoStr == 'atendido') {
                                $claseBadge = 'status-resuelto'; 
                            } elseif ($estadoStr == 'no atendido') {
                                $claseBadge = 'status-peligro'; 
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($temp['id_caso']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($temp['fecha_inicio'])); ?></td>
                                <td><?php echo htmlspecialchars($temp['tipo_caso']); ?></td>
                                <td><?php echo ($temp['fecha_cierre']) ? date('d/m/Y', strtotime($temp['fecha_cierre'])) : 'N/A'; ?></td>
                                <td><span class='status-badge <?php echo $claseBadge; ?>'><?php echo htmlspecialchars($temp['estado']); ?></span></td>
                                <td><?php echo htmlspecialchars($temp['comisionado']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <table class='signature-table' style="page-break-inside: avoid;">
                <tr>
                    <td class='sig-cell'>
                        <div class='signature-line'>Firma del Comisionado Responsable</div>
                        <div class='signature-role'><?php echo htmlspecialchars($_SESSION['user']['username']); ?></div>
                    </td>
                    <td class='sig-gap'>&nbsp;</td>
                    <td class='sig-cell'>
                        <div class='signature-line'>Firma del Supervisor</div>
                    </td>
                </tr>
            </table>
        </body>

        </html>
<?php
        $html = ob_get_clean();

        set_time_limit(60);

        $dompdf = new Dompdf();

        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        $dompdf->stream("Reporte_Mis_Casos_PQRSD_SENA.pdf", ['Attachment' => true]);
        exit;
    } else {
        http_response_code(500);
        echo "Error: No se pudieron obtener los casos de la base de datos o registrar el informe.";
    }
} else {
    http_response_code(405);
    echo "Método no permitido. Utilice POST.";
}
?>