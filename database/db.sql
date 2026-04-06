-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db_sena
-- Tiempo de generación: 06-04-2026 a las 00:21:19
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
CREATE PROCEDURE `sp_activar_2FA` (IN `p_documento` VARCHAR(20), IN `p_estado_2fa` INT)   BEGIN

UPDATE usuario SET 2FA = p_estado_2fa WHERE documento = p_documento;

END$$

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

CREATE PROCEDURE `sp_actualizar_password_usuario` (IN `p_documento` VARCHAR(20), IN `p_nueva_password` VARCHAR(255))   BEGIN
    UPDATE usuario
    SET contraseña = p_nueva_password
    WHERE documento = p_documento;
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

CREATE PROCEDURE `sp_cambiar_estado_proceso` (IN `p_id_proceso` INT, IN `p_motivo` TEXT, IN `p_documento` VARCHAR(20), IN `p_estado` INT)   BEGIN 
	
	DECLARE v_nombre VARCHAR(100);
	DECLARE v_nombre_admin VARCHAR(200);
    
	DECLARE v_mensaje_admin TEXT;
	DECLARE v_mensaje_comi TEXT;
	DECLARE v_mensaje_encargado TEXT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        ROLLBACK;
    END;

	START TRANSACTION;
    
	SELECT nombre INTO v_nombre FROM procesoorganizacional WHERE id_proceso = p_id_proceso;
	SELECT CONCAT(nombre, ' ', apellido) INTO v_nombre_admin FROM usuario WHERE documento = p_documento;

	IF p_estado = 0 THEN
	
    SET v_mensaje_admin = CONCAT('El proceso "', v_nombre, '" ha sido desactivado por el administrador ', v_nombre_admin, ', por el siguiente motivo: "', p_motivo, '". Todos los casos relacionados a este proceso seguiran siendo del mismo, pero ahora no se podran registrar casos para este proceso.');

	SET v_mensaje_comi = CONCAT('El proceso "', v_nombre, '" ha sido desactivado por el administrador ', v_nombre_admin, ', por el siguiente motivo: "', p_motivo, '". Todos los casos relacionados a este proceso seguiran siendo del mismo, pero ahora no se podran registrar casos para este proceso.');

	SET v_mensaje_encargado = CONCAT('Has desactivado el proceso "', v_nombre,'", ten en cuenta que esto no hara que los casos relacionado a este proceso se desligen del mismo, pero los comisionados ya no podran registrar casos para este proceso');

	ELSEIF p_estado = 1 THEN

	SET v_mensaje_admin = CONCAT('El proceso "', v_nombre, '" ha sido activado por el administrador ', v_nombre_admin, ', por el siguiente motivo: "', p_motivo, '". Todos los casos relacionados a este proceso seguiran siendo del mismo.');

	SET v_mensaje_comi = CONCAT('El proceso "', v_nombre, '" ha sido activado por el administrador ', v_nombre_admin, ', por el siguiente motivo: "', p_motivo, '". Todos los casos relacionados a este proceso seguiran siendo del mismo.');

	SET v_mensaje_encargado = CONCAT('Has activado el proceso "', v_nombre,'", ten en cuenta que esto hara que los comisionados ahora podran registrar casos para este proceso');
    
    END IF;

	UPDATE procesoorganizacional SET estado = p_estado WHERE id_proceso = p_id_proceso;
    
    INSERT INTO monitoreo(documento, fecha, tipo, descripcion) 
	VALUES(p_documento, NOW(), 2, p_motivo);
    
    INSERT INTO noti_administrador (documento, mensaje, fecha)
    SELECT documento, v_mensaje_admin, NOW()
	FROM usuario WHERE id_rol = 1 AND documento <> p_documento;

	INSERT INTO noti_comisionado (documento, mensaje, fecha) 
	SELECT documento, v_mensaje_comi, NOW()
	FROM usuario WHERE id_rol = 2; 

	INSERT INTO noti_administrador (documento, mensaje, fecha)
	VALUES (p_documento, v_mensaje_encargado, NOW());

    COMMIT;
END$$

CREATE PROCEDURE `sp_cambiar_estado_usuario` (IN `p_documento` VARCHAR(20), IN `p_estado` INT, IN `p_motivo` TEXT, IN `p_documento_admin` VARCHAR(20))   BEGIN 	

DECLARE v_nombre VARCHAR(200);
DECLARE v_nombre_admin VARCHAR(200);
DECLARE v_admin_email VARCHAR(100);
DECLARE v_rol INT;

DECLARE v_mensaje_admin TEXT;
DECLARE v_mensaje_comi TEXT;
DECLARE v_mensaje_admin_2 TEXT;

DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        ROLLBACK;
    END;

START TRANSACTION;

SELECT CONCAT(nombre, ' ', apellido) INTO v_nombre FROM usuario WHERE documento = p_documento;
SELECT CONCAT(nombre, ' ', apellido), email INTO v_nombre_admin, v_admin_email FROM usuario WHERE documento = p_documento_admin;

SELECT id_rol INTO v_rol FROM usuario WHERE documento = p_documento;

IF p_estado = 0 AND v_rol = 2 THEN

	SET v_mensaje_admin = CONCAT('HAS INHABILITADO AL USUARIO "', v_nombre, '" DEL SISTEMA el día: ',  CURRENT_DATE, ', por el siguiente motivo: "', p_motivo, '". En caso de error, revierta de inmediato la acción. Tenga en cuenta que los casos "Por Atender" del usuario que ha deshabilitado se encontrarán en el estado "Por Reasignar", y para volver a asignar dichos casos a su Comisionado encargado deberá hacerlo manualmente uno por uno. Recuerde que desactivar un usuario es una acción riesgosa, y se aconseja realizarse únicamente en casos de absoluta necesidad. El sistema por sí solo, una vez se cumple con la vigencia, desactiva de forma automática a todos los usuarios caducados.');
	
	SET v_mensaje_comi = CONCAT('HAS SIDO INHABILITADO DEL SISTEMA: ', v_nombre, ' el día: ',  CURRENT_DATE,  
'. Has sido INHABILITADO por el administrador encargado: ', v_nombre_admin, ', por el siguiente motivo: "', p_motivo, '". En caso de error comuníquese con el administrador encargado a través del siguiente correo: ', v_admin_email, '.');

ELSEIF p_estado = 1 AND v_rol = 2 THEN 

	SET v_mensaje_admin = CONCAT('HAS HABILITADO AL USUARIO "', v_nombre, '" el día: ',  CURRENT_DATE, ', por el siguiente motivo: "', p_motivo, '". En caso de error revierta de inmediato la acción, y se aconseja realizar esta acción únicamente en casos de absoluta necesidad.');
    
	SET v_mensaje_comi = CONCAT('HAS SIDO HABILITADO EN EL SISTEMA: ', v_nombre, ' el día: ',  CURRENT_DATE,  
'. Has sido HABILITADO por el administrador encargado: ', v_nombre_admin, ', por el siguiente motivo: "', p_motivo, '". Ahora tienes acceso nuevamente a las funciones de tu rol como comisionado, pero tus casos antiguos han sido asignados a otro comisionado, o en su defecto se encuentran en el estado "Por Asignar". En caso de error comuníquese con el administrador encargado a través del siguiente correo: ', v_admin_email, '.');

ELSEIF p_estado = 0 AND v_rol = 1 THEN

	SET v_mensaje_admin = CONCAT('HAS INHABILITADO AL ADMINISTRADOR "', v_nombre, '" DEL SISTEMA el día: ',  CURRENT_DATE, ', por el siguiente motivo: "', p_motivo, '". En caso de error revierta de inmediato la acción, y recuerde que desactivar un usuario es una acción riesgosa, y se aconseja realizarse únicamente en casos de absoluta necesidad.');
	
	SET v_mensaje_admin_2 = CONCAT('HAS SIDO INHABILITADO DEL SISTEMA: ', v_nombre, ' el día: ',  CURRENT_DATE,  
'. Has sido INHABILITADO por el administrador: ', v_nombre_admin, ', por el siguiente motivo: "', p_motivo, '". En caso de error comuníquese con el administrador encargado a través del siguiente correo: ', v_admin_email, '.');

ELSEIF p_estado = 1 AND v_rol = 1 THEN 

	SET v_mensaje_admin = CONCAT('HAS HABILITADO AL USUARIO "', v_nombre, '" el día: ',  CURRENT_DATE, ', por el siguiente motivo: "', p_motivo, '". El usuario en cuestión es un administrador, así que tenga en cuenta que al habilitarlo nuevamente lo hace con dicho rol. En caso de error revierta de inmediato la acción, y se aconseja realizar esta acción únicamente en casos de absoluta necesidad.');
    
	SET v_mensaje_admin_2 = CONCAT('HAS SIDO HABILITADO EN EL SISTEMA: ', v_nombre, ' el día: ',  CURRENT_DATE,  
'. Has sido HABILITADO por el administrador encargado: ', v_nombre_admin, ' por el siguiente motivo: "', p_motivo, '". Nuevamente tienes acceso a las funciones de tu rol como administrador. En caso de error comuníquese con el administrador encargado a través del siguiente correo: ', v_admin_email, '.');

END IF;

UPDATE usuario SET id_estado = p_estado WHERE documento = p_documento;

-- Cambiado el 2 por 'accion' para coincidir exactamente con el ENUM
INSERT INTO monitoreo(documento, fecha, tipo, descripcion) VALUES(p_documento, NOW(), 'accion', p_motivo);

IF v_mensaje_comi IS NOT NULL THEN 

	INSERT INTO noti_comisionado(documento, mensaje, fecha) 
	VALUES(p_documento, v_mensaje_comi,  NOW());

ELSEIF v_mensaje_admin_2 IS NOT NULL THEN

	INSERT INTO noti_administrador(documento, mensaje, fecha) 
	VALUES(p_documento, v_mensaje_admin_2,  NOW());

END IF;

IF v_mensaje_admin IS NOT NULL THEN
	INSERT INTO noti_administrador(documento, mensaje, fecha) VALUES(p_documento_admin, v_mensaje_admin, NOW());
END IF;

SELECT id_estado FROM usuario WHERE documento = p_documento;

COMMIT;


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

CREATE PROCEDURE `sp_casos_por_comisionado_doc` (IN `p_documento` VARCHAR(50))   BEGIN
    DECLARE v_gran_total INT;

    SELECT COUNT(id_caso) INTO v_gran_total 
    FROM caso
    WHERE documento = p_documento;

    SELECT 
        e.estado AS nombre_estado,
        COUNT(c.id_caso) AS total_casos,
        v_gran_total AS gran_total
    FROM estado e
    LEFT JOIN caso c ON e.id_estado = c.id_estado AND c.documento = p_documento
    GROUP BY e.id_estado, e.estado;

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

CREATE PROCEDURE `sp_estadisticas_procesos_comisionado` (IN `p_documento` VARCHAR(50))   BEGIN
    SELECT 
        p.nombre AS proceso,
        COUNT(c.id_caso) AS total_casos
    FROM 
        caso c
    INNER JOIN 
        procesoorganizacional p ON c.id_proceso = p.id_proceso
    WHERE 
        c.documento = p_documento
    GROUP BY 
        p.id_proceso, p.nombre
    ORDER BY 
        total_casos DESC;
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

CREATE PROCEDURE `sp_guardar_cookie` (IN `p_documento` VARCHAR(20), IN `p_cookie` VARCHAR(20))   UPDATE usuario SET cookie = p_cookie WHERE documento = p_documento$$

CREATE PROCEDURE `sp_guardar_token_2fa` (IN `p_documento` VARCHAR(20), IN `p_token` VARCHAR(70))   INSERT INTO token_usuario(documento, token) VALUES(p_documento, p_token)$$

CREATE PROCEDURE `sp_insertar_archivo_caso` (IN `p_id_caso` INT, IN `p_nombre_archivo` VARCHAR(255), IN `p_ruta` VARCHAR(255), IN `p_tipo_archivo` VARCHAR(50))   BEGIN
INSERT INTO archivo (id_caso, nombre_archivo, ruta, tipo_archivo, fecha_subida)
VALUES (p_id_caso, p_nombre_archivo, p_ruta, p_tipo_archivo, NOW());
END$$

CREATE PROCEDURE `sp_listar_archivos_caso` (IN `p_id_caso` INT)   BEGIN
	SELECT id_archivo, nombre_archivo, ruta, tipo_archivo, fecha_subida
	FROM archivo
   	WHERE id_caso = p_id_caso
	ORDER BY fecha_subida DESC;
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

CREATE PROCEDURE `sp_listar_casos_comisionado_doc` (IN `p_documento` VARCHAR(50))   BEGIN
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
    WHERE c.documento = p_documento 
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

CREATE PROCEDURE `sp_listar_comisionados_y_casos` ()   BEGIN
    	
        SELECT 
    u.documento,
    CONCAT(u.nombre, ' ', u.apellido) AS comisionado,
    COUNT(c.id_caso) AS total_casos
FROM usuario u
LEFT JOIN caso c 
    ON u.documento = c.documento
WHERE u.id_rol = 2 
  AND u.id_estado = 1
GROUP BY u.documento, u.nombre, u.apellido
ORDER BY total_casos DESC;
        
    END$$

CREATE PROCEDURE `sp_listar_estados_caso` ()   BEGIN
    SELECT 
        id_estado,
        estado
    FROM estado
    ORDER BY id_estado ASC;
END$$

CREATE PROCEDURE `sp_listar_noti_admin` (IN `p_documento` VARCHAR(20))   BEGIN
SELECT	
	id_notificacion AS id, 
	documento, 
	mensaje AS descripción,
    fecha 
FROM noti_administrador
WHERE documento = p_documento
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
    SET documento = p_documento_nuevo, id_estado = 2
    WHERE id_caso = p_id_caso AND id_estado = 2 OR id_caso = p_id_caso AND id_estado = 3 OR id_caso = p_id_caso AND id_estado = 4;

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

CREATE PROCEDURE `sp_usuario_por_token` (IN `p_token` VARCHAR(255))   BEGIN
    SELECT 
        u.documento,
        u.nombre,
        u.email
    FROM token_usuario tu
    INNER JOIN usuario u 
        ON u.documento = tu.documento
    WHERE tu.token = p_token
    LIMIT 1;
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

--
-- Volcado de datos para la tabla `archivo`
--

INSERT INTO `archivo` (`id_archivo`, `id_caso`, `nombre_archivo`, `fecha_subida`, `ruta`, `tipo_archivo`) VALUES
(2, 135, 'Los 12 Trabajos de Heracles y su significado profundo.pdf', '2026-04-06 00:17:53', 'uploads/casos/caso#135/archivo_69d2fbb1c3dcd.pdf', 'documento'),
(3, 135, 'Reporte_Usuarios_SENA.pdf', '2026-04-06 00:17:53', 'uploads/casos/caso#135/archivo_69d2fbb1d91cd.pdf', 'documento'),
(4, 135, '579e4c2063571440.png', '2026-04-06 00:17:54', 'uploads/casos/caso#135/archivo_69d2fbb1ea740.png', 'imagen');

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
(50, 'Posible trato desigual en asignación de incentivos', NULL, '1020304050', NULL, 13, NULL, '2026-02-09 12:49:15', '2026-03-30 16:36:03', 1, 1, 'El funcionario manifiesta inconformidad debido a que considera que los criterios de evaluación no se aplicaron de manera equitativa en su área, afectando la asignación de incentivos.'),
(52, 'Presunto maltrato laboral por parte de superior', NULL, '1456333298', NULL, 10, NULL, '2026-02-23 12:50:29', '2026-03-30 16:36:03', 1, 1, 'El colaborador reporta comportamientos reiterados de trato inapropiado y comunicación inadecuada por parte de su jefe inmediato, solicitando revisión del caso.'),
(53, 'Programación de examen médico ocupacional', NULL, '1656966633', 50, 11, '2026-04-05 23:49:26', '2026-02-19 12:53:33', '2026-04-05 22:28:50', 2, 2, 'El colaborador solicita la programación de su examen médico ocupacional periódico para seguimiento de su estado de salud laboral.'),
(54, 'Capacitación en prevención de riesgos laborales', NULL, '1756664828', NULL, 14, NULL, '2026-02-23 12:53:57', '2026-04-05 22:23:59', 1, 2, 'Se solicita capacitación para el equipo de trabajo en temas de prevención de riesgos con el fin de fortalecer prácticas seguras.'),
(55, 'Estado de solicitud de incentivo institucional', NULL, '1020304050', NULL, 13, NULL, '2026-02-23 14:11:57', NULL, 2, 3, 'El peticionario solicita conocer el estado actual de su solicitud de incentivo y los tiempos estimados de respuesta.'),
(56, 'Copia de resultados de examen médico ocupacional', NULL, '1756664828', 44, 11, '2026-04-05 23:41:18', '2026-02-23 14:12:24', NULL, 2, 3, 'Se solicita copia de los resultados del examen médico ocupacional realizado recientemente.'),
(59, 'Riesgo laboral no atendido oportunamente', NULL, '1756664828', 42, 14, '2026-04-05 23:39:37', '2026-02-21 14:17:12', NULL, 2, 4, 'Se solicita protección de derechos fundamentales ante la persistencia de un riesgo laboral que no ha sido intervenido.'),
(60, 'Negación de apoyo social en situación urgente', NULL, '1020304050', NULL, 10, NULL, '2026-02-17 14:17:51', '2026-03-30 16:36:03', 1, 4, 'El accionante solicita intervención inmediata al considerar vulnerados sus derechos por la negación de un apoyo social urgente.'),
(61, 'Denuncia cableado expuesto', NULL, '1756664828', 45, 14, '2026-04-05 23:44:12', '2022-01-18 07:40:00', '2022-01-20 15:10:00', 2, 1, 'Denuncia por cableado expuesto en sala de sistemas'),
(62, 'Solicitud inspección ruido', NULL, '1456333298', NULL, 14, NULL, '2022-02-12 09:15:00', '2026-03-30 16:36:03', 1, 2, 'Solicitud de inspección por condiciones de ruido en taller'),
(63, 'Incidente menor área operativa', NULL, '1020304050', NULL, 14, NULL, '2022-03-03 10:30:00', '2022-03-07 11:20:00', 2, 1, 'Reporte de incidente menor sin lesiones en área operativa'),
(64, 'Derecho petición seguimiento SST', NULL, '1756664828', NULL, 14, NULL, '2022-04-21 14:10:00', NULL, 2, 3, 'Derecho de petición por seguimiento a reporte de seguridad'),
(65, 'Denuncia falta señalización', NULL, '1020304050', NULL, 14, NULL, '2023-01-11 08:00:00', '2023-01-15 17:00:00', 2, 1, 'Denuncia por falta de señalización en zona de carga'),
(66, 'Solicitud capacitación SST', NULL, '1456333298', NULL, 14, NULL, '2023-02-09 09:55:00', '2026-03-30 16:36:03', 1, 2, 'Solicitud de capacitación en prevención de riesgos'),
(67, 'Incidente leve laboratorio', NULL, '1756664828', 48, 14, '2026-04-05 23:45:40', '2023-03-14 11:25:00', '2023-03-18 13:40:00', 2, 1, 'Incidente leve durante práctica en laboratorio'),
(68, 'Solicitud revisión EPP', NULL, '1756664828', NULL, 14, NULL, '2023-05-22 15:00:00', NULL, 2, 2, 'Solicitud de revisión de equipos de protección'),
(69, 'Derecho petición auditoría SST', NULL, '1020304050', NULL, 14, NULL, '2024-01-05 07:50:00', '2026-03-30 16:36:03', 1, 3, 'Derecho de petición sobre estado de auditoría de seguridad'),
(70, 'Denuncia riesgo ergonómico', NULL, '1456333298', 49, 14, '2026-04-05 23:46:16', '2024-02-17 10:20:00', '2024-02-21 16:30:00', 4, 1, 'Denuncia por riesgo ergonómico en puesto administrativo'),
(71, 'Solicitud evaluación riesgos', NULL, '1020304050', 32, 14, '2026-04-05 23:10:52', '2024-03-29 12:10:00', NULL, 2, 2, 'Solicitud de evaluación de riesgos en aula técnica'),
(72, 'Reporte caída leve', NULL, '1020304050', NULL, 14, NULL, '2024-05-03 09:00:00', '2024-05-06 14:00:00', 2, 1, 'Reporte de caída sin consecuencias graves'),
(73, 'Solicitud inspección preventiva', NULL, '1020304050', NULL, 14, NULL, '2025-01-09 08:15:00', '2026-03-30 16:36:03', 1, 2, 'Solicitud de inspección preventiva general'),
(74, 'Denuncia incumplimiento SST', NULL, '1020304050', NULL, 14, NULL, '2025-02-20 10:45:00', '2025-02-25 12:30:00', 2, 1, 'Denuncia por incumplimiento de protocolo de seguridad'),
(75, 'Derecho petición seguimiento caso', NULL, '1020304050', NULL, 14, NULL, '2025-04-10 13:30:00', NULL, 2, 3, 'Derecho de petición por seguimiento a caso SST'),
(76, 'Solicitud revisión locativa', NULL, '1756664828', NULL, 14, NULL, '2026-01-16 09:10:00', '2026-03-30 16:36:03', 1, 2, 'Solicitud de revisión de condiciones locativas'),
(77, 'Solicitud apoyo psicológico', NULL, '1020304050', NULL, 10, NULL, '2022-02-01 10:10:00', '2022-02-03 12:00:00', 2, 2, 'Solicitud de apoyo psicológico institucional'),
(78, 'Denuncia conflicto interpersonal', NULL, '1456333298', NULL, 10, NULL, '2022-06-18 11:20:00', '2026-03-30 16:36:03', 1, 1, 'Denuncia por conflicto interpersonal entre funcionarios'),
(79, 'Solicitud programa bienestar', NULL, '1020304050', NULL, 10, NULL, '2023-02-12 08:40:00', '2023-02-18 15:10:00', 2, 2, 'Solicitud de inclusión en programa de bienestar'),
(80, 'Derecho petición beneficios', NULL, '1756664828', NULL, 10, NULL, '2023-07-07 14:25:00', NULL, 2, 3, 'Derecho de petición por información de beneficios'),
(82, 'Denuncia acoso laboral', NULL, '1020304050', NULL, 10, NULL, '2024-04-11 11:15:00', '2024-04-16 16:00:00', 2, 1, 'Denuncia por presunto acoso laboral'),
(83, 'Solicitud actividad deportiva', NULL, '1656966633', NULL, 10, NULL, '2025-03-03 10:50:00', '2026-03-30 16:36:03', 1, 2, 'Solicitud de inscripción a actividad deportiva'),
(84, 'Derecho petición subsidios', NULL, '1756664828', NULL, 10, NULL, '2026-02-08 13:05:00', NULL, 2, 3, 'Derecho de petición sobre subsidios'),
(86, 'Derecho petición tecnológica', NULL, '1456333298', NULL, 11, NULL, '2023-06-02 09:10:00', '2026-03-30 16:36:03', 1, 3, 'Derecho de petición por respuesta a solicitud tecnológica'),
(87, 'Solicitud actualización usuario', NULL, '1020304050', NULL, 11, NULL, '2024-02-19 11:00:00', '2024-02-23 12:30:00', 2, 2, 'Solicitud de actualización de usuario'),
(88, 'Denuncia fallas plataforma', NULL, '1756664828', NULL, 11, NULL, '2025-05-05 14:10:00', NULL, 2, 1, 'Denuncia por fallas recurrentes en plataforma'),
(89, 'Solicitud dotación uniforme', NULL, '1020304050', NULL, 12, NULL, '2022-04-01 10:10:00', '2022-04-05 11:00:00', 2, 2, 'Solicitud de dotación de uniforme'),
(90, 'Derecho petición dotación', NULL, '1456333298', NULL, 12, NULL, '2023-08-15 09:40:00', '2026-03-30 16:36:03', 1, 3, 'Derecho de petición por entrega tardía de dotación'),
(91, 'Solicitud reposición botas', NULL, '1756664828', 46, 12, '2026-04-05 23:44:57', '2024-03-20 12:30:00', '2024-03-25 15:10:00', 2, 2, 'Solicitud de reposición de botas de seguridad'),
(94, 'Solicitud inscripción incentivos', NULL, '1456333298', NULL, 13, NULL, '2026-01-20 11:20:00', NULL, 4, 2, 'Solicitud de inscripción a programa de incentivos'),
(106, 'Denuncia riesgo eléctrico', NULL, '1756664828', NULL, 14, NULL, '2026-01-10 08:15:00', NULL, 1, 1, 'Se reporta posible riesgo eléctrico por tomas sobrecargadas en oficina administrativa'),
(107, 'Solicitud inspección seguridad', NULL, '1656966633', NULL, 14, NULL, '2026-01-22 10:20:00', NULL, 1, 2, 'Se solicita inspección preventiva en zona de almacenamiento'),
(108, 'Denuncia falta señalización', NULL, '1456333298', NULL, 14, NULL, '2026-02-03 09:10:00', NULL, 1, 1, 'Se reporta ausencia de señalización en área de tránsito interno'),
(109, 'Solicitud capacitación SST', NULL, '1020304050', NULL, 14, NULL, '2026-02-18 11:40:00', NULL, 2, 2, 'Funcionario solicita capacitación sobre prevención de riesgos laborales'),
(110, 'Derecho petición seguimiento', NULL, '1756664828', NULL, 14, NULL, '2026-03-02 14:30:00', NULL, 2, 3, 'Se solicita información sobre seguimiento de reporte de seguridad'),
(111, 'Denuncia condiciones inseguras', NULL, '1656966633', NULL, 14, NULL, '2026-03-19 08:55:00', NULL, 1, 1, 'Se reportan condiciones inseguras en zona de carga'),
(112, 'Solicitud revisión extintores', NULL, '1456333298', NULL, 14, NULL, '2026-04-07 10:05:00', NULL, 1, 2, 'Se solicita verificación de estado de extintores'),
(113, 'Denuncia accidente leve', NULL, '1020304050', NULL, 14, NULL, '2026-04-21 13:10:00', NULL, 2, 1, 'Se reporta accidente menor durante jornada laboral'),
(114, 'Solicitud evaluación riesgos', NULL, '1756664828', NULL, 14, NULL, '2026-05-04 09:30:00', NULL, 1, 2, 'Se solicita evaluación de riesgos en área técnica'),
(115, 'Denuncia incumplimiento protocolo', NULL, '1656966633', NULL, 14, NULL, '2026-05-16 15:20:00', NULL, 1, 1, 'Se reporta incumplimiento de normas de seguridad'),
(116, 'Solicitud inspección preventiva', NULL, '1456333298', NULL, 14, NULL, '2026-06-01 08:40:00', NULL, 1, 2, 'Se solicita inspección preventiva en área administrativa'),
(117, 'Denuncia cableado deteriorado', NULL, '1020304050', NULL, 14, NULL, '2026-06-20 11:10:00', NULL, 2, 1, 'Se detecta cableado deteriorado en zona de equipos'),
(118, 'Solicitud apoyo psicológico', NULL, '1756664828', NULL, 10, NULL, '2026-01-15 09:10:00', NULL, 1, 2, 'Funcionario solicita acompañamiento psicológico institucional'),
(119, 'Denuncia conflicto laboral', NULL, '1656966633', NULL, 10, NULL, '2026-02-12 10:20:00', NULL, 1, 1, 'Se reporta conflicto interpersonal entre funcionarios'),
(120, 'Derecho petición beneficios', NULL, '1456333298', NULL, 10, NULL, '2026-03-28 11:00:00', NULL, 4, 3, 'Se solicita información sobre beneficios institucionales'),
(121, 'Solicitud actividad bienestar', NULL, '1020304050', NULL, 10, NULL, '2026-04-18 14:10:00', NULL, 1, 2, 'Se solicita participación en actividad de bienestar laboral'),
(122, 'Denuncia acoso laboral', NULL, '1756664828', NULL, 10, NULL, '2026-05-26 13:30:00', NULL, 1, 1, 'Se presenta denuncia por presunto acoso laboral'),
(123, 'Solicitud apoyo social', NULL, '1020304050', NULL, 10, NULL, '2026-06-09 10:10:00', NULL, 2, 2, 'Funcionario solicita apoyo social por situación familiar'),
(124, 'Solicitud soporte plataforma', NULL, '1456333298', NULL, 11, NULL, '2026-01-27 08:50:00', NULL, 1, 2, 'Se solicita soporte técnico en plataforma institucional'),
(125, 'Derecho petición sistema', NULL, '1456333298', 33, 11, '2026-04-05 23:14:00', '2026-03-05 10:15:00', NULL, 4, 3, 'Se solicita información sobre estado de solicitud tecnológica'),
(126, 'Denuncia fallas sistema', NULL, '1756664828', NULL, 11, NULL, '2026-04-30 09:45:00', NULL, 1, 1, 'Se reportan fallas recurrentes en plataforma institucional'),
(127, 'Solicitud actualización usuario', NULL, '1656966633', NULL, 11, NULL, '2026-06-11 11:25:00', NULL, 1, 2, 'Se solicita actualización de permisos de usuario'),
(128, 'Solicitud dotación uniforme', NULL, '1456333298', NULL, 12, NULL, '2026-02-14 09:30:00', NULL, 1, 2, 'Se solicita entrega de uniforme institucional'),
(129, 'Derecho petición dotación', NULL, '1020304050', NULL, 12, NULL, '2026-04-09 10:50:00', NULL, 2, 3, 'Se solicita información sobre entrega de dotación pendiente'),
(130, 'Solicitud información incentivos', NULL, '1756664828', NULL, 13, NULL, '2026-03-11 08:20:00', NULL, 1, 2, 'Funcionario solicita información sobre plan de incentivos'),
(131, 'Derecho petición incentivos', NULL, '1020304050', NULL, 13, NULL, '2026-05-08 11:40:00', NULL, 2, 3, 'Se solicita respuesta sobre participación en programa de incentivos'),
(132, 'Juanito le pego a una profesora', '123456', '1456333298', 34, 11, '2026-04-05 23:24:39', '2026-04-05 23:19:01', NULL, 4, 1, 'Juanito le pego a una profesora por que no le dio la nota que merecia'),
(135, 'Reporte comisionado', '56723', '1756664828', NULL, 10, NULL, '2026-04-06 00:17:53', NULL, 2, 2, 'Ver archivos adjuntos en registrar caso');

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
	SELECT
    NEW.documento, 
   	mensaje_comi,
    NOW() WHERE OLD.id_estado = 1;
	END IF;

IF mensaje_admin IS NOT NULL THEN

INSERT INTO noti_administrador(documento, mensaje, fecha)
SELECT 
    u_admin.documento, 
    mensaje_admin, 
    NOW()
FROM usuario u_admin
WHERE u_admin.id_rol = 1 AND OLD.id_estado = 1;
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
(3, 'No atendido'),
(4, 'Por asignar');

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
(49, '1487569254', '2026-03-03 14:39:10', 'EXCEL', NULL),
(50, '1456333298', '2026-04-02 22:49:41', 'PDF', 'Reporte Mis Casos PQRSD'),
(51, '1456333298', '2026-04-02 22:49:53', 'PDF', 'Reporte Usuarios'),
(52, '1456333298', '2026-04-02 22:49:57', 'PDF', 'Reporte Procesos Comisionado'),
(53, '1487569254', '2026-04-02 22:50:50', 'PDF', 'Reporte Casos'),
(54, '1487569254', '2026-04-02 22:51:17', 'PDF', 'Reporte Usuarios'),
(55, '1487569254', '2026-04-02 22:51:44', 'PDF', 'Reporte Procesos');

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
(5, '1487569254', '2026-03-16 16:30:33', 'accion', 'KORY CARRERA'),
(6, '1487569254', '2026-04-01 13:01:01', 'accion', 'gqwgqgbqwsbs'),
(7, '1487569254', '2026-04-01 13:01:33', 'accion', 'vdssbdsbsfg'),
(8, '1487569254', '2026-04-01 13:04:33', 'accion', 'fqwgqegwqe4'),
(9, '1487569254', '2026-04-01 13:04:50', 'accion', 'wfwqagqahrrhn'),
(10, '1487569254', '2026-04-01 13:05:38', 'accion', 'gsrhrjerjyrryts'),
(11, '1487569254', '2026-04-01 13:21:22', 'accion', 'Se reasigna'),
(16, '1487569254', '2026-04-04 00:20:13', 'accion', 'Prueba de reasignacion'),
(17, '1487569254', '2026-04-04 00:23:09', 'accion', 'Prueba 2, hubo un error'),
(20, '1656966633', '2026-04-05 22:31:33', 'accion', 'Por que si'),
(21, '1487569254', '2026-04-05 22:33:45', 'accion', 'Se desactivo el usuario anterior'),
(22, '1487569254', '2026-04-05 22:34:52', 'accion', 'Por que si'),
(23, '1656966633', '2026-04-05 22:35:59', 'accion', 'Prueba'),
(24, '1487569254', '2026-04-05 22:38:27', 'accion', 'xd'),
(25, '1487569254', '2026-04-05 22:43:09', 'accion', 'Por que si'),
(26, '1487569254', '2026-04-05 23:10:52', 'accion', '71'),
(27, '1487569254', '2026-04-05 23:14:00', 'accion', 'OTRO INTENTO'),
(28, '1656966633', '2026-04-05 23:23:31', 'accion', 'Hola bb'),
(29, '1487569254', '2026-04-05 23:24:39', 'accion', 'Caso sin usuario asignado'),
(30, '1487569254', '2026-04-05 23:36:50', 'accion', 'hola'),
(31, '1487569254', '2026-04-05 23:37:24', 'accion', 'Usuario con muchos casos'),
(32, '1487569254', '2026-04-05 23:37:25', 'accion', 'Usuario con muchos casos'),
(33, '1487569254', '2026-04-05 23:37:40', 'accion', 'Usuario con muchos casos'),
(34, '1487569254', '2026-04-05 23:37:55', 'accion', 'Usuario con muchos casos'),
(35, '1487569254', '2026-04-05 23:38:18', 'accion', 'Usuario con muchos casos'),
(36, '1487569254', '2026-04-05 23:38:58', 'accion', 'Usuario con muchos casos'),
(37, '1487569254', '2026-04-05 23:39:37', 'accion', 'Usuario con muchos casos'),
(38, '1487569254', '2026-04-05 23:41:18', 'accion', 'Usuario con muchos casos'),
(39, '1487569254', '2026-04-05 23:41:18', 'accion', 'Usuario con muchos casos'),
(40, '1487569254', '2026-04-05 23:44:12', 'accion', 'Usuario con muchos casos'),
(41, '1487569254', '2026-04-05 23:44:57', 'accion', 'Usuario con muchos casos'),
(42, '1487569254', '2026-04-05 23:45:39', 'accion', 'Usuario con muchos casos'),
(43, '1487569254', '2026-04-05 23:45:40', 'accion', 'Usuario con muchos casos'),
(44, '1487569254', '2026-04-05 23:46:16', 'accion', 'Usuario con muchos casos'),
(45, '1656966633', '2026-04-05 23:47:00', 'accion', 'Prueba'),
(46, '1456333298', '2026-04-05 23:47:47', 'accion', 'Prueba'),
(47, '1487569254', '2026-04-05 23:49:26', 'accion', 'Sin casos por atender');

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
(123, '1487569254', 'AVISO: Se realizó un seguimiento al caso \"Demora en atención médica ocupacional\" con ID: 58 por el comisionado Juan Manuel Correal', '2026-03-23 18:13:01'),
(143, '1487569254', 'AVISO: Se realizó un seguimiento al caso \"Demora en atención médica ocupacional\" con ID: 58 por el comisionado Simon Gonzalez Pelaez', '2026-04-01 13:01:33'),
(144, '1487569254', 'AVISO: Se realizó un seguimiento al caso \"Derecho de petición – Estado de incentivo institucional\" con ID: 49 por el comisionado Juan Manuel Correal', '2026-04-01 13:04:33'),
(145, '1487569254', 'AVISO: Se realizó un seguimiento al caso \"Demora en atención médica ocupacional\" con ID: 58 por el comisionado Juan Manuel Correal', '2026-04-01 13:04:50'),
(146, '1487569254', 'Nuevo registro: El usuario Isaac Carvajal con el documento: 2030405060 se ha unido con el rol de \"administrador\". Fecha de registro: 2026-04-03 23:35:24. Vigencia: 2026-2028.', '2026-04-03 23:35:24'),
(150, '1487569254', 'HAS INHABILITADO AL ADMINISTRADOR \"Isaac Carvajal\" DEL SISTEMA el dia: 2026-04-03, por el siguiente motivo: \"por que si\" en caso de error revierta de inmediato la accion, y recuerde que desactivar un usuario es una accion riesgoza, y se aconseja realizarse unicamente en casos de absoluta necesidad.', '2026-04-03 23:39:36'),
(152, '1487569254', 'HAS HABILITADO AL USUARIO \"Isaac Carvajal\" el dia: 2026-04-04, por el siguiente motivo: \"Es para la prueba\", el usuario en cuestion es un administrador, asi que tenga en cuenta que al habilitarlo nuevamente lo hace con dicho rol, en caso de error revierta de inmediato la accion, y se aconseja realizar esta acciónn unicamente en casos de absoluta necesidad.', '2026-04-04 00:06:22'),
(153, '1487569254', 'Nuevo registro: El usuario Saimon Pelaez con el documento: 3040506070 se ha unido con el rol de \"comisionado\". Fecha de registro: 2026-04-04 00:08:43. Vigencia: 2026-2028.', '2026-04-04 00:08:43'),
(156, '1487569254', 'NUEVO CASO: \"Le robaron la dignidad\" ID CASO: 105. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Saimon Pelaez', '2026-04-04 00:11:34'),
(160, '1487569254', 'HAS INHABILITADO AL ADMINISTRADOR \"Isaac Carvajal\" DEL SISTEMA el dia: 2026-04-04, por el siguiente motivo: \"Es necesario probar\" en caso de error revierta de inmediato la accion, y recuerde que desactivar un usuario es una accion riesgoza, y se aconseja realizarse unicamente en casos de absoluta necesidad.', '2026-04-04 00:18:16'),
(161, '1487569254', 'AVISO: El caso \"Le robaron la dignidad\" CON LA ID: 105 cambió deL estado \"Por atender\" a \"Por asignar\". Por su Comisionado Responsable: Saimon Pelaez', '2026-04-04 00:18:39'),
(164, '1487569254', 'HAS INHABILITADO AL USUARIO \"Saimon Pelaez\" DEL SISTEMA el dia: 2026-04-04, por el siguiente motivo: \"Prueba nuevamente\" en caso de error revierta de inmediato la accion, y tenga en cuenta que los casos Por Atender del usuario que ha desabilitado se encontraran en el estado \"Por Reasignar\", y para volver a asignar dichos casos a su Comisionado encargado debera hacerlo manualmente uno por uno, recuerde que desactivar un usuario es una accion riesgoza, y se aconseja realizarse unicamente en casos de absoluta necesidad, recuerde que el sistema por si solo, una vez se cumple con la vigencia, desactiva de forma automatica a todos los usuarios caducados.', '2026-04-04 00:18:39'),
(165, '1487569254', 'AVISO: Se realizó un seguimiento al caso \"Le robaron la dignidad\" con ID: 105 por el comisionado Simon Gonzalez Pelaez', '2026-04-04 00:23:09'),
(168, '1487569254', 'AVISO: El caso \"Le robaron la dignidad\" CON LA ID: 105 cambió deL estado \"Por asignar\" a \"Por atender\". Por su Comisionado Responsable: Simon Gonzalez Pelaez', '2026-04-04 00:24:05'),
(172, '1487569254', 'HAS HABILITADO AL USUARIO \"Isaac Carvajal\" el dia: 2026-04-04, por el siguiente motivo: \"desactive por error\", el usuario en cuestion es un administrador, asi que tenga en cuenta que al habilitarlo nuevamente lo hace con dicho rol, en caso de error revierta de inmediato la accion, y se aconseja realizar esta acciónn unicamente en casos de absoluta necesidad.', '2026-04-04 00:25:00'),
(173, '1487569254', 'HAS HABILITADO AL USUARIO \"Saimon Pelaez\" el dia: 2026-04-04, por el siguiente motivo: \"prueba con comisionado\" en caso de error revierta de inmediato la accion, y se aconseja realizar esta acciónn unicamente en casos de absoluta necesidad.', '2026-04-04 00:25:13'),
(174, '1487569254', 'NUEVO CASO: \"Denuncia riesgo eléctrico\" ID CASO: 106. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(175, '1487569254', 'NUEVO CASO: \"Solicitud inspección seguridad\" ID CASO: 107. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(176, '1487569254', 'NUEVO CASO: \"Denuncia falta señalización\" ID CASO: 108. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal', '2026-04-04 20:49:06'),
(177, '1487569254', 'NUEVO CASO: \"Solicitud capacitación SST\" ID CASO: 109. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-04 20:49:06'),
(178, '1487569254', 'NUEVO CASO: \"Derecho petición seguimiento\" ID CASO: 110. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(179, '1487569254', 'NUEVO CASO: \"Denuncia condiciones inseguras\" ID CASO: 111. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(180, '1487569254', 'NUEVO CASO: \"Solicitud revisión extintores\" ID CASO: 112. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal', '2026-04-04 20:49:06'),
(181, '1487569254', 'NUEVO CASO: \"Denuncia accidente leve\" ID CASO: 113. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-04 20:49:06'),
(182, '1487569254', 'NUEVO CASO: \"Solicitud evaluación riesgos\" ID CASO: 114. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(183, '1487569254', 'NUEVO CASO: \"Denuncia incumplimiento protocolo\" ID CASO: 115. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(184, '1487569254', 'NUEVO CASO: \"Solicitud inspección preventiva\" ID CASO: 116. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal', '2026-04-04 20:49:06'),
(185, '1487569254', 'NUEVO CASO: \"Denuncia cableado deteriorado\" ID CASO: 117. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-04 20:49:06'),
(186, '1487569254', 'NUEVO CASO: \"Solicitud apoyo psicológico\" ID CASO: 118. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(187, '1487569254', 'NUEVO CASO: \"Denuncia conflicto laboral\" ID CASO: 119. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(188, '1487569254', 'NUEVO CASO: \"Derecho petición beneficios\" ID CASO: 120. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Juan Manuel Correal', '2026-04-04 20:49:06'),
(189, '1487569254', 'NUEVO CASO: \"Solicitud actividad bienestar\" ID CASO: 121. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Simon Gonzalez Pelaez', '2026-04-04 20:49:06'),
(190, '1487569254', 'NUEVO CASO: \"Denuncia acoso laboral\" ID CASO: 122. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(191, '1487569254', 'NUEVO CASO: \"Solicitud apoyo social\" ID CASO: 123. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(192, '1487569254', 'NUEVO CASO: \"Solicitud soporte plataforma\" ID CASO: 124. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Juan Manuel Correal', '2026-04-04 20:49:06'),
(193, '1487569254', 'NUEVO CASO: \"Derecho petición sistema\" ID CASO: 125. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Simon Gonzalez Pelaez', '2026-04-04 20:49:06'),
(194, '1487569254', 'NUEVO CASO: \"Denuncia fallas sistema\" ID CASO: 126. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(195, '1487569254', 'NUEVO CASO: \"Solicitud actualización usuario\" ID CASO: 127. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(196, '1487569254', 'NUEVO CASO: \"Solicitud dotación uniforme\" ID CASO: 128. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Juan Manuel Correal', '2026-04-04 20:49:06'),
(197, '1487569254', 'NUEVO CASO: \"Derecho petición dotación\" ID CASO: 129. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Simon Gonzalez Pelaez', '2026-04-04 20:49:06'),
(198, '1487569254', 'NUEVO CASO: \"Solicitud información incentivos\" ID CASO: 130. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(199, '1487569254', 'NUEVO CASO: \"Derecho petición incentivos\" ID CASO: 131. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(200, '1487569254', 'AVISO: El caso \"Programación de examen médico ocupacional\" CON LA ID: 53 cambió deL estado \"Atendido\" a \"No atendido\". Por su Comisionado Responsable: Zack Lopez', '2026-04-05 22:28:50'),
(201, '1487569254', 'HAS INHABILITADO AL USUARIO \"Marleny Gaviria\" DEL SISTEMA el día: 2026-04-05, por el siguiente motivo: \"Por que si\". En caso de error, revierta de inmediato la acción. Tenga en cuenta que los casos \"Por Atender\" del usuario que ha deshabilitado se encontrarán en el estado \"Por Reasignar\", y para volver a asignar dichos casos a su Comisionado encargado deberá hacerlo manualmente uno por uno. Recuerde que desactivar un usuario es una acción riesgosa, y se aconseja realizarse únicamente en casos de absoluta necesidad. El sistema por sí solo, una vez se cumple con la vigencia, desactiva de forma automática a todos los usuarios caducados.', '2026-04-05 22:31:33'),
(202, '1487569254', 'HAS HABILITADO AL USUARIO \"Marleny Gaviria\" el día: 2026-04-05, por el siguiente motivo: \"Prueba\". En caso de error revierta de inmediato la acción, y se aconseja realizar esta acción únicamente en casos de absoluta necesidad.', '2026-04-05 22:35:59'),
(203, '1487569254', 'NUEVO CASO: \"Juanito le pego a una profesora\" ID CASO: 132. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Marleny Gaviria', '2026-04-05 23:19:01'),
(205, '1487569254', 'HAS INHABILITADO AL USUARIO \"Marleny Gaviria\" DEL SISTEMA el día: 2026-04-05, por el siguiente motivo: \"Hola bb\". En caso de error, revierta de inmediato la acción. Tenga en cuenta que los casos \"Por Atender\" del usuario que ha deshabilitado se encontrarán en el estado \"Por Reasignar\", y para volver a asignar dichos casos a su Comisionado encargado deberá hacerlo manualmente uno por uno. Recuerde que desactivar un usuario es una acción riesgosa, y se aconseja realizarse únicamente en casos de absoluta necesidad. El sistema por sí solo, una vez se cumple con la vigencia, desactiva de forma automática a todos los usuarios caducados.', '2026-04-05 23:23:31'),
(206, '1487569254', 'HAS HABILITADO AL USUARIO \"Marleny Gaviria\" el día: 2026-04-05, por el siguiente motivo: \"Prueba\". En caso de error revierta de inmediato la acción, y se aconseja realizar esta acción únicamente en casos de absoluta necesidad.', '2026-04-05 23:47:00'),
(207, '1487569254', 'HAS INHABILITADO AL USUARIO \"Juan Manuel Correal\" DEL SISTEMA el día: 2026-04-05, por el siguiente motivo: \"Prueba\". En caso de error, revierta de inmediato la acción. Tenga en cuenta que los casos \"Por Atender\" del usuario que ha deshabilitado se encontrarán en el estado \"Por Reasignar\", y para volver a asignar dichos casos a su Comisionado encargado deberá hacerlo manualmente uno por uno. Recuerde que desactivar un usuario es una acción riesgosa, y se aconseja realizarse únicamente en casos de absoluta necesidad. El sistema por sí solo, una vez se cumple con la vigencia, desactiva de forma automática a todos los usuarios caducados.', '2026-04-05 23:47:47'),
(208, '1487569254', 'NUEVO CASO: \"Reporte comisionado\" ID CASO: 135. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Zack Lopez', '2026-04-06 00:17:53');

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
(114, '1456333298', 'Realizaste un nuevo seguimiento al caso: \"Demora en atención médica ocupacional\" con ID: 58, en la fecha : 2026-03-23 18:13:01', '2026-03-23 18:13:01'),
(121, '1456333298', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Juan Manuel Correal\", se te ha asignado un caso con el nombre: \"Derecho de petición – Estado de incentivo institucional\" con la id 49', '2026-04-01 13:01:01'),
(122, '1656966633', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Marleny\", uno de tus casos con el nombre Derecho de petición – Estado de incentivo institucional y la id 49 se le ha asignado al comisonado: \"Juan Manuel Correal', '2026-04-01 13:01:01'),
(123, '1020304050', 'Realizaste un nuevo seguimiento al caso: \"Demora en atención médica ocupacional\" con ID: 58, en la fecha : 2026-04-01 13:01:33', '2026-04-01 13:01:33'),
(124, '1020304050', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Simon Gonzalez Pelaez\", se te ha asignado un caso con el nombre: \"Demora en atención médica ocupacional\" con la id 58', '2026-04-01 13:01:33'),
(125, '1456333298', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Juan Manuel\", uno de tus casos con el nombre Demora en atención médica ocupacional y la id 58 se le ha asignado al comisonado: \"Simon Gonzalez Pelaez', '2026-04-01 13:01:33'),
(126, '1456333298', 'Realizaste un nuevo seguimiento al caso: \"Derecho de petición – Estado de incentivo institucional\" con ID: 49, en la fecha : 2026-04-01 13:04:33', '2026-04-01 13:04:33'),
(127, '1456333298', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Juan Manuel Correal\", se te ha asignado un caso con el nombre: \"Derecho de petición – Estado de incentivo institucional\" con la id 49', '2026-04-01 13:04:33'),
(128, '1456333298', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Juan Manuel\", uno de tus casos con el nombre Derecho de petición – Estado de incentivo institucional y la id 49 se le ha asignado al comisonado: \"Juan Manuel Correal', '2026-04-01 13:04:33'),
(129, '1456333298', 'Realizaste un nuevo seguimiento al caso: \"Demora en atención médica ocupacional\" con ID: 58, en la fecha : 2026-04-01 13:04:50', '2026-04-01 13:04:50'),
(130, '1456333298', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Juan Manuel Correal\", se te ha asignado un caso con el nombre: \"Demora en atención médica ocupacional\" con la id 58', '2026-04-01 13:04:50'),
(131, '1020304050', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Simon\", uno de tus casos con el nombre Demora en atención médica ocupacional y la id 58 se le ha asignado al comisonado: \"Juan Manuel Correal', '2026-04-01 13:04:50'),
(132, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Solicitud de acceso al plan anual de SST\" con la id 57', '2026-04-01 13:05:38'),
(133, '1456333298', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Juan Manuel\", uno de tus casos con el nombre Solicitud de acceso al plan anual de SST y la id 57 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-01 13:05:38'),
(134, '1656966633', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Marleny Gaviria\", se te ha asignado un caso con el nombre: \"Incumplimiento en entrega de dotación operativa\" con la id 51', '2026-04-01 13:21:22'),
(135, '1456333298', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Juan Manuel\", uno de tus casos con el nombre Incumplimiento en entrega de dotación operativa y la id 51 se le ha asignado al comisonado: \"Marleny Gaviria', '2026-04-01 13:21:22'),
(139, '1020304050', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Simon Gonzalez Pelaez\", se te ha asignado un caso con el nombre: \"Le robaron la dignidad\" con la id 105', '2026-04-04 00:20:13'),
(141, '1020304050', 'Realizaste un nuevo seguimiento al caso: \"Le robaron la dignidad\" con ID: 105, en la fecha : 2026-04-04 00:23:09', '2026-04-04 00:23:09'),
(142, '1456333298', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Juan Manuel Correal\", se te ha asignado un caso con el nombre: \"Le robaron la dignidad\" con la id 105', '2026-04-04 00:23:09'),
(143, '1020304050', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Simon\", uno de tus casos con el nombre Le robaron la dignidad y la id 105 se le ha asignado al comisonado: \"Juan Manuel Correal', '2026-04-04 00:23:09'),
(144, '1020304050', 'El caso \"Le robaron la dignidad\" con el ID: 105 perteneciente al proceso \"Ropa de Trabajo\", pasó del estado: \"Por asignar\" al estado: \"Por atender\" por el usuario encargado Simon Gonzalez Pelaez', '2026-04-04 00:24:05'),
(146, '1756664828', 'NUEVO CASO: \"Denuncia riesgo eléctrico\" ID CASO: 106. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(147, '1656966633', 'NUEVO CASO: \"Solicitud inspección seguridad\" ID CASO: 107. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(148, '1456333298', 'NUEVO CASO: \"Denuncia falta señalización\" ID CASO: 108. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal', '2026-04-04 20:49:06'),
(149, '1020304050', 'NUEVO CASO: \"Solicitud capacitación SST\" ID CASO: 109. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-04 20:49:06'),
(150, '1756664828', 'NUEVO CASO: \"Derecho petición seguimiento\" ID CASO: 110. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(151, '1656966633', 'NUEVO CASO: \"Denuncia condiciones inseguras\" ID CASO: 111. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(152, '1456333298', 'NUEVO CASO: \"Solicitud revisión extintores\" ID CASO: 112. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal', '2026-04-04 20:49:06'),
(153, '1020304050', 'NUEVO CASO: \"Denuncia accidente leve\" ID CASO: 113. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-04 20:49:06'),
(154, '1756664828', 'NUEVO CASO: \"Solicitud evaluación riesgos\" ID CASO: 114. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(155, '1656966633', 'NUEVO CASO: \"Denuncia incumplimiento protocolo\" ID CASO: 115. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(156, '1456333298', 'NUEVO CASO: \"Solicitud inspección preventiva\" ID CASO: 116. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal', '2026-04-04 20:49:06'),
(157, '1020304050', 'NUEVO CASO: \"Denuncia cableado deteriorado\" ID CASO: 117. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-04 20:49:06'),
(158, '1756664828', 'NUEVO CASO: \"Solicitud apoyo psicológico\" ID CASO: 118. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(159, '1656966633', 'NUEVO CASO: \"Denuncia conflicto laboral\" ID CASO: 119. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(160, '1456333298', 'NUEVO CASO: \"Derecho petición beneficios\" ID CASO: 120. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Juan Manuel Correal', '2026-04-04 20:49:06'),
(161, '1020304050', 'NUEVO CASO: \"Solicitud actividad bienestar\" ID CASO: 121. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Simon Gonzalez Pelaez', '2026-04-04 20:49:06'),
(162, '1756664828', 'NUEVO CASO: \"Denuncia acoso laboral\" ID CASO: 122. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(163, '1656966633', 'NUEVO CASO: \"Solicitud apoyo social\" ID CASO: 123. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(164, '1456333298', 'NUEVO CASO: \"Solicitud soporte plataforma\" ID CASO: 124. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Juan Manuel Correal', '2026-04-04 20:49:06'),
(165, '1020304050', 'NUEVO CASO: \"Derecho petición sistema\" ID CASO: 125. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Simon Gonzalez Pelaez', '2026-04-04 20:49:06'),
(166, '1756664828', 'NUEVO CASO: \"Denuncia fallas sistema\" ID CASO: 126. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(167, '1656966633', 'NUEVO CASO: \"Solicitud actualización usuario\" ID CASO: 127. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(168, '1456333298', 'NUEVO CASO: \"Solicitud dotación uniforme\" ID CASO: 128. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Juan Manuel Correal', '2026-04-04 20:49:06'),
(169, '1020304050', 'NUEVO CASO: \"Derecho petición dotación\" ID CASO: 129. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Simon Gonzalez Pelaez', '2026-04-04 20:49:06'),
(170, '1756664828', 'NUEVO CASO: \"Solicitud información incentivos\" ID CASO: 130. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Zack Lopez', '2026-04-04 20:49:06'),
(171, '1656966633', 'NUEVO CASO: \"Derecho petición incentivos\" ID CASO: 131. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Marleny Gaviria', '2026-04-04 20:49:06'),
(172, '1756664828', 'El caso \"Programación de examen médico ocupacional\" con el ID: 53 perteneciente al proceso \"SSEMI\", pasó del estado: \"Atendido\" al estado: \"No atendido\" por el usuario encargado Zack Lopez', '2026-04-05 22:28:50'),
(173, '1656966633', 'HAS SIDO INHABILITADO DEL SISTEMA: Marleny Gaviria el día: 2026-04-05. Has sido INHABILITADO por el administrador encargado: Kory Carrerita, por el siguiente motivo: \"Por que si\". En caso de error comuníquese con el administrador encargado a través del siguiente correo: kory.carrera.dev@gmail.com.', '2026-04-05 22:31:33'),
(174, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Solicitud evaluación riesgos\" con la id 71', '2026-04-05 22:33:45'),
(175, '1656966633', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Marleny\", uno de tus casos con el nombre Solicitud evaluación riesgos y la id 71 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 22:33:45'),
(176, '1456333298', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Juan Manuel Correal\", se te ha asignado un caso con el nombre: \"Solicitud evaluación riesgos\" con la id 71', '2026-04-05 22:34:52'),
(177, '1656966633', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Marleny\", uno de tus casos con el nombre Solicitud evaluación riesgos y la id 71 se le ha asignado al comisonado: \"Juan Manuel Correal', '2026-04-05 22:34:52'),
(178, '1656966633', 'HAS SIDO HABILITADO EN EL SISTEMA: Marleny Gaviria el día: 2026-04-05. Has sido HABILITADO por el administrador encargado: Kory Carrerita, por el siguiente motivo: \"Prueba\". Ahora tienes acceso nuevamente a las funciones de tu rol como comisionado, pero tus casos antiguos han sido asignados a otro comisionado, o en su defecto se encuentran en el estado \"Por Asignar\". En caso de error comuníquese con el administrador encargado a través del siguiente correo: kory.carrera.dev@gmail.com.', '2026-04-05 22:35:59'),
(179, '1020304050', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Simon Gonzalez Pelaez\", se te ha asignado un caso con el nombre: \"Solicitud evaluación riesgos\" con la id 71', '2026-04-05 22:38:27'),
(180, '1656966633', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Marleny\", uno de tus casos con el nombre Solicitud evaluación riesgos y la id 71 se le ha asignado al comisonado: \"Simon Gonzalez Pelaez', '2026-04-05 22:38:27'),
(181, '1020304050', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Simon Gonzalez Pelaez\", se te ha asignado un caso con el nombre: \"Solicitud evaluación riesgos\" con la id 71', '2026-04-05 22:43:09'),
(182, '1656966633', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Marleny\", uno de tus casos con el nombre Solicitud evaluación riesgos y la id 71 se le ha asignado al comisonado: \"Simon Gonzalez Pelaez', '2026-04-05 22:43:09'),
(183, '1020304050', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Simon Gonzalez Pelaez\", se te ha asignado un caso con el nombre: \"Solicitud evaluación riesgos\" con la id 71', '2026-04-05 23:10:52'),
(184, '1656966633', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Marleny\", uno de tus casos con el nombre Solicitud evaluación riesgos y la id 71 se le ha asignado al comisonado: \"Simon Gonzalez Pelaez', '2026-04-05 23:10:52'),
(185, '1456333298', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Juan Manuel Correal\", se te ha asignado un caso con el nombre: \"Derecho petición sistema\" con la id 125', '2026-04-05 23:14:00'),
(186, '1020304050', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Simon\", uno de tus casos con el nombre Derecho petición sistema y la id 125 se le ha asignado al comisonado: \"Juan Manuel Correal', '2026-04-05 23:14:00'),
(187, '1656966633', 'NUEVO CASO: \"Juanito le pego a una profesora\" ID CASO: 132. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Marleny Gaviria', '2026-04-05 23:19:01'),
(188, '1656966633', 'NUEVO CASO: \"Se eyaculo en el salon\" ID CASO: 134. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Marleny Gaviria', '2026-04-05 23:20:42'),
(189, '1656966633', 'HAS SIDO INHABILITADO DEL SISTEMA: Marleny Gaviria el día: 2026-04-05. Has sido INHABILITADO por el administrador encargado: Kory Carrerita, por el siguiente motivo: \"Hola bb\". En caso de error comuníquese con el administrador encargado a través del siguiente correo: kory.carrera.dev@gmail.com.', '2026-04-05 23:23:31'),
(190, '1456333298', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Juan Manuel Correal\", se te ha asignado un caso con el nombre: \"Juanito le pego a una profesora\" con la id 132', '2026-04-05 23:24:39'),
(191, '1656966633', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Marleny\", uno de tus casos con el nombre Juanito le pego a una profesora y la id 132 se le ha asignado al comisonado: \"Juan Manuel Correal', '2026-04-05 23:24:39'),
(192, '1456333298', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Juan Manuel Correal\", se te ha asignado un caso con el nombre: \"Programación de examen médico ocupacional\" con la id 53', '2026-04-05 23:36:50'),
(193, '1020304050', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Simon\", uno de tus casos con el nombre Programación de examen médico ocupacional y la id 53 se le ha asignado al comisonado: \"Juan Manuel Correal', '2026-04-05 23:36:50'),
(194, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Copia de resultados de examen médico ocupacional\" con la id 56', '2026-04-05 23:37:24'),
(195, '1020304050', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Simon\", uno de tus casos con el nombre Copia de resultados de examen médico ocupacional y la id 56 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:37:24'),
(196, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Copia de resultados de examen médico ocupacional\" con la id 56', '2026-04-05 23:37:25'),
(197, '1756664828', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Zack\", uno de tus casos con el nombre Copia de resultados de examen médico ocupacional y la id 56 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:37:25'),
(198, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Copia de resultados de examen médico ocupacional\" con la id 56', '2026-04-05 23:37:40'),
(199, '1756664828', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Zack\", uno de tus casos con el nombre Copia de resultados de examen médico ocupacional y la id 56 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:37:40'),
(200, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Riesgo laboral no atendido oportunamente\" con la id 59', '2026-04-05 23:37:55'),
(201, '1756664828', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Zack\", uno de tus casos con el nombre Riesgo laboral no atendido oportunamente y la id 59 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:37:55'),
(202, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Riesgo laboral no atendido oportunamente\" con la id 59', '2026-04-05 23:38:18'),
(203, '1756664828', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Zack\", uno de tus casos con el nombre Riesgo laboral no atendido oportunamente y la id 59 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:38:18'),
(204, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Copia de resultados de examen médico ocupacional\" con la id 56', '2026-04-05 23:38:58'),
(205, '1756664828', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Zack\", uno de tus casos con el nombre Copia de resultados de examen médico ocupacional y la id 56 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:38:58'),
(206, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Riesgo laboral no atendido oportunamente\" con la id 59', '2026-04-05 23:39:37'),
(207, '1756664828', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Zack\", uno de tus casos con el nombre Riesgo laboral no atendido oportunamente y la id 59 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:39:37'),
(208, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Copia de resultados de examen médico ocupacional\" con la id 56', '2026-04-05 23:41:18'),
(209, '1756664828', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Zack\", uno de tus casos con el nombre Copia de resultados de examen médico ocupacional y la id 56 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:41:18'),
(210, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Copia de resultados de examen médico ocupacional\" con la id 56', '2026-04-05 23:41:18'),
(211, '1756664828', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Zack\", uno de tus casos con el nombre Copia de resultados de examen médico ocupacional y la id 56 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:41:18'),
(212, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Denuncia cableado expuesto\" con la id 61', '2026-04-05 23:44:12'),
(213, '1020304050', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Simon\", uno de tus casos con el nombre Denuncia cableado expuesto y la id 61 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:44:12'),
(214, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Solicitud reposición botas\" con la id 91', '2026-04-05 23:44:57'),
(215, '1020304050', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Simon\", uno de tus casos con el nombre Solicitud reposición botas y la id 91 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:44:57'),
(216, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Incidente leve laboratorio\" con la id 67', '2026-04-05 23:45:39'),
(217, '1020304050', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Simon\", uno de tus casos con el nombre Incidente leve laboratorio y la id 67 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:45:39'),
(218, '1756664828', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Zack Lopez\", se te ha asignado un caso con el nombre: \"Incidente leve laboratorio\" con la id 67', '2026-04-05 23:45:40'),
(219, '1756664828', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Zack\", uno de tus casos con el nombre Incidente leve laboratorio y la id 67 se le ha asignado al comisonado: \"Zack Lopez', '2026-04-05 23:45:40'),
(220, '1456333298', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Juan Manuel Correal\", se te ha asignado un caso con el nombre: \"Denuncia riesgo ergonómico\" con la id 70', '2026-04-05 23:46:16'),
(221, '1020304050', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Simon\", uno de tus casos con el nombre Denuncia riesgo ergonómico y la id 70 se le ha asignado al comisonado: \"Juan Manuel Correal', '2026-04-05 23:46:16'),
(222, '1656966633', 'HAS SIDO HABILITADO EN EL SISTEMA: Marleny Gaviria el día: 2026-04-05. Has sido HABILITADO por el administrador encargado: Kory Carrerita, por el siguiente motivo: \"Prueba\". Ahora tienes acceso nuevamente a las funciones de tu rol como comisionado, pero tus casos antiguos han sido asignados a otro comisionado, o en su defecto se encuentran en el estado \"Por Asignar\". En caso de error comuníquese con el administrador encargado a través del siguiente correo: kory.carrera.dev@gmail.com.', '2026-04-05 23:47:00'),
(223, '1456333298', 'HAS SIDO INHABILITADO DEL SISTEMA: Juan Manuel Correal el día: 2026-04-05. Has sido INHABILITADO por el administrador encargado: Kory Carrerita, por el siguiente motivo: \"Prueba\". En caso de error comuníquese con el administrador encargado a través del siguiente correo: kory.carrera.dev@gmail.com.', '2026-04-05 23:47:47'),
(224, '1656966633', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Marleny Gaviria\", se te ha asignado un caso con el nombre: \"Programación de examen médico ocupacional\" con la id 53', '2026-04-05 23:49:26'),
(225, '1456333298', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Juan Manuel\", uno de tus casos con el nombre Programación de examen médico ocupacional y la id 53 se le ha asignado al comisonado: \"Marleny Gaviria', '2026-04-05 23:49:26'),
(226, '1756664828', 'NUEVO CASO: \"Reporte comisionado\" ID CASO: 135. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Zack Lopez', '2026-04-06 00:17:53');

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
(19, '2026-03-23 18:13:01', 'El caso aun sigue en proceso, se espera respuesta de las entidades encargadas', '1456333298', 58),
(26, '2026-04-04 00:20:13', 'Prueba de reasignacion', '1487569254', 105),
(27, '2026-04-04 00:23:09', 'Prueba 2, hubo un error', '1487569254', 105),
(28, '2026-04-05 22:33:45', 'Se desactivo el usuario anterior', '1487569254', 71),
(29, '2026-04-05 22:34:52', 'Por que si', '1487569254', 71),
(30, '2026-04-05 22:38:27', 'xd', '1487569254', 71),
(31, '2026-04-05 22:43:09', 'Por que si', '1487569254', 71),
(32, '2026-04-05 23:10:52', '71', '1487569254', 71),
(33, '2026-04-05 23:14:00', 'OTRO INTENTO', '1487569254', 125),
(34, '2026-04-05 23:24:39', 'Caso sin usuario asignado', '1487569254', 132),
(35, '2026-04-05 23:36:50', 'hola', '1487569254', 53),
(36, '2026-04-05 23:37:24', 'Usuario con muchos casos', '1487569254', 56),
(37, '2026-04-05 23:37:25', 'Usuario con muchos casos', '1487569254', 56),
(38, '2026-04-05 23:37:40', 'Usuario con muchos casos', '1487569254', 56),
(39, '2026-04-05 23:37:55', 'Usuario con muchos casos', '1487569254', 59),
(40, '2026-04-05 23:38:18', 'Usuario con muchos casos', '1487569254', 59),
(41, '2026-04-05 23:38:58', 'Usuario con muchos casos', '1487569254', 56),
(42, '2026-04-05 23:39:37', 'Usuario con muchos casos', '1487569254', 59),
(43, '2026-04-05 23:41:18', 'Usuario con muchos casos', '1487569254', 56),
(44, '2026-04-05 23:41:18', 'Usuario con muchos casos', '1487569254', 56),
(45, '2026-04-05 23:44:12', 'Usuario con muchos casos', '1487569254', 61),
(46, '2026-04-05 23:44:57', 'Usuario con muchos casos', '1487569254', 91),
(47, '2026-04-05 23:45:39', 'Usuario con muchos casos', '1487569254', 67),
(48, '2026-04-05 23:45:40', 'Usuario con muchos casos', '1487569254', 67),
(49, '2026-04-05 23:46:16', 'Usuario con muchos casos', '1487569254', 70),
(50, '2026-04-05 23:49:26', 'Sin casos por atender', '1487569254', 53);

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
('1020304050', 'Simon', 'Gonzalez Pelaez', 'pelaezgonzalezsimon919@gmail.com', '', 2, '$2y$10$/adpXMz4t00apED8Njy3j.2u8oRPBehzxuXwppb8MMW7wKdAMBTDm', '2026-02-12 14:18:58', '2028-02-12 14:18:58', '2026-2028', '2026-04-04 21:00:49', 1, 0, NULL),
('1456333298', 'Juan Manuel', 'Correal', 'juangalvis.developer@gmail.com', '', 2, '$2y$10$D9v783uPM.afAM21MN7D8.KbSDy9uLEQryCieSOkleXydooL18oAS', '2026-02-12 14:22:31', '2028-02-12 14:22:31', '2026-2028', '2026-04-04 20:44:02', 0, 0, NULL),
('1487569254', 'Kory', 'Carrerita', 'kory.carrera.dev@gmail.com', '3001234567', 1, '$2y$10$Jv38fJwprb95GT4MUs8n1elsr42/1fWNevWmOlYixG.NgZdhbF9US', '2026-01-24 03:14:09', '2028-01-24 03:14:09', '2026-2028', '2026-04-06 00:11:43', 1, 0, '7be3757a753976a4ca6e'),
('1656966633', 'Marleny', 'Gaviria', 'koritocarrera@gmail.com', '', 2, '$2y$10$D5GVEeDtEo.Obd0zjk.IxO0M2WZ5iTz1t.B9TGWYgdlJ3mahQT4/u', '2026-02-12 14:28:54', '2028-02-12 14:28:54', '2026-2028', '2026-04-05 23:18:03', 1, 0, NULL),
('1756664828', 'Zack', 'Lopez', 'isaaccarvajal1356@gmail.com', '3001234567', 2, '$2y$10$w4lPg411h/NW/uu2KYJFtec20RxgOG1eX28ReWtrhyjWQtzh.bruW', '2026-02-12 14:20:29', '2028-02-12 14:20:29', '2026-2028', '2026-04-06 00:14:13', 1, 1, '4f629435a7217caa25cc');

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
            ' con el documento: ', NEW.documento, ' se ha unido con el rol de "', r.nombre_rol, 
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
DELIMITER $$
CREATE TRIGGER `tr_reasignar_casos` AFTER UPDATE ON `usuario` FOR EACH ROW BEGIN
	IF OLD.id_estado = 1 and NEW.id_estado = 0 THEN
	UPDATE caso SET id_estado = 4 WHERE documento = OLD.documento AND id_estado = 2;
    END IF;
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
  MODIFY `id_archivo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para encontrar y relacionar', AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `caso`
--
ALTER TABLE `caso`
  MODIFY `id_caso` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK de casos', AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT de la tabla `configuracionusuario`
--
ALTER TABLE `configuracionusuario`
  MODIFY `id_configuracion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para encontrar y relacionar';

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `informe`
--
ALTER TABLE `informe`
  MODIFY `id_informe` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para ubicar y relacionar', AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de la tabla `monitoreo`
--
ALTER TABLE `monitoreo`
  MODIFY `id_monitoreo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Llave primaria para reconocimiento y relacion', AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `noti_administrador`
--
ALTER TABLE `noti_administrador`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT de la tabla `noti_comisionado`
--
ALTER TABLE `noti_comisionado`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para relacionar y encontrar', AUTO_INCREMENT=227;

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
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para encontrar y relacionar', AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `tipo_caso`
--
ALTER TABLE `tipo_caso`
  MODIFY `id_tipo_caso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `token_usuario`
--
ALTER TABLE `token_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
AND id_estado = 1 AND id_rol <> 1;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
