<?php

/**
 * Procesa y guarda múltiples archivos adjuntos a un caso
 * 
 * @param PDO $pdo Conexión a la base de datos
 * @param int $idCaso ID del caso al que pertenecen los archivos
 * @param array $archivos Array $_FILES['archivos']
 * @return array ['success' => bool, 'mensaje' => string, 'archivos' => array]
 */
function procesarArchivos($pdo, $idCaso, $archivos)
{
    // Directorio base para uploads
    $directorioBase = __DIR__ . '/../../uploads/casos/';
    
    // Crear estructura de directorios por año/mes
    $anio = date('Y');
    $mes = date('m');
    $directorioDestino = $directorioBase . $anio . '/' . $mes . '/';
    
    // Crear directorios si no existen
    if (!file_exists($directorioDestino)) {
        if (!mkdir($directorioDestino, 0755, true)) {
            return [
                'success' => false,
                'mensaje' => 'No se pudo crear el directorio de destino',
                'archivos' => []
            ];
        }
    }
    
    // Configuración
    $maxArchivos = 3;
    $maxTamano = 10 * 1024 * 1024; // 10MB
    $extensionesPermitidas = [
        // Imágenes
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        // Videos
        'mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv',
        // Documentos
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv'
    ];
    
    $cantidadArchivos = count($archivos['name']);
    
    // Validar cantidad
    if ($cantidadArchivos > $maxArchivos) {
        return [
            'success' => false,
            'mensaje' => "Máximo {$maxArchivos} archivos permitidos",
            'archivos' => []
        ];
    }
    
    $archivosGuardados = [];
    $errores = [];
    
    // Procesar cada archivo
    for ($i = 0; $i < $cantidadArchivos; $i++) {
        
        // Saltar si hay error en el archivo
        if ($archivos['error'][$i] !== UPLOAD_ERR_OK) {
            $errores[] = "Error al subir {$archivos['name'][$i]}";
            continue;
        }
        
        $nombreOriginal = $archivos['name'][$i];
        $tamano = $archivos['size'][$i];
        $tmpName = $archivos['tmp_name'][$i];
        $tipoMime = $archivos['type'][$i];
        
        // Validar tamaño
        if ($tamano > $maxTamano) {
            $errores[] = "{$nombreOriginal} supera el tamaño máximo de 10MB";
            continue;
        }
        
        // Obtener extensión
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        
        // Validar extensión
        if (!in_array($extension, $extensionesPermitidas)) {
            $errores[] = "{$nombreOriginal} tiene una extensión no permitida";
            continue;
        }
        
        // Generar nombre único para evitar colisiones
        $nombreUnico = 'caso_' . $idCaso . '_' . uniqid() . '.' . $extension;
        $rutaCompleta = $directorioDestino . $nombreUnico;
        
        // Mover archivo
        if (move_uploaded_file($tmpName, $rutaCompleta)) {
            
            // Ruta relativa para guardar en BD (sin __DIR__)
            $rutaBD = '/uploads/casos/' . $anio . '/' . $mes . '/' . $nombreUnico;
            
            // Determinar tipo de archivo
            $tipoArchivo = 'documento'; // Por defecto
            if (strpos($tipoMime, 'image/') === 0) {
                $tipoArchivo = 'imagen';
            } elseif (strpos($tipoMime, 'video/') === 0) {
                $tipoArchivo = 'video';
            }
            
            // Guardar en base de datos
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO archivos (id_caso, nombre_archivo, ruta, tipo_archivo, fecha_subida)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $idCaso,
                    $nombreOriginal,
                    $rutaBD,
                    $tipoArchivo
                ]);
                
                $archivosGuardados[] = [
                    'nombre' => $nombreOriginal,
                    'ruta' => $rutaBD,
                    'tipo' => $tipoArchivo
                ];
                
            } catch (PDOException $e) {
                error_log("Error al guardar archivo en BD: " . $e->getMessage());
                $errores[] = "Error al registrar {$nombreOriginal} en la base de datos";
                
                // Eliminar archivo físico si falla la BD
                if (file_exists($rutaCompleta)) {
                    unlink($rutaCompleta);
                }
            }
            
        } else {
            $errores[] = "Error al mover {$nombreOriginal} al servidor";
        }
    }
    
    // Resultado final
    if (count($archivosGuardados) > 0) {
        return [
            'success' => true,
            'mensaje' => count($archivosGuardados) . ' archivo(s) guardado(s) correctamente',
            'archivos' => $archivosGuardados,
            'errores' => $errores
        ];
    } else {
        return [
            'success' => false,
            'mensaje' => 'No se pudo guardar ningún archivo',
            'archivos' => [],
            'errores' => $errores
        ];
    }
}