-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db_sena
-- Tiempo de generación: 30-03-2026 a las 19:33:03
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
	DECLARE v_cierre DATETIME DEFAULT NULL;
    
    IF NOT EXISTS (SELECT 1 FROM caso WHERE id_caso = p_id_caso) THEN
		SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'EL caso no existe';
	END IF;
    
  	IF p_id_estado = 1 OR p_id_estado = 3 THEN
        SET v_cierre = NOW();
    END IF;
    
    UPDATE caso SET id_estado = p_id_estado, fecha_cierre = v_cierre WHERE id_caso = p_id_caso AND documento = p_documento;
    
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

CREATE PROCEDURE `sp_buscar_usuario` (IN `p_documento` VARCHAR(20))   BEGIN

SELECT 
u.documento, 
CONCAT(u.nombre, ' ', u.apellido) as nombre, 
u.email, 
u.fecha_registro, 
u.contraseña,
u.ultimo_inicio_sesion, 
r.nombre_rol
FROM usuario u INNER JOIN rol r ON u.id_rol = r.id_rol WHERE documento = p_documento;

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

SELECT id_estado WHERE documento = p_documento; 

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
WHERE u.id_rol = 2 AND YEAR(c.fecha_inicio) = YEAR(CURRENT_DATE)
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
WHERE YEAR(fecha_inicio) = YEAR(CURRENT_DATE)
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
AND YEAR(c.fecha_inicio) = YEAR(CURRENT_DATE)
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
        nombre = COALESCE(NULLIF(p_nombre, ''), nombre),
        apellido = COALESCE(NULLIF(p_apellido, ''), apellido),
        email = COALESCE(NULLIF(p_email, ''), email),
        numero = COALESCE(NULLIF(p_numero, ''), numero),
        
        contraseña = CASE 
            WHEN p_contrasena IS NULL OR p_contrasena = '' THEN contraseña 
            ELSE p_contrasena 
        END
    WHERE documento = p_documento;
END$$

CREATE PROCEDURE `sp_consultar_token_2fa` (IN `p_documento` VARCHAR(20))   SELECT token FROM token_usuario WHERE documento = p_documento$$

CREATE PROCEDURE `sp_consultar_token_cookie` (IN `p_documento` VARCHAR(20))   SELECT cookie FROM usuario WHERE documento = p_documento$$

CREATE PROCEDURE `sp_contear_casos_tipo` ()   BEGIN

SELECT 
    tc.nombre_caso,
    COUNT(c.id_caso) AS total
FROM caso c
INNER JOIN tipo_caso tc ON c.id_tipo_caso = tc.id_tipo_caso
WHERE YEAR(c.fecha_inicio) = YEAR(CURRENT_DATE)
GROUP BY tc.nombre_caso
ORDER BY tc.nombre_caso;

END$$

CREATE PROCEDURE `sp_contear_casos_tipo_comi` (IN `p_documento` VARCHAR(20))   BEGIN

SELECT 
    tc.nombre_caso,
    COUNT(c.id_caso) AS total
FROM caso c
INNER JOIN tipo_caso tc ON c.id_tipo_caso = tc.id_tipo_caso
WHERE c.documento = p_documento and 
YEAR(c.fecha_inicio) = YEAR(CURRENT_DATE)
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

CREATE PROCEDURE `sp_editar_usuario` (IN `p_documento` VARCHAR(50), IN `p_nombre` VARCHAR(100), IN `p_apellido` VARCHAR(100), IN `p_email` VARCHAR(150), IN `p_rol` INT, IN `p_contraseña` VARCHAR(255), IN `p_numero` VARCHAR(30))   BEGIN
    UPDATE usuario 
    SET 
        nombre = p_nombre,
        apellido = p_apellido,
        email = p_email,
        numero = p_numero,
        id_rol = p_rol,
        contraseña = CASE 
            WHEN p_contraseña IS NULL OR p_contraseña = '' THEN contraseña 
            ELSE p_contraseña 
        END
    WHERE documento = p_documento;
END$$

CREATE PROCEDURE `sp_eliminar_token_2fa` (IN `p_documento` VARCHAR(20))   DELETE FROM token_usuario WHERE documento = p_documento$$

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

CREATE PROCEDURE `sp_guardar_cookie` (IN `p_documento` VARCHAR(20), IN `p_cookie` VARCHAR(20))   UPDATE usuario SET cookie = p_cookie WHERE documento = p_documento$$

CREATE PROCEDURE `sp_guardar_token_2fa` (IN `p_documento` VARCHAR(20), IN `p_token` VARCHAR(10))   INSERT INTO token_usuario(documento, token) VALUES(p_documento, p_token)$$

CREATE PROCEDURE `sp_insertar_archivo_caso` (IN `p_id_caso` INT, IN `p_nombre_archivo` VARCHAR(255), IN `p_ruta` VARCHAR(255), IN `p_tipo_archivo` VARCHAR(50))   BEGIN
INSERT INTO archivo (id_caso, nombre_archivo, ruta, tipo_archivo, fecha_subida)
VALUES (p_id_caso, p_nombre_archivo, p_ruta, p_tipo_archivo, NOW());
END$$

CREATE PROCEDURE `sp_listar_casos` ()   BEGIN
	SELECT 
			c.id_caso,
            c.nombre,
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
SELECT 
	id_notificacion AS id, 
	documento, 
	mensaje AS descripción,
    fecha
FROM noti_administrador 
ORDER BY fecha DESC, id_notificacion DESC;
END$$

CREATE PROCEDURE `sp_listar_noti_comi` (IN `p_documento` VARCHAR(20))   BEGIN
SELECT	
	id_notificacion AS id, 
	documento, 
	mensaje AS descripción,
    fecha 
FROM noti_comisionado 
WHERE documento = p_documento
ORDER BY fecha DESC, id_notificacion DESC;
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

 SELECT documento, nombre, apellido, email, id_rol, id_estado, vigencia_usuario, ultimo_inicio_sesion FROM usuario;

END$$

CREATE PROCEDURE `sp_login_usuario` (IN `p_documento` VARCHAR(50))   BEGIN
    SELECT
        documento,
        CONCAT(nombre, ' ', apellido) AS     
        username,
        email,
        id_rol,
        contraseña,
        2FA
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

CREATE PROCEDURE `sp_reasignar_caso` (IN `p_documento` VARCHAR(20), IN `p_documento_nuevo` VARCHAR(20), IN `p_id_caso` INT, IN `p_descripcion` TEXT, IN `p_documento_viejo` VARCHAR(20))   BEGIN
    DECLARE v_nombre_usuario VARCHAR(200);
    DECLARE v_nombre_caso VARCHAR(200);
    DECLARE v_nombre_viejo varchar(200);

    START TRANSACTION;

    SELECT CONCAT(nombre, ' ', apellido) INTO v_nombre_usuario 
    FROM usuario WHERE documento = p_documento_nuevo;
    
    SELECT nombre INTO v_nombre_caso 
    FROM caso WHERE id_caso = p_id_caso;
    
    SELECT nombre INTO v_nombre_viejo
    FROM usuario WHERE documento = p_documento_viejo;

    UPDATE caso 
    SET documento = p_documento_nuevo 
    WHERE id_caso = p_id_caso;

    INSERT INTO monitoreo(documento, fecha, tipo, descripcion) 
    VALUES(p_documento, NOW(), 2, p_descripcion);

    INSERT INTO seguimiento(observacion, documento, id_caso) 
    VALUES(p_descripcion, p_documento, p_id_caso);

    INSERT INTO noti_comisionado(documento, mensaje, fecha) 
    VALUES(
        p_documento_nuevo, 
        CONCAT('SE TE HA ASIGNADO UN CASO: Estimado Comisionado "', v_nombre_usuario, 
               '", se te ha asignado un caso con el nombre: "', v_nombre_caso, '" con la id ', p_id_caso), 
        NOW()
    );
    
    INSERT INTO noti_comisionado(documento, mensaje, fecha)
    VALUES(
    	p_documento_viejo,
        CONCAT('UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado "', v_nombre_viejo, 
               '", uno de tus casos con el nombre ', v_nombre_caso,' y la id ', p_id_caso,
		' se le ha asignado al comisonado: "',
		v_nombre_usuario), 
        NOW()
    );
    COMMIT;
END$$

CREATE PROCEDURE `sp_registrar_caso` (IN `p_documento` VARCHAR(20), IN `p_id_proceso` INT, IN `p_id_tipo_caso` INT, IN `p_descripcion` TEXT, IN `p_nombre` VARCHAR(255), IN `p_radicado` VARCHAR(50))   BEGIN
DECLARE v_id_caso INT;
    
INSERT INTO caso (documento, id_proceso, id_tipo_caso, descripcion, nombre, radicado) VALUES (p_documento, p_id_proceso, p_id_tipo_caso, p_descripcion, p_nombre, p_radicado);
    
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
	DECLARE v_id_proceso INT;
    
    INSERT INTO procesoorganizacional (descripcion, nombre, documento_usuario)
    VALUES (p_descripcion, p_nombre, p_documento_usuario);
    
    SET v_id_proceso = LAST_INSERT_ID();

	SELECT 
    	p.id_proceso, 
        p.nombre,
        p.descripcion,
        p.documento_usuario,
        CONCAT(u.nombre, ' ', u.apellido) AS comisionado
        FROM procesoorganizacional p
        LEFT JOIN usuario u ON p.documento_usuario = u.documento
        WHERE p.id_proceso = v_id_proceso;
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

CREATE PROCEDURE `sp_registrar_usuario` (IN `p_documento` VARCHAR(50), IN `p_nombre` VARCHAR(50), IN `p_apellido` VARCHAR(50), IN `p_email` VARCHAR(100), IN `p_id_rol` INT(11), IN `p_contraseña` VARCHAR(255), IN `p_numero` VARCHAR(30))   BEGIN 
    
    DECLARE v_f_registro DATETIME;
    DECLARE v_f_caducidad DATETIME;
    DECLARE v_vigencia VARCHAR(20);

    
    SET v_f_registro = NOW();
    SET v_f_caducidad = DATE_ADD(v_f_registro, INTERVAL 2 YEAR);
    SET v_vigencia = CONCAT(YEAR(v_f_registro), '-', YEAR(v_f_caducidad));

    
    INSERT INTO usuario (
        documento, 
        nombre, 
        apellido, 
        email, 
        id_rol, 
        contraseña, 
        numero, 
        fecha_registro, 
        fecha_caducidad, 
        vigencia_usuario, 
        ultimo_inicio_sesion,
        id_estado
    ) 
    VALUES (
        p_documento, 
        p_nombre, 
        p_apellido, 
        p_email, 
        p_id_rol, 
        p_contraseña, 
        p_numero, 
        v_f_registro, 
        v_f_caducidad,
        v_vigencia, 
        NULL,
        1
    );
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

SELECT documento, nombre, apellido, email, numero, id_rol, id_estado, vigencia_usuario, ultimo_inicio_sesion, 2FA FROM usuario WHERE documento = TRIM(p_documento COLLATE utf8mb4_general_ci);

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
  `radicado` varchar(50) DEFAULT NULL,
  `documento` varchar(20) NOT NULL COMMENT 'FK para relacionar casos y usuarios ',
  `id_seguimiento` int(11) DEFAULT NULL,
  `id_proceso` int(11) NOT NULL,
  `fecha_ultimo_seguimiento` datetime DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha en la que se realiza el caso',
  `fecha_cierre` datetime DEFAULT NULL,
  `id_estado` int(11) NOT NULL DEFAULT 2 COMMENT 'FK de la tabla estados de los casos',
  `id_tipo_caso` int(11) NOT NULL COMMENT 'FK de la tabla tipo de los casos',
  `descripcion` text NOT NULL COMMENT 'contenido de los casos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `caso`
--

INSERT INTO `caso` (`id_caso`, `nombre`, `radicado`, `documento`, `id_seguimiento`, `id_proceso`, `fecha_ultimo_seguimiento`, `fecha_inicio`, `fecha_cierre`, `id_estado`, `id_tipo_caso`, `descripcion`) VALUES
(49, 'Derecho de petición – Estado de incentivo institucional', NULL, '1656966633', NULL, 13, NULL, '2026-02-02 15:01:46', NULL, 2, 3, 'Mediante el presente derecho de petición solicito información sobre el estado de evaluación de mi postulación al incentivo por desempeño correspondiente al segundo semestre de 2025. Agradezco se me informe el resultado del proceso y los criterios aplicados en la evaluación.'),
(50, 'Posible trato desigual en asignación de incentivos', NULL, '1020304050', NULL, 13, NULL, '2026-02-09 12:49:15', '2026-03-30 16:36:03', 1, 1, 'El funcionario manifiesta inconformidad debido a que considera que los criterios de evaluación no se aplicaron de manera equitativa en su área, afectando la asignación de incentivos.'),
(51, 'Incumplimiento en entrega de dotación operativa', NULL, '1456333298', NULL, 12, NULL, '2026-02-23 12:50:03', NULL, 2, 1, 'Se informa que el personal del área operativa no ha recibido la dotación correspondiente al periodo vigente, lo que afecta el cumplimiento seguro de sus funciones.'),
(52, 'Presunto maltrato laboral por parte de superior', NULL, '1456333298', NULL, 10, NULL, '2026-02-23 12:50:29', '2026-03-30 16:36:03', 1, 1, 'El colaborador reporta comportamientos reiterados de trato inapropiado y comunicación inadecuada por parte de su jefe inmediato, solicitando revisión del caso.'),
(53, 'Programación de examen médico ocupacional', NULL, '1756664828', NULL, 11, NULL, '2026-02-19 12:53:33', NULL, 2, 2, 'El colaborador solicita la programación de su examen médico ocupacional periódico para seguimiento de su estado de salud laboral.'),
(54, 'Capacitación en prevención de riesgos laborales', NULL, '1756664828', NULL, 14, NULL, '2026-02-23 12:53:57', NULL, 2, 2, 'Se solicita capacitación para el equipo de trabajo en temas de prevención de riesgos con el fin de fortalecer prácticas seguras.'),
(55, 'Estado de solicitud de incentivo institucional', NULL, '1020304050', NULL, 13, NULL, '2026-02-23 14:11:57', NULL, 2, 3, 'El peticionario solicita conocer el estado actual de su solicitud de incentivo y los tiempos estimados de respuesta.'),
(56, 'Copia de resultados de examen médico ocupacional', NULL, '1020304050', NULL, 11, NULL, '2026-02-23 14:12:24', NULL, 2, 3, 'Se solicita copia de los resultados del examen médico ocupacional realizado recientemente.'),
(57, 'Solicitud de acceso al plan anual de SST', NULL, '1456333298', NULL, 14, NULL, '2026-02-23 14:15:09', NULL, 2, 3, 'Se solicita acceso o copia del plan anual de seguridad y salud en el trabajo para conocer las actividades programadas.'),
(58, 'Demora en atención médica ocupacional', NULL, '1456333298', 19, 11, '2026-03-23 18:13:01', '2026-02-23 14:15:31', NULL, 2, 4, 'El accionante manifiesta que la demora en la asignación de cita médica afecta su derecho fundamental a la salud, solicitando atención prioritaria.'),
(59, 'Riesgo laboral no atendido oportunamente', NULL, '1756664828', NULL, 14, NULL, '2026-02-21 14:17:12', NULL, 2, 4, 'Se solicita protección de derechos fundamentales ante la persistencia de un riesgo laboral que no ha sido intervenido.'),
(60, 'Negación de apoyo social en situación urgente', NULL, '1020304050', NULL, 10, NULL, '2026-02-17 14:17:51', '2026-03-30 16:36:03', 1, 4, 'El accionante solicita intervención inmediata al considerar vulnerados sus derechos por la negación de un apoyo social urgente.'),
(61, 'Denuncia cableado expuesto', NULL, '1020304050', NULL, 14, NULL, '2022-01-18 07:40:00', '2022-01-20 15:10:00', 3, 1, 'Denuncia por cableado expuesto en sala de sistemas'),
(62, 'Solicitud inspección ruido', NULL, '1456333298', NULL, 14, NULL, '2022-02-12 09:15:00', '2026-03-30 16:36:03', 1, 2, 'Solicitud de inspección por condiciones de ruido en taller'),
(63, 'Incidente menor área operativa', NULL, '1656966633', NULL, 14, NULL, '2022-03-03 10:30:00', '2022-03-07 11:20:00', 3, 1, 'Reporte de incidente menor sin lesiones en área operativa'),
(64, 'Derecho petición seguimiento SST', NULL, '1756664828', NULL, 14, NULL, '2022-04-21 14:10:00', NULL, 2, 3, 'Derecho de petición por seguimiento a reporte de seguridad'),
(65, 'Denuncia falta señalización', NULL, '1020304050', NULL, 14, NULL, '2023-01-11 08:00:00', '2023-01-15 17:00:00', 3, 1, 'Denuncia por falta de señalización en zona de carga'),
(66, 'Solicitud capacitación SST', NULL, '1456333298', NULL, 14, NULL, '2023-02-09 09:55:00', '2026-03-30 16:36:03', 1, 2, 'Solicitud de capacitación en prevención de riesgos'),
(67, 'Incidente leve laboratorio', NULL, '1656966633', NULL, 14, NULL, '2023-03-14 11:25:00', '2023-03-18 13:40:00', 3, 1, 'Incidente leve durante práctica en laboratorio'),
(68, 'Solicitud revisión EPP', NULL, '1756664828', NULL, 14, NULL, '2023-05-22 15:00:00', NULL, 2, 2, 'Solicitud de revisión de equipos de protección'),
(69, 'Derecho petición auditoría SST', NULL, '1020304050', NULL, 14, NULL, '2024-01-05 07:50:00', '2026-03-30 16:36:03', 1, 3, 'Derecho de petición sobre estado de auditoría de seguridad'),
(70, 'Denuncia riesgo ergonómico', NULL, '1456333298', NULL, 14, NULL, '2024-02-17 10:20:00', '2024-02-21 16:30:00', 3, 1, 'Denuncia por riesgo ergonómico en puesto administrativo'),
(71, 'Solicitud evaluación riesgos', NULL, '1656966633', NULL, 14, NULL, '2024-03-29 12:10:00', NULL, 2, 2, 'Solicitud de evaluación de riesgos en aula técnica'),
(72, 'Reporte caída leve', NULL, '1756664828', NULL, 14, NULL, '2024-05-03 09:00:00', '2024-05-06 14:00:00', 3, 1, 'Reporte de caída sin consecuencias graves'),
(73, 'Solicitud inspección preventiva', NULL, '1020304050', NULL, 14, NULL, '2025-01-09 08:15:00', '2026-03-30 16:36:03', 1, 2, 'Solicitud de inspección preventiva general'),
(74, 'Denuncia incumplimiento SST', NULL, '1456333298', NULL, 14, NULL, '2025-02-20 10:45:00', '2025-02-25 12:30:00', 3, 1, 'Denuncia por incumplimiento de protocolo de seguridad'),
(75, 'Derecho petición seguimiento caso', NULL, '1656966633', NULL, 14, NULL, '2025-04-10 13:30:00', NULL, 2, 3, 'Derecho de petición por seguimiento a caso SST'),
(76, 'Solicitud revisión locativa', NULL, '1756664828', NULL, 14, NULL, '2026-01-16 09:10:00', '2026-03-30 16:36:03', 1, 2, 'Solicitud de revisión de condiciones locativas'),
(77, 'Solicitud apoyo psicológico', NULL, '1020304050', NULL, 10, NULL, '2022-02-01 10:10:00', '2022-02-03 12:00:00', 3, 2, 'Solicitud de apoyo psicológico institucional'),
(78, 'Denuncia conflicto interpersonal', NULL, '1456333298', NULL, 10, NULL, '2022-06-18 11:20:00', '2026-03-30 16:36:03', 1, 1, 'Denuncia por conflicto interpersonal entre funcionarios'),
(79, 'Solicitud programa bienestar', NULL, '1656966633', NULL, 10, NULL, '2023-02-12 08:40:00', '2023-02-18 15:10:00', 3, 2, 'Solicitud de inclusión en programa de bienestar'),
(80, 'Derecho petición beneficios', NULL, '1756664828', NULL, 10, NULL, '2023-07-07 14:25:00', NULL, 2, 3, 'Derecho de petición por información de beneficios'),
(82, 'Denuncia acoso laboral', NULL, '1456333298', NULL, 10, NULL, '2024-04-11 11:15:00', '2024-04-16 16:00:00', 3, 1, 'Denuncia por presunto acoso laboral'),
(83, 'Solicitud actividad deportiva', NULL, '1656966633', NULL, 10, NULL, '2025-03-03 10:50:00', '2026-03-30 16:36:03', 1, 2, 'Solicitud de inscripción a actividad deportiva'),
(84, 'Derecho petición subsidios', NULL, '1756664828', NULL, 10, NULL, '2026-02-08 13:05:00', NULL, 2, 3, 'Derecho de petición sobre subsidios'),
(86, 'Derecho petición tecnológica', NULL, '1456333298', NULL, 11, NULL, '2023-06-02 09:10:00', '2026-03-30 16:36:03', 1, 3, 'Derecho de petición por respuesta a solicitud tecnológica'),
(87, 'Solicitud actualización usuario', NULL, '1656966633', NULL, 11, NULL, '2024-02-19 11:00:00', '2024-02-23 12:30:00', 3, 2, 'Solicitud de actualización de usuario'),
(88, 'Denuncia fallas plataforma', NULL, '1756664828', NULL, 11, NULL, '2025-05-05 14:10:00', NULL, 2, 1, 'Denuncia por fallas recurrentes en plataforma'),
(89, 'Solicitud dotación uniforme', NULL, '1020304050', NULL, 12, NULL, '2022-04-01 10:10:00', '2022-04-05 11:00:00', 3, 2, 'Solicitud de dotación de uniforme'),
(90, 'Derecho petición dotación', NULL, '1456333298', NULL, 12, NULL, '2023-08-15 09:40:00', '2026-03-30 16:36:03', 1, 3, 'Derecho de petición por entrega tardía de dotación'),
(91, 'Solicitud reposición botas', NULL, '1656966633', NULL, 12, NULL, '2024-03-20 12:30:00', '2024-03-25 15:10:00', 3, 2, 'Solicitud de reposición de botas de seguridad'),
(94, 'Solicitud inscripción incentivos', NULL, '1456333298', NULL, 13, NULL, '2026-01-20 11:20:00', NULL, 2, 2, 'Solicitud de inscripción a programa de incentivos');

--
-- Disparadores `caso`
--
DELIMITER $$
CREATE TRIGGER `tr_notificar_cambio_estado_caso` AFTER UPDATE ON `caso` FOR EACH ROW BEGIN

DECLARE mensaje_comi TEXT;
DECLARE mensaje_admin TEXT;

IF OLD.id_estado <> NEW.id_estado THEN
SELECT CONCAT (
        'El caso "', NEW.nombre, 
        '" con el ID: ', NEW.id_caso, 
        ' perteneciente al proceso "', p.nombre, 
        '", pasó del estado: "', e_old.estado,
        '" al estado: "', e_new.estado,        
        '" por el usuario encargado ', u.nombre, ' ', u.apellido
    ) INTO mensaje_comi 
	FROM usuario u
	INNER JOIN procesoorganizacional p ON p.id_proceso = NEW.id_proceso
    INNER JOIN estado e_new ON e_new.id_estado = NEW.id_estado
    INNER JOIN estado e_old ON e_old.id_estado = OLD.id_estado
    WHERE u.documento = NEW.documento;
	
	SELECT CONCAT(
        'AVISO: El caso "', NEW.nombre, 
        '" CON LA ID: ', NEW.id_caso,
        ' cambió deL estado "', e_old.estado, '" a "', e_new.estado,
        '". Por su Comisionado Responsable: ', u_resp.nombre, ' ', u_resp.apellido
    ) INTO mensaje_admin
	FROM usuario u_resp
    INNER JOIN procesoorganizacional p ON p.id_proceso = NEW.id_proceso
    INNER JOIN estado e_new ON e_new.id_estado = NEW.id_estado
    INNER JOIN estado e_old ON e_old.id_estado = OLD.id_estado
    WHERE u_resp.documento = NEW.documento;

	ELSEIF OLD.id_seguimiento <> NEW.id_seguimiento THEN
	
	SELECT CONCAT(
        'Realizaste un nuevo seguimiento al caso: "', NEW.nombre,
        '" con ID: ', NEW.id_caso,
        ', en la fecha : ', NEW.fecha_ultimo_seguimiento
    ) INTO mensaje_comi
	FROM usuario
	WHERE documento = NEW.documento;

	SELECT CONCAT(
        'AVISO: Se realizó un seguimiento al caso "', NEW.nombre,
        '" con ID: ', NEW.id_caso,
        ' por el comisionado ', u_resp.nombre, ' ', u_resp.apellido
    ) INTO mensaje_admin
	FROM usuario u_resp
	WHERE u_resp.documento = NEW.documento;

	END IF;
	
	IF mensaje_comi IS NOT NULL THEN
	
	INSERT INTO noti_comisionado(documento, mensaje, fecha)
	VALUES(
    NEW.documento, 
   	mensaje_comi,
    NOW());
	END IF;

IF mensaje_admin IS NOT NULL THEN

INSERT INTO noti_administrador(documento, mensaje, fecha)
SELECT 
    u_admin.documento, 
    mensaje_admin, 
    NOW()
FROM usuario u_admin
WHERE u_admin.id_rol = 1;
END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_notificar_registro_caso` AFTER INSERT ON `caso` FOR EACH ROW BEGIN
    INSERT INTO noti_comisionado(documento, mensaje, fecha)
    SELECT 
        NEW.documento, 
        CONCAT('NUEVO CASO: "', NEW.nombre,'" ID CASO: ', NEW.id_caso, '. \nSe ha registrado un nuevo caso de ', t.nombre_caso ,' Por Atender perteneciente al Proceso Organizacional ', p.nombre, ' asignado al comisionado ', u.nombre, ' ', u.apellido), 
        NOW()
    FROM usuario u 
	INNER JOIN tipo_caso t ON t.id_tipo_caso = NEW.id_tipo_caso
    INNER JOIN procesoorganizacional p ON p.id_proceso = NEW.id_proceso
    WHERE u.documento = NEW.documento;

	INSERT INTO noti_administrador(documento, mensaje, fecha)
	SELECT 
    	u_admin.documento,
		CONCAT('NUEVO CASO: "', NEW.nombre,'" ID CASO: ', NEW.id_caso, '. \nSe ha registrado un nuevo caso de ', t.nombre_caso ,' Por Atender perteneciente al Proceso Organizacional ', p.nombre, ' asignado al comisionado ', u_resp.nombre, ' ', u_resp.apellido),
		NOW()
FROM usuario u_admin
INNER JOIN usuario u_resp ON u_resp.documento = NEW.documento
INNER JOIN tipo_caso t ON t.id_tipo_caso = NEW.id_tipo_caso
INNER JOIN procesoorganizacional p ON p.id_proceso = NEW.id_proceso
WHERE u_admin.id_rol = 1;
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
(47, '1487569254', '2026-02-14 16:45:17', 'PDF', 'trhrthrt'),
(48, '1487569254', '2026-03-03 14:38:01', 'PDF', 'Reporte Casos'),
(49, '1487569254', '2026-03-03 14:39:10', 'EXCEL', NULL);

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

--
-- Volcado de datos para la tabla `monitoreo`
--

INSERT INTO `monitoreo` (`id_monitoreo`, `documento`, `fecha`, `tipo`, `descripcion`) VALUES
(1, '1756664828', '2026-03-16 15:23:19', 'inicio_sesion', 'NO SE'),
(2, '1487569254', '2026-03-16 15:24:02', 'inicio_sesion', 'NO SE 2'),
(3, '1487569254', '2026-03-16 15:25:47', 'accion', '3'),
(4, '1487569254', '2026-03-16 16:07:29', 'accion', 'TEXT'),
(5, '1487569254', '2026-03-16 16:30:33', 'accion', 'KORY CARRERA');

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
(120, '1487569254', 'AVISO: El caso \"d\" CON LA ID: 98 cambió deL estado \"Por atender\" a \"Atendido\". Por su Comisionado Responsable: Juan Manuel Correal', '2026-03-23 17:48:52'),
(123, '1487569254', 'AVISO: Se realizó un seguimiento al caso \"Demora en atención médica ocupacional\" con ID: 58 por el comisionado Juan Manuel Correal', '2026-03-23 18:13:01');

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
(113, '1456333298', 'El caso \"d\" con el ID: 98 perteneciente al proceso \"Plan de incentivos\", pasó del estado: \"Por atender\" al estado: \"Atendido\" por el usuario encargado Juan Manuel Correal', '2026-03-23 17:48:52'),
(114, '1456333298', 'Realizaste un nuevo seguimiento al caso: \"Demora en atención médica ocupacional\" con ID: 58, en la fecha : 2026-03-23 18:13:01', '2026-03-23 18:13:01');

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
(19, '2026-03-23 18:13:01', 'El caso aun sigue en proceso, se espera respuesta de las entidades encargadas', '1456333298', 58);

--
-- Disparadores `seguimiento`
--
DELIMITER $$
CREATE TRIGGER `tr_actualizar_ultimo_seguimiento` AFTER INSERT ON `seguimiento` FOR EACH ROW BEGIN
    UPDATE caso 
    SET 
        id_seguimiento = NEW.id_seguimiento,
        fecha_ultimo_seguimiento = NEW.fecha_seguimiento
    WHERE id_caso = NEW.id_caso;
END
$$
DELIMITER ;

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
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `token_usuario`
--

INSERT INTO `token_usuario` (`id`, `documento`, `token`, `fecha_creacion`) VALUES
(17, '1487569254', '11af60', '2026-03-23 15:49:01');

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
  `fecha_caducidad` datetime DEFAULT NULL,
  `vigencia_usuario` varchar(20) DEFAULT NULL,
  `ultimo_inicio_sesion` datetime DEFAULT NULL,
  `id_estado` tinyint(1) NOT NULL DEFAULT 1,
  `2FA` int(1) NOT NULL DEFAULT 0,
  `cookie` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`documento`, `nombre`, `apellido`, `email`, `numero`, `id_rol`, `contraseña`, `fecha_registro`, `fecha_caducidad`, `vigencia_usuario`, `ultimo_inicio_sesion`, `id_estado`, `2FA`, `cookie`) VALUES
('1020304050', 'Simon', 'Gonzalez Pelaez', 'pelaezgonzalezsimon919@gmail.com', NULL, 2, '$2y$10$GLchohxxzqrGdqUzrdhkx.W6EDHdax489rqyZskrPiNbNkzdBbjNm', '2026-02-12 14:18:58', '2028-02-12 14:18:58', '2026-2028', '2026-03-23 17:42:46', 1, 0, NULL),
('1456333298', 'Juan Manuel', 'Correal', 'gavliscorreal@gmail.com', NULL, 2, '$2y$10$fTBbRgMER/FyoOVR5e2eGuKdn0x.lxRxYQa9ZOSrYwQWylv4M6z4O', '2026-02-12 14:22:31', '2028-02-12 14:22:31', '2026-2028', '2026-03-30 17:14:35', 1, 0, NULL),
('1487569254', 'Kory', 'Carrerita', 'kory.carrera.dev@gmail.com', '3001234567', 1, '$2y$10$.ojGM8lAXRkAo9tY8JFuEOF5RJ0jrcwL05ErUzfZnaS5/fJWt6Xxq', '2026-01-24 03:14:09', '2028-01-24 03:14:09', '2026-2028', '2026-03-30 16:36:23', 1, 0, '7be3757a753976a4ca6e'),
('1656966633', 'Marleny', 'Gaviria', 'gaviriamarleny@gmail.com', NULL, 2, '$2y$10$Yszox29CROyfqKeSUdHYYuoYGJahybUK6MEOe0nRiVFjkmkQNGf2G', '2026-02-12 14:28:54', '2028-02-12 14:28:54', '2026-2028', '2026-03-02 15:52:20', 1, 0, NULL),
('1756664828', 'Zack', 'Lopez', 'isaacmanuelcavajal1356@gmail.com', '3001234567', 2, '$2y$10$ddgxYzealY0ADRBf3t/0NO/ZNWCaJ/aaIXUaAvIJUFIzw9hABitkW', '2026-02-12 14:20:29', '2028-02-12 14:20:29', '2026-2028', '2026-03-12 12:55:03', 1, 1, NULL);

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
            '". Fecha de registro: ', NEW.fecha_registro, '. Vigencia: ', NEW.vigencia_usuario, '.'
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
  ADD KEY `id_proceso` (`id_proceso`),
  ADD KEY `fk_id_ultimo_seguimiento` (`id_seguimiento`);

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
  MODIFY `id_archivo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para encontrar y relacionar', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `caso`
--
ALTER TABLE `caso`
  MODIFY `id_caso` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK de casos', AUTO_INCREMENT=105;

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
  MODIFY `id_informe` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para ubicar y relacionar', AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `monitoreo`
--
ALTER TABLE `monitoreo`
  MODIFY `id_monitoreo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Llave primaria para reconocimiento y relacion', AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `noti_administrador`
--
ALTER TABLE `noti_administrador`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT de la tabla `noti_comisionado`
--
ALTER TABLE `noti_comisionado`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para relacionar y encontrar', AUTO_INCREMENT=121;

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
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para encontrar y relacionar', AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `tipo_caso`
--
ALTER TABLE `tipo_caso`
  MODIFY `id_tipo_caso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `token_usuario`
--
ALTER TABLE `token_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
  ADD CONSTRAINT `fk_id_ultimo_seguimiento` FOREIGN KEY (`id_seguimiento`) REFERENCES `seguimiento` (`id_seguimiento`) ON DELETE CASCADE ON UPDATE CASCADE,
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
CREATE EVENT `ev_caso_caducado` ON SCHEDULE EVERY 1 DAY STARTS '2026-03-17 23:00:00' ON COMPLETION PRESERVE ENABLE DO BEGIN
    UPDATE caso
    SET id_estado = 3
    WHERE 
        
        COALESCE(fecha_ultimo_seguimiento, fecha_inicio) <= NOW() - INTERVAL 2 MONTH
        AND id_estado <> 3;
END$$

CREATE EVENT `ev_caducar_usuarios_vencidos` ON SCHEDULE EVERY 1 DAY STARTS '2026-03-18 00:00:00' ON COMPLETION PRESERVE ENABLE DO BEGIN
UPDATE usuario
SET id_estado = 0
WHERE fecha_caducidad <= NOW()
AND id_estado = 1;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
