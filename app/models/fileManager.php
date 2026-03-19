<?php

require_once __DIR__ . '/baseHelper.php';

class FileManager extends BaseHelper {
    
    private $maxArchivos = 3;
    private $maxTamano = 10485760; // 10MB
    private $extensionesPermitidas = [
        'jpg', 'jpeg', 'png', 'webp', 'mp4', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'
    ];

    public function guardarArchivosCaso($idCaso, $archivos) {
        // 1. Validar cantidad
        $cantidad = count($archivos['name']);
        if ($cantidad > $this->maxArchivos) {
            throw new Exception("Máximo {$this->maxArchivos} archivos permitidos.");
        }

        // 2. Definir ruta: uploads/casos/caso#12/
        $directorioDestino = __DIR__ . "/../uploads/casos/caso#{$idCaso}/";
        
        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0755, true);
        }

        $resultados = [];

        for ($i = 0; $i < $cantidad; $i++) {
            if ($archivos['error'][$i] !== UPLOAD_ERR_OK) continue;

            $nombreOriginal = $archivos['name'][$i];
            $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            
            // Validaciones básicas
            if ($archivos['size'][$i] > $this->maxTamano) throw new Exception("Archivo $nombreOriginal muy pesado.");
            if (!in_array($extension, $this->extensionesPermitidas)) throw new Exception("Formato de $nombreOriginal no permitido.");

            // Nombre único y rutas
            $nombreUnico = "archivo_" . uniqid() . "." . $extension;
            $rutaFisica = $directorioDestino . $nombreUnico;
            $rutaBD = "uploads/casos/caso#{$idCaso}/" . $nombreUnico;

            // Mover archivo
            if (move_uploaded_file($archivos['tmp_name'][$i], $rutaFisica)) {
                
                // 3. Guardar en BD (Sin SP por ahora, usamos SQL directo vía BaseHelper)
                $sql = "sp_insertar_archivo_caso";
                $tipo = $this->determinarTipo($archivos['type'][$i]);
                
                $data = [
                    ['value' => $idCaso, 'type' => PDO::PARAM_INT],
                    ['value' => $nombreOriginal, 'type' => PDO::PARAM_STR],
                    ['value' => $rutaBD, 'type' => PDO::PARAM_STR],
                    ['value' => $tipo, 'type' => PDO::PARAM_STR]
                ];

                $this->insertOrUpdateData($sql, $data);
                $resultados[] = $nombreOriginal;
            }
        }
        return $resultados;
    }

    private function determinarTipo($mime) {
        if (strpos($mime, 'image/') === 0) return 'imagen';
        if (strpos($mime, 'video/') === 0) return 'video';
        return 'documento';
    }
}