-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db_sena
-- Tiempo de generación: 10-02-2026 a las 01:39:14
-- Versión del servidor: 10.6.24-MariaDB-ubu2204
-- Versión de PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyectosena_db`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_estado_caso` (IN `p_id_caso` INT, IN `p_id_estado` INT)   BEGIN
    IF NOT EXISTS (SELECT 1 FROM caso WHERE id_caso = p_id_caso) THEN
		SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'EL caso no existe';
	END IF;
    
    UPDATE caso SET id_estado = p_id_estado WHERE id_caso = p_id_caso;
    
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_analisis_demanda` ()   BEGIN
    SELECT
        tc.nombre_caso,

        COUNT(c.id_caso) AS total_casos_ultimo_mes,

        (SELECT COUNT(*)
         FROM caso
         WHERE DATE(fecha_inicio) = CURDATE()
        ) AS total_casos_hoy,

        (SELECT COUNT(*)
         FROM caso c2
         JOIN estado e2 ON e2.id_estado = c2.id_estado
         WHERE e2.estado = 'NO ATENDIDO'
           AND TIMESTAMPDIFF(HOUR, c2.fecha_inicio, NOW()) > 168
           AND c2.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
        ) AS casos_criticos

    FROM caso c
    JOIN tipo_caso tc ON tc.id_tipo_caso = c.id_tipo_caso
    WHERE c.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    GROUP BY tc.id_tipo_caso, tc.nombre_caso
    ORDER BY total_casos_ultimo_mes DESC
    LIMIT 5;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_buscar_usuario` (IN `p_documento` VARCHAR(50))   BEGIN

SELECT u.documento, CONCAT(u.nombre, ' ', u.apellido) as nombre, u.email, r.rol FROM usuario u INNER JOIN rol r ON u.id_rol = r.id_rol WHERE documento = p_documento;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cambiar_estado_usuario` (IN `p_documento` VARCHAR(50), IN `p_estado` INT)   BEGIN 	

UPDATE usuario SET id_estado = p_estado WHERE documento = p_documento;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_caracterizacion_usuarios` ()   BEGIN
    SELECT
        r.nombre_rol,
        COUNT(c.id_caso) AS total_pqrsd_mes
    FROM caso c
    JOIN usuario u ON u.documento = c.documento
    JOIN rol r ON r.id_rol = u.id_rol
    WHERE u.id_rol = 2
      AND c.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    GROUP BY r.id_rol, r.nombre_rol;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_casos_por_comisionado` ()   BEGIN

SELECT 
    CONCAT(u.nombre, ' ', u.apellido) AS comisionado,
    COUNT(c.id_caso) AS total_casos
FROM usuario u
LEFT JOIN caso c ON u.documento = c.documento
WHERE u.id_rol = 2
GROUP BY u.documento, u.nombre, u.apellido
ORDER BY total_casos DESC;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_casos_por_estado` ()   BEGIN

SELECT 
    e.estado AS nombre_estado,
    COUNT(c.id_caso) AS total_casos,
    (SELECT COUNT(*) FROM caso WHERE YEAR(fecha_inicio) = YEAR(CURDATE())) AS gran_total
FROM caso c
JOIN estado e ON c.id_estado = e.id_estado
WHERE YEAR(c.fecha_inicio) = YEAR(CURDATE())
GROUP BY e.estado
ORDER BY total_casos DESC;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_casos_por_mes` ()   BEGIN

SELECT 
    MONTH(fecha_inicio) AS mes,
    COUNT(*) AS total_casos
FROM caso
GROUP BY MONTH(fecha_inicio)
ORDER BY mes;


END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_casos_por_proceso` ()   BEGIN

SELECT 
    p.nombre AS proceso,
    COUNT(c.id_caso) AS total_casos
FROM procesoorganizacional p
LEFT JOIN caso c ON c.id_proceso = p.id_proceso
GROUP BY p.id_proceso, p.nombre
ORDER BY total_casos DESC;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_contear_casos_tipo` ()   BEGIN

SELECT 
    tc.nombre_caso,
    COUNT(c.id_caso) AS total
FROM caso c
INNER JOIN tipo_caso tc ON c.id_tipo_caso = tc.id_tipo_caso
GROUP BY tc.nombre_caso
ORDER BY tc.nombre_caso;

END$$

CREATE DEFINER=`root`@`%` PROCEDURE `sp_desactivar_proceso` (IN `p_id_proceso` INT)   BEGIN 
	UPDATE procesoorganizacional SET estado = 0 WHERE id_proceso = p_id_proceso;
END$$

CREATE DEFINER=`root`@`%` PROCEDURE `sp_editar_usuario` (IN `p_documento` VARCHAR(50), IN `p_nombre` VARCHAR(100), IN `p_apellido` VARCHAR(100), IN `p_email` VARCHAR(150), IN `p_rol` INT, IN `p_contraseña` VARCHAR(255))   BEGIN
    UPDATE usuario 
    SET 
        nombre = p_nombre,
        apellido = p_apellido,
        email = p_email,
        id_rol = p_rol,
        contraseña = CASE 
            WHEN p_contraseña IS NULL OR p_contraseña = '' THEN contraseña 
            ELSE p_contraseña 
        END
    WHERE documento = p_documento;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listar_casos` ()   BEGIN
	SELECT 
			c.id_caso,
			CONCAT(u.nombre, ' ', u.apellido) AS comisionado,
			c.fecha_inicio,
			c.fecha_cierre,
			e.estado AS estado,
			t.nombre_caso AS tipo_caso,
			p.nombre AS proceso,
			c.descripcion
		FROM caso c
		LEFT JOIN usuario u ON c.documento = u.documento
		JOIN estado e ON c.id_estado = e.id_estado
		JOIN tipo_caso t ON c.id_tipo_caso = t.id_tipo_caso
		JOIN procesoorganizacional p ON c.id_proceso = p.id_proceso
		ORDER BY c.fecha_inicio DESC LIMIT 20;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listar_caso_por_comisionado` (IN `p_documento` VARCHAR(50))   BEGIN
    SELECT 
			c.id_caso,
			CONCAT(u.nombre, ' ', u.apellido) AS comisionado,
			c.fecha_inicio,
			c.fecha_cierre,
			e.estado AS estado,
			t.nombre_caso AS tipo_caso,
			p.nombre AS proceso,
			c.descripcion
		FROM caso c
		JOIN usuario u ON c.documento = u.documento
		JOIN estado e ON c.id_estado = e.id_estado
		JOIN tipo_caso t ON c.id_tipo_caso = t.id_tipo_caso
		JOIN procesoorganizacional p ON c.id_proceso = p.id_proceso
        WHERE u.documento = p_documento
		ORDER BY c.fecha_inicio DESC LIMIT 30;
    END$$

CREATE DEFINER=`root`@`%` PROCEDURE `sp_listar_proceso_organizacional` ()   BEGIN 
	SELECT p.id_proceso, p.nombre AS nombre_proceso, p.descripcion, p.fecha_creacion, p.estado, CONCAT(u.nombre, ' ', u.apellido) AS nombre_creador, u.documento, u.email
 	FROM procesoorganizacional p INNER JOIN usuario u ON p.documento_usuario = u.documento ORDER BY p.fecha_creacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listar_seguimientos_por_caso` (`p_caso` INT)   BEGIN
    
    SELECT 
        s.id_seguimiento,
        s.fecha_seguimiento,
        s.observacion
    FROM seguimiento s
    WHERE s.id_caso = p_caso
    ORDER BY s.fecha_seguimiento DESC
    LIMIT 20;
    
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listar_usuarios` ()   BEGIN

 SELECT documento, nombre, apellido, email, id_rol, id_estado FROM usuario;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_login_usuario` (IN `p_documento` VARCHAR(50))   BEGIN
    SELECT
        documento,
        CONCAT(nombre, ' ', apellido) AS     
        username,
        email,
        id_rol,
        contraseña
    FROM
        usuario
    WHERE
        documento = p_documento COLLATE utf8mb4_general_ci 
AND id_estado = 1 
        LIMIT 1;

    IF FOUND_ROWS() > 0 THEN
        UPDATE usuario
        SET ultimo_inicio_sesion = NOW()
        WHERE documento = p_documento COLLATE utf8mb4_general_ci;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_obtener_caso_por_id` (IN `p_id_caso` INT)   BEGIN
    SELECT 
        c.id_caso,
        CONCAT(u.nombre, ' ', u.apellido) AS comisionado,
        c.fecha_inicio,
        c.fecha_cierre,
        e.estado AS estado,
        t.nombre_caso AS tipo_caso,
        p.nombre AS proceso,
        c.descripcion
    FROM caso c
    LEFT JOIN usuario u ON c.documento = u.documento
    INNER JOIN estado e ON c.id_estado = e.id_estado
    INNER JOIN tipo_caso t ON c.id_tipo_caso = t.id_tipo_caso
    INNER JOIN procesoorganizacional p ON c.id_proceso = p.id_proceso
    WHERE c.id_caso = p_id_caso
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`%` PROCEDURE `sp_reactivar_proceso` (IN `p_id_proceso` INT)   BEGIN 
    UPDATE procesoorganizacional 
    SET estado = 1 
    WHERE id_proceso = p_id_proceso;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_caso` (IN `p_documento` VARCHAR(20), IN `p_id_proceso` INT(11), IN `p_id_estado` INT(11), IN `p_id_tipo_caso` INT(11), IN `p_descripcion` TEXT, IN `p_fecha_inicio` DATE)   BEGIN 

INSERT INTO caso (documento, fecha_inicio, id_proceso, id_estado, id_tipo_caso, descripcion) 
VALUES (p_documento, p_fecha_inicio, p_id_proceso, p_id_estado, p_id_tipo_caso, p_descripcion);

SELECT 
        c.id_caso,
        CONCAT(u.nombre, ' ', u.apellido) AS comisionado,
        c.fecha_inicio,
        c.fecha_cierre,
        e.estado AS estado,
        t.nombre_caso AS tipo_caso,
        p.nombre AS proceso,
        c.descripcion
    FROM caso c
    LEFT JOIN usuario u ON c.documento = u.documento
    JOIN estado e ON c.id_estado = e.id_estado
    JOIN tipo_caso t ON c.id_tipo_caso = t.id_tipo_caso
    JOIN procesoorganizacional p ON c.id_proceso = p.id_proceso
    WHERE c.id_caso = LAST_INSERT_ID();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_informe` (IN `p_documento` VARCHAR(50), IN `p_formato` VARCHAR(10), IN `p_contenido` TEXT)   BEGIN



    DECLARE v_fecha_actual DATETIME;

    DECLARE v_ultimo_id INT;



    SET v_fecha_actual = NOW();



    INSERT INTO informe (documento, fecha_generacion, tipo_informe, contenido)

    VALUES (p_documento, v_fecha_actual, p_formato, p_contenido);

    

    SET v_ultimo_id = LAST_INSERT_ID();



    SELECT v_ultimo_id AS id_generado, v_fecha_actual AS fecha_registro;



END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_monitoreo` (IN `p_documento` VARCHAR(50), IN `p_tipo` ENUM('inicio_sesion','accion'), IN `p_descripcion` TEXT)   BEGIN
INSERT INTO usuario (documento, fecha, tipo, descripcion) VALUES (p_documento, NOW(), p_tipo, p_descripcion);
END$$

CREATE DEFINER=`root`@`%` PROCEDURE `sp_registrar_proceso_organizacional` (IN `p_descripcion` TEXT, IN `p_nombre` VARCHAR(100), IN `p_documento_usuario` VARCHAR(50))   BEGIN
    INSERT INTO procesoorganizacional (descripcion, nombre, documento_usuario)
    VALUES (p_descripcion, p_nombre, p_documento_usuario);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_seguimiento` (`p_observacion` TEXT, `p_caso` INT)   BEGIN
    
    IF NOT EXISTS (SELECT 1 FROM caso WHERE p_caso = caso.id_caso)
    	THEN SIGNAL SQLSTATE '45000'
        	SET MESSAGE_TEXT = 'No existe el caso buscado.';
	END IF;
    
    INSERT INTO seguimiento (fecha_seguimiento, observacion, id_caso) 
    VALUES (
    	NOW(),
        p_observacion,
        p_caso
    );
    
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_usuario` (IN `p_documento` VARCHAR(50), IN `p_nombre` VARCHAR(50), IN `p_apellido` VARCHAR(50), IN `p_email` VARCHAR(100), IN `p_id_rol` INT(11), IN `p_contraseña` VARCHAR(255))   BEGIN 

INSERT INTO usuario (documento, nombre, apellido, email, id_rol, contraseña, fecha_registro, ultimo_inicio_sesion) 
VALUES (p_documento, p_nombre, p_apellido, p_email, p_id_rol, p_contraseña, NOW(), NULL);

END$$

CREATE DEFINER=`root`@`%` PROCEDURE `sp_reporte_pqrs_excel` ()   BEGIN
    DECLARE anio_actual INT;
    SET anio_actual = YEAR(CURDATE());

    SELECT
        u.documento                                           AS Documento,
        c.id_caso                                             AS Id,
        CONCAT(u.nombre, ' ', u.apellido)                     AS Comisionado,

        CASE 
            WHEN u.id_estado = 1 THEN 'Activo'
            ELSE 'Inactivo'
        END                                                   AS `Estado Usuario`,

        -- Mes en español
        CASE MONTH(c.fecha_inicio)
            WHEN 1 THEN 'enero'
            WHEN 2 THEN 'febrero'
            WHEN 3 THEN 'marzo'
            WHEN 4 THEN 'abril'
            WHEN 5 THEN 'mayo'
            WHEN 6 THEN 'junio'
            WHEN 7 THEN 'julio'
            WHEN 8 THEN 'agosto'
            WHEN 9 THEN 'septiembre'
            WHEN 10 THEN 'octubre'
            WHEN 11 THEN 'noviembre'
            WHEN 12 THEN 'diciembre'
        END                                                   AS Mes,

        tc.nombre_caso                                        AS Tipo,
        e.estado                                              AS Estado,
        po.nombre                                             AS Proceso,

        c.fecha_inicio                                        AS `Fecha de Registro`,
        c.fecha_cierre                                        AS `Fecha de Radicado`

    FROM caso c
    INNER JOIN usuario u 
        ON c.documento = u.documento

    INNER JOIN tipo_caso tc 
        ON c.id_tipo_caso = tc.id_tipo_caso

    INNER JOIN estado e 
        ON c.id_estado = e.id_estado

    INNER JOIN procesoorganizacional po 
        ON c.id_proceso = po.id_proceso

    WHERE YEAR(c.fecha_inicio) = anio_actual
    ORDER BY c.fecha_inicio ASC;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_resumen_productividad_comisionados` ()   BEGIN
    SELECT
        u.documento,
        u.nombre,
        u.apellido,

        SUM(CASE WHEN e.estado = 'ATENDIDO' THEN 1 ELSE 0 END) AS casos_atendidos,

        SUM(CASE WHEN e.estado = 'POR ATENDER' THEN 1 ELSE 0 END) AS casos_en_proceso,

        ROUND(
            IFNULL(
                (SUM(CASE WHEN e.estado = 'ATENDIDO' THEN 1 ELSE 0 END) 
                 / NULLIF(COUNT(c.id_caso), 0)) * 100,
            0),
        2) AS tasa_resolucion,

        IFNULL(
            AVG(
                CASE 
                    WHEN c.fecha_cierre IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, c.fecha_inicio, c.fecha_cierre)
                END
            ),
        0) AS tiempo_promedio_cierre_horas

    FROM usuario u
    LEFT JOIN caso c 
        ON c.documento = u.documento
       AND DATE(c.fecha_inicio) = CURDATE()
    LEFT JOIN estado e 
        ON e.id_estado = c.id_estado

    GROUP BY u.documento, u.nombre, u.apellido;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_traer_usuario` (IN `p_documento` VARCHAR(50))   BEGIN

SELECT documento, nombre, apellido, email, id_rol, id_estado FROM usuario WHERE documento = TRIM(p_documento COLLATE utf8mb4_general_ci);

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivo`
--

CREATE TABLE `archivo` (
  `id_archivo` int(11) NOT NULL COMMENT 'PK para encontrar y relacionar',
  `id_caso` int(11) NOT NULL COMMENT 'FK para relacionar caso con archivo',
  `nombre_archivo` varchar(255) NOT NULL COMMENT 'Nombre del archivo',
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de subida del archivo',
  `ruta` varchar(255) NOT NULL COMMENT 'Ruta del archivo',
  `tipo_archivo` varchar(50) NOT NULL COMMENT 'Formato del archivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caso`
--

CREATE TABLE `caso` (
  `id_caso` int(11) NOT NULL COMMENT 'PK de casos',
  `documento` varchar(20) NOT NULL COMMENT 'FK para relacionar casos y usuarios ',
  `id_proceso` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL COMMENT 'Fecha en la que se realiza el caso',
  `fecha_cierre` datetime DEFAULT NULL,
  `id_estado` int(11) NOT NULL COMMENT 'FK de la tabla estados de los casos',
  `id_tipo_caso` int(11) NOT NULL COMMENT 'FK de la tabla tipo de los casos',
  `descripcion` text NOT NULL COMMENT 'contenido de los casos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `caso`
--

INSERT INTO `caso` (`id_caso`, `documento`, `id_proceso`, `fecha_inicio`, `fecha_cierre`, `id_estado`, `id_tipo_caso`, `descripcion`) VALUES
(1, '112233', 2, '2025-12-02 15:27:39', NULL, 2, 2, 'bla bla bla bla bla bla'),
(2, '112233', 3, '2025-11-26 15:59:35', NULL, 1, 4, 'bla bla bla bla bla bla'),
(3, '123456', 1, '2018-10-10 16:01:22', NULL, 1, 3, 'blo blo blo bla bla bla'),
(4, '123456', 2, '2025-12-02 16:14:23', NULL, 3, 1, 'example example example'),
(5, '123456', 2, '2022-05-17 16:15:18', NULL, 3, 1, 'SDSADWQD QW 12 E12 1E ASD'),
(7, '11223344', 4, '2025-12-02 17:04:52', NULL, 2, 1, '3123131dasda dad w g<AW fw ege hd hgg'),
(55, '3001', 1, '2026-01-14 08:00:00', '2026-01-14 09:30:00', 1, 1, 'Caso atendido en la mañana'),
(56, '3001', 1, '2026-01-14 10:15:00', NULL, 2, 2, 'Caso en proceso'),
(57, '3002', 1, '2026-01-14 09:00:00', '2026-01-14 11:00:00', 1, 1, 'Caso cerrado correctamente'),
(58, '3002', 1, '2026-01-14 13:00:00', NULL, 3, 3, 'Caso no atendido'),
(59, '3003', 1, '2026-01-14 07:45:00', '2026-01-14 08:50:00', 1, 2, 'Caso resuelto rápidamente'),
(60, '3003', 1, '2026-01-14 14:30:00', NULL, 2, 1, 'Caso pendiente de atención'),
(61, '2222222222', 6, '2026-02-07 17:36:02', NULL, 2, 1, 'pidiendo algo importante'),
(62, '2222222222', 2, '2026-02-09 00:00:00', NULL, 2, 3, 'example caso fecha');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracionusuario`
--

CREATE TABLE `configuracionusuario` (
  `id_configuracion` int(11) NOT NULL COMMENT 'PK para encontrar y relacionar',
  `frecuencia` enum('diario','semanal','mensual') NOT NULL COMMENT 'Tipo de frecuencia que desea el usuario',
  `documento` varchar(20) NOT NULL COMMENT 'FK para relacionar ConfiguracionUsuario y Usuario',
  `preferencias` enum('sistema','gmail') NOT NULL COMMENT 'preferencia del usuario '
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `id_estado` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`id_estado`, `estado`) VALUES
(1, 'Atendido'),
(2, 'Por atender'),
(3, 'No atendido');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_usuario`
--

CREATE TABLE `estado_usuario` (
  `id_estado` tinyint(1) NOT NULL DEFAULT 1,
  `estado_usuario` varchar(30) NOT NULL DEFAULT 'habilitado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_usuario`
--

INSERT INTO `estado_usuario` (`id_estado`, `estado_usuario`) VALUES
(0, 'inhabilitado'),
(1, 'Habilitado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `informe`
--

CREATE TABLE `informe` (
  `id_informe` int(11) NOT NULL COMMENT 'PK para ubicar y relacionar',
  `documento` varchar(50) NOT NULL,
  `fecha_generacion` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora del generación del informe',
  `tipo_informe` varchar(5) NOT NULL COMMENT 'Tipo de informe por selección',
  `contenido` text DEFAULT NULL COMMENT 'contenido del informe'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `informe`
--

INSERT INTO `informe` (`id_informe`, `documento`, `fecha_generacion`, `tipo_informe`, `contenido`) VALUES
(1, '98674523', '2025-12-30 20:19:07', 'PDF', 'artjrytj'),
(2, '98674523', '2025-12-30 20:22:42', 'PDF', 'artjrytj'),
(3, '98674523', '2025-12-30 20:22:56', 'PDF', 'rethnahnjatg'),
(4, '98674523', '2025-12-30 20:24:41', 'PDF', 'dgeghehg'),
(5, '98674523', '2025-12-30 20:26:41', 'PDF', 'fhgfhfhj'),
(6, '98674523', '2025-12-30 20:33:17', 'PDF', 'awtgagawghya'),
(7, '98674523', '2025-12-30 20:35:05', 'PDF', 'asgagasd'),
(8, '98674523', '2025-12-30 20:36:47', 'PDF', 'dshrthsdf'),
(9, '98674523', '2025-12-30 20:44:24', 'PDF', 'gfjrftkj'),
(10, '98674523', '2025-12-30 20:45:30', 'PDF', 'dfjndfgjm'),
(11, '98674523', '2025-12-30 20:46:20', 'PDF', 'jredfm'),
(12, '98674523', '2025-12-30 20:50:36', 'PDF', 'tjrtjk'),
(13, '98674523', '2025-12-30 20:53:05', 'PDF', 'rehewshews'),
(14, '98674523', '2025-12-30 20:56:28', 'PDF', 'rehrh'),
(15, '98674523', '2025-12-30 21:01:18', 'PDF', 'rnenern'),
(16, '98674523', '2025-12-30 21:03:01', 'PDF', 'afjhjkvfghakg'),
(17, '98674523', '2025-12-30 21:03:53', 'PDF', 'afjhjkvfghakg'),
(18, '98674523', '2025-12-31 21:21:09', 'PDF', 'R<HN<HN<HHATJATNJTJ'),
(19, '98674523', '2026-01-07 13:55:01', 'PDF', 'example example'),
(20, '98674523', '2026-01-07 13:57:53', 'PDF', 'herhehj'),
(21, '98674523', '2026-01-07 14:00:25', 'PDF', '6jwq6j6'),
(22, '98674523', '2026-01-07 14:07:19', 'PDF', 'rethqthjt6hj'),
(23, '98674523', '2026-01-07 14:07:57', 'PDF', 'rethqthjt6hj'),
(24, '98674523', '2026-01-07 14:08:47', 'PDF', 'q5h5hq56ht'),
(25, '98674523', '2026-01-07 14:12:37', 'PDF', 'qj5jq56jq564'),
(26, '98674523', '2026-01-07 14:24:56', 'PDF', 'El sistema ha demostrado una estabilidad del 98.5% bajo carga operativa. Como recomendación técnica, se sugiere realizar una auditoría de logs quincenal para asegurar la integridad de la base de datos de casos y optimizar el proceso de cierre de incidencias pendientes. El cumplimiento de metas de atención se mantiene dentro de los umbrales institucionales establecidos para el año 2026.'),
(27, '98674523', '2026-01-22 19:27:55', 'PDF', ''),
(28, '98674523', '2026-01-22 19:37:10', 'PDF', ''),
(29, '98674523', '2026-01-22 21:03:23', 'PDF', ''),
(30, '1111111111', '2026-02-07 04:50:36', 'EXCEL', ''),
(31, '1111111111', '2026-02-07 04:51:54', 'EXCEL', ''),
(32, '1111111111', '2026-02-07 04:54:54', 'EXCEL', ''),
(33, '1111111111', '2026-02-07 04:57:12', 'PDF', 'example'),
(34, '1111111111', '2026-02-07 05:01:47', 'EXCEL', NULL),
(35, '1111111111', '2026-02-07 05:02:26', 'EXCEL', NULL),
(36, '1111111111', '2026-02-07 05:09:40', 'EXCEL', NULL),
(37, '1111111111', '2026-02-07 05:10:43', 'PDF', '111111111111111111111'),
(38, '1111111111', '2026-02-07 05:16:52', 'EXCEL', NULL),
(41, '1111111111', '2026-02-07 05:30:34', 'EXCEL', NULL),
(42, '1111111111', '2026-02-10 00:11:57', 'EXCEL', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `monitoreo`
--

CREATE TABLE `monitoreo` (
  `id_monitoreo` int(11) NOT NULL COMMENT 'Llave primaria para reconocimiento y relacion',
  `documento` varchar(20) NOT NULL COMMENT 'FK proveniente de la tabla Usuarios',
  `fecha` datetime NOT NULL,
  `tipo` enum('inicio_sesion','accion') NOT NULL COMMENT 'tipo de monitoreo por ENUM',
  `descripcion` text NOT NULL COMMENT 'Descripcion del monitoreo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id_notificacion` int(11) NOT NULL COMMENT 'PK para relacionar y encontrar',
  `documento` varchar(20) NOT NULL COMMENT 'Llave primaria de la tabla usuarios para relacionar ambas tablas',
  `mensaje` text NOT NULL COMMENT 'contenido de la notifiacion',
  `fecha` datetime NOT NULL COMMENT 'fecha de la notificación',
  `leida` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'si esta leida o no '
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `procesoorganizacional`
--

CREATE TABLE `procesoorganizacional` (
  `id_proceso` int(11) NOT NULL COMMENT 'PK para ubicar y relacionar',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha de creación del proceso',
  `descripcion` text NOT NULL COMMENT 'Corta descripción del proceso',
  `documento_usuario` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre del propio proceso',
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Estado del proceso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `procesoorganizacional`
--

INSERT INTO `procesoorganizacional` (`id_proceso`, `fecha_creacion`, `descripcion`, `documento_usuario`, `nombre`, `estado`) VALUES
(1, '2018-09-03 15:11:55', 'Proceso para las peticiones sobre mejorar los computadores', '98674523', 'Nuevos computadores', 1),
(2, '2020-07-16 15:15:14', 'Proceso para las quejas sobre la ropa de trabajo', '98674523', 'ropa de trabajo', 1),
(3, '2025-10-17 15:15:49', 'Proceso sobre la reparacion del centro', '98674523', 'reparacion del centro', 1),
(4, '2025-12-02 15:16:55', 'Proceso sobre el ruido del centro', '98674523', 'queja de ruido', 1),
(5, '2026-01-14 17:45:11', 'Gestión de PQRSD', '98674523', 'PQRSD', 1),
(6, '2026-01-14 17:45:11', 'Trámites internos administrativos', '98674523', 'TRÁMITE INTERNO', 1),
(7, '2026-01-15 22:59:08', 'Mesas para los salones', '98674523', 'example name', 1),
(8, '2026-01-19 19:11:07', 'example description', '654321', 'example name', 0),
(9, '2026-01-19 19:19:44', 'example description', '98674523', 'example name', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'administrador', 'Acceso para realizar el crud completo a excepcion de delete siendo cambiado por disable'),
(2, 'comisionado', 'Acceso limitado al crud (leer, crear y actualizar) encargado de gestion de casos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento`
--

CREATE TABLE `seguimiento` (
  `id_seguimiento` int(11) NOT NULL COMMENT 'PK para encontrar y relacionar',
  `fecha_seguimiento` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha en la que se inicia el seguimiento',
  `observacion` text NOT NULL COMMENT 'Observaciones del caso',
  `id_caso` int(11) NOT NULL COMMENT 'Relación entre seguimiento y caso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_caso`
--

CREATE TABLE `tipo_caso` (
  `id_tipo_caso` int(11) NOT NULL,
  `nombre_caso` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_caso`
--

INSERT INTO `tipo_caso` (`id_tipo_caso`, `nombre_caso`) VALUES
(1, 'peticion'),
(2, 'queja'),
(3, 'reclamo'),
(4, 'sugerencia'),
(5, 'denuncia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `documento` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL COMMENT 'Nombres (1ro y 2do) del usario',
  `apellido` varchar(50) NOT NULL COMMENT 'Apellido materno y paterno',
  `email` varchar(100) NOT NULL COMMENT 'email institucional o personal',
  `id_rol` int(11) NOT NULL COMMENT 'FK para relacionar rol del usario con la tabla rol',
  `contraseña` varchar(255) NOT NULL COMMENT 'Contraseña del usuario para su ingreso ',
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha del dia que se registró el usuario',
  `ultimo_inicio_sesion` datetime DEFAULT NULL,
  `id_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`documento`, `nombre`, `apellido`, `email`, `id_rol`, `contraseña`, `fecha_registro`, `ultimo_inicio_sesion`, `id_estado`) VALUES
('1020304010', 'Isaac', 'Carvajal', 'zacki@hotmail.com', 2, '$2y$10$XJPcDeP8NIq87Z1wuKvrreLIUkUzNqyfY1yOD0K46Bi70jMs3AImi', '2025-11-24 06:54:17', '2025-12-10 11:26:13', 1),
('1111111111', 'Admin', 'Tester', 'tester.admin@example.com', 1, '$2y$10$.ojGM8lAXRkAo9tY8JFuEOF5RJ0jrcwL05ErUzfZnaS5/fJWt6Xxq', '2026-01-24 03:14:09', '2026-02-10 01:29:57', 1),
('112233', 'pepito', 'perez', 'pepito@perez.com', 2, '$2y$10$0RrhJZXlddSMRJGTKJCs3.Vd6GpJTSgLvjb2X2mn73dRVm1oNKf9m', '2025-12-01 17:43:14', NULL, 1),
('11223344', 'Pepo', 'Peraz', 'pepito@hola.com', 2, '$2y$10$Zt/ebqk4NLWfRf0wIOaOrOPG1T4gFW0h7j11ZIkUo8yjlREos8P/a', '2025-12-01 18:08:24', NULL, 1),
('123456', 'Kory', 'Carrera', 'Kory@carrera.com', 2, '$2y$10$gQ6trQAwy.dl3XF8i3PPieem3.wauWb.daIwa3VWCMsXlojO7z9dO', '2025-12-01 18:11:51', NULL, 1),
('123456789', 'Juan', 'Galvis', 'juan@galvis.com', 1, '$2y$10$O/YRYjCjYN09us2MOEpPT.c.GYNWs7/arm/aeShBQry/zG8b/BiMS', '2025-12-01 18:10:06', '2025-12-05 15:50:48', 1),
('12345678910', 'floppy', 'carrera', 'floppy.carrera@gmail.com', 1, '$2y$10$3KpsnHx05KaGQIqS6EmeDO7K.zLZ8TcWff5H.tbvXsi0YzmXqSsEa', '2025-12-03 17:56:38', NULL, 1),
('2222222222', 'Comisionado', 'Tester', 'tester.comi@example.com', 2, '$2y$10$.ojGM8lAXRkAo9tY8JFuEOF5RJ0jrcwL05ErUzfZnaS5/fJWt6Xxq', '2026-01-24 03:14:09', '2026-02-10 01:35:17', 1),
('3001', 'Ana', 'Perez', 'ana@correo.com', 2, '$2y$10$vzXg/V5raMW7lgm6S0dbT.KkK5xtVQRm8sEwXBQoO7YH9dprCRdge', '2026-01-14 18:51:42', NULL, 1),
('3002', 'Juan', 'Diaz', 'juan@correo.com', 2, '$2y$10$8ONAl./BZNqfZQ6wzzm12.jleYO.G5oj7bi20gIiVbweBeFABWrpG', '2026-01-14 18:51:42', NULL, 1),
('3003', 'Laura', 'Gomez', 'laura@correo.com', 2, '$2y$10$VE2n6FX32T1ahxghvQc/N.CutKcmngjWgcH0Bwpm5iLGhd2kjiccS', '2026-01-14 18:51:42', NULL, 1),
('654321', 'Pepita', 'arnolfita', 'pepitaArnolfa@gmail.com', 1, '$2y$10$yIL85M7u2V8sm/unCLu5uullP6h3mLkpxGi9.Yqp3hcSPF4opETxW', '2025-12-02 17:08:51', '2025-12-18 11:56:45', 1),
('98674523', 'Yldegar', 'Alvarez', 'karrerita@gmail.com', 1, '$2y$10$YUBDZSJqh2/LCH9jlPeeJei1i.4P/zYGPYRTCHUK2qjDbK3qE7K6S', '2025-11-24 07:46:37', '2026-01-24 02:47:12', 1),
('987654321', 'Isaac', 'carvajal', 'isaac@carvajal.com', 1, '$2y$10$aSUvDXhUTg7PXmhvqX.efuw7ggQhXAbzC2/U2VATfUgC9Uab0auh6', '2025-12-01 18:11:02', '2025-12-18 11:56:57', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivo`
--
ALTER TABLE `archivo`
  ADD PRIMARY KEY (`id_archivo`),
  ADD KEY `id_caso` (`id_caso`);

--
-- Indices de la tabla `caso`
--
ALTER TABLE `caso`
  ADD PRIMARY KEY (`id_caso`),
  ADD KEY `documento` (`documento`),
  ADD KEY `id_estado` (`id_estado`),
  ADD KEY `id_tipo_caso` (`id_tipo_caso`),
  ADD KEY `id_proceso` (`id_proceso`);

--
-- Indices de la tabla `configuracionusuario`
--
ALTER TABLE `configuracionusuario`
  ADD PRIMARY KEY (`id_configuracion`),
  ADD KEY `documento` (`documento`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `estado_usuario`
--
ALTER TABLE `estado_usuario`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `informe`
--
ALTER TABLE `informe`
  ADD PRIMARY KEY (`id_informe`),
  ADD KEY `documento` (`documento`);

--
-- Indices de la tabla `monitoreo`
--
ALTER TABLE `monitoreo`
  ADD PRIMARY KEY (`id_monitoreo`),
  ADD KEY `documento` (`documento`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `usuario` (`documento`);

--
-- Indices de la tabla `procesoorganizacional`
--
ALTER TABLE `procesoorganizacional`
  ADD PRIMARY KEY (`id_proceso`),
  ADD KEY `fk_usuario_proceso` (`documento_usuario`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD PRIMARY KEY (`id_seguimiento`),
  ADD KEY `seguimientocaso` (`id_caso`);

--
-- Indices de la tabla `tipo_caso`
--
ALTER TABLE `tipo_caso`
  ADD PRIMARY KEY (`id_tipo_caso`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`documento`),
  ADD KEY `rol` (`id_rol`),
  ADD KEY `fk_estado_usuario` (`id_estado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivo`
--
ALTER TABLE `archivo`
  MODIFY `id_archivo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para encontrar y relacionar';

--
-- AUTO_INCREMENT de la tabla `caso`
--
ALTER TABLE `caso`
  MODIFY `id_caso` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK de casos', AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `configuracionusuario`
--
ALTER TABLE `configuracionusuario`
  MODIFY `id_configuracion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para encontrar y relacionar';

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `informe`
--
ALTER TABLE `informe`
  MODIFY `id_informe` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para ubicar y relacionar', AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `monitoreo`
--
ALTER TABLE `monitoreo`
  MODIFY `id_monitoreo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Llave primaria para reconocimiento y relacion';

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para relacionar y encontrar';

--
-- AUTO_INCREMENT de la tabla `procesoorganizacional`
--
ALTER TABLE `procesoorganizacional`
  MODIFY `id_proceso` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para ubicar y relacionar', AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para encontrar y relacionar';

--
-- AUTO_INCREMENT de la tabla `tipo_caso`
--
ALTER TABLE `tipo_caso`
  MODIFY `id_tipo_caso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `archivo`
--
ALTER TABLE `archivo`
  ADD CONSTRAINT `archivo_ibfk_1` FOREIGN KEY (`id_caso`) REFERENCES `caso` (`id_caso`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `caso`
--
ALTER TABLE `caso`
  ADD CONSTRAINT `caso_ibfk_1` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `caso_ibfk_2` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `caso_ibfk_3` FOREIGN KEY (`id_tipo_caso`) REFERENCES `tipo_caso` (`id_tipo_caso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_proceso` FOREIGN KEY (`id_proceso`) REFERENCES `procesoorganizacional` (`id_proceso`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `configuracionusuario`
--
ALTER TABLE `configuracionusuario`
  ADD CONSTRAINT `ConfiguracionUsuario_ibfk_1` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`);

--
-- Filtros para la tabla `informe`
--
ALTER TABLE `informe`
  ADD CONSTRAINT `documento` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `monitoreo`
--
ALTER TABLE `monitoreo`
  ADD CONSTRAINT `monitoreo_ibfk_1` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `usuario` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `procesoorganizacional`
--
ALTER TABLE `procesoorganizacional`
  ADD CONSTRAINT `fk_usuario_proceso` FOREIGN KEY (`documento_usuario`) REFERENCES `usuario` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`id_estado`) REFERENCES `estado_usuario` (`id_estado`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
