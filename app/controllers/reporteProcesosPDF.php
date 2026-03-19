<?php
// Cargamos la session
session_start();

// Llamar al archivo necesario para dompdf y otros archivos necesarios para obtener datos
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/insertData.php";
require_once __DIR__ . "/../models/getData.php";

// referenciar dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

// Verificamos si la petición es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Llamamos a la función de listar procesos (asegúrate de que esté en getData.php)
    $resProcesos = listarProcesos($pdo);
    
    // Registramos el informe en la bitácora
    $datosInforme = registrarInforme($pdo, $_SESSION['user']['documento'], 'PDF', 'Reporte Procesos');

    // Validamos que tengamos la lista de procesos y el registro del informe
    if ($resProcesos && $resProcesos['status'] === 'ok' && $datosInforme) {

        $listaCompleta = $resProcesos['data'];
        $totalProcesos = count($listaCompleta);
        $totalActivos = 0;
        $totalInactivos = 0;

        foreach ($listaCompleta as $proceso) {
            // Conteo por Estado (1: Activo, 0: Inactivo)
            if ($proceso['estado'] == 1) {
                $totalActivos++;
            } else {
                $totalInactivos++;
            }
        }

        // Calculamos porcentajes
        $porcentajeActivos = ($totalProcesos > 0) ? number_format((($totalActivos / $totalProcesos) * 100), 1) : 0;
        $porcentajeInactivos = ($totalProcesos > 0) ? number_format((($totalInactivos / $totalProcesos) * 100), 1) : 0;

        // Limitamos la tabla para el PDF a los últimos registros para evitar desbordamiento si es muy larga
        // O puedes quitar el slice si prefieres que salgan todos (Dompdf creará nuevas páginas)
        $dataParaTabla = $listaCompleta; 

        // Convertimos el logo a base64
        $logoPath = __DIR__ . '/../../Public/assets/img/logo_sena.png';
        $logoSrc = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }
        
        // Iniciamos el buffer de salida
        ob_start();
?>
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Reporte de Procesos Organizacionales</title>
            <style>
                @page { margin: 2cm 1.5cm; size: A4; }
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; font-size: 10pt; color: #333; line-height: 1.5; padding: 1cm; }
                .header { width: 100%; border-bottom: 3px solid #39A900; padding-bottom: 15px; margin-bottom: 25px; }
                .header-table { width: 100%; border-collapse: collapse; }
                .logo-cell { width: 70px; }
                .company-info-cell { text-align: right; vertical-align: top; }
                .company-name { font-size: 16pt; font-weight: bold; color: #39A900; }
                .report-title { text-align: center; background-color: #2c3e50; color: white; padding: 15px; margin: 20px 0; font-weight: bold; text-transform: uppercase; }
                .report-info { width: 100%; margin-bottom: 20px; border: 1px solid #ddd; }
                .info-row { border-bottom: 1px solid #eee; clear: both; overflow: hidden; }
                .info-label { width: 30%; float: left; background-color: #f8f9fa; padding: 8px; font-weight: bold; border-right: 1px solid #ddd; }
                .info-value { width: 65%; float: left; padding: 8px; }
                .stats-table { width: 100%; border-collapse: separate; border-spacing: 10px; margin-bottom: 20px; }
                .stat-box { border: 2px solid #2c3e50; padding: 15px; text-align: center; }
                .stat-number { font-size: 20pt; font-weight: bold; color: #2c3e50; display: block; }
                .stat-label { font-size: 8pt; color: #666; text-transform: uppercase; font-weight: bold; }
                .section-title { background-color: #34495e; color: white; padding: 10px; margin: 20px 0 10px 0; font-weight: bold; }
                .content-box { border: 1px solid #ddd; padding: 15px; background-color: #fafafa; text-align: justify; }
                .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                .data-table th { background-color: #2c3e50; color: white; padding: 10px; text-align: left; font-size: 9pt; }
                .data-table td { padding: 8px; border: 1px solid #ddd; font-size: 8.5pt; }
                .signature-table { width: 100%; margin-top: 50px; }
                .sig-cell { width: 45%; text-align: center; }
                .signature-line { border-top: 2px solid #333; margin-top: 40px; padding-top: 5px; font-weight: bold; }
                .status-active { color: #28a745; font-weight: bold; }
                .status-inactive { color: #dc3545; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='header'>
                <table class='header-table'>
                    <tr>
                        <td class='logo-cell'>
                            <?php if($logoSrc): ?>
                                <img width='70' height="70" src='<?php echo $logoSrc; ?>' alt='Logo'>
                            <?php endif; ?>
                        </td>
                        <td class='company-info-cell'>
                            <div class='company-name'>SENA</div>
                            <div class='company-details'>Sistema de Gestión de Procesos</div>
                            <div class='company-details'>Responsable: <?php echo $_SESSION['user']['email']; ?></div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class='report-title'>Reporte General de Procesos</div>

            <div class='report-info'>
                <div class='info-row'>
                    <div class='info-label'>Fecha de Emisión:</div>
                    <div class='info-value'><?php echo $datosInforme['fecha_registro']; ?></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Generado por:</div>
                    <div class='info-value'><?php echo $_SESSION['user']['username']; ?> (<?php echo $_SESSION['user']['documento']; ?>)</div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Código de Reporte:</div>
                    <div class='info-value'><?php echo $datosInforme['id_generado']; ?></div>
                </div>
            </div>

            <table class='stats-table'>
                <tr>
                    <td class='stat-box'>
                        <span class='stat-number'><?php echo $totalProcesos; ?></span>
                        <span class='stat-label'>Total de Procesos</span>
                    </td>
                    <td class='stat-box'>
                        <span class='stat-number' style="color: #28a745;"><?php echo $totalActivos; ?></span>
                        <span class='stat-label'>Activos (<?php echo $porcentajeActivos; ?>%)</span>
                    </td>
                    <td class='stat-box'>
                        <span class='stat-number' style="color: #dc3545;"><?php echo $totalInactivos; ?></span>
                        <span class='stat-label'>Inactivos (<?php echo $porcentajeInactivos; ?>%)</span>
                    </td>
                </tr>
            </table>

            <div class='section-title'>1. RESUMEN EJECUTIVO</div>
            <div class='content-box'>
                A la fecha del reporte, se encuentran registrados un total de <strong><?php echo $totalProcesos; ?></strong> procesos dentro del sistema. 
                El análisis de disponibilidad indica que el <strong><?php echo $porcentajeActivos; ?>%</strong> de los procesos operativos se encuentran en estado 
                <strong>ACTIVO</strong>, mientras que un <strong><?php echo $porcentajeInactivos; ?>%</strong> permanecen inactivos o suspendidos.
            </div>

            <div class='section-title'>2. DETALLE DE PROCESOS REGISTRADOS</div>
            <table class='data-table'>
                <thead>
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th style="width: 25%;">Nombre del Proceso</th>
                        <th style="width: 40%;">Descripción</th>
                        <th style="width: 15%;">Fecha Creación</th>
                        <th style="width: 10%;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataParaTabla as $proceso): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($proceso['id_proceso']); ?></td>
                        <td><strong><?php echo htmlspecialchars($proceso['nombre']); ?></strong></td>
                        <td><?php echo htmlspecialchars($proceso['descripcion']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($proceso['fecha_creacion'])); ?></td>
                        <td class="<?php echo ($proceso['estado'] == 1) ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ($proceso['estado'] == 1) ? 'ACTIVO' : 'INACTIVO'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <table class='signature-table'>
                <tr>
                    <td class='sig-cell'>
                        <div class='signature-line'>Firma Responsable</div>
                        <div><?php echo $_SESSION['user']['username']; ?></div>
                    </td>
                    <td style="width: 10%;"></td>
                    <td class='sig-cell'>
                        <div class='signature-line'>Sello de Oficina</div>
                        <div>Área de Gestión Organizacional</div>
                    </td>
                </tr>
            </table>
        </body>
        </html>
<?php
        $html = ob_get_clean();

        // Configuración de Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true); // Útil si cargas imágenes externas
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Limpiar buffer y enviar PDF
        if (ob_get_length()) ob_end_clean();
        $dompdf->stream("Reporte_Procesos_SENA.pdf", ["Attachment" => true]);
        exit;
    } else {
        http_response_code(500);
        echo "Error: No se pudieron obtener los datos de los procesos o registrar el informe.";
    }
}