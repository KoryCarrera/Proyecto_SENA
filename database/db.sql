-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db_sena
-- Tiempo de generación: 07-02-2026 a las 05:31:56
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
(41, '1111111111', '2026-02-07 05:30:34', 'EXCEL', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `informe`
--
ALTER TABLE `informe`
  ADD PRIMARY KEY (`id_informe`),
  ADD KEY `documento` (`documento`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `informe`
--
ALTER TABLE `informe`
  MODIFY `id_informe` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK para ubicar y relacionar', AUTO_INCREMENT=42;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `informe`
--
ALTER TABLE `informe`
  ADD CONSTRAINT `documento` FOREIGN KEY (`documento`) REFERENCES `usuario` (`documento`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
