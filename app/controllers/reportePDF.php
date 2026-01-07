<?php
//Debug
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

//Cargamos la session
session_start();

//Llamar al archivo necesario para dompdf y otros archivos necesarios para obtener datos
require '../../vendor/autoload.php';
require_once "../config/conexion.php";
require_once "../models/insertData.php";
require_once "../models/getData.php";

//referenciar dompdf
use Dompdf\Dompdf;

//Recibimos los datos del front

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //Capturamos los datos por metodo post
    $tituloObservacion = $_POST['titulo'];
    $contenidoObservacion = $_POST['contenidoObservacion'];
    $conclusion = $_POST['conclusiones'];

    //Llamamos las funciones necesarias
    $estados = casosPorEstado($pdo);
    $casosListados = listarCasos($pdo);
    $datosInforme = registrarInforme($pdo, $_SESSION['user']['documento'], 'PDF', $conclusion);

    //Validamos el retorno datos de todas las funcione
    if ($estados && $casosListados && $datosInforme) {

        //Se buscara el indice donde estan los estados para luego usar ese indice pra mostrar el total  por estado
        $indiceAtendidos = array_search('Atendido', $estados['estado']);
        $indicePorAtender = array_search('Por atender', $estados['estado']);
        $indiceNoAtendido = array_search('No atendido', $estados['estado']);

        //Se limita unicamente a los ultimos 10 casos para reutilizar el sp de listar casos con limit 30
        $casosListados['data'] = array_slice($casosListados['data'], 0, 10);

        //Se valida si los indices devolvieron el indice esperado
        if ($indiceAtendidos !== false && $indicePorAtender !== false && $indiceNoAtendido !== false) {

            //Usamos el indice encontrado para encontrar el total del estado
            $totalAtendidos = $estados['casos'][$indiceAtendidos];
            $totalPorAtender = $estados['casos'][$indicePorAtender];
            $totalNoAtendido = $estados['casos'][$indiceNoAtendido];
        } else {
            $totalAtendidos = 0;
            $totalPorAtender = 0;
            $totalNoAtendido = 0;
        }

        //Conseguimos el porcentaje de la cantidad de casos por cada tipo de estado en relacion al total
        if ($totalAtendidos > 0 && $estados['total'] > 0) {
            $porcentajeAtendidos = number_format((($totalAtendidos / $estados['total']) * 100), 1);
        } else {
            $porcentajeAtendidos = 0;
        }

        if ($totalPorAtender > 0 && $estados['total'] > 0) {
            $porcentajePorAtender = number_format((($totalPorAtender / $estados['total']) * 100),1);
        } else {
            $porcentajePorAtender = 0;
        }

        if ($totalNoAtendido > 0 && $estados['total'] > 0) {
            $porcentajeNoAtendidos = number_format((($totalNoAtendidos / $estados['total']) * 100), 1);
        } else {
            $porcentajeNoAtendidos = 0;
        }

        //Convertimos la imagen a base64 para mejor entendimiento de la misma por DOMPDF
        $logoPath = __DIR__ . '/../../Public/assets/img/logo_sena.png';

        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        } else {
            // Si no encuentra el logo, usar el placeholder
            $logoSrc = '';
        }

        //Usamos las funciones reservadas de ob para obtener el html y almacenarlo en una variable
?>

        <?php ob_start(); ?>

        <!DOCTYPE html>
        <html lang='es'>

        <head>
            <meta charset='UTF-8'>
            <title>Reporte de PQRSD</title>
            <style>
                @page {
                    margin: 2cm 1.5cm;
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

                /* --- CABECERA --- */
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

                /* --- TITULO Y INFO --- */
                .report-title {
                    text-align: center;
                    background-color: #2c3e50;
                    color: white;
                    padding: 15px 10px;
                    margin: 25px 0;
                    font-size: 14pt;
                    font-weight: bold;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .report-info {
                    width: 100%;
                    margin-bottom: 25px;
                    border: 1px solid #ddd;
                }

                .info-row {
                    width: 100%;
                    border-bottom: 1px solid #eee;
                }

                .info-label {
                    width: 30%;
                    float: left;
                    background-color: #f8f9fa;
                    padding: 10px 15px;
                    font-weight: bold;
                    color: #2c3e50;
                    border-right: 1px solid #ddd;
                }

                .info-value {
                    width: 70%;
                    float: left;
                    padding: 10px 15px;
                }

                /* --- SOLUCIÓN PARA LAS CAJAS (ESTADÍSTICAS) --- */
                /* Usamos tablas para garantizar estructura en DOMPDF */
                .stats-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0 15px;
                    /* Espacio vertical entre filas */
                    margin-bottom: 30px;
                }

                .stat-box-td {
                    width: 48%;
                    /* Ancho fijo para las cajas */
                    border: 2px solid #2c3e50;
                    padding: 15px 10px;
                    text-align: center;
                    background-color: white;
                    vertical-align: middle;
                }

                .stat-gap-td {
                    width: 4%;
                    /* El espacio exacto en el medio */
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

                /* --- ESTILOS GENERALES --- */
                .clear {
                    clear: both;
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

                /* --- TABLA DE DATOS --- */
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

                /* --- BADGES --- */
                .status-badge {
                    padding: 5px 8px;
                    border-radius: 3px;
                    font-size: 8pt;
                    font-weight: bold;
                    display: inline-block;
                    text-align: center;
                }

                .status-pendiente {
                    background-color: #fff3cd;
                    color: #856404;
                    border: 1px solid #ffeaa7;
                }

                .status-proceso {
                    background-color: #d1ecf1;
                    color: #0c5460;
                    border: 1px solid #bee5eb;
                }

                .status-resuelto {
                    background-color: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }

                .observation-box {
                    border-left: 4px solid #2c3e50;
                    background-color: #f8f9fa;
                    padding: 15px 18px;
                    margin: 15px 0;
                    line-height: 1.6;
                }

                /* --- FIRMAS --- */
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
            </style>
        </head>

        <body>
            <div class='header'>
                <table class='header-table'>
                    <tr>
                        <td class='logo-cell'>
                            <div style='width: 70px; height: 70px; background-color: #2c3e50; color: white; text-align: center; line-height: 70px; font-size: 20pt; font-weight: bold;'>
                                <img width='70px' height="70px" src='<?php echo $logoSrc; ?>' alt='No encontré esa chimbada'>
                            </div>
                        </td>
                        <td class='company-info-cell'>
                            <div class='company-name'>SENA</div>
                            <div class='company-details'>
                                NIT: 900.123.456-7<br>
                                Dirección: Calle 123 #45-67, Medellín, Antioquia<br>
                                Email: <?php echo $_SESSION['user']['email']; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class='report-title'>
                Reporte de Administrador
            </div>

            <div class='report-info'>
                <div class='info-row'>
                    <div class='info-label'>Fecha de Generación:</div>
                    <div class='info-value'><?php echo $datosInforme['fecha_registro']; ?></div>
                    <div class='clear'></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Responsable:</div>
                    <div class='info-value'><?php echo $_SESSION['user']['username']; ?></div>
                    <div class='clear'></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Código de Reporte:</div>
                    <div class='info-value'><?php echo $datosInforme['id_generado'] ?></div>
                    <div class='clear'></div>
                </div>
            </div>

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

            <div class='section-title'>2. Ultimos 10 Casos Registrados En El Transcurso Del Año</div>
            <table class='data-table'>
                <thead>
                    <tr>
                        <th style='width: 13%;'>ID</th>
                        <th style='width: 15%;'>Fecha De Registro</th>
                        <th style='width: 25%;'>Tipo</th>
                        <th style='width: 14%;'>Fecha respuesta</th>
                        <th style='width: 18%;'>Estado</th>
                        <th style='width: 15%;'>Encargado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    foreach ($casosListados['data'] as $temp) {
                        echo "
                    <tr>
                        <td>" . $temp['id_caso'] . "</td>
                        <td>" . $temp['fecha_inicio'] . "</td>
                        <td>" . $temp['tipo_caso'] . "</td>
                        <td>" . $temp['fecha_cierre'] . "</td>
                        <td><span class='status-badge status-proceso'>" . $temp['estado'] . "</span></td>
                        <td>" . $temp['comisionado'] . "</td>
                    </tr>
                        ";
                    }

                    ?>
                </tbody>
            </table>

            <div class='section-title'>3. OBSERVACIONES Y SEGUIMIENTO</div>
            <div class='observation-box'>
                <strong><?php echo $tituloObservacion ?> </strong> <?php echo $contenidoObservacion ?>
            </div>

            <div class='section-title'>4. CONCLUSIONES Y RECOMENDACIONES</div>
            <div class='content-box'>
                <?php echo $conclusion ?>
            </div>

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

        //Inicializamos y usamos la clase de Dompdf
        $dompdf = new Dompdf();

        //Usamos la variable html previamente generada
        $dompdf->loadHtml($html);

        //Configuramos la orientacion y tamaño
        $dompdf->setPaper('A4', 'portrait');

        //Renderizamos el pdf
        $dompdf->render();


        //Enviamos el pdf al navegador para descargarlo o abrirlo en otra pagina
        $dompdf->stream("Reporte_Confidencial_SENA.pdf", ['Attachment' => true]);
        exit;
    }
}

//manejo de error http
http_response_code(500);
echo "Error al generar el reporte";
