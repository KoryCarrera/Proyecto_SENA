-- phpMyAdmin SQL Dump
-- version 5.0.4deb2+deb11u2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 14-01-2026 a las 19:02:38
-- Versión del servidor: 10.5.29-MariaDB-0+deb11u1
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyecto_senadb`
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
  `id_rol` int(11) NOT NULL COMMENT 'FK para relacionar rol del usario con la tabla rol',
  `contraseña` varchar(255) NOT NULL COMMENT 'Contraseña del usuario para su ingreso ',
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha del dia que se registró el usuario',
  `ultimo_inicio_sesion` datetime DEFAULT NULL,
  `id_estado` tinyint(1) NOT NULL DEFAULT 1,
  `token` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`documento`, `nombre`, `apellido`, `email`, `id_rol`, `contraseña`, `fecha_registro`, `ultimo_inicio_sesion`, `id_estado`, `token`) VALUES
('1020304010', 'Isaac', 'Carvajal', 'zacki@hotmail.com', 2, '$2y$10$XJPcDeP8NIq87Z1wuKvrreLIUkUzNqyfY1yOD0K46Bi70jMs3AImi', '2025-11-24 06:54:17', '2025-12-10 11:26:13', 1, ''),
('112233', 'pepito', 'perez', 'pepito@perez.com', 2, 'contraseña', '2025-12-01 17:43:14', NULL, 1, ''),
('11223344', 'Pepo', 'Peraz', 'pepito@hola.com', 2, '$2y$10$Zt/ebqk4NLWfRf0wIOaOrOPG1T4gFW0h7j11ZIkUo8yjlREos8P/a', '2025-12-01 18:08:24', NULL, 1, ''),
('123456', 'Kory', 'Carrera', 'Kory@carrera.com', 2, '$2y$10$gQ6trQAwy.dl3XF8i3PPieem3.wauWb.daIwa3VWCMsXlojO7z9dO', '2025-12-01 18:11:51', NULL, 1, ''),
('123456789', 'Juan', 'Galvis', 'juan@galvis.com', 1, '$2y$10$O/YRYjCjYN09us2MOEpPT.c.GYNWs7/arm/aeShBQry/zG8b/BiMS', '2025-12-01 18:10:06', '2025-12-05 15:50:48', 1, ''),
('12345678910', 'floppy', 'carrera', 'floppy.carrera@gmail.com', 1, '$2y$10$3KpsnHx05KaGQIqS6EmeDO7K.zLZ8TcWff5H.tbvXsi0YzmXqSsEa', '2025-12-03 17:56:38', NULL, 1, ''),
('3001', 'Ana', 'Perez', 'ana@correo.com', 2, '123456', '2026-01-14 18:51:42', NULL, 1, 'tok3001'),
('3002', 'Juan', 'Diaz', 'juan@correo.com', 2, '123456', '2026-01-14 18:51:42', NULL, 1, 'tok3002'),
('3003', 'Laura', 'Gomez', 'laura@correo.com', 2, '123456', '2026-01-14 18:51:42', NULL, 1, 'tok3003'),
('4896736', 'Kory', 'Carrera', 'koritocarrera@gmail.com', 1, '31199437Kory', '2025-11-23 03:48:06', '2025-11-29 12:35:02', 1, ''),
('654321', 'Eren', 'Jegar', 'Eren@jegar.com', 1, '$2y$10$XGcTeK/wdWCItm4UKFQ7GOWnDeWZYukZlQgeONPdDaPyG3CSIAzsy', '2025-12-02 17:08:51', '2025-12-18 11:56:45', 0, ''),
('98674523', 'Yldegar', 'Alvarez', 'karrerita@gmail.com', 1, '$2y$10$YUBDZSJqh2/LCH9jlPeeJei1i.4P/zYGPYRTCHUK2qjDbK3qE7K6S', '2025-11-24 07:46:37', '2026-01-07 14:22:32', 1, ''),
('987654321', 'Isaac', 'carvajal', 'isaac@carvajal.com', 1, '$2y$10$aSUvDXhUTg7PXmhvqX.efuw7ggQhXAbzC2/U2VATfUgC9Uab0auh6', '2025-12-01 18:11:02', '2025-12-18 11:56:57', 1, '');

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
