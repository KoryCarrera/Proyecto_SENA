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

// Verificamos si la petición es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Llamamos solo las funciones que sabemos que funcionan
    $usuariosListados = listarUsuarios($pdo);
    $datosInforme = registrarInforme($pdo, $_SESSION['user']['documento'], 'PDF', 'Reporte Usuarios');

    // Validamos que tengamos la lista de usuarios y el registro del informe
    if ($usuariosListados && isset($usuariosListados['data']) && $datosInforme) {

        $totalUsuarios = count($usuariosListados['data']);
        $totalActivos = 0;
        $totalInactivos = 0;
        $totalAdministradores = 0;
        $totalComisionados = 0;

        foreach ($usuariosListados['data'] as $user) {
            // Conteo por Estado (según tu dump: 1 es Activo)
            if ($user['id_estado'] == 1) {
                $totalActivos++;
            } else {
                $totalInactivos++;
            }

            // Conteo por Rol (según tu dump: 1 Admin, 2 Comisionado)
            if ($user['id_rol'] == 1) {
                $totalAdministradores++;
            } elseif ($user['id_rol'] == 2) {
                $totalComisionados++;
            }
        }

        // Calculamos porcentajes con base al total
        $porcentajeActivos = ($totalUsuarios > 0) ? number_format((($totalActivos / $totalUsuarios) * 100), 1) : 0;
        $porcentajeInactivos = ($totalUsuarios > 0) ? number_format((($totalInactivos / $totalUsuarios) * 100), 1) : 0;

        // Limitamos la tabla para el PDF a los últimos 5 para que no se desborde
        $dataParaTabla = array_slice($usuariosListados['data'], 0, 5);

        // Convertimos la imagen a base64
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
            <title>Reporte de Usuarios</title>
            <style>
                @page { margin: 2cm 1.5cm; size: A4; }
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; font-size: 10pt; color: #333; line-height: 1.5; padding: 1cm; }
                .header { width: 100%; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; margin-bottom: 25px; }
                .header-table { width: 100%; border-collapse: collapse; }
                .logo-cell { width: 70px; }
                .company-info-cell { text-align: right; vertical-align: top; }
                .company-name { font-size: 16pt; font-weight: bold; color: #2c3e50; }
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
                            <div class='company-details'>Email: <?php echo $_SESSION['user']['email']; ?></div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class='report-title'>Reporte de Usuarios</div>

            <div class='report-info'>
                <div class='info-row'>
                    <div class='info-label'>Fecha:</div>
                    <div class='info-value'><?php echo $datosInforme['fecha_registro']; ?></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Responsable:</div>
                    <div class='info-value'><?php echo $_SESSION['user']['username']; ?></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Código Reporte:</div>
                    <div class='info-value'><?php echo $datosInforme['id_generado']; ?></div>
                </div>
            </div>

            <table class='stats-table'>
                <tr>
                    <td class='stat-box'>
                        <span class='stat-number'><?php echo $totalUsuarios; ?></span>
                        <span class='stat-label'>Total Usuarios</span>
                    </td>
                    <td class='stat-box'>
                        <span class='stat-number'><?php echo $totalActivos; ?></span>
                        <span class='stat-label'>Activos (<?php echo $porcentajeActivos; ?>%)</span>
                    </td>
                </tr>
                <tr>
                    <td class='stat-box'>
                        <span class='stat-number'><?php echo $totalInactivos; ?></span>
                        <span class='stat-label'>Inactivos (<?php echo $porcentajeInactivos; ?>%)</span>
                    </td>
                    <td class='stat-box'>
                        <span class='stat-number'><?php echo ($totalAdministradores + $totalComisionados); ?></span>
                        <span class='stat-label'>Personal Staff</span>
                    </td>
                </tr>
            </table>

            <div class='section-title'>1. RESUMEN EJECUTIVO</div>
            <div class='content-box'>
                Se reporta un total de <strong><?php echo $totalUsuarios; ?></strong> usuarios registrados. 
                De estos, <strong><?php echo $totalAdministradores; ?></strong> operan como administradores y 
                <strong><?php echo $totalComisionados; ?></strong> como comisionados. 
                La tasa de disponibilidad actual es del <strong><?php echo $porcentajeActivos; ?>%</strong>.
            </div>

            <div class='section-title'>2. ÚLTIMOS REGISTROS</div>
            <table class='data-table'>
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Rol</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataParaTabla as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['documento']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo ($usuario['id_rol'] == 1) ? 'Administrador' : 'Comisionado'; ?></td>
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
                        <div class='signature-line'>Firma Supervisor</div>
                        <div>Área Administrativa</div>
                    </td>
                </tr>
            </table>
        </body>
        </html>
<?php
        $html = ob_get_clean();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Reporte_Usuarios_SENA.pdf", ['Attachment' => true]);
        exit;
    }
}

// Si falla algo
http_response_code(500);
echo "Error: No se pudieron obtener los datos necesarios para el PDF.";