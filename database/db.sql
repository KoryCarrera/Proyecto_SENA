-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db_sena
-- Tiempo de generación: 26-02-2026 a las 16:28:17
-- Versión del servidor: 10.6.25-MariaDB-ubu2204
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
CREATE PROCEDURE `sp_actualizar_estado_caso` (IN `p_id_caso` INT, IN `p_id_estado` INT, IN `p_documento` VARCHAR(20))   BEGIN
    IF NOT EXISTS (SELECT 1 FROM caso WHERE id_caso = p_id_caso) THEN
		SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'EL caso no existe';
	END IF;
    
    UPDATE caso SET id_estado = p_id_estado WHERE id_caso = p_id_caso AND documento = p_documento;
    
    END$$

CREATE PROCEDURE `sp_analisis_demanda` ()   BEGIN
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

CREATE PROCEDURE `sp_buscar_usuario` (IN `p_documento` VARCHAR(50))   BEGIN

SELECT u.documento, CONCAT(u.nombre, ' ', u.apellido) as nombre, u.email, u.fecha_registro, u.ultimo_inicio_sesion, r.rol FROM usuario u INNER JOIN rol r ON u.id_rol = r.id_rol WHERE documento = p_documento;

END$$

CREATE PROCEDURE `sp_cambiar_contrasena_con_token` (IN `p_token` VARCHAR(255), IN `p_password_hash` VARCHAR(255))   BEGIN
    DECLARE v_documento VARCHAR(50);

    SELECT documento INTO v_documento
    FROM token_usuario
    WHERE token = p_token
      AND usado = 0
      AND fecha_expiracion >= NOW()
    LIMIT 1;

    IF v_documento IS NULL THEN
        SELECT 'TOKEN_INVALIDO' AS resultado;
    ELSE

        UPDATE usuario
        SET contraseña = p_password_hash
        WHERE documento = v_documento;

        UPDATE token_usuario
        SET usado = 1
        WHERE token = p_token;

        SELECT 'PASSWORD_ACTUALIZADA' AS resultado;
    END IF;
END$$

CREATE PROCEDURE `sp_cambiar_estado_usuario` (IN `p_documento` VARCHAR(50), IN `p_estado` INT)   BEGIN 	

UPDATE usuario SET id_estado = p_estado WHERE documento = p_documento;

END$$

CREATE PROCEDURE `sp_caracterizacion_usuarios` ()   BEGIN
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

CREATE PROCEDURE `sp_casos_por_comisionado` ()   BEGIN

SELECT 
    CONCAT(u.nombre, ' ', u.apellido) AS comisionado,
    COUNT(c.id_caso) AS total_casos
FROM usuario u
LEFT JOIN caso c ON u.documento = c.documento
WHERE u.id_rol = 2
GROUP BY u.documento, u.nombre, u.apellido
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_comi_mes` ()   BEGIN
	SELECT 
    CONCAT(u.nombre, ' ', u.apellido) AS comisionado,
    COUNT(c.id_caso) AS total_casos
FROM usuario u
LEFT JOIN caso c ON u.documento = c.documento
WHERE u.id_rol = 2 AND MONTH(c.fecha_inicio) = MONTH(CURDATE()) AND YEAR(c.fecha_inicio) = YEAR(CURDATE())
GROUP BY u.documento, u.nombre, u.apellido
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_comi_semana` ()   BEGIN
SELECT 
    CONCAT(u.nombre, ' ', u.apellido) AS comisionado,
    COUNT(c.id_caso) AS total_casos
FROM usuario u
LEFT JOIN caso c ON u.documento = c.documento
WHERE u.id_rol = 2 AND YEARWEEK(c.fecha_inicio) = YEARWEEK(CURDATE())
GROUP BY u.documento, u.nombre, u.apellido
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_estado` ()   BEGIN

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

CREATE PROCEDURE `sp_casos_por_estado_comi` (IN `p_documento` VARCHAR(20))   BEGIN

SELECT 
    e.estado AS nombre_estado,
    COUNT(c.id_caso) AS total_casos,
    (SELECT COUNT(*) FROM caso WHERE YEAR(fecha_inicio) = YEAR(CURDATE())) AS gran_total
FROM caso c
JOIN estado e ON c.id_estado = e.id_estado
WHERE YEAR(c.fecha_inicio) = YEAR(CURDATE()) 
AND c.documento = p_documento
GROUP BY e.estado
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_estado_mes` ()   BEGIN

SELECT 
    e.estado AS nombre_estado,
    COUNT(c.id_caso) AS total_casos,
    (SELECT COUNT(*) FROM caso WHERE YEAR(fecha_inicio) = YEAR(CURDATE())) AS gran_total
FROM caso c
JOIN estado e ON c.id_estado = e.id_estado
WHERE MONTH(c.fecha_inicio) = MONTH(CURDATE()) AND YEAR(c.fecha_inicio) = YEAR(CURDATE())
GROUP BY e.estado
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_estado_mes_comi` (IN `p_documento` VARCHAR(20))   BEGIN

SELECT 
    e.estado AS nombre_estado,
    COUNT(c.id_caso) AS total_casos,
    (SELECT COUNT(*) FROM caso WHERE YEAR(fecha_inicio) = YEAR(CURDATE())) AS gran_total
FROM caso c
JOIN estado e ON c.id_estado = e.id_estado
WHERE MONTH(c.fecha_inicio) = MONTH(CURDATE()) AND YEAR(c.fecha_inicio) = YEAR(CURDATE())
AND c.documento = p_documento
GROUP BY e.estado
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_estado_semana` ()   BEGIN

SELECT 
    e.estado AS nombre_estado,
    COUNT(c.id_caso) AS total_casos,
    (SELECT COUNT(*) FROM caso WHERE YEAR(fecha_inicio) = YEAR(CURDATE())) AS gran_total
FROM caso c
JOIN estado e ON c.id_estado = e.id_estado
WHERE YEARWEEK(c.fecha_inicio) = YEARWEEK(CURDATE()) 
GROUP BY e.estado
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_estado_semana_comi` (IN `p_documento` VARCHAR(20))   BEGIN

SELECT 
    e.estado AS nombre_estado,
    COUNT(c.id_caso) AS total_casos,
    (SELECT COUNT(*) FROM caso WHERE YEAR(fecha_inicio) = YEAR(CURDATE())) AS gran_total
FROM caso c
JOIN estado e ON c.id_estado = e.id_estado
WHERE YEARWEEK(c.fecha_inicio) = YEARWEEK(CURDATE()) 
AND c.documento = p_documento
GROUP BY e.estado
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_mes` ()   BEGIN

SELECT 
    MONTH(fecha_inicio) AS mes,
    COUNT(*) AS total_casos
FROM caso
GROUP BY MONTH(fecha_inicio)
ORDER BY mes;


END$$

CREATE PROCEDURE `sp_casos_por_proceso` ()   BEGIN

SELECT 
    p.nombre AS proceso,
    COUNT(c.id_caso) AS total_casos
FROM procesoorganizacional p
LEFT JOIN caso c ON c.id_proceso = p.id_proceso
GROUP BY p.id_proceso, p.nombre
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_proceso_comi` (IN `p_documento` VARCHAR(20))   BEGIN

SELECT 
    p.nombre AS proceso,
    COUNT(c.id_caso) AS total_casos
FROM procesoorganizacional p
LEFT JOIN caso c ON c.id_proceso = p.id_proceso
WHERE c.documento = p_documento
GROUP BY p.id_proceso, p.nombre
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_proceso_mes` ()   BEGIN

SELECT 
    p.nombre AS proceso,
    COUNT(c.id_caso) AS total_casos
FROM procesoorganizacional p
LEFT JOIN caso c ON c.id_proceso = p.id_proceso
WHERE MONTH(c.fecha_inicio) = MONTH(CURDATE()) AND YEAR(c.fecha_inicio) = YEAR(CURDATE())
GROUP BY p.id_proceso, p.nombre
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_proceso_mes_comi` (IN `p_documento` VARCHAR(20))   BEGIN

SELECT 
    p.nombre AS proceso,
    COUNT(c.id_caso) AS total_casos
FROM procesoorganizacional p
LEFT JOIN caso c ON c.id_proceso = p.id_proceso
WHERE MONTH(c.fecha_inicio) = MONTH(CURDATE()) AND YEAR(c.fecha_inicio) = YEAR(CURDATE()) AND
c.documento = p_documento
GROUP BY p.id_proceso, p.nombre
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_proceso_semana` ()   BEGIN

SELECT 
    p.nombre AS proceso,
    COUNT(c.id_caso) AS total_casos
FROM procesoorganizacional p
LEFT JOIN caso c ON c.id_proceso = p.id_proceso
WHERE c.documento = p_documento AND YEARWEEK(c.fecha_inicio) = YEARWEEK(CURDATE()) 
GROUP BY p.id_proceso, p.nombre
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_proceso_semana_comi` (IN `p_documento` VARCHAR(20))   BEGIN

SELECT 
    p.nombre AS proceso,
    COUNT(c.id_caso) AS total_casos
FROM procesoorganizacional p
LEFT JOIN caso c ON c.id_proceso = p.id_proceso
WHERE c.documento = p_documento AND YEARWEEK(c.fecha_inicio) = YEARWEEK(CURDATE()) 
GROUP BY p.id_proceso, p.nombre
ORDER BY total_casos DESC;

END$$

CREATE PROCEDURE `sp_casos_por_semana` ()   BEGIN
    SELECT 
    DAYOFWEEK(fecha_inicio) AS dia_semana,
        COUNT(*) AS casos_dia
    FROM caso
    WHERE YEARWEEK(fecha_inicio, 0) = YEARWEEK(CURDATE(), 0)
    GROUP BY WEEK(fecha_inicio, 0), DAYOFWEEK(fecha_inicio), DAYNAME(fecha_inicio)
    ORDER BY dia_semana;
END$$

CREATE PROCEDURE `sp_casos_por_un_mes` ()   BEGIN
    SELECT 
        DAY(fecha_inicio) AS dia,
        COUNT(*) AS total_casos
    FROM caso
    WHERE MONTH(fecha_inicio) = MONTH(CURDATE())
      AND YEAR(fecha_inicio) = YEAR(CURDATE())
    GROUP BY MONTH(fecha_inicio), DAY(fecha_inicio)
    ORDER BY dia;
END$$

CREATE PROCEDURE `sp_configurar_usuario` (IN `p_documento` VARCHAR(20), IN `p_nombre` VARCHAR(100), IN `p_apellido` VARCHAR(100), IN `p_email` VARCHAR(150), IN `p_contraseña` VARCHAR(255), IN `p_numero` VARCHAR(30))   BEGIN
    UPDATE usuario 
    SET 
        nombre = p_nombre,
        apellido = p_apellido,
        email = p_email,
        numero = p_numero,
        contraseña = CASE
            WHEN p_contraseña IS NULL OR p_contraseña = '' THEN contraseña 
            ELSE p_contraseña 
        END
    WHERE documento = p_documento;
END$$

CREATE PROCEDURE `sp_contear_casos_tipo` ()   BEGIN

SELECT 
    tc.nombre_caso,
    COUNT(c.id_caso) AS total
FROM caso c
INNER JOIN tipo_caso tc ON c.id_tipo_caso = tc.id_tipo_caso
GROUP BY tc.nombre_caso
ORDER BY tc.nombre_caso;

END$$

CREATE PROCEDURE `sp_contear_casos_tipo_comi` (IN `p_documento` VARCHAR(20))   BEGIN

SELECT 
    tc.nombre_caso,
    COUNT(c.id_caso) AS total
FROM caso c
INNER JOIN tipo_caso tc ON c.id_tipo_caso = tc.id_tipo_caso
WHERE c.documento = p_documento
GROUP BY tc.nombre_caso
ORDER BY tc.nombre_caso;

END$$

CREATE PROCEDURE `sp_contear_casos_tipo_mes` ()   BEGIN
SELECT 
    tc.nombre_caso,
    COUNT(c.id_caso) AS total
FROM caso c
INNER JOIN tipo_caso tc ON c.id_tipo_caso = tc.id_tipo_caso
WHERE MONTH(c.fecha_inicio) = MONTH(CURDATE()) AND YEAR(c.fecha_inicio) = YEAR(CURDATE())
GROUP BY tc.nombre_caso
ORDER BY tc.nombre_caso;

END$$

CREATE PROCEDURE `sp_contear_casos_tipo_mes_comi` (IN `p_documento` VARCHAR(20))   BEGIN
SELECT 
    tc.nombre_caso,
    COUNT(c.id_caso) AS total
FROM caso c
INNER JOIN tipo_caso tc ON c.id_tipo_caso = tc.id_tipo_caso
WHERE MONTH(c.fecha_inicio) = MONTH(CURDATE()) AND YEAR(c.fecha_inicio) = YEAR(CURDATE()) AND c.documento = p_documento
GROUP BY tc.nombre_caso
ORDER BY tc.nombre_caso;

END$$

CREATE PROCEDURE `sp_contear_casos_tipo_semana` ()   BEGIN
SELECT 
    tc.nombre_caso,
    COUNT(c.id_caso) AS total
FROM caso c
INNER JOIN tipo_caso tc ON c.id_tipo_caso = tc.id_tipo_caso
WHERE YEARWEEK(c.fecha_inicio) = YEARWEEK(CURDATE())
GROUP BY tc.nombre_caso
ORDER BY tc.nombre_caso;
END$$

CREATE PROCEDURE `sp_contear_casos_tipo_semana_comi` (IN `p_documento` VARCHAR(20))   BEGIN
SELECT 
    tc.nombre_caso,
    COUNT(c.id_caso) AS total
FROM caso c
INNER JOIN tipo_caso tc ON c.id_tipo_caso = tc.id_tipo_caso
WHERE YEARWEEK(c.fecha_inicio) = YEARWEEK(CURDATE()) AND c.documento = p_documento
GROUP BY tc.nombre_caso
ORDER BY tc.nombre_caso;
END$$

CREATE PROCEDURE `sp_desactivar_proceso` (IN `p_id_proceso` INT)   BEGIN 
	UPDATE procesoorganizacional SET estado = 0 WHERE id_proceso = p_id_proceso;
END$$

CREATE PROCEDURE `sp_editar_usuario` (IN `p_documento` VARCHAR(50), IN `p_nombre` VARCHAR(100), IN `p_apellido` VARCHAR(100), IN `p_email` VARCHAR(150), IN `p_rol` INT, IN `p_contraseña` VARCHAR(255))   BEGIN
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

CREATE PROCEDURE `sp_generar_token_recuperacion` (IN `p_documento` VARCHAR(50))   BEGIN
    DECLARE v_token VARCHAR(255);
    DECLARE v_expira DATETIME;

    
    SET v_token = UPPER(SUBSTRING(REPLACE(UUID(), '-', ''), 1, 15));

    SET v_expira = DATE_ADD(NOW(), INTERVAL 30 MINUTE);

    UPDATE token_usuario
    SET usado = 1
    WHERE documento = p_documento
      AND usado = 0;

    INSERT INTO token_usuario (
        documento,
        token,
        fecha_expiracion
    )
    VALUES (
        p_documento,
        v_token,
        v_expira
    );

    SELECT v_token AS token, v_expira AS expira;
END$$

CREATE PROCEDURE `sp_listar_casos` ()   BEGIN
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
		ORDER BY c.fecha_inicio DESC;
	END$$

CREATE PROCEDURE `sp_listar_caso_por_comisionado` (IN `p_documento` VARCHAR(50))   BEGIN
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
		ORDER BY c.fecha_inicio DESC;
    END$$

CREATE PROCEDURE `sp_listar_estados_caso` ()   BEGIN
    SELECT 
        id_estado,
        estado
    FROM estado
    ORDER BY id_estado ASC;
END$$

CREATE PROCEDURE `sp_listar_noti_admin` ()   BEGIN
SELECT * FROM noti_administrador;
END$$

CREATE PROCEDURE `sp_listar_procesos_activos` ()   BEGIN
    SELECT 
        id_proceso,
        nombre
    FROM procesoorganizacional
    WHERE estado = 1
    ORDER BY nombre ASC;
END$$

CREATE PROCEDURE `sp_listar_proceso_organizacional` ()   BEGIN 
	SELECT p.id_proceso, p.nombre AS nombre_proceso, p.descripcion, p.fecha_creacion, p.estado, CONCAT(u.nombre, ' ', u.apellido) AS nombre_creador, u.documento, u.email
 	FROM procesoorganizacional p INNER JOIN usuario u ON p.documento_usuario = u.documento ORDER BY p.fecha_creacion DESC;
END$$

CREATE PROCEDURE `sp_listar_seguimientos_por_caso` (IN `p_caso` INT)   BEGIN
    SELECT 
        s.id_seguimiento,
        s.fecha_seguimiento,
        s.observacion,
        CONCAT(u.nombre, ' ', u.apellido) AS usuario
    FROM seguimiento s
    JOIN usuario u ON s.documento = u.documento
    WHERE s.id_caso = p_caso
    ORDER BY s.fecha_seguimiento DESC
    LIMIT 20;
END$$

CREATE PROCEDURE `sp_listar_tipos_caso` ()   BEGIN
    SELECT 
        id_tipo_caso,
        nombre_caso
    FROM tipo_caso
    ORDER BY nombre_caso ASC;
END$$

CREATE PROCEDURE `sp_listar_usuarios` ()   BEGIN

 SELECT documento, nombre, apellido, email, id_rol, id_estado FROM usuario;

END$$

CREATE PROCEDURE `sp_login_usuario` (IN `p_documento` VARCHAR(50))   BEGIN
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

CREATE PROCEDURE `sp_obtener_caso_por_id` (IN `p_id_caso` INT)   BEGIN
    SELECT 
    	c.documento,
        c.id_caso,
        CONCAT(u.nombre, ' ', u.apellido) AS comisionado,
        c.fecha_inicio,
        c.fecha_cierre,
        e.estado AS estado,
        t.nombre_caso AS tipo_caso,
        p.nombre AS proceso,
        c.descripcion,
        u.id_rol
    FROM caso c
    LEFT JOIN usuario u ON c.documento = u.documento
    INNER JOIN estado e ON c.id_estado = e.id_estado
    INNER JOIN tipo_caso t ON c.id_tipo_caso = t.id_tipo_caso
    INNER JOIN procesoorganizacional p ON c.id_proceso = p.id_proceso
    WHERE c.id_caso = p_id_caso
    LIMIT 1;
END$$

CREATE PROCEDURE `sp_reactivar_proceso` (IN `p_id_proceso` INT)   BEGIN 
    UPDATE procesoorganizacional 
    SET estado = 1 
    WHERE id_proceso = p_id_proceso;
END$$

CREATE PROCEDURE `sp_registrar_caso` (IN `p_documento` VARCHAR(20), IN `p_id_proceso` INT, IN `p_id_tipo_caso` INT, IN `p_descripcion` TEXT, IN `p_nombre` VARCHAR(255))   BEGIN
DECLARE v_id_caso INT;
    
INSERT INTO caso (documento, id_proceso, id_tipo_caso, descripcion, nombre) VALUES (p_documento, p_id_proceso, p_id_tipo_caso, p_descripcion, p_nombre);
    
    SET v_id_caso = LAST_INSERT_ID();

	SELECT 
    	c.id_caso, 
        c.nombre,
        c.documento,
        c.fecha_inicio,
        c.fecha_cierre,
        e.estado,
        t.nombre_caso AS tipo_caso,
        p.nombre AS proceso,
        c.descripcion,
        CONCAT(u.nombre, ' ', u.apellido) AS comisionado
        FROM caso c
        INNER JOIN estado e ON c.id_estado = e.id_estado
        INNER JOIN tipo_caso t ON c.id_tipo_caso = t.id_tipo_caso
        INNER JOIN procesoorganizacional p ON c.id_proceso = p.id_proceso
        LEFT JOIN usuario u ON c.documento = u.documento
        WHERE c.id_caso = v_id_caso;

END$$

CREATE PROCEDURE `sp_registrar_informe` (IN `p_documento` VARCHAR(50), IN `p_formato` VARCHAR(10), IN `p_contenido` TEXT)   BEGIN



    DECLARE v_fecha_actual DATETIME;

    DECLARE v_ultimo_id INT;



    SET v_fecha_actual = NOW();



    INSERT INTO informe (documento, fecha_generacion, tipo_informe, contenido)

    VALUES (p_documento, v_fecha_actual, p_formato, p_contenido);

    

    SET v_ultimo_id = LAST_INSERT_ID();



    SELECT v_ultimo_id AS id_generado, v_fecha_actual AS fecha_registro;



END$$

CREATE PROCEDURE `sp_registrar_monitoreo` (IN `p_documento` VARCHAR(50), IN `p_tipo` ENUM('inicio_sesion','accion'), IN `p_descripcion` TEXT)   BEGIN
INSERT INTO usuario (documento, fecha, tipo, descripcion) VALUES (p_documento, NOW(), p_tipo, p_descripcion);
END$$

CREATE PROCEDURE `sp_registrar_proceso_organizacional` (IN `p_descripcion` TEXT, IN `p_nombre` VARCHAR(100), IN `p_documento_usuario` VARCHAR(50))   BEGIN
    INSERT INTO procesoorganizacional (descripcion, nombre, documento_usuario)
    VALUES (p_descripcion, p_nombre, p_documento_usuario);
END$$

CREATE PROCEDURE `sp_registrar_seguimiento` (IN `p_observacion` TEXT, IN `p_caso` INT, IN `p_documento` VARCHAR(50))   BEGIN
    
    IF NOT EXISTS (SELECT 1 FROM caso WHERE p_caso = caso.id_caso)
    	THEN SIGNAL SQLSTATE '45000'
        	SET MESSAGE_TEXT = 'No existe el caso buscado.';
	END IF;
    
    INSERT INTO seguimiento (fecha_seguimiento, observacion, id_caso, documento) 
    VALUES (
    	NOW(),
        p_observacion,
        p_caso,
        p_documento
    );
    
    END$$

CREATE PROCEDURE `sp_registrar_usuario` (IN `p_documento` VARCHAR(50), IN `p_nombre` VARCHAR(50), IN `p_apellido` VARCHAR(50), IN `p_email` VARCHAR(100), IN `p_id_rol` INT(11), IN `p_contraseña` VARCHAR(255))   BEGIN 

INSERT INTO usuario (documento, nombre, apellido, email, id_rol, contraseña, fecha_registro, ultimo_inicio_sesion) 
VALUES (p_documento, p_nombre, p_apellido, p_email, p_id_rol, p_contraseña, NOW(), NULL);

END$$

CREATE PROCEDURE `sp_reporte_pqrs_excel` ()   BEGIN
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

CREATE PROCEDURE `sp_resumen_casos_global` ()   BEGIN
    SELECT
        COUNT(c.id_caso)                                                          AS total_casos,

        SUM(CASE WHEN tc.nombre_caso = 'Denuncia'            THEN 1 ELSE 0 END)  AS total_denuncias,
        SUM(CASE WHEN tc.nombre_caso = 'Solicitud'           THEN 1 ELSE 0 END)  AS total_solicitudes,
        SUM(CASE WHEN tc.nombre_caso = 'Acción de Tutela'    THEN 1 ELSE 0 END)  AS total_acciones_tutela,
        SUM(CASE WHEN tc.nombre_caso = 'Derecho de Petición' THEN 1 ELSE 0 END)  AS total_derechos_peticion,

        SUM(CASE WHEN e.estado = 'Atendido'    THEN 1 ELSE 0 END)                AS total_atendidos,
        SUM(CASE WHEN e.estado = 'Por atender' THEN 1 ELSE 0 END)                AS total_pendientes,
        SUM(CASE WHEN e.estado = 'No atendido' THEN 1 ELSE 0 END)                AS total_no_atendidos

    FROM caso c
    INNER JOIN tipo_caso tc ON c.id_tipo_caso = tc.id_tipo_caso
    INNER JOIN estado    e  ON c.id_estado    = e.id_estado
    WHERE YEAR(c.fecha_inicio) = YEAR(CURDATE());

END$$

CREATE PROCEDURE `sp_resumen_casos_por_documento` (IN `p_documento` VARCHAR(50))   BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuario WHERE documento = p_documento) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El documento no corresponde a ningun usuario registrado.';
    END IF;

    SELECT
        COUNT(c.id_caso)                                                          AS total_casos,

        SUM(CASE WHEN tc.nombre_caso = 'Denuncia'            THEN 1 ELSE 0 END)  AS total_denuncias,
        SUM(CASE WHEN tc.nombre_caso = 'Solicitud'           THEN 1 ELSE 0 END)  AS total_solicitudes,
        SUM(CASE WHEN tc.nombre_caso = 'Acción de Tutela'    THEN 1 ELSE 0 END)  AS total_acciones_tutela,
        SUM(CASE WHEN tc.nombre_caso = 'Derecho de Petición' THEN 1 ELSE 0 END)  AS total_derechos_peticion,

        SUM(CASE WHEN e.estado = 'Atendido'    THEN 1 ELSE 0 END)                AS total_atendidos,
        SUM(CASE WHEN e.estado = 'Por atender' THEN 1 ELSE 0 END)                AS total_pendientes,
        SUM(CASE WHEN e.estado = 'No atendido' THEN 1 ELSE 0 END)                AS total_no_atendidos

    FROM caso c
    INNER JOIN tipo_caso tc ON c.id_tipo_caso = tc.id_tipo_caso
    INNER JOIN estado    e  ON c.id_estado    = e.id_estado
    WHERE c.documento = p_documento
      AND YEAR(c.fecha_inicio) = YEAR(CURDATE());
END$$

CREATE PROCEDURE `sp_resumen_productividad_comisionados` ()   BEGIN
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

CREATE PROCEDURE `sp_traer_usuario` (IN `p_documento` VARCHAR(50))   BEGIN

SELECT documento, nombre, apellido, email, id_rol, id_estado FROM usuario WHERE documento = TRIM(p_documento COLLATE utf8mb4_general_ci);

END$$

CREATE PROCEDURE `sp_validacion_estado_caso` (IN `p_id_caso` INT)   BEGIN
	SELECT id_estado AS id_estado
    FROM caso 
    WHERE id_caso = p_id_caso;
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
  `nombre` varchar(255) NOT NULL,
  `documento` varchar(20) NOT NULL COMMENT 'FK para relacionar casos y usuarios ',
  `id_proceso` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha en la que se realiza el caso',
  `fecha_cierre` datetime DEFAULT NULL,
  `id_estado` int(11) NOT NULL DEFAULT 2 COMMENT 'FK de la tabla estados de los casos',
  `id_tipo_caso` int(11) NOT NULL COMMENT 'FK de la tabla tipo de los casos',
  `descripcion` text NOT NULL COMMENT 'contenido de los casos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `caso`
--

INSERT INTO `caso` (`id_caso`, `nombre`, `documento`, `id_proceso`, `fecha_inicio`, `fecha_cierre`, `id_estado`, `id_tipo_caso`, `descripcion`) VALUES
(82, 'Reporte de accidente laboral en oficina administrativa', '1756664828', 14, '2026-01-22 14:59:17', NULL, 1, 2, 'El día 10 de febrero de 2026 sufrí una caída dentro de la oficina debido a piso mojado sin señalización. Presenté dolor en la muñeca derecha y fui valorado por la ARL. Solicito se realice la investigación correspondiente y se implementen medidas preventivas para evitar futuros incidentes.'),
(84, 'Derecho de petición – Estado de incentivo institucional', '1656966633', 13, '2026-01-01 15:01:46', NULL, 2, 3, 'Mediante el presente derecho de petición solicito información sobre el estado de evaluación de mi postulación al incentivo por desempeño correspondiente al segundo semestre de 2025. Agradezco se me informe el resultado del proceso y los criterios aplicados en la evaluación.'),
(93, 'Posible trato desigual en asignación de incentivos', '1020304050', 13, '2026-02-23 12:49:15', NULL, 1, 1, 'El funcionario manifiesta inconformidad debido a que considera que los criterios de evaluación no se aplicaron de manera equitativa en su área, afectando la asignación de incentivos.'),
(94, 'Incumplimiento en entrega de dotación operativa', '1456333298', 12, '2026-02-23 12:50:03', NULL, 2, 1, 'Se informa que el personal del área operativa no ha recibido la dotación correspondiente al periodo vigente, lo que afecta el cumplimiento seguro de sus funciones.'),
(95, 'Presunto maltrato laboral por parte de superior', '1456333298', 10, '2026-02-23 12:50:29', NULL, 1, 1, 'El colaborador reporta comportamientos reiterados de trato inapropiado y comunicación inadecuada por parte de su jefe inmediato, solicitando revisión del caso.'),
(99, 'Programación de examen médico ocupacional', '1756664828', 11, '2026-02-19 12:53:33', NULL, 2, 2, 'El colaborador solicita la programación de su examen médico ocupacional periódico para seguimiento de su estado de salud laboral.'),
(100, 'Capacitación en prevención de riesgos laborales', '1756664828', 14, '2026-02-23 12:53:57', NULL, 2, 2, 'Se solicita capacitación para el equipo de trabajo en temas de prevención de riesgos con el fin de fortalecer prácticas seguras.'),
(103, 'Estado de solicitud de incentivo institucional', '1020304050', 13, '2026-02-23 14:11:57', NULL, 2, 3, 'El peticionario solicita conocer el estado actual de su solicitud de incentivo y los tiempos estimados de respuesta.'),
(104, 'Copia de resultados de examen médico ocupacional', '1020304050', 11, '2026-02-23 14:12:24', NULL, 2, 3, 'Se solicita copia de los resultados del examen médico ocupacional realizado recientemente.'),
(106, 'Solicitud de acceso al plan anual de SST', '1456333298', 14, '2026-02-23 14:15:09', NULL, 2, 3, 'Se solicita acceso o copia del plan anual de seguridad y salud en el trabajo para conocer las actividades programadas.'),
(107, 'Demora en atención médica ocupacional', '1456333298', 11, '2026-02-23 14:15:31', NULL, 2, 4, 'El accionante manifiesta que la demora en la asignación de cita médica afecta su derecho fundamental a la salud, solicitando atención prioritaria.'),
(110, 'Riesgo laboral no atendido oportunamente', '1756664828', 14, '2026-02-21 14:17:12', NULL, 2, 4, 'Se solicita protección de derechos fundamentales ante la persistencia de un riesgo laboral que no ha sido intervenido.'),
(111, 'Negación de apoyo social en situación urgente', '1020304050', 10, '2026-02-17 14:17:51', NULL, 1, 4, 'El accionante solicita intervención inmediata al considerar vulnerados sus derechos por la negación de un apoyo social urgente.');

--
-- Disparadores `caso`
--
DELIMITER $$
CREATE TRIGGER `tr_notificar_cambio_estado_caso` AFTER UPDATE ON `caso` FOR EACH ROW BEGIN
INSERT INTO noti_comisionado(documento, mensaje, fecha)
SELECT 
    NEW.documento, 
    CONCAT(
        'El caso "', NEW.nombre, 
        '" con el ID: ', NEW.id_caso, 
        ' perteneciente al proceso "', p.nombre, 
        '", pasó del estado: "', e_old.estado,
        '" al estado: "', e_new.estado,        
        '" por el usuario encargado ', u.nombre, ' ', u.apellido
    ), 
    NOW()
FROM usuario u
INNER JOIN procesoorganizacional p ON p.id_proceso = NEW.id_proceso
INNER JOIN estado e_new ON e_new.id_estado = NEW.id_estado
INNER JOIN estado e_old ON e_old.id_estado = OLD.id_estado
WHERE u.documento = NEW.documento;


INSERT INTO noti_administrador(documento, mensaje, fecha)
SELECT 
    u_admin.documento, 
    CONCAT(
        'AVISO: El caso "', NEW.nombre, 
        '" CON LA ID: ', NEW.id_caso,
        ' cambió deL estado "', e_old.estado, '" a "', e_new.estado,
        '". Por su Comisionado Responsable: ', u_resp.nombre, ' ', u_resp.apellido
    ), 
    NOW()
FROM usuario u_admin
INNER JOIN usuario u_resp ON u_resp.documento = NEW.documento
INNER JOIN procesoorganizacional p ON p.id_proceso = NEW.id_proceso
INNER JOIN estado e_new ON e_new.id_estado = NEW.id_estado
INNER JOIN estado e_old ON e_old.id_estado = OLD.id_estado
WHERE u_admin.id_rol = 1;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_notificar_registro_caso` AFTER INSERT ON `caso` FOR EACH ROW BEGIN
    INSERT INTO noti_comisionado(documento, mensaje, fecha)
    SELECT 
        NEW.documento, 
        CONCAT('NUEVO CASO: ', NEW.nombre,' ID CASO: ', NEW.id_caso, '. \nSe ha registrado un nuevo caso de ', t.nombre_caso ,' Por Atender perteneciente al Proceso Organizacional ', p.nombre, ' asignado al comisionado ', u.nombre, ' ', u.apellido), 
        NOW()
    FROM usuario u 
	INNER JOIN tipo_caso t ON t.id_tipo_caso = NEW.id_tipo_caso
    INNER JOIN procesoorganizacional p ON p.id_proceso = NEW.id_proceso
    WHERE u.documento = NEW.documento;
END
$$
DELIMITER ;

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
(30, '1487569254', '2026-02-07 04:50:36', 'EXCEL', ''),
(31, '1487569254', '2026-02-07 04:51:54', 'EXCEL', ''),
(32, '1487569254', '2026-02-07 04:54:54', 'EXCEL', ''),
(33, '1487569254', '2026-02-07 04:57:12', 'PDF', 'example'),
(34, '1487569254', '2026-02-07 05:01:47', 'EXCEL', NULL),
(35, '1487569254', '2026-02-07 05:02:26', 'EXCEL', NULL),
(36, '1487569254', '2026-02-07 05:09:40', 'EXCEL', NULL),
(37, '1487569254', '2026-02-07 05:10:43', 'PDF', '111111111111111111111'),
(38, '1487569254', '2026-02-07 05:16:52', 'EXCEL', NULL),
(41, '1487569254', '2026-02-07 05:30:34', 'EXCEL', NULL),
(42, '1487569254', '2026-02-10 00:11:57', 'EXCEL', NULL),
(43, '1487569254', '2026-02-14 16:35:47', 'PDF', 'RHAHGTREHTG'),
(44, '1487569254', '2026-02-14 16:37:50', 'PDF', 'qgr5ert'),
(45, '1487569254', '2026-02-14 16:37:54', 'PDF', 'qgr5ert'),
(46, '1487569254', '2026-02-14 16:38:02', 'PDF', 'qgr5ert'),
(47, '1487569254', '2026-02-14 16:45:17', 'PDF', 'trhrthrt');

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
-- Estructura de tabla para la tabla `noti_administrador`
--

CREATE TABLE `noti_administrador` (
  `id_notificacion` int(11) NOT NULL,
  `documento` varchar(20) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `noti_administrador`
--

INSERT INTO `noti_administrador` (`id_notificacion`, `documento`, `mensaje`, `fecha`) VALUES
(1, '1487569254', 'ALERTA ADMIN: El caso Presunto maltrato laboral por parte de superior (ID: 95) cambió de Por atender a Atendido. Responsable: Juan Manuel Correal', '2026-02-24 15:19:08'),
(2, '1487569254', 'AVISO: El caso \"Demora en atención médica ocupacional\" CON LA ID: 107 cambió deL estado \"Atendido\" a \"Por atender\". Por su Comisionado Responsable: Juan Manuel Correal', '2026-02-24 15:24:32'),
(3, '1487569254', 'AVISO: El caso \"Reporte de accidente laboral en oficina administrativa\" CON LA ID: 82 cambió deL estado \"Atendido\" a \"Atendido\". Por su Comisionado Responsable: Zack Lopez', '2026-02-24 16:05:50'),
(4, '1487569254', 'AVISO: El caso \"Derecho de petición – Estado de incentivo institucional\" CON LA ID: 84 cambió deL estado \"Por atender\" a \"Por atender\". Por su Comisionado Responsable: Marleny Gaviria', '2026-02-24 16:05:57'),
(5, '1487569254', 'AVISO: El caso \"Negación de apoyo social en situación urgente\" CON LA ID: 111 cambió deL estado \"Atendido\" a \"Atendido\". Por su Comisionado Responsable: Simón Gonzalez Pelaez', '2026-02-24 16:06:06'),
(6, '1487569254', 'AVISO: El caso \"Riesgo laboral no atendido oportunamente\" CON LA ID: 110 cambió deL estado \"Por atender\" a \"Por atender\". Por su Comisionado Responsable: Zack Lopez', '2026-02-24 16:06:12'),
(7, '1487569254', 'AVISO: El caso \"Programación de examen médico ocupacional\" CON LA ID: 99 cambió deL estado \"Por atender\" a \"Por atender\". Por su Comisionado Responsable: Zack Lopez', '2026-02-24 16:08:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noti_comisionado`
--

CREATE TABLE `noti_comisionado` (
  `id_notificacion` int(11) NOT NULL COMMENT 'PK para relacionar y encontrar',
  `documento` varchar(20) NOT NULL COMMENT 'Llave primaria de la tabla usuarios para relacionar ambas tablas',
  `mensaje` text NOT NULL COMMENT 'contenido de la notifiacion',
  `fecha` datetime NOT NULL COMMENT 'fecha de la notificación'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `noti_comisionado`
--

INSERT INTO `noti_comisionado` (`id_notificacion`, `documento`, `mensaje`, `fecha`) VALUES
(9, '1456333298', 'El caso Presunto maltrato laboral por parte de superior con el ID: 95 perteneciente al proceso Bienestar Social, pasó del estado: Por atender al estado: Atendido por el usuario encargado Juan Manuel Correal', '2026-02-24 15:19:08'),
(10, '1456333298', 'El caso \"Demora en atención médica ocupacional\" con el ID: 107 perteneciente al proceso \"SSEMI\", pasó del estado: \"Atendido\" al estado: \"Por atender\" por el usuario encargado Juan Manuel Correal', '2026-02-24 15:24:32'),
(11, '1756664828', 'El caso \"Reporte de accidente laboral en oficina administrativa\" con el ID: 82 perteneciente al proceso \"SST\", pasó del estado: \"Atendido\" al estado: \"Atendido\" por el usuario encargado Zack Lopez', '2026-02-24 16:05:50'),
(12, '1656966633', 'El caso \"Derecho de petición – Estado de incentivo institucional\" con el ID: 84 perteneciente al proceso \"Plan de incentivos\", pasó del estado: \"Por atender\" al estado: \"Por atender\" por el usuario encargado Marleny Gaviria', '2026-02-24 16:05:57'),
(13, '1020304050', 'El caso \"Negación de apoyo social en situación urgente\" con el ID: 111 perteneciente al proceso \"Bienestar Social\", pasó del estado: \"Atendido\" al estado: \"Atendido\" por el usuario encargado Simón Gonzalez Pelaez', '2026-02-24 16:06:06'),
(14, '1756664828', 'El caso \"Riesgo laboral no atendido oportunamente\" con el ID: 110 perteneciente al proceso \"SST\", pasó del estado: \"Por atender\" al estado: \"Por atender\" por el usuario encargado Zack Lopez', '2026-02-24 16:06:12'),
(15, '1756664828', 'El caso \"Programación de examen médico ocupacional\" con el ID: 99 perteneciente al proceso \"SSEMI\", pasó del estado: \"Por atender\" al estado: \"Por atender\" por el usuario encargado Zack Lopez', '2026-02-24 16:08:14');

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
(10, '2026-02-11 22:43:31', 'N/a', '1487569254', 'Bienestar Social', 1),
(11, '2026-02-11 22:43:58', 'N/a', '1487569254', 'SSEMI', 1),
(12, '2026-02-11 22:44:36', 'N/a', '1487569254', 'Ropa de Trabajo', 1),
(13, '2026-02-11 22:45:22', 'N/a', '1487569254', 'Plan de incentivos', 1),
(14, '2026-02-11 22:45:37', 'N/a', '1487569254', 'SST', 1);

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
  `documento` varchar(50) NOT NULL,
  `id_caso` int(11) NOT NULL COMMENT 'Relación entre seguimiento y caso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `seguimiento`
--

INSERT INTO `seguimiento` (`id_seguimiento`, `fecha_seguimiento`, `observacion`, `documento`, `id_caso`) VALUES
(8, '2026-02-23 14:01:18', 'Seguimiento del caso', '1020304050', 81),
(9, '2026-02-23 14:03:02', 'Seguimiento del caso', '1020304050', 85),
(10, '2026-02-23 14:03:26', 'Seguimiento del caso', '1020304050', 92),
(11, '2026-02-23 14:03:53', 'Seguimiento del caso', '1020304050', 93);

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
(1, 'Denuncia'),
(2, 'Solicitud'),
(3, 'Derecho de Petición'),
(4, 'Acción de Tutela');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `token_usuario`
--

CREATE TABLE `token_usuario` (
  `id` int(11) NOT NULL,
  `documento` varchar(50) NOT NULL,
  `token` varchar(255) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `token_usuario`
--

INSERT INTO `token_usuario` (`id`, `documento`, `token`, `fecha_creacion`, `fecha_expiracion`, `usado`) VALUES
(4, '1020304050', '3569CE3D131A11F', '2026-02-26 14:42:40', '2026-02-26 15:12:40', 0),
(5, '1456333298', '6339DF91131A11F', '2026-02-26 14:43:57', '2026-02-26 15:13:57', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `documento` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL COMMENT 'Nombres (1ro y 2do) del usario',
  `apellido` varchar(50) NOT NULL COMMENT 'Apellido materno y paterno',
  `email` varchar(100) NOT NULL COMMENT 'email institucional o personal',
  `numero` varchar(30) DEFAULT NULL,
  `id_rol` int(11) NOT NULL COMMENT 'FK para relacionar rol del usario con la tabla rol',
  `contraseña` varchar(255) NOT NULL COMMENT 'Contraseña del usuario para su ingreso ',
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha del dia que se registró el usuario',
  `ultimo_inicio_sesion` datetime DEFAULT NULL,
  `id_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`documento`, `nombre`, `apellido`, `email`, `numero`, `id_rol`, `contraseña`, `fecha_registro`, `ultimo_inicio_sesion`, `id_estado`) VALUES
('1020304050', 'Simón', 'Gonzalez Pelaez', 'pelaezsimon@gmail.com', NULL, 2, '1013341532', '2026-02-12 14:18:58', '2026-02-24 14:15:39', 1),
('1456333298', 'Juan Manuel', 'Correal', 'gavliscorreal@gmail.com', NULL, 2, 'galvis123', '2026-02-12 14:22:31', '2026-02-24 15:18:47', 1),
('1487569254', 'Kory', 'Carrerita', 'carreritakory@gmail.com', NULL, 1, '$2y$10$.ojGM8lAXRkAo9tY8JFuEOF5RJ0jrcwL05ErUzfZnaS5/fJWt6Xxq', '2026-01-24 03:14:09', '2026-02-23 23:19:50', 1),
('1656966633', 'Marleny', 'Gaviria', 'gaviriamarleny@gmail.com', NULL, 2, '$2y$10$Yszox29CROyfqKeSUdHYYuoYGJahybUK6MEOe0nRiVFjkmkQNGf2G', '2026-02-12 14:28:54', '2026-02-12 15:01:09', 1),
('1756664828', 'Zack', 'Lopez', 'zackycarvajal@gmail.com', '3001234567', 2, '$2y$10$urjYpXJh5Dt2iMs1ECUJcuiaaZuxUNv9HLM4UBN9qjq3LIy2NJWWW', '2026-02-12 14:20:29', '2026-02-23 23:22:33', 1);

--
-- Disparadores `usuario`
--
DELIMITER $$
CREATE TRIGGER `tr_noti_reg_usuario` AFTER INSERT ON `usuario` FOR EACH ROW BEGIN
    INSERT INTO noti_administrador (documento, mensaje, fecha)
    SELECT 
        u_admin.documento,
        CONCAT(
            'Nuevo registro: El usuario ', NEW.nombre, ' ', NEW.apellido, 
            ' con el documento: ', NEW.documento, ') se ha unido con el rol de "', r.nombre_rol, 
            '". Fecha de registro: ', NEW.fecha_registro, '.'
        ),
        NOW()
    FROM usuario u_admin
    INNER JOIN rol r ON r.id_rol = NEW.id_rol
    INNER JOIN estado_usuario e ON e.id_estado = NEW.id_estado
    WHERE u_admin.id_rol = 1;
END
$$
DELIMITER ;

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
-- Indices de la tabla `noti_administrador`
--
ALTER TABLE `noti_administrador`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `documento` (`documento`);

--
-- Indices de la tabla `noti_comisionado`
--
ALTER TABLE `noti_comisionado`
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
  ADD KEY `seguimientocaso` (`id_caso`),
  ADD KEY `documento` (`documento`);

--
-- Indices de la tabla `tipo_caso`
--
ALTER TABLE `tipo_caso`
  ADD PRIMARY KEY (`id_tipo_caso`);

--
-- Indices de la tabla `token_usuario`
--
ALTER TABLE `token_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token` (`token`),
  ADD KEY `documento` (`documento`);

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
  MODIFY `id_caso` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK de casos', AUTO_INCREMENT=118;

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
  MODIFY `id_informe` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para ubicar y relacionar', AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `monitoreo`
--
ALTER TABLE `monitoreo`
  MODIFY `id_monitoreo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Llave primaria para reconocimiento y relacion';

--
-- AUTO_INCREMENT de la tabla `noti_administrador`
--
ALTER TABLE `noti_administrador`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `noti_comisionado`
--
ALTER TABLE `noti_comisionado`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para relacionar y encontrar', AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `procesoorganizacional`
--
ALTER TABLE `procesoorganizacional`
  MODIFY `id_proceso` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para ubicar y relacionar', AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para encontrar y relacionar', AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `tipo_caso`
--
ALTER TABLE `tipo_caso`
  MODIFY `id_tipo_caso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `token_usuario`
--
ALTER TABLE `token_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Filtros para la tabla `noti_administrador`
--
ALTER TABLE `noti_administrador`
  ADD CONSTRAINT `noti_administrador_ibfk_1` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`);

--
-- Filtros para la tabla `noti_comisionado`
--
ALTER TABLE `noti_comisionado`
  ADD CONSTRAINT `usuario` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `procesoorganizacional`
--
ALTER TABLE `procesoorganizacional`
  ADD CONSTRAINT `fk_usuario_proceso` FOREIGN KEY (`documento_usuario`) REFERENCES `usuario` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD CONSTRAINT `seguimiento_ibfk_1` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `token_usuario`
--
ALTER TABLE `token_usuario`
  ADD CONSTRAINT `fk_token_usuario` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`id_estado`) REFERENCES `estado_usuario` (`id_estado`) ON DELETE CASCADE ON UPDATE CASCADE;

DELIMITER $$
--
-- Eventos
--
CREATE EVENT `ev_caso_caducado` ON SCHEDULE EVERY 1 DAY STARTS '2026-02-26 15:49:18' ON COMPLETION PRESERVE ENABLE COMMENT 'Marca como no atendido los casos con mas de 2 meses' DO BEGIN
UPDATE caso
   SET id_estado = 3
   WHERE fecha_inicio <= NOW() - INTERVAL 2 MONTH
   AND id_estado <> 3;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
