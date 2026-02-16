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
require_once __DIR__ . "/../models/insertData.php";
require_once __DIR__ . "/../models/getData.php";

//referenciar dompdf
use Dompdf\Dompdf;

//Recibimos los datos del front
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //Llamamos las funciones necesarias
    $usuariosPorRol = usuariosPorRol($pdo);
    $usuariosPorEstado = usuariosPorEstado($pdo);
    $usuariosListados = listarUsuarios($pdo);
    $datosInforme = registrarInforme($pdo, $_SESSION['user']['documento'], 'PDF', 'Reporte Usuarios');

    //Validamos el retorno datos de todas las funciones
    if ($usuariosPorRol && $usuariosPorEstado && $usuariosListados && $datosInforme) {

        // Procesamos usuarios por ROL
        $totalAdministradores = 0;
        $totalComisionados = 0;

        if (isset($usuariosPorRol['rol']) && is_array($usuariosPorRol['rol'])) {
            foreach ($usuariosPorRol['rol'] as $index => $nombreRol) {
                $rolNormalizado = strtolower(trim($nombreRol));
                
                if (strpos($rolNormalizado, 'administrador') !== false) {
                    $totalAdministradores = (int)$usuariosPorRol['usuarios'][$index];
                } 
                elseif (strpos($rolNormalizado, 'comisionado') !== false) {
                    $totalComisionados = (int)$usuariosPorRol['usuarios'][$index];
                } 
            }
        }

        // Procesamos usuarios por ESTADO
        $totalActivos = 0;
        $totalInactivos = 0;

        if (isset($usuariosPorEstado['estado']) && is_array($usuariosPorEstado['estado'])) {
            foreach ($usuariosPorEstado['estado'] as $index => $nombreEstado) {
                $estadoNormalizado = trim($nombreEstado);
                
                if (strpos($estadoNormalizado, 'Habilitado') !== false && strpos($estadoNormalizado, 'inhabilitado') === false) {
                    $totalActivos = (int)$usuariosPorEstado['usuarios'][$index];
                } 
                elseif (strpos($estadoNormalizado, '') !== false) {
                    $totalInactivos = (int)$usuariosPorEstado['usuarios'][$index];
                }
            }
        }

        //Se limita a los ultimos 20 usuarios
        $usuariosListados['data'] = array_slice($usuariosListados['data'], 0, 5);

        //Conseguimos porcentajes
        $porcentajeActivos = ($totalActivos > 0 && $usuariosPorEstado['total'] > 0) 
            ? number_format((($totalActivos / $usuariosPorEstado['total']) * 100), 1) 
            : 0;
            
        $porcentajeInactivos = ($totalInactivos > 0 && $usuariosPorEstado['total'] > 0) 
            ? number_format((($totalInactivos / $usuariosPorEstado['total']) * 100), 1) 
            : 0;

        //Convertimos la imagen a base64
        $logoPath = __DIR__ . '/../../Public/assets/img/logo_sena.png';

        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        } else {
            $logoSrc = '';
        }
        
        //Usamos las funciones reservadas de ob para obtener el html
        ob_start();
?>
        <!DOCTYPE html>
        <html lang='es'>

        <head>
            <meta charset='UTF-8'>
            <title>Reporte de Usuarios</title>
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

                /* --- ESTADÍSTICAS --- */
                .stats-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0 15px;
                    margin-bottom: 30px;
                }

                .stat-box-td {
                    width: 48%;
                    border: 2px solid #2c3e50;
                    padding: 15px 10px;
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

                .status-activo {
                    background-color: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }

                .status-inactivo {
                    background-color: #f8d7da;
                    color: #721c24;
                    border: 1px solid #f5c6cb;
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
                                <img width='70px' height="70px" src='<?php echo $logoSrc; ?>' alt='Logo SENA'>
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
                Reporte de Usuarios
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
                        <span class='stat-number'><?php echo $usuariosPorEstado['total']; ?></span>
                        <span class='stat-label'>Total de Usuarios</span>
                    </td>
                    <td class='stat-gap-td'>&nbsp;</td>
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalActivos; ?></span>
                        <span class='stat-label'>Usuarios Activos</span>
                    </td>
                </tr>
                <tr style='height: 15px;'>
                    <td colspan='3'></td>
                </tr>
                <tr>
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalInactivos; ?></span>
                        <span class='stat-label'>Usuarios Inactivos</span>
                    </td>
                    <td class='stat-gap-td'>&nbsp;</td>
                    <td class='stat-box-td'>
                        <span class='stat-number'><?php echo $totalAdministradores + $totalComisionados; ?></span>
                        <span class='stat-label'>Personal SENA</span>
                    </td>
                </tr>
            </table>
            <br>
            <div class='section-title'>1. RESUMEN EJECUTIVO</div>
            <br>
            <div class='content-box'>
                Durante el periodo analizado, se registró un total de <strong><?php echo $usuariosPorEstado['total']; ?></strong> usuarios en el sistema para el año <strong><?php echo date('Y'); ?></strong>.
                De este total, el <strong><?php echo $porcentajeActivos; ?>%</strong> corresponde a usuarios en estado <strong>activo</strong>,
                mientras que el <strong><?php echo $porcentajeInactivos; ?>%</strong> restante se encuentra en estado <strong>inactivo</strong>.
                En cuanto a la distribución por roles, se identifican <strong><?php echo $totalAdministradores; ?></strong> administradores,
                <strong><?php echo $totalComisionados; ?></strong> comisionados registrados en la plataforma.
            </div>
            <br>
            <br>
            <br>
            <br>
            <div class='section-title'>2. Últimos 20 Usuarios Registrados</div>
            <table class='data-table'>
                <thead>
                    <tr>
                        <th style='width: 20%;'>Documento</th>
                        <th style='width: 30%;'>Nombre Completo</th>
                        <th style='width: 30%;'>Email</th>
                        <th style='width: 20%;'>Rol</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($usuariosListados['data'] as $usuario) {
                        echo "
                    <tr>
                        <td>" . htmlspecialchars($usuario['documento']) . "</td>
                        <td>" . htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) . "</td>
                        <td>" . htmlspecialchars($usuario['email']) . "</td>
                        <td>" . htmlspecialchars($usuario['id_rol']) . "</td>
                    </tr> ";
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

        //Inicializamos y usamos la clase de Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Reporte_Usuarios_SENA.pdf", ['Attachment' => true]);
        exit;
    }
}

http_response_code(500);
echo "Error al generar el reporte";