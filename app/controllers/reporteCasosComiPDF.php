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

    // 1. Llamamos al NUEVO SP que creamos para las estadísticas
    $estadosRaw = $model->consultObjectWithParams('sp_casos_por_comisionado_doc(?)', $paramComisionado);
    // --- DEBUG TEMPORAL (Puedes borrarlo o comentarlo luego de probar) ---
    // echo "<h3>Documento en Sesión: " . $documento . "</h3>";
    // echo "<pre>";
    // print_r($estadosRaw);
    // echo "</pre>";
    // exit;
    // ----------------------------------------------------------------------
    
    // 2. Llamamos al SP para el listado de casos
    // IMPORTANTE: Debes crear en tu BD el sp_listar_casos_comisionado_doc copiando la lógica de tu listado original,
    // agregándole el parámetro (IN p_documento VARCHAR(50)) y el WHERE c.documento = p_documento.
    $casosListados = $model->consultObjectWithParams('sp_listar_casos_comisionado_doc(?)', $paramComisionado);

    // se registra el informe
    $datosInforme = $model->insertOrUpdateData('sp_registrar_informe(?, ?, ?)', $data);

    //Validamos el retorno de datos
    if ($estadosRaw && is_array($casosListados) && $datosInforme) {

        $est = [];
        $casos = [];
        // Protegemos el gran total por si el comisionado aún no tiene casos asignados (evita errores de offset)
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

        // se obtiene el total de casos atendidos
        $totalAtendidos = ($indiceAtendidos !== false) ? $estados['casos'][$indiceAtendidos] : 0;
        // se obtiene el total de casos por atender
        $totalPorAtender = ($indicePorAtender !== false) ? $estados['casos'][$indicePorAtender] : 0;
        // se obtiene el total de casos no atendidos
        $totalNoAtendido = ($indiceNoAtendido !== false) ? $estados['casos'][$indiceNoAtendido] : 0;

        //Conseguimos el porcentaje evitando divisiones por cero
        $porcentajeAtendidos = ($totalAtendidos > 0 && $estados['total'] > 0) ? number_format((($totalAtendidos / $estados['total']) * 100), 1) : 0;
        $porcentajePorAtender = ($totalPorAtender > 0 && $estados['total'] > 0) ? number_format((($totalPorAtender / $estados['total']) * 100), 1) : 0;
        $porcentajeNoAtendidos = ($totalNoAtendido > 0 && $estados['total'] > 0) ? number_format((($totalNoAtendido / $estados['total']) * 100), 1) : 0;

        // se obtiene la ruta del logo
        $logoPath = __DIR__ . '/../../Public/assets/img/logo_sena.png';
        // se codifica el logo en base64
        $logoSrc = (file_exists($logoPath)) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        // Configuración QuickChart
        $chartCasosConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => ['Atendidos', 'Por Atender', 'No Atendidos'],
                'datasets' => [[
                    'data' => [$totalAtendidos, $totalPorAtender, $totalNoAtendido],
                    'backgroundColor' => ['#27ae60', '#f39c12', '#e74c3c'] 
                ]]
            ],
            // se define las opciones del grafico
            'options' => [
                'plugins' => [
                    'datalabels' => ['color' => '#fff', 'font' => ['weight' => 'bold']]
                ]
            ]
        ];
        
        // se obtiene la url del grafico
        $chartCasosUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartCasosConfig)) . "&w=400&h=250";
        // se obtiene el grafico
        $chartCasosData = @file_get_contents($chartCasosUrl);
        // se codifica el grafico en base64
        $chartCasosSrc = $chartCasosData ? 'data:image/png;base64,' . base64_encode($chartCasosData) : '';
        
        // se inicia el buffer
        ob_start();
?>
        <!DOCTYPE html>
        <html lang='es'>

        <head>
            <meta charset='UTF-8'>
            <!-- se escrube el titulo -->
            <title>Reporte Mis Casos PQRSD</title>
            <style>
                /* se define el estilo del documento */

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
            <!-- se define el header -->
            <div class='header'>
                <!-- se define la tabla del header -->
                <table class='header-table'>
                    <tr>
                        <td class='logo-cell'>
                            <!-- se define el logo -->
                            <div style='width: 70px; height: 70px; background-color: #2c3e50; color: white; text-align: center; line-height: 70px; font-size: 20pt; font-weight: bold;'>
                                <!-- se valida si el logo existe -->
                                <?php if ($logoSrc): ?>
                                    <!-- se muestra el logo -->
                                    <img width='70px' height="70px" src='<?php echo $logoSrc; ?>' alt='Logo SENA'>
                                <?php else: ?>
                                    <!-- se muestra el nombre de la empresa -->
                                    SENA
                                <?php endif; ?>
                            </div>
                        </td>
                        <!-- se define la informacion de la empresa -->
                        <td class='company-info-cell'>
                            <!-- se define el nombre de la empresa -->
                            <div class='company-name'>SENA</div>
                            <!-- se define el correo de la empresa -->
                            <div class='company-details'>
                                Email: <?php echo $_SESSION['user']['email']; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- se define el titulo del reporte -->
            <div class='report-title'>
                Reporte de Mis Casos PQRSD
            </div>

            <!-- se define la tabla de informacion del reporte -->
            <table class='report-info-table'>
                <tr>
                    <!-- se define la fecha de generacion -->
                    <td class='info-label'>Fecha de Generación:</td>
                    <!-- se muestra la fecha de generacion -->
                    <td class='info-value'><?php echo $datosInforme['fecha_registro']; ?></td>
                </tr>
                <tr>
                    <!-- se define el comisionado responsable -->
                    <td class='info-label'>Comisionado Responsable:</td>
                    <td class='info-value'><?php echo $_SESSION['user']['username']; ?></td>
                </tr>
                <tr>
                    <!-- se define el codigo del reporte -->
                    <td class='info-label'>Código de Reporte:</td>
                    <!-- se muestra el codigo del reporte -->
                    <td class='info-value'><?php echo $datosInforme['id_generado']; ?></td>
                </tr>
            </table>
            <!-- se define la tabla de estadisticas -->
            <table class='stats-table'>
                <tr>
                    <!-- se define el total de casos -->
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $estados['total']; ?></span>
                        <span class='stat-label'>Total de Mis Casos</span>
                    </td>
                    <!-- se define un espacio entre las columnas -->
                    <td class='stat-gap-td'>&nbsp;</td>
                    <!-- se define el total de casos atendidos -->
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalAtendidos ?></span>
                        <span class='stat-label'>Casos Atendidos</span>
                    </td>
                </tr>
                <!-- se define un espacio entre las filas -->
                <tr style='height: 15px;'>
                    <td colspan='3'></td>
                </tr>
                <tr>
                    <!-- se define el total de casos por atender -->
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalPorAtender ?></span>
                        <span class='stat-label'>Por atender</span>
                    </td>
                    <!-- se define un espacio entre las columnas -->
                    <td class='stat-gap-td'>&nbsp;</td>
                    <!-- se define el total de casos no atendidos -->
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalNoAtendido ?></span>
                        <span class='stat-label'>No Atendidos</span>
                    </td>
                </tr>
            </table>
            <!-- se define el titulo del resumen ejecutivo -->
            <div class='section-title'>1. RESUMEN EJECUTIVO</div>
            <!-- se define el contenido del resumen ejecutivo -->
            <div class='content-box'>
                Durante el ciclo analizado, se registró un volumen global de <strong><?php echo $estados['total']; ?></strong> casos de PQRSD asignados a su perfil en el transcurso del año <strong><?php echo date('Y'); ?></strong>.
                De este total, el <strong><?php echo $porcentajeAtendidos; ?>%</strong> corresponde a solicitudes que ya han sido <strong>atendidas</strong> formalmente.
                Por otro lado, se identifica que un <strong><?php echo $porcentajePorAtender; ?>%</strong> de los casos se encuentra actualmente en estado <strong>por atender</strong>,
                mientras que el <strong><?php echo $porcentajeNoAtendidos; ?>%</strong> restante se clasifica bajo el estatus de <strong>no atendido</strong>.
            </div>
            <!-- se define la grafica de los casos -->
            <div style="page-break-inside: avoid;">
                <!-- se define el titulo de la grafica -->
                <div class='section-title'>2. ANÁLISIS VISUAL DE MIS PQRSD</div>
                <!-- se define la grafica -->
                <div style="text-align: center; margin-bottom: 25px;">
                    <?php if ($chartCasosSrc): ?>
                        <!-- se muestra la grafica -->
                        <img src="<?php echo $chartCasosSrc; ?>" style="width: 300px; height: auto;" alt="Gráfica de Casos">
                    <?php else: ?>
                        <!-- se muestra un mensaje de error si no se pudo generar la grafica -->
                        <p style="color: red;">No se pudo generar la gráfica de casos.</p>
                    <?php endif; ?>
                </div>
            </div>
            <!-- se define el titulo de los ultimos casos registrados -->
            <div class='section-title'>3. ÚLTIMOS CASOS REGISTRADOS</div>
            <table class='data-table'>
                <thead>
                    <tr>
                        <!-- se define el id del caso -->
                        <th style='width: 10%;'>ID</th>
                        <!-- se define la fecha de registro -->
                        <th style='width: 18%;'>Fecha Registro</th>
                        <!-- se define el tipo de caso -->
                        <th style='width: 20%;'>Tipo</th>
                        <!-- se define la fecha de respuesta -->
                        <th style='width: 18%;'>Fecha Respuesta</th>
                        <!-- se define el estado del caso -->
                        <th style='width: 16%;'>Estado</th>
                        <th style='width: 18%;'>Encargado</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- se muestra un mensaje si no hay casos registrados -->
                    <?php if(empty($casosListados)): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 20px;">No tienes casos PQRSD registrados.</td></tr>
                    <?php else: ?>
                        <!-- se recorre la lista de casos -->
                        <?php foreach ($casosListados as $temp): ?>
                            <?php
                            // se define el estado del caso
                            $estadoStr = strtolower(trim($temp['estado']));
                            // se define la clase del badge
                            $claseBadge = 'status-pendiente'; 

                            // se define el color del badge segun el estado
                            if ($estadoStr == 'atendido') {
                                $claseBadge = 'status-resuelto'; 
                            } elseif ($estadoStr == 'no atendido') {
                                $claseBadge = 'status-peligro'; 
                            }
                            ?>
                            <tr>
                                <!-- se muestra el id del caso -->
                                <td><?php echo $temp['id_caso']; ?></td>
                                <!-- se muestra la fecha de registro -->
                                <td><?php echo date('d/m/Y', strtotime($temp['fecha_inicio'])); ?></td>
                                <!-- se muestra el tipo de caso -->
                                <td><?php echo htmlspecialchars($temp['tipo_caso']); ?></td>
                                <!-- se muestra la fecha de respuesta -->
                                <td><?php echo ($temp['fecha_cierre']) ? date('d/m/Y', strtotime($temp['fecha_cierre'])) : 'N/A'; ?></td>
                                <!-- se muestra el estado del caso -->
                                <td><span class='status-badge <?php echo $claseBadge; ?>'><?php echo $temp['estado']; ?></span></td>
                                <td><?php echo htmlspecialchars($temp['comisionado']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <table class='signature-table'>
                <tr>
                    <!-- se define la firma del comisionado responsable -->
                    <td class='sig-cell'>
                        <div class='signature-line'>Firma del Comisionado Responsable</div>
                        <div class='signature-role'><?php echo $_SESSION['user']['username']; ?></div>
                    </td>
                    <!-- se define un espacio entre las columnas -->
                    <td class='sig-gap'>&nbsp;</td>
                    <td class='sig-cell'>
                        <div class='signature-line'>Firma del Supervisor</div>
                    </td>
                </tr>
            </table>
        </body>

        </html>
<?php
        // se obtiene el contenido del buffer   
        $html = ob_get_clean();

        // Evitar bloqueos
        set_time_limit(60);

        // Inicializamos y usamos la clase de Dompdf
        $dompdf = new Dompdf();

        // Permitir carga de imágenes externas
        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);

        // Usamos la variable html previamente generada
        $dompdf->loadHtml($html);

        // Configuramos la orientacion y tamaño
        $dompdf->setPaper('A4', 'portrait');

        // Renderizamos el pdf
        $dompdf->render();

        // Enviamos el pdf al navegador para descargarlo o abrirlo en otra pagina
        $dompdf->stream("Reporte_Mis_Casos_PQRSD_SENA.pdf", ['Attachment' => true]);
        exit;
    } else {
        // si no se pudo generar el pdf por la base de datos o por registrar el informe, muestra un error
        http_response_code(500);
        echo "Error: No se pudieron obtener los casos de la base de datos o registrar el informe.";
    }
} else {
    // si el metodo no es POST, muestra un error
    http_response_code(405);
    echo "Método no permitido. Utilice POST.";
}
?>