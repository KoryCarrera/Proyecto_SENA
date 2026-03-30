-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db_sena
-- Tiempo de generación: 30-03-2026 a las 15:28:21
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
('1456333298', 'Juan Manuel', 'Correal', 'gavliscorreal@gmail.com', NULL, 2, '$2y$10$fTBbRgMER/FyoOVR5e2eGuKdn0x.lxRxYQa9ZOSrYwQWylv4M6z4O', '2026-02-12 14:22:31', '2028-02-12 14:22:31', '2026-2028', '2026-03-30 14:49:28', 1, 0, NULL),
('1487569254', 'Kory', 'Carrerita', 'kory.carrera.dev@gmail.com', '3001234567', 1, '$2y$10$.ojGM8lAXRkAo9tY8JFuEOF5RJ0jrcwL05ErUzfZnaS5/fJWt6Xxq', '2026-01-24 03:14:09', '2028-01-24 03:14:09', '2026-2028', '2026-03-30 14:41:54', 1, 0, '7be3757a753976a4ca6e'),
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
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`documento`),
  ADD KEY `rol` (`id_rol`),
  ADD KEY `fk_estado_usuario` (`id_estado`);

--
-- Restricciones para tablas volcadas
--

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
