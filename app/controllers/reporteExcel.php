<?php
//Definimos los headers (instrucciones) para el navegador

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //Le decimos al navegador que le estamos dando un archivo .xlsx
header('Content-Disposition: attachment;filename="Informe_Excel_SENA.xlsx"'); //Le especificamos que lo descargue y lo haga con el nombre que definimos
header('Cache-Control: max-age=0'); //Definimos que no lo guarde en cache, que siempre genere uno nuevo
header('Expires: 0'); //Indicamos que caduca instantaneamente

ob_start(); //Activamos el buffer de salida
session_start(); //Cargamos la sesión activa

//Hacemos los require de los archivos necesarios
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../models/insertData.php";
require_once __DIR__ . "/../models/getData.php";

// Importamos todas las herramientas que necesitamos para evitar codigo ilegible
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

//Iniciamos un array vacio para mayor facilidad
$datosReporte = [];

//Ingresamos en la base de datos la generación del reporte
$datosInforme = registrarInforme($pdo, $_SESSION['user']['documento'], 'EXCEL', null);

//En el array vacio asignamos el resultado de la consulta 
$datosReporte = tablaBaseExcel($pdo);

//Asignamos la clase a una variable con el constructor vacio
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet(); //Capturamos la hoja activa
$sheet->setTitle("Tabla Base"); //definimos el nombre de la hoja que capturamos más arriba

$sheet->mergeCells('B2:K2'); //Combinamos y centramos de la celda B2 a la K2 para definir el titulo de la pestaña
$sheet->setCellValue('B2', 'INFORME DE GESTIÓN SENA'); //Ingresamos lo que ira en esas celdas mergeadas
$sheet->mergeCells('B3:K3'); //Combinamos y centramos una nueva celda para ingresar datos relacionados al reporte
$sheet->setCellValue('B3', 'ID de reporte: ' . $datosInforme['id_generado']. ' Fecha de generación: ' . $datosInforme['fecha_registro']); //Insertamos los datos de auditoria en el documento (ID y fecha)

$estiloTitulo = [ //En un array definimos las parametros de estilos
    'font' => [
        'bold' => true,
        'size' => 14,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '39A900'],
    ],
    'borders' => [
        'outline' => ['borderStyle' => Border::BORDER_THIN],
    ],
];

$estiloSubTitulo = [ //Definimos los estilos de la segunda celda combinada y centrada
    'font' => [
        'size' => 14,
        'color' => ['rgb' => '000000'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'outline' => ['borderStyle' => Border::BORDER_THIN],
        'color' => ['rgb' => '000000'],
    ],
];

//Aplicamos los estilos con el metodo aplyFromArray

$sheet->getStyle('B2:K2')->applyFromArray($estiloTitulo);
$sheet->getStyle('B3:K3')->applyFromArray($estiloSubTitulo);


$encabezados = ['Documento', 'Id', 'Comisionado', 'Estado Usuario', 'Mes', 'Tipo', 'Estado', 'Proceso', 'Fecha Registro', 'Fecha Radicado']; //Definimos las cabeceras de las tablas
$sheet->fromArray($encabezados, NULL, 'B4'); //Los aplicamos a la hoja que tenemos activa

$estiloEncabezado = [ //Definimos los estilos de las cabeceras de nuestra tabla base
    'font' => [
        'bold' => true,
        'color' => ['rgb' => '000000'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '39A900'],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$sheet->getStyle('B4:K4')->applyFromArray($estiloEncabezado); //Aplicamos el estilo
$sheet->setAutoFilter('B4:K4'); //Aplicamos la herramienta filtro automatico de Excel


if (!empty($datosReporte) && is_array($datosReporte)) { //Validamos los datos que nos llegaron de la BD

    $sheet->fromArray($datosReporte, NULL, 'B5'); //Inserta los datos del array empezando por la B5 (Si hay data null deja la celda vacia sin más);

    $numeroDeFilas = count($datosReporte); //Contamos las filas que nos regresó el array
    $ultimaFila = 4 + $numeroDeFilas; //Encontramos la ultima fila
    $rangoCuerpo = 'B5:K' . $ultimaFila; //Definimos el rango del cuerpo de la tabla

    $estiloDatos = [ //Definimos los estilos de el cuerpo de la tabla
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];
    $sheet->getStyle($rangoCuerpo)->applyFromArray($estiloDatos); //Aplicamos el estilo con el rango que obtuvimos

    $sheet->getStyle('B5:B' . $ultimaFila)
        ->getNumberFormat()
        ->setFormatCode(NumberFormat::FORMAT_TEXT); //Formato especial para mostrar correctamente el documento

} else { //En caso de que no haya datos se mostrará esto
    $sheet->setCellValue('B5', 'No se encontraron registros en la base de datos.');
    $sheet->mergeCells('B5:K5');
    $sheet->getStyle('B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}


foreach (range('B', 'K') as $col) { //Aqui reiteramos la opcion en cada celda
    $sheet->getColumnDimension($col)->setAutoSize(true); //Definimos AutoSize para que textos largos se vean bien
}


if (ob_get_contents()) ob_end_clean(); //Vacia el buffer de salida para evitar corrupciones en el binario del archivo


$writer = new Xlsx($spreadsheet); //Genera el archivo con lo definido
$writer->save('php://output'); //Lo enviamos al navegador

//Matamos el script para que se detenga
exit;
