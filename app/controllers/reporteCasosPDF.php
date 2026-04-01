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

//Recibimos los datos del front
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $model = new baseHelper($pdo);
    $documento = $_SESSION['user']['documento'];
    $formato = 'PDF';
    $descripcion = 'Reporte Casos';

    $data = [
        ['value' => $documento, 'type' => PDO::PARAM_STR],
        ['value' => $formato, 'type' => PDO::PARAM_STR],
        ['value' => $descripcion, 'type' => PDO::PARAM_STR]
    ];

    //Llamamos las funciones necesarias
    $estados = $model->consultObjectHelper('sp_casos_por_estado');
    $casosListados =$model->consultObjectHelper('sp_listar_casos');
    $datosInforme = $model->insertOrUpdateData('sp_registrar_informe(?, ?, ?)', $data);
    

    //Validamos el retorno datos de todas las funciones
    if ($estados && $casosListados && $datosInforme) {

        $est = [];
        $casos = [];

        foreach ($estados as $temp) { //Palabra reservada para recorrer arrays
                $est[] = $temp['nombre_estado'];
                $casos[] = (int) $temp['total_casos'];
            }

            $estados = [
                'estado' => $est,
                'casos' => $casos,
                'total' => $estados[0]['gran_total']
            ];

        //Se buscara el indice donde estan los estados para luego usar ese indice para mostrar el total  por estado
        $indiceAtendidos = array_search('Atendido', $estados['estado']);
        $indicePorAtender = array_search('Por atender', $estados['estado']);
        $indiceNoAtendido = array_search('No atendido', $estados['estado']);

        //Se limita unicamente a los ultimos 10 casos para reutilizar el sp de listar casos con limit 30
        $casosListados = array_slice($casosListados, 0, 10);

        $totalAtendidos = ($indiceAtendidos !== false) ? $estados['casos'][$indiceAtendidos] : 0;
        $totalPorAtender = ($indicePorAtender !== false) ? $estados['casos'][$indicePorAtender] : 0;
        $totalNoAtendido = ($indiceNoAtendido !== false) ? $estados['casos'][$indiceNoAtendido] : 0;

        //Conseguimos el porcentaje de la cantidad de casos por cada tipo de estado en relacion al total
        if ($totalAtendidos > 0 && $estados['total'] > 0) {
            $porcentajeAtendidos = number_format((($totalAtendidos / $estados['total']) * 100), 1);
        } else {
            $porcentajeAtendidos = 0;
        }

        if ($totalPorAtender > 0 && $estados['total'] > 0) {
            $porcentajePorAtender = number_format((($totalPorAtender / $estados['total']) * 100), 1);
        } else {
            $porcentajePorAtender = 0;
        }

        if ($totalNoAtendido > 0 && $estados['total'] > 0) {
            $porcentajeNoAtendidos = number_format((($totalNoAtendido / $estados['total']) * 100), 1);
        } else {
            $porcentajeNoAtendidos = 0;
        }

        $logoPath = __DIR__ . '/../../Public/assets/img/logo_sena.png';

        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        } else {
            $logoSrc = '';
        }

        $chartCasosConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => ['Atendidos', 'Por Atender', 'No Atendidos'],
                'datasets' => [[
                    'data' => [$totalAtendidos, $totalPorAtender, $totalNoAtendido],
                    'backgroundColor' => ['#27ae60', '#f39c12', '#e74c3c'] // Verde, Naranja, Rojo
                ]]
            ],
            'options' => [
                'plugins' => [
                    'datalabels' => ['color' => '#fff', 'font' => ['weight' => 'bold']]
                ]
            ]
        ];
        $chartCasosUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartCasosConfig)) . "&w=400&h=250";
        $chartCasosData = @file_get_contents($chartCasosUrl);
        $chartCasosSrc = $chartCasosData ? 'data:image/png;base64,' . base64_encode($chartCasosData) : '';
        ob_start();
?>
        <!DOCTYPE html>
        <html lang='es'>

        <head>
            <meta charset='UTF-8'>
            <title>Reporte de PQRSD</title>
            <style>
                @page {
                    margin: 1.5cm 1cm;
                    size: A4;
                }

                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: Arial, Helvetica, sans-serif;
                    font-size: 10pt;
                    color: #333;
                    line-height: 1.5;
                    padding: 1cm;
                }

                .header {
                    width: 100%;
                    border-bottom: 3px solid #2c3e50;
                    padding-bottom: 15px;
                    margin-bottom: 25px;
                }

                .header-table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .logo-cell {
                    width: 20%;
                    vertical-align: top;
                }

                .company-info-cell {
                    width: 80%;
                    text-align: right;
                    vertical-align: top;
                }

                .company-name {
                    font-size: 16pt;
                    font-weight: bold;
                    color: #2c3e50;
                    margin-bottom: 8px;
                }

                .company-details {
                    font-size: 8.5pt;
                    color: #666;
                    line-height: 1.6;
                }

                .report-title {
                    text-align: center;
                    background-color: #2c3e50;
                    color: white;
                    padding: 12px 10px;
                    margin: 15px 0;
                    font-size: 14pt;
                    font-weight: bold;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                /* --- CORRECCIÓN DE LA TABLA DE INFO --- */
                .report-info-table {
                    width: 100%;
                    margin-bottom: 25px;
                    border-collapse: collapse;
                    border: 1px solid #ddd;
                }

                .report-info-table td {
                    padding: 8px 12px;
                    border-bottom: 1px solid #eee;
                }

                .report-info-table .info-label {
                    width: 30%;
                    background-color: #f8f9fa;
                    font-weight: bold;
                    color: #2c3e50;
                    border-right: 1px solid #ddd;
                }

                .report-info-table .info-value {
                    width: 70%;
                }

                .stats-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0 15px;
                    margin-bottom: 15px;
                }

                .stat-box-td {
                    width: 48%;
                    border: 2px solid #2c3e50;
                    padding: 10px 10px;
                    text-align: center;
                    background-color: white;
                    vertical-align: middle;
                }

                .stat-gap-td {
                    width: 4%;
                    background-color: transparent;
                    border: none;
                }

                .stat-number {
                    font-size: 26pt;
                    font-weight: bold;
                    color: #2c3e50;
                    margin-bottom: 5px;
                    line-height: 1;
                    display: block;
                }

                .stat-label {
                    font-size: 9pt;
                    color: #666;
                    text-transform: uppercase;
                    font-weight: bold;
                    line-height: 1.3;
                    display: block;
                }

                .section-title {
                    background-color: #34495e;
                    color: white;
                    padding: 12px 15px;
                    margin-top: 30px;
                    margin-bottom: 18px;
                    font-size: 12pt;
                    font-weight: bold;
                }

                .content-box {
                    border: 1px solid #ddd;
                    padding: 18px;
                    background-color: #fafafa;
                    margin-bottom: 25px;
                    text-align: justify;
                    line-height: 1.7;
                }

                .data-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 25px;
                }

                .data-table th {
                    background-color: #2c3e50;
                    color: white;
                    padding: 12px 8px;
                    text-align: left;
                    font-weight: bold;
                    border: 1px solid #2c3e50;
                    font-size: 9.5pt;
                }

                .data-table td {
                    padding: 10px 8px;
                    border: 1px solid #ddd;
                    background-color: white;
                    font-size: 9pt;
                    vertical-align: middle;
                }

                .data-table tr:nth-child(even) td {
                    background-color: #f8f9fa;
                }

                /* --- BADGES DINÁMICOS --- */
                .status-badge {
                    padding: 5px 8px;
                    border-radius: 3px;
                    font-size: 8pt;
                    font-weight: bold;
                    display: inline-block;
                    text-align: center;
                    width: 100%;
                }

                .status-pendiente {
                    background-color: #fff3cd;
                    color: #856404;
                    border: 1px solid #ffeaa7;
                }

                .status-resuelto {
                    background-color: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }

                .status-peligro {
                    background-color: #f8d7da;
                    color: #721c24;
                    border: 1px solid #f5c6cb;
                }

                /* Para No Atendidos */

                .signature-table {
                    width: 100%;
                    margin-top: 80px;
                    border-collapse: collapse;
                }

                .sig-cell {
                    width: 45%;
                    text-align: center;
                    vertical-align: top;
                }

                .sig-gap {
                    width: 10%;
                }

                .signature-line {
                    border-top: 2px solid #333;
                    margin-top: 10px;
                    padding-top: 10px;
                    font-weight: bold;
                    font-size: 10pt;
                }

                .signature-role {
                    font-size: 9pt;
                    color: #666;
                    margin-top: 5px;
                }

                img {
                    max-width: 100%;
                    height: auto;
                    page-break-inside: avoid;
                }
            </style>
        </head>

        <body>
            <div class='header'>
                <table class='header-table'>
                    <tr>
                        <td class='logo-cell'>
                            <div style='width: 70px; height: 70px; background-color: #2c3e50; color: white; text-align: center; line-height: 70px; font-size: 20pt; font-weight: bold;'>
                                <?php if ($logoSrc): ?>
                                    <img width='70px' height="70px" src='<?php echo $logoSrc; ?>' alt='No encontré esa chimbada'>
                                <?php else: ?>
                                    SENA
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class='company-info-cell'>
                            <div class='company-name'>SENA</div>
                            <div class='company-details'>
                                Email: <?php echo $_SESSION['user']['email']; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class='report-title'>
                Reporte de Casos PQRSD
            </div>

            <table class='report-info-table'>
                <tr>
                    <td class='info-label'>Fecha de Generación:</td>
                    <td class='info-value'><?php echo $datosInforme['fecha_registro']; ?></td>
                </tr>
                <tr>
                    <td class='info-label'>Responsable:</td>
                    <td class='info-value'><?php echo $_SESSION['user']['username']; ?></td>
                </tr>
                <tr>
                    <td class='info-label'>Código de Reporte:</td>
                    <td class='info-value'><?php echo $datosInforme['id_generado'] ?></td>
                </tr>
            </table>

            <table class='stats-table'>
                <tr>
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $estados['total']; ?></span>
                        <span class='stat-label'>Total de Casos</span>
                    </td>
                    <td class='stat-gap-td'>&nbsp;</td>
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalAtendidos ?></span>
                        <span class='stat-label'>Casos Atendidos</span>
                    </td>
                </tr>
                <tr style='height: 15px;'>
                    <td colspan='3'></td>
                </tr>
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
                Durante el ciclo analizado, se registró un volumen global de <strong><?php echo $estados['total']; ?></strong> casos de PQRSD en el transcurso del año <strong><?php echo date('Y'); ?></strong>.
                De este total, el <strong><?php echo $porcentajeAtendidos; ?>%</strong> corresponde a solicitudes que ya han sido <strong>atendidas</strong> formalmente.
                Por otro lado, se identifica que un <strong><?php echo $porcentajePorAtender; ?>%</strong> de los casos se encuentra actualmente en estado <strong>por atender</strong>,
                mientras que el <strong><?php echo $porcentajeNoAtendidos; ?>%</strong> restante se clasifica bajo el estatus de <strong>no atendido</strong> en relación con el consolidado general.
            </div>

            <div style="page-break-inside: avoid;">
                <div class='section-title'>2. ANÁLISIS VISUAL DE PQRSD</div>
                <div style="text-align: center; margin-bottom: 25px;">
                    <?php if ($chartCasosSrc): ?>
                        <img src="<?php echo $chartCasosSrc; ?>" style="width: 300px; height: auto;" alt="Gráfica de Casos">
                    <?php else: ?>
                        <p style="color: red;">No se pudo generar la gráfica de casos.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class='section-title'>3. ÚLTIMOS 10 CASOS REGISTRADOS</div>
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
                    <?php
                    foreach ($casosListados as $temp) {

                        // Lógica para cambiar el color del badge dinámicamente
                        $estadoStr = strtolower(trim($temp['estado']));
                        $claseBadge = 'status-pendiente'; // Por defecto amarillo

                        if ($estadoStr == 'atendido') {
                            $claseBadge = 'status-resuelto'; // Verde
                        } elseif ($estadoStr == 'no atendido') {
                            $claseBadge = 'status-peligro'; // Rojo
                        }

                        echo "
                        <tr>
                            <td>" . $temp['id_caso'] . "</td>
                            <td>" . $temp['fecha_inicio'] . "</td>
                            <td>" . $temp['tipo_caso'] . "</td>
                            <td>" . $temp['fecha_cierre'] . "</td>
                            <td><span class='status-badge " . $claseBadge . "'>" . $temp['estado'] . "</span></td>
                            <td>" . $temp['comisionado'] . "</td>
                        </tr>
                        ";
                    }
                    ?>
                </tbody>
            </table>

            <table class='signature-table'>
                <tr>
                    <td class='sig-cell'>
                        <div class='signature-line'>Firma del Responsable</div>
                        <div class='signature-role'><?php echo $_SESSION['user']['username']; ?></div>
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

        //Evitar bloqueos
        set_time_limit(60);

        //Inicializamos y usamos la clase de Dompdf
        $dompdf = new Dompdf();

        // Permitir carga de imágenes externas
        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);

        //Usamos la variable html previamente generada
        $dompdf->loadHtml($html);

        //Configuramos la orientacion y tamaño
        $dompdf->setPaper('A4', 'portrait');

        //Renderizamos el pdf
        $dompdf->render();

        //Enviamos el pdf al navegador para descargarlo o abrirlo en otra pagina
        $dompdf->stream("Reporte_PQRSD_SENA.pdf", ['Attachment' => true]);
        exit;
    }
}

//manejo de error http
http_response_code(500);
echo "Error al generar el reporte";
