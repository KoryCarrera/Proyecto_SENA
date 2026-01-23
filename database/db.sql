-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db_sena
-- Tiempo de generación: 23-01-2026 a las 00:38:20
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
CREATE DATABASE IF NOT EXISTS `proyectosena_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `proyectosena_db`;

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
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha en la que se realiza el caso',
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
(4, '123456', 2, '2025-12-02 16:14:23', NULL, 3, 1, 'XD XD XD XD XD XD XD'),
(5, '123456', 2, '2022-05-17 16:15:18', NULL, 3, 1, 'SDSADWQD QW 12 E12 1E ASD'),
(7, '11223344', 4, '2025-12-02 17:04:52', NULL, 2, 1, '3123131dasda dad w g<AW fw ege hd hgg'),
(55, '3001', 1, '2026-01-14 08:00:00', '2026-01-14 09:30:00', 1, 1, 'Caso atendido en la mañana'),
(56, '3001', 1, '2026-01-14 10:15:00', NULL, 2, 2, 'Caso en proceso'),
(57, '3002', 1, '2026-01-14 09:00:00', '2026-01-14 11:00:00', 1, 1, 'Caso cerrado correctamente'),
(58, '3002', 1, '2026-01-14 13:00:00', NULL, 3, 3, 'Caso no atendido'),
(59, '3003', 1, '2026-01-14 07:45:00', '2026-01-14 08:50:00', 1, 2, 'Caso resuelto rápidamente'),
(60, '3003', 1, '2026-01-14 14:30:00', NULL, 2, 1, 'Caso pendiente de atención');

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
  `contenido` text NOT NULL COMMENT 'contenido del informe'
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
(29, '98674523', '2026-01-22 21:03:23', 'PDF', '');

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
(7, '2026-01-15 22:59:08', 'Mesas para los salones', '98674523', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 1),
(8, '2026-01-19 19:11:07', 'no se', '654321', 'no se', 0),
(9, '2026-01-19 19:19:44', 'xd', '98674523', 'xd', 0);

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
('98674523', 'Yldegar', 'Alvarez', 'karrerita@gmail.com', 1, '$2y$10$YUBDZSJqh2/LCH9jlPeeJei1i.4P/zYGPYRTCHUK2qjDbK3qE7K6S', '2025-11-24 07:46:37', '2026-01-23 00:37:40', 1, ''),
('987654321', 'Isaac', 'carvajal', 'isaac@carvajal.com', 1, '$2y$10$aSUvDXhUTg7PXmhvqX.efuw7ggQhXAbzC2/U2VATfUgC9Uab0auh6', '2025-12-01 18:11:02', '2025-12-18 11:56:57', 1, '');

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
  MODIFY `id_caso` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK de casos', AUTO_INCREMENT=61;

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
  MODIFY `id_informe` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para ubicar y relacionar', AUTO_INCREMENT=30;

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
  ADD CONSTRAINT `archivo_ibfk_1` FOREIGN KEY (`id_caso`) REFERENCES `caso` (`id_caso`);

--
-- Filtros para la tabla `caso`
--
ALTER TABLE `caso`
  ADD CONSTRAINT `caso_ibfk_1` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`),
  ADD CONSTRAINT `caso_ibfk_2` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`),
  ADD CONSTRAINT `caso_ibfk_3` FOREIGN KEY (`id_tipo_caso`) REFERENCES `tipo_caso` (`id_tipo_caso`),
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
  ADD CONSTRAINT `monitoreo_ibfk_1` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`);

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `usuario` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`);

--
-- Filtros para la tabla `procesoorganizacional`
--
ALTER TABLE `procesoorganizacional`
  ADD CONSTRAINT `fk_usuario_proceso` FOREIGN KEY (`documento_usuario`) REFERENCES `usuario` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
