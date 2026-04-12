-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db_sena
-- Tiempo de generación: 12-04-2026 a las 15:24:15
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

CREATE PROCEDURE `sp_actualizar_estado_caso_con_motivo` (IN `p_id_caso` INT, IN `p_id_estado` INT, IN `p_documento` VARCHAR(20), IN `p_motivo` TEXT)   BEGIN
    DECLARE v_cierre DATETIME DEFAULT NULL;
    DECLARE v_rows INT;

    
    IF NOT EXISTS (SELECT 1 FROM caso WHERE id_caso = p_id_caso) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'EL caso no existe';
    END IF;

    
    IF p_id_estado = 1 OR p_id_estado = 3 THEN
        SET v_cierre = NOW();
    END IF;

    
    UPDATE caso
    SET id_estado = p_id_estado,
        fecha_cierre = v_cierre
    WHERE id_caso = p_id_caso
      AND documento = p_documento;

    SET v_rows = ROW_COUNT();

    
    
    
    IF (v_rows > 0 OR v_rows = 0) AND (p_motivo IS NOT NULL AND p_motivo != '') THEN
        INSERT INTO seguimiento (
            observacion,
            documento,
            id_caso
        )
        VALUES (
            p_motivo,
            p_documento,
            p_id_caso
        );
    END IF;

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
    		c.nombre,
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
        SUM(CASE WHEN e.estado = 'Por asignar' THEN 1 ELSE 0 END)                AS total_por_asignar,
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
(136, 'Denuncia cable suelto', NULL, '1013341532', NULL, 14, NULL, '2026-01-06 08:20:00', '2026-01-08 03:20:00', 1, 1, 'Se reporta cable suelto en zona de trabajo que puede generar riesgo eléctrico'),
(137, 'Solicitud inspección seguridad', NULL, '1027961396', NULL, 14, NULL, '2026-01-18 10:15:00', '2026-01-19 17:15:00', 1, 2, 'Se solicita inspección preventiva de seguridad en área operativa'),
(138, 'Denuncia caída trabajador', NULL, '1013342119', NULL, 14, NULL, '2026-02-04 09:10:00', NULL, 2, 1, 'Se reporta caída leve de trabajador durante jornada laboral'),
(139, 'Solicitud revisión herramientas', NULL, '1020304050', NULL, 14, NULL, '2026-02-22 11:30:00', '2026-02-23 11:30:00', 1, 2, 'Se solicita revisión de herramientas deterioradas'),
(140, 'Derecho petición accidente', NULL, '1013341532', NULL, 14, NULL, '2026-03-03 14:20:00', NULL, 2, 3, 'Se solicita información sobre accidente laboral reportado'),
(141, 'Denuncia falta señalización', NULL, '1027961396', NULL, 14, NULL, '2026-03-16 09:40:00', '2026-03-17 12:40:00', 1, 1, 'Se reporta falta de señalización en zona de tránsito interno'),
(142, 'Solicitud capacitación riesgos', NULL, '1013342119', NULL, 14, NULL, '2026-04-02 10:50:00', NULL, 2, 2, 'Funcionario solicita capacitación en prevención de riesgos'),
(143, 'Denuncia riesgo eléctrico', NULL, '1020304050', NULL, 14, NULL, '2026-04-19 13:15:00', '2026-04-21 06:15:00', 1, 1, 'Se detecta posible riesgo eléctrico en tomas de corriente'),
(144, 'Solicitud revisión extintores', NULL, '1013341532', NULL, 14, NULL, '2026-05-07 08:45:00', '2026-05-07 22:45:00', 1, 2, 'Se solicita revisión del estado de los extintores'),
(145, 'Denuncia obstrucción salida', NULL, '1027961396', NULL, 14, NULL, '2026-05-24 12:20:00', '2026-05-26 07:20:00', 1, 1, 'Se reporta salida de emergencia bloqueada por materiales'),
(146, 'Derecho petición seguimiento SST', NULL, '1013342119', NULL, 14, NULL, '2026-06-05 09:00:00', NULL, 2, 3, 'Se solicita seguimiento a reporte de condiciones inseguras'),
(147, 'Solicitud evaluación riesgos', NULL, '1020304050', NULL, 14, NULL, '2026-06-18 11:35:00', '2026-06-19 16:35:00', 1, 2, 'Se solicita evaluación de riesgos en área administrativa'),
(148, 'Solicitud apoyo psicológico', NULL, '1013341532', NULL, 10, NULL, '2026-01-12 09:30:00', '2026-01-13 03:30:00', 1, 2, 'Funcionario solicita acompañamiento psicológico'),
(149, 'Denuncia conflicto laboral', NULL, '1027961396', NULL, 10, NULL, '2026-02-11 10:20:00', '2026-02-13 09:20:00', 1, 1, 'Se reporta conflicto interpersonal entre compañeros'),
(150, 'Derecho petición subsidios', NULL, '1013342119', NULL, 10, NULL, '2026-03-27 11:10:00', NULL, 2, 3, 'Se solicita información sobre subsidios institucionales'),
(151, 'Solicitud actividad bienestar', NULL, '1020304050', NULL, 10, NULL, '2026-04-14 14:05:00', '2026-04-16 05:05:00', 1, 2, 'Se solicita participación en jornada de bienestar laboral'),
(152, 'Denuncia trato inadecuado', NULL, '1013341532', NULL, 10, NULL, '2026-05-29 13:50:00', '2026-05-29 19:50:00', 1, 1, 'Se reporta trato inadecuado por parte de un superior'),
(153, 'Solicitud soporte sistema', NULL, '1027961396', NULL, 11, NULL, '2026-01-28 08:10:00', '2026-01-28 17:10:00', 1, 2, 'Se solicita soporte para acceso a sistema institucional'),
(154, 'Derecho petición tecnológica', NULL, '1013342119', NULL, 11, NULL, '2026-03-10 09:45:00', NULL, 2, 3, 'Se solicita información sobre estado de solicitud tecnológica'),
(155, 'Denuncia fallas plataforma', NULL, '1020304050', NULL, 11, NULL, '2026-05-12 10:25:00', '2026-05-13 13:25:00', 1, 1, 'Se reportan fallas recurrentes en plataforma institucional'),
(156, 'Solicitud dotación uniforme', NULL, '1013341532', NULL, 12, NULL, '2026-02-08 09:15:00', '2026-02-08 19:15:00', 1, 2, 'Se solicita dotación de uniforme institucional'),
(157, 'Derecho petición botas', NULL, '1027961396', NULL, 12, NULL, '2026-04-25 10:40:00', NULL, 2, 3, 'Se solicita información sobre entrega de botas de seguridad'),
(158, 'Solicitud inscripción incentivos', NULL, '1013342119', NULL, 13, NULL, '2026-03-15 11:20:00', '2026-03-16 04:20:00', 1, 2, 'Funcionario solicita inscripción al plan de incentivos'),
(159, 'Derecho petición resultados', NULL, '1020304050', NULL, 13, NULL, '2026-06-09 12:00:00', NULL, 2, 3, 'Se solicita información sobre resultados del programa de incentivos'),
(160, 'Reporte luminaria fundida', NULL, '1013341532', NULL, 14, NULL, '2026-01-10 08:15:22', '2026-01-12 14:30:10', 3, 1, 'Se reporta falta de iluminación en el pasillo principal del bloque B'),
(161, 'Solicitud recarga botiquín', NULL, '1027961396', NULL, 14, NULL, '2026-01-15 10:30:45', '2026-01-16 15:20:11', 1, 2, 'Elementos de primer auxilio agotados tras simulacro de evacuación'),
(162, 'Denuncia piso resbaladizo', NULL, '1013342119', NULL, 14, NULL, '2026-01-22 09:12:05', NULL, 2, 1, 'Se detecta filtración de agua que genera riesgo de caída en la entrada peatonal'),
(163, 'Solicitud señalética seguridad', NULL, '1020304050', NULL, 14, NULL, '2026-02-05 14:15:00', '2026-02-10 09:12:44', 1, 2, 'Se requiere actualización de avisos de peligro en el área de máquinas'),
(164, 'Solicitud auxilio educativo', NULL, '1013341532', NULL, 10, NULL, '2026-02-12 11:05:33', '2026-02-18 16:30:00', 3, 2, 'Funcionario radica documentos para solicitud de auxilio de postgrado'),
(165, 'Denuncia ruido excesivo', NULL, '1027961396', NULL, 10, NULL, '2026-02-20 15:45:12', '2026-02-21 11:10:05', 1, 1, 'Reporte de contaminación auditiva por reparaciones en la oficina contigua'),
(167, 'Falla restablecimiento clave', NULL, '1020304050', NULL, 11, NULL, '2026-03-05 10:05:44', '2026-03-05 10:55:12', 1, 1, 'Usuario reporta que no recibe el correo de recuperación de contraseña'),
(168, 'Solicitud licencia software', NULL, '1013341532', NULL, 11, NULL, '2026-03-10 13:20:10', '2026-03-12 08:15:00', 3, 2, 'Se requiere activación de licencia para suite de herramientas de diseño'),
(169, 'Solicitud cambio de talla', NULL, '1027961396', NULL, 12, NULL, '2026-03-15 09:10:55', '2026-03-16 11:30:22', 1, 2, 'Funcionario solicita cambio de pantalón de dotación por talla incorrecta'),
(170, 'Reporte dotación incompleta', NULL, '1013342119', NULL, 12, NULL, '2026-03-18 16:00:15', '2026-03-20 09:30:45', 3, 1, 'Se recibió el calzado pero faltan los guantes de protección industrial'),
(171, 'Postulación empleado del mes', NULL, '1020304050', NULL, 13, NULL, '2026-03-22 11:45:00', '2026-03-25 17:05:11', 1, 2, 'Envío de formulario y evidencias para reconocimiento por desempeño'),
(172, 'Queja puntaje incentivos', NULL, '1013341532', NULL, 13, NULL, '2026-03-28 14:30:20', '2026-04-01 10:00:55', 3, 1, 'Funcionario manifiesta inconformidad con el cálculo de puntos del trimestre'),
(173, 'Reporte extintor vencido', NULL, '1013341532', NULL, 14, NULL, '2026-01-05 09:22:15', NULL, 4, 1, 'Se identifica extintor con fecha de recarga vencida en bloque C'),
(174, 'Solicitud examen periódico', NULL, '1020304050', 51, 14, '2026-04-06 01:38:45', '2026-01-14 14:45:10', NULL, 2, 2, 'Funcionario solicita agendamiento de exámenes médicos ocupacionales'),
(175, 'Denuncia falta de EPP', NULL, '1013342119', NULL, 14, NULL, '2026-02-02 10:12:55', NULL, 4, 1, 'Se reporta personal operando sin los elementos de protección adecuados'),
(176, 'Solicitud permiso calamidad', NULL, '1020304050', NULL, 10, NULL, '2026-02-10 07:30:22', NULL, 4, 2, 'Funcionario solicita permiso por calamidad doméstica familiar'),
(177, 'Derecho petición vivienda', NULL, '1013341532', NULL, 10, NULL, '2026-02-15 11:15:40', NULL, 4, 3, 'Se solicita información sobre convenios de vivienda institucional'),
(178, 'Reporte equipo lento', NULL, '1027961396', NULL, 11, NULL, '2026-03-01 08:55:12', NULL, 4, 1, 'Equipo de cómputo presenta lentitud extrema al abrir el IDE'),
(179, 'Solicitud acceso VPN', NULL, '1013342119', NULL, 11, NULL, '2026-03-08 13:10:05', NULL, 4, 2, 'Se requiere acceso remoto para realizar tareas de soporte'),
(180, 'Solicitud chaqueta térmica', NULL, '1020304050', NULL, 12, NULL, '2026-03-12 15:40:22', NULL, 4, 2, 'Personal de archivo solicita chaqueta por bajas temperaturas en sótano'),
(181, 'Denuncia dotación dañada', NULL, '1013341532', NULL, 12, NULL, '2026-03-20 09:20:11', NULL, 4, 1, 'Calzado de seguridad presenta desprendimiento de suela'),
(182, 'Consulta puntos acumulados', NULL, '1027961396', NULL, 13, NULL, '2026-03-25 10:05:33', NULL, 4, 2, 'Funcionario desea conocer el saldo actual de sus puntos de incentivo'),
(183, 'Sugerencia programa bienestar', NULL, '1027961396', 52, 13, '2026-04-06 01:39:29', '2026-03-29 16:30:45', NULL, 2, 1, 'Propuesta para incluir actividades deportivas en el plan de incentivos');

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
    NOW() WHERE NEW.id_estado = 1;
	END IF;

IF mensaje_admin IS NOT NULL THEN

INSERT INTO noti_administrador(documento, mensaje, fecha)
SELECT 
    u_admin.documento, 
    mensaje_admin, 
    NOW()
FROM usuario u_admin
WHERE u_admin.id_rol = 1 AND NEW.id_estado = 1;
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
(56, '1487569254', '2026-04-06 01:40:20', 'PDF', 'Reporte Casos'),
(57, '1487569254', '2026-04-06 01:40:53', 'PDF', 'Reporte Usuarios'),
(58, '1487569254', '2026-04-06 01:41:29', 'PDF', 'Reporte Procesos'),
(59, '1487569254', '2026-04-06 01:41:54', 'EXCEL', 'Reporte general de casos');

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
(48, '1487569254', '2026-04-06 01:38:45', 'accion', 'Caso por asignar'),
(49, '1487569254', '2026-04-06 01:39:29', 'accion', 'Caso por asignar');

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
(209, '1487569254', 'Nuevo registro: El usuario Simon Gonzalez Pelaez con el documento: 1013341532 se ha unido con el rol de \"comisionado\". Fecha de registro: 2026-04-06 01:11:50. Vigencia: 2026-2028.', '2026-04-06 01:11:50'),
(210, '1487569254', 'Nuevo registro: El usuario Juan Manuel Correal Galvis con el documento: 1027961396 se ha unido con el rol de \"comisionado\". Fecha de registro: 2026-04-06 01:20:43. Vigencia: 2026-2028.', '2026-04-06 01:20:43'),
(211, '1487569254', 'Nuevo registro: El usuario Isaac Manuel Carvajal Lopez con el documento: 1013342119 se ha unido con el rol de \"comisionado\". Fecha de registro: 2026-04-06 01:22:41. Vigencia: 2026-2028.', '2026-04-06 01:22:41'),
(212, '1487569254', 'Nuevo registro: El usuario Marleny Gaviria con el documento: 1020304050 se ha unido con el rol de \"comisionado\". Fecha de registro: 2026-04-06 01:24:53. Vigencia: 2026-2028.', '2026-04-06 01:24:53'),
(213, '1487569254', 'NUEVO CASO: \"Denuncia cable suelto\" ID CASO: 136. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:28:58'),
(214, '1487569254', 'NUEVO CASO: \"Solicitud inspección seguridad\" ID CASO: 137. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:28:58'),
(215, '1487569254', 'NUEVO CASO: \"Denuncia caída trabajador\" ID CASO: 138. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:28:58'),
(216, '1487569254', 'NUEVO CASO: \"Solicitud revisión herramientas\" ID CASO: 139. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-06 01:28:58'),
(217, '1487569254', 'NUEVO CASO: \"Derecho petición accidente\" ID CASO: 140. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:28:58'),
(218, '1487569254', 'NUEVO CASO: \"Denuncia falta señalización\" ID CASO: 141. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:28:58'),
(219, '1487569254', 'NUEVO CASO: \"Solicitud capacitación riesgos\" ID CASO: 142. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:28:58'),
(220, '1487569254', 'NUEVO CASO: \"Denuncia riesgo eléctrico\" ID CASO: 143. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-06 01:28:58'),
(221, '1487569254', 'NUEVO CASO: \"Solicitud revisión extintores\" ID CASO: 144. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:28:58'),
(222, '1487569254', 'NUEVO CASO: \"Denuncia obstrucción salida\" ID CASO: 145. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:28:58'),
(223, '1487569254', 'NUEVO CASO: \"Derecho petición seguimiento SST\" ID CASO: 146. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:28:58'),
(224, '1487569254', 'NUEVO CASO: \"Solicitud evaluación riesgos\" ID CASO: 147. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-06 01:28:58'),
(225, '1487569254', 'NUEVO CASO: \"Solicitud apoyo psicológico\" ID CASO: 148. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:28:58'),
(226, '1487569254', 'NUEVO CASO: \"Denuncia conflicto laboral\" ID CASO: 149. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:28:58'),
(227, '1487569254', 'NUEVO CASO: \"Derecho petición subsidios\" ID CASO: 150. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:28:58'),
(228, '1487569254', 'NUEVO CASO: \"Solicitud actividad bienestar\" ID CASO: 151. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Marleny Gaviria', '2026-04-06 01:28:58'),
(229, '1487569254', 'NUEVO CASO: \"Denuncia trato inadecuado\" ID CASO: 152. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:28:58'),
(230, '1487569254', 'NUEVO CASO: \"Solicitud soporte sistema\" ID CASO: 153. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:28:58'),
(231, '1487569254', 'NUEVO CASO: \"Derecho petición tecnológica\" ID CASO: 154. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:28:58'),
(232, '1487569254', 'NUEVO CASO: \"Denuncia fallas plataforma\" ID CASO: 155. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Marleny Gaviria', '2026-04-06 01:28:58'),
(233, '1487569254', 'NUEVO CASO: \"Solicitud dotación uniforme\" ID CASO: 156. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:28:58'),
(234, '1487569254', 'NUEVO CASO: \"Derecho petición botas\" ID CASO: 157. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:28:58'),
(235, '1487569254', 'NUEVO CASO: \"Solicitud inscripción incentivos\" ID CASO: 158. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:28:58'),
(236, '1487569254', 'NUEVO CASO: \"Derecho petición resultados\" ID CASO: 159. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Marleny Gaviria', '2026-04-06 01:28:58'),
(237, '1487569254', 'NUEVO CASO: \"Reporte luminaria fundida\" ID CASO: 160. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:35:50'),
(238, '1487569254', 'NUEVO CASO: \"Solicitud recarga botiquín\" ID CASO: 161. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:35:50'),
(239, '1487569254', 'NUEVO CASO: \"Denuncia piso resbaladizo\" ID CASO: 162. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:35:50'),
(240, '1487569254', 'NUEVO CASO: \"Solicitud señalética seguridad\" ID CASO: 163. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-06 01:35:50'),
(241, '1487569254', 'NUEVO CASO: \"Solicitud auxilio educativo\" ID CASO: 164. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:35:50'),
(242, '1487569254', 'NUEVO CASO: \"Denuncia ruido excesivo\" ID CASO: 165. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:35:50'),
(243, '1487569254', 'NUEVO CASO: \"Derecho petición clima laboral\" ID CASO: 166. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:35:50'),
(244, '1487569254', 'NUEVO CASO: \"Falla restablecimiento clave\" ID CASO: 167. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Marleny Gaviria', '2026-04-06 01:35:50'),
(245, '1487569254', 'NUEVO CASO: \"Solicitud licencia software\" ID CASO: 168. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:35:50'),
(246, '1487569254', 'NUEVO CASO: \"Solicitud cambio de talla\" ID CASO: 169. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:35:50'),
(247, '1487569254', 'NUEVO CASO: \"Reporte dotación incompleta\" ID CASO: 170. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:35:50'),
(248, '1487569254', 'NUEVO CASO: \"Postulación empleado del mes\" ID CASO: 171. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Marleny Gaviria', '2026-04-06 01:35:50'),
(249, '1487569254', 'NUEVO CASO: \"Queja puntaje incentivos\" ID CASO: 172. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:35:50'),
(250, '1487569254', 'NUEVO CASO: \"Reporte extintor vencido\" ID CASO: 173. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:37:51'),
(251, '1487569254', 'NUEVO CASO: \"Solicitud examen periódico\" ID CASO: 174. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:37:51'),
(252, '1487569254', 'NUEVO CASO: \"Denuncia falta de EPP\" ID CASO: 175. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:37:51'),
(253, '1487569254', 'NUEVO CASO: \"Solicitud permiso calamidad\" ID CASO: 176. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Marleny Gaviria', '2026-04-06 01:37:51'),
(254, '1487569254', 'NUEVO CASO: \"Derecho petición vivienda\" ID CASO: 177. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:37:51'),
(255, '1487569254', 'NUEVO CASO: \"Reporte equipo lento\" ID CASO: 178. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:37:51'),
(256, '1487569254', 'NUEVO CASO: \"Solicitud acceso VPN\" ID CASO: 179. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:37:51'),
(257, '1487569254', 'NUEVO CASO: \"Solicitud chaqueta térmica\" ID CASO: 180. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Marleny Gaviria', '2026-04-06 01:37:51'),
(258, '1487569254', 'NUEVO CASO: \"Denuncia dotación dañada\" ID CASO: 181. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:37:51'),
(259, '1487569254', 'NUEVO CASO: \"Consulta puntos acumulados\" ID CASO: 182. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:37:51'),
(260, '1487569254', 'NUEVO CASO: \"Sugerencia programa bienestar\" ID CASO: 183. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:37:51');

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
(227, '1013341532', 'NUEVO CASO: \"Denuncia cable suelto\" ID CASO: 136. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:28:58'),
(228, '1027961396', 'NUEVO CASO: \"Solicitud inspección seguridad\" ID CASO: 137. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:28:58'),
(229, '1013342119', 'NUEVO CASO: \"Denuncia caída trabajador\" ID CASO: 138. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:28:58'),
(230, '1020304050', 'NUEVO CASO: \"Solicitud revisión herramientas\" ID CASO: 139. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-06 01:28:58'),
(231, '1013341532', 'NUEVO CASO: \"Derecho petición accidente\" ID CASO: 140. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:28:58'),
(232, '1027961396', 'NUEVO CASO: \"Denuncia falta señalización\" ID CASO: 141. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:28:58'),
(233, '1013342119', 'NUEVO CASO: \"Solicitud capacitación riesgos\" ID CASO: 142. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:28:58'),
(234, '1020304050', 'NUEVO CASO: \"Denuncia riesgo eléctrico\" ID CASO: 143. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-06 01:28:58'),
(235, '1013341532', 'NUEVO CASO: \"Solicitud revisión extintores\" ID CASO: 144. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:28:58'),
(236, '1027961396', 'NUEVO CASO: \"Denuncia obstrucción salida\" ID CASO: 145. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:28:58'),
(237, '1013342119', 'NUEVO CASO: \"Derecho petición seguimiento SST\" ID CASO: 146. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:28:58'),
(238, '1020304050', 'NUEVO CASO: \"Solicitud evaluación riesgos\" ID CASO: 147. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-06 01:28:58'),
(239, '1013341532', 'NUEVO CASO: \"Solicitud apoyo psicológico\" ID CASO: 148. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:28:58'),
(240, '1027961396', 'NUEVO CASO: \"Denuncia conflicto laboral\" ID CASO: 149. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:28:58'),
(241, '1013342119', 'NUEVO CASO: \"Derecho petición subsidios\" ID CASO: 150. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:28:58'),
(242, '1020304050', 'NUEVO CASO: \"Solicitud actividad bienestar\" ID CASO: 151. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Marleny Gaviria', '2026-04-06 01:28:58'),
(243, '1013341532', 'NUEVO CASO: \"Denuncia trato inadecuado\" ID CASO: 152. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:28:58'),
(244, '1027961396', 'NUEVO CASO: \"Solicitud soporte sistema\" ID CASO: 153. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:28:58'),
(245, '1013342119', 'NUEVO CASO: \"Derecho petición tecnológica\" ID CASO: 154. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:28:58'),
(246, '1020304050', 'NUEVO CASO: \"Denuncia fallas plataforma\" ID CASO: 155. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Marleny Gaviria', '2026-04-06 01:28:58'),
(247, '1013341532', 'NUEVO CASO: \"Solicitud dotación uniforme\" ID CASO: 156. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:28:58'),
(248, '1027961396', 'NUEVO CASO: \"Derecho petición botas\" ID CASO: 157. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:28:58'),
(249, '1013342119', 'NUEVO CASO: \"Solicitud inscripción incentivos\" ID CASO: 158. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:28:58'),
(250, '1020304050', 'NUEVO CASO: \"Derecho petición resultados\" ID CASO: 159. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Marleny Gaviria', '2026-04-06 01:28:58'),
(251, '1013341532', 'NUEVO CASO: \"Reporte luminaria fundida\" ID CASO: 160. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:35:50'),
(252, '1027961396', 'NUEVO CASO: \"Solicitud recarga botiquín\" ID CASO: 161. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:35:50'),
(253, '1013342119', 'NUEVO CASO: \"Denuncia piso resbaladizo\" ID CASO: 162. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:35:50'),
(254, '1020304050', 'NUEVO CASO: \"Solicitud señalética seguridad\" ID CASO: 163. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Marleny Gaviria', '2026-04-06 01:35:50'),
(255, '1013341532', 'NUEVO CASO: \"Solicitud auxilio educativo\" ID CASO: 164. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:35:50'),
(256, '1027961396', 'NUEVO CASO: \"Denuncia ruido excesivo\" ID CASO: 165. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:35:50'),
(257, '1013342119', 'NUEVO CASO: \"Derecho petición clima laboral\" ID CASO: 166. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:35:50'),
(258, '1020304050', 'NUEVO CASO: \"Falla restablecimiento clave\" ID CASO: 167. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Marleny Gaviria', '2026-04-06 01:35:50'),
(259, '1013341532', 'NUEVO CASO: \"Solicitud licencia software\" ID CASO: 168. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:35:50'),
(260, '1027961396', 'NUEVO CASO: \"Solicitud cambio de talla\" ID CASO: 169. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:35:50'),
(261, '1013342119', 'NUEVO CASO: \"Reporte dotación incompleta\" ID CASO: 170. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:35:50'),
(262, '1020304050', 'NUEVO CASO: \"Postulación empleado del mes\" ID CASO: 171. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Marleny Gaviria', '2026-04-06 01:35:50'),
(263, '1013341532', 'NUEVO CASO: \"Queja puntaje incentivos\" ID CASO: 172. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:35:50'),
(264, '1013341532', 'NUEVO CASO: \"Reporte extintor vencido\" ID CASO: 173. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:37:51'),
(265, '1027961396', 'NUEVO CASO: \"Solicitud examen periódico\" ID CASO: 174. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:37:51'),
(266, '1013342119', 'NUEVO CASO: \"Denuncia falta de EPP\" ID CASO: 175. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SST asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:37:51'),
(267, '1020304050', 'NUEVO CASO: \"Solicitud permiso calamidad\" ID CASO: 176. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Marleny Gaviria', '2026-04-06 01:37:51'),
(268, '1013341532', 'NUEVO CASO: \"Derecho petición vivienda\" ID CASO: 177. \nSe ha registrado un nuevo caso de Derecho de Petición Por Atender perteneciente al Proceso Organizacional Bienestar Social asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:37:51'),
(269, '1027961396', 'NUEVO CASO: \"Reporte equipo lento\" ID CASO: 178. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:37:51'),
(270, '1013342119', 'NUEVO CASO: \"Solicitud acceso VPN\" ID CASO: 179. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional SSEMI asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:37:51'),
(271, '1020304050', 'NUEVO CASO: \"Solicitud chaqueta térmica\" ID CASO: 180. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Marleny Gaviria', '2026-04-06 01:37:51'),
(272, '1013341532', 'NUEVO CASO: \"Denuncia dotación dañada\" ID CASO: 181. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Ropa de Trabajo asignado al comisionado Simon Gonzalez Pelaez', '2026-04-06 01:37:51'),
(273, '1027961396', 'NUEVO CASO: \"Consulta puntos acumulados\" ID CASO: 182. \nSe ha registrado un nuevo caso de Solicitud Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Juan Manuel Correal Galvis', '2026-04-06 01:37:51'),
(274, '1013342119', 'NUEVO CASO: \"Sugerencia programa bienestar\" ID CASO: 183. \nSe ha registrado un nuevo caso de Denuncia Por Atender perteneciente al Proceso Organizacional Plan de incentivos asignado al comisionado Isaac Manuel Carvajal Lopez', '2026-04-06 01:37:51'),
(275, '1020304050', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Marleny Gaviria\", se te ha asignado un caso con el nombre: \"Solicitud examen periódico\" con la id 174', '2026-04-06 01:38:45'),
(276, '1027961396', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Juan Manuel\", uno de tus casos con el nombre Solicitud examen periódico y la id 174 se le ha asignado al comisonado: \"Marleny Gaviria', '2026-04-06 01:38:45'),
(277, '1027961396', 'SE TE HA ASIGNADO UN CASO: Estimado Comisionado \"Juan Manuel Correal Galvis\", se te ha asignado un caso con el nombre: \"Sugerencia programa bienestar\" con la id 183', '2026-04-06 01:39:29'),
(278, '1013342119', 'UNO DE TUS CASOS SE HA REASIGNADO: Estimado Comisionado \"Isaac Manuel\", uno de tus casos con el nombre Sugerencia programa bienestar y la id 183 se le ha asignado al comisonado: \"Juan Manuel Correal Galvis', '2026-04-06 01:39:29');

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
(51, '2026-04-06 01:38:45', 'Caso por asignar', '1487569254', 174),
(52, '2026-04-06 01:39:29', 'Caso por asignar', '1487569254', 183);

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
('1013341532', 'Simon', 'Gonzalez Pelaez', 'pelaezgonzalezsimon919@gmail.com', '3207619679', 2, '$2y$10$9/SLzD/EK5z.H428GsH3.uKgWVAJpbE6v.Dy508UPDzslms0eD8Jm', '2026-04-06 01:11:50', '2028-04-06 01:11:50', '2026-2028', '2026-04-12 15:12:50', 1, 0, NULL),
('1013342119', 'Isaac Manuel', 'Carvajal Lopez', 'isaaccarvajal1356@gmail.com', '3243389897', 2, '$2y$10$MsUHwJtTJ9Txsd1o7mvQ2uV/rEkKfRFfaWTHYFZDOMUbKMi.fvKGy', '2026-04-06 01:22:41', '2028-04-06 01:22:41', '2026-2028', '2026-04-06 05:15:20', 1, 0, NULL),
('1020304050', 'Marleny', 'Gaviria', 'koritocarrera@gmail.com', '3001234567', 2, '$2y$10$MO98eVba1jIkuZgsDsZ3NOmMQwwXRnpL650wjeTSGnhstE0dh17h6', '2026-04-06 01:24:53', '2028-04-06 01:24:53', '2026-2028', '2026-04-06 04:22:41', 1, 0, NULL),
('1027961396', 'Juan Manuel', 'Correal Galvis', 'juangalvis.developer@gmail.com', '3243740191', 2, '$2y$10$rpd99onGDNu3Mm7g2f0uoOhX9P00afAeEsilbuegithqkfrDUaIPK', '2026-04-06 01:20:43', '2028-04-06 01:20:43', '2026-2028', NULL, 1, 0, NULL),
('1487569254', 'Kory', 'Carrerita', 'kory.carrera.dev@gmail.com', '3001234567', 1, '$2y$10$Jv38fJwprb95GT4MUs8n1elsr42/1fWNevWmOlYixG.NgZdhbF9US', '2026-01-24 03:14:09', '2028-01-24 03:14:09', '2026-2028', '2026-04-06 04:19:25', 1, 0, '7be3757a753976a4ca6e');

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
  MODIFY `id_archivo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para encontrar y relacionar';

--
-- AUTO_INCREMENT de la tabla `caso`
--
ALTER TABLE `caso`
  MODIFY `id_caso` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK de casos', AUTO_INCREMENT=184;

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
  MODIFY `id_informe` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para ubicar y relacionar', AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de la tabla `monitoreo`
--
ALTER TABLE `monitoreo`
  MODIFY `id_monitoreo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Llave primaria para reconocimiento y relacion', AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `noti_administrador`
--
ALTER TABLE `noti_administrador`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

--
-- AUTO_INCREMENT de la tabla `noti_comisionado`
--
ALTER TABLE `noti_comisionado`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para relacionar y encontrar', AUTO_INCREMENT=279;

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
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para encontrar y relacionar', AUTO_INCREMENT=55;

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
