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

//verificamos que sea de tipo post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // se crea una instancia del modelo de casos
    $model = new baseHelper($pdo);
    // se obtiene el documento del usuario
    $documento = $_SESSION['user']['documento'];
    // se define el formato
    $formato = 'PDF';
    // se define la descripcion
    $descripcion = 'Reporte Casos';
    // se crea un array con los datos del informe
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

            // se crea un array con los estados
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
        //  el porcentaje de casos por atender

        if ($totalPorAtender > 0 && $estados['total'] > 0) {
            $porcentajePorAtender = number_format((($totalPorAtender / $estados['total']) * 100), 1);
        } else {
            $porcentajePorAtender = 0;
        }
        //  el porcentaje de casos no atendidos

        if ($totalNoAtendido > 0 && $estados['total'] > 0) {
            $porcentajeNoAtendidos = number_format((($totalNoAtendido / $estados['total']) * 100), 1);
        } else {
            $porcentajeNoAtendidos = 0;
        }
        // la ruta del logo

        $logoPath = __DIR__ . '/../../Public/assets/img/logo_sena.png';

        // se codifica el logo en base64

        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        } // si no existe el logo se define como vacio
        else {
            $logoSrc = '';
        }

        // se define la configuracion del grafico

        $chartCasosConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => ['Atendidos', 'Por Atender', 'No Atendidos'],
                'datasets' => [[
                    'data' => [$totalAtendidos, $totalPorAtender, $totalNoAtendido],
                    'backgroundColor' => ['#27ae60', '#f39c12', '#e74c3c'] // Verde, Naranja, Rojo
                ]]
            ],
            // se definen las opciones del grafico
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
            <title>Reporte de PQRSD</title>
            <style>
                /*se define el tamaño de la pagina*/
                @page {
                    /* se define el margen*/
                    margin: 1.5cm 1cm;
                    /* se define el tamaño de la pagina*/
                    size: A4;
                }

                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    /*fuente*/
                    font-family: Arial, Helvetica, sans-serif;
                    /*tamaño de la fuente*/
                    font-size: 10pt;
                    /*color de la fuente*/
                    color: #333;
                    /*interlineado*/
                    line-height: 1.5;
                    /*padding*/
                    padding: 1cm;
                }

                .header {
                    /*ancho*/
                    width: 100%;
                    /*borde*/
                    border-bottom: 3px solid #2c3e50;
                    /*padding*/
                    padding-bottom: 15px;
                    /*margen*/
                    margin-bottom: 25px;
                }

                .header-table {
                    /*ancho*/
                    width: 100%;
                    /*colapsar bordes*/
                    border-collapse: collapse;
                }

                .logo-cell {
                    /*ancho*/
                    width: 20%;
                    /*vertica, no altura*/
                    vertical-align: top;
                }

                .company-info-cell {
                    /*ancho*/
                    width: 80%;
                    /*alinear texto*/
                    text-align: right;
                    /*vertical no altura*/
                    vertical-align: top;
                }

                .company-name {
                    /*tamaño de la fuente*/
                    font-size: 16pt;
                    /*negrita*/
                    font-weight: bold;
                    /*color*/
                    color: #2c3e50;
                    /*margen*/
                    margin-bottom: 8px;
                }

                .company-details {
                    /*tamaño de la fuente*/
                    font-size: 8.5pt;
                    /*color*/
                    color: #666;
                    /*interlineado*/
                    line-height: 1.6;
                }

                .report-title {
                    /*centrar texto*/
                    text-align: center;
                    /*color de fondo*/
                    background-color: #2c3e50;
                    /*color de texto*/
                    color: white;
                    /*padding*/
                    padding: 12px 10px;
                    /*margen*/
                    margin: 15px 0;
                    /*tamaño de la fuente*/
                    font-size: 14pt;
                    /*negrita*/
                    font-weight: bold;
                    /*transformar texto*/
                    text-transform: uppercase;
                    /*espaciado*/
                    letter-spacing: 0.5px;
                }

                /* --- CORRECCIÓN DE LA TABLA DE INFO --- */
                .report-info-table {
                    /*ancho*/
                    width: 100%;
                    /*margen*/
                    margin-bottom: 25px;
                    /*colapsar bordes, es decir,fusionar bordes*/
                    border-collapse: collapse;
                    /*borde*/
                    border: 1px solid #ddd;
                }

                .report-info-table td {
                    /*padding*/
                    padding: 8px 12px;
                    /*borde*/
                    border-bottom: 1px solid #eee;
                }

                .report-info-table .info-label {
                    /*ancho*/
                    width: 30%;
                    /*color de fondo*/
                    background-color: #f8f9fa;
                    /*negrita*/
                    font-weight: bold;
                    /*color*/
                    color: #2c3e50;
                    /*borde*/
                    border-right: 1px solid #ddd;
                }

                .report-info-table .info-value {
                    /*ancho*/
                    width: 70%;
                }

                .stats-table {
                    /*ancho*/
                    width: 100%;
                    /*colapsar bordes*/
                    border-collapse: separate;
                    /*espaciado*/
                    border-spacing: 0 15px;
                    /*margen*/
                    margin-bottom: 15px;
                }

                .stat-box-td {
                    /*ancho*/
                    width: 48%;
                    /*borde*/
                    border: 2px solid #2c3e50;
                    /*padding*/
                    padding: 10px 10px;
                    /*centrar texto*/
                    text-align: center;
                    /*color de fondo*/
                    background-color: white;
                    /*vertical no altura*/
                    vertical-align: middle;
                }

                .stat-gap-td {
                    /*ancho*/
                    width: 4%;
                    /*color de fondo*/
                    background-color: transparent;
                    /*borde*/
                    border: none;
                }

                .stat-number {
                    /*tamaño de la fuente*/
                    font-size: 26pt;
                    /*negrita*/
                    font-weight: bold;
                    /*color*/
                    color: #2c3e50;
                    /*margen*/
                    margin-bottom: 5px;
                    /*interlineado*/
                    line-height: 1;
                    /*mostrar como bloque*/
                    display: block;
                }

                .stat-label {
                    /*tamaño de la fuente*/
                    font-size: 9pt;
                    /*color*/
                    color: #666;
                    /*transformar texto*/
                    text-transform: uppercase;
                    /*negrita*/
                    font-weight: bold;
                    /*interlineado*/
                    line-height: 1.3;
                    /*mostrar como bloque*/
                    display: block;
                }

                .section-title {
                    /*color de fondo*/
                    background-color: #34495e;
                    /*color de las letras*/
                    color: white;
                    /*padding*/
                    padding: 12px 15px;
                    /*margen*/
                    margin-top: 30px;
                    /*margen*/
                    margin-bottom: 18px;
                    /*tamaño de la fuente*/
                    font-size: 12pt;
                    /*negrita*/
                    font-weight: bold;
                }

                .content-box {
                    /*borde*/
                    border: 1px solid #ddd;
                    /*padding*/
                    padding: 18px;
                    /*color de fondo*/
                    background-color: #fafafa;
                    /*margen*/
                    margin-bottom: 25px;
                    /*justificar texto*/
                    text-align: justify;
                    /*interlineado*/
                    line-height: 1.7;
                }

                .data-table {
                    /*ancho*/
                    width: 100%;
                    /*colapsar bordes*/
                    border-collapse: collapse;
                    /*margen*/
                    margin-bottom: 25px;
                }

                .data-table th {
                    /*color de fondo*/
                    background-color: #2c3e50;
                    /*color de las letras*/
                    color: white;
                    /*padding*/
                    padding: 12px 8px;
                    /*centrar texto*/
                    text-align: left;
                    /*negrita*/
                    font-weight: bold;
                    /*borde*/
                    border: 1px solid #2c3e50;
                    /*tamaño de la fuente*/
                    font-size: 9.5pt;
                }

                .data-table td {
                    /*padding*/
                    padding: 10px 8px;
                    /*borde*/
                    border: 1px solid #ddd;
                    /*color de fondo*/
                    background-color: white;
                    /*tamaño de la fuente*/
                    font-size: 9pt;
                    /*vertical*/
                    vertical-align: middle;
                }

                .data-table tr:nth-child(even) td {
                    /*color de fondo*/
                    background-color: #f8f9fa;
                }

                /* --- BADGES DINÁMICOS --- */
                .status-badge {
                    /*padding*/
                    padding: 5px 8px;
                    /*redondeado*/
                    border-radius: 3px;
                    /*tamaño de la fuente*/
                    font-size: 8pt;
                    /*negrita*/
                    font-weight: bold;
                    /*mostrar como bloque*/
                    display: inline-block;
                    /*centrar texto*/
                    text-align: center;
                    /*ancho*/
                    width: 100%;
                }

                .status-pendiente {
                    /*color de fondo*/
                    background-color: #fff3cd;
                    /*color de las letras*/
                    color: #856404;
                    /*borde*/
                    border: 1px solid #ffeaa7;
                }

                .status-resuelto {
                    /*color de fondo*/
                    background-color: #d4edda;
                    /*color de las letras*/
                    color: #155724;
                    /*borde*/
                    border: 1px solid #c3e6cb;
                }

                .status-peligro {
                    /*color de fondo*/
                    background-color: #f8d7da;
                    /*color de las letras*/
                    color: #721c24;
                    /*borde*/
                    border: 1px solid #f5c6cb;
                }

                /* Para No Atendidos */

                .signature-table {
                    /*ancho*/
                    width: 100%;
                    /*margen*/
                    margin-top: 80px;
                    /*colapsar bordes*/
                    border-collapse: collapse;
                }

                .sig-cell {
                    /*ancho*/
                    width: 45%;
                    /*centrar texto*/
                    text-align: center;
                    /*vertical*/
                    vertical-align: top;
                }

                .sig-gap {
                    /*ancho*/
                    width: 10%;
                }

                .signature-line {
                    /*borde de arriba*/
                    border-top: 2px solid #333;
                    /*margen de arriba*/
                    margin-top: 10px;
                    /*padding*/
                    padding-top: 10px;
                    /*negrita*/
                    font-weight: bold;
                    /*tamaño de la fuente*/
                    font-size: 10pt;
                }

                .signature-role {
                    /*tamaño de la fuente*/
                    font-size: 9pt;
                    /*color*/
                    color: #666;
                    /*margen de arriba*/
                    margin-top: 5px;
                }

                img {
                    /*ancho*/
                    max-width: 100%;
                    /*altura*/
                    height: auto;
                    /*evitar que se rompa la imagen*/
                    page-break-inside: avoid;
                }
            </style>
        </head>

        <body>
            <!--Encabezado-->
            <div class='header'>
                <table class='header-table'>
                    <tr>
                        <!--Logo-->
                        <td class='logo-cell'>
                            <!--Se define el tamaño del logo-->
                            <div style='width: 70px; height: 70px; background-color: #2c3e50; color: white; text-align: center; line-height: 70px; font-size: 20pt; font-weight: bold;'>
                                <?php if ($logoSrc): ?>
                                    <img width='70px' height="70px" src='<?php echo $logoSrc; ?>' alt='No encontré esa chimbada'>
                                <?php else: ?>
                                    SENA
                                <?php endif; ?>
                            </div>
                        </td>
                        <!--Información de la empresa-->
                        <td class='company-info-cell'>
                            <!--Nombre de la empresa-->
                            <div class='company-name'>SENA</div>
                            <!--Correo electrónico-->
                            <div class='company-details'>
                                Email: <?php echo $_SESSION['user']['email']; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <!--Titulo del reporte-->
            <div class='report-title'>
                Reporte de Casos PQRSD
            </div>
            <!--Tabla con la información del reporte-->
            <table class='report-info-table'>
                <tr>
                    <!--Fecha de generación-->
                    <td class='info-label'>Fecha de Generación:</td>
                    <!--Valor de la fecha de generación-->
                    <td class='info-value'><?php echo $datosInforme['fecha_registro']; ?></td>
                </tr>
                <tr>
                    <!--Responsable-->
                    <td class='info-label'>Responsable:</td>
                    <td class='info-value'><?php echo $_SESSION['user']['username']; ?></td>
                </tr>
                <tr>
                    <!--Código del reporte-->
                    <td class='info-label'>Código de Reporte:</td>
                    <td class='info-value'><?php echo $datosInforme['id_generado'] ?></td>
                </tr>
            </table>
            <!--Tabla con las estadísticas-->
            <table class='stats-table'>
                <tr>
                    <!--Total de casos-->
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $estados['total']; ?></span>
                        <span class='stat-label'>Total de Casos</span>
                    </td>
                    <!--Espacio entre las celdas-->
                    <td class='stat-gap-td'>&nbsp;</td>
                    <!--Casos atendidos-->
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalAtendidos ?></span>
                        <span class='stat-label'>Casos Atendidos</span>
                    </td>
                </tr>
                <!--Espacio entre las filas-->
                <tr style='height: 15px;'>
                    <td colspan='3'></td>
                </tr>
                <tr>
                    <!--Casos por atender-->
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalPorAtender ?></span>
                        <span class='stat-label'>Por atender</span>
                    </td>
                    <!--Espacio entre las celdas-->
                    <td class='stat-gap-td'>&nbsp;</td>
                    <!--Casos no atendidos-->
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalNoAtendido ?></span>
                        <span class='stat-label'>No Atendidos</span>
                    </td>
                </tr>
            </table>
            <!--Titulo del resumen ejecutivo-->
            <div class='section-title'>1. RESUMEN EJECUTIVO</div>
            <!--Contenido del resumen ejecutivo-->
            <div class='content-box'>
                Durante el ciclo analizado, se registró un volumen global de <strong><?php echo $estados['total']; ?></strong> casos de PQRSD en el transcurso del año <strong><?php echo date('Y'); ?></strong>.
                De este total, el <strong><?php echo $porcentajeAtendidos; ?>%</strong> corresponde a solicitudes que ya han sido <strong>atendidas</strong> formalmente.
                Por otro lado, se identifica que un <strong><?php echo $porcentajePorAtender; ?>%</strong> de los casos se encuentra actualmente en estado <strong>por atender</strong>,
                mientras que el <strong><?php echo $porcentajeNoAtendidos; ?>%</strong> restante se clasifica bajo el estatus de <strong>no atendido</strong> en relación con el consolidado general.
            </div>

            <!--Titulo del análisis visual-->
            <div style="page-break-inside: avoid;">
                <div class='section-title'>2. ANÁLISIS VISUAL DE PQRSD</div>
                <!--Contenido del análisis visual-->
                <div style="text-align: center; margin-bottom: 25px;">
                    <?php if ($chartCasosSrc): ?>
                        <!--Se define el tamaño del grafico-->
                        <img src="<?php echo $chartCasosSrc; ?>" style="width: 300px; height: auto;" alt="Gráfica de Casos">
                    <?php else: ?>
                        <!--Se define el tamaño del grafico-->
                        <p style="color: red;">No se pudo generar la gráfica de casos.</p>
                    <?php endif; ?>
                </div>
            </div>
            <!--Titulo de los ultimos 10 casos-->
            <div class='section-title'>3. ÚLTIMOS 10 CASOS REGISTRADOS</div>
            <table class='data-table'>
                <thead>
                    <tr>
                        <!--Se define ancho de las columnas-->
                        <th style='width: 10%;'>ID</th>
                        <th style='width: 18%;'>Fecha Registro</th>
                        <th style='width: 20%;'>Tipo</th>
                        <th style='width: 18%;'>Fecha Respuesta</th>
                        <th style='width: 16%;'>Estado</th>
                        <th style='width: 18%;'>Encargado</th>
                    </tr>
                </thead>
                <tbody>
                    <!--Se recorre el array de casos-->
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
                        //genera dinamincamente los datos de la tabla
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
            <!--Tabla con la firma del responsable-->
            <table class='signature-table'>
                <tr>
                    <td class='sig-cell'>
                        <div class='signature-line'>Firma del Responsable</div>
                        <div class='signature-role'><?php echo $_SESSION['user']['username']; ?></div>
                    </td>
                    <!--Espacio entre las celdas-->
                    <td class='sig-gap'>&nbsp;</td>
                    <!--Firma del supervisor-->
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
        $dompdf->stream("Reporte_Casos_SENA.pdf", ['Attachment' => true]);
        exit;
    }
}

//manejo de error http
http_response_code(500);
echo "Error al generar el reporte";
