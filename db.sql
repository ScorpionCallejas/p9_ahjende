-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 15-08-2025 a las 22:44:48
-- Versión del servidor: 10.1.38-MariaDB
-- Versión de PHP: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db_test`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cit` int(11) NOT NULL,
  `nom_cit` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fec_cit` datetime NOT NULL,
  `id_eje` int(11) NOT NULL,
  `id_pla` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cit`, `nom_cit`, `fec_cit`, `id_eje`, `id_pla`) VALUES
(1, 'Reunión estratégica corporativa', '2025-08-11 02:02:11', 1, 1),
(2, 'Presentación resultados trimestrales', '2025-08-12 04:32:11', 1, 1),
(3, 'Junta directiva', '2025-08-13 07:02:11', 1, 1),
(4, 'Revisión presupuesto anual', '2025-08-14 03:02:11', 1, 1),
(5, 'Entrevista con prensa', '2025-08-15 09:32:11', 1, 1),
(6, 'Revisión operativa plantel norte', '2025-08-10 01:33:27', 2, 1),
(7, 'Planificación comercial regional', '2025-08-12 06:03:27', 2, 1),
(8, 'Seguimiento proyectos clave', '2025-08-13 08:48:27', 2, 1),
(9, 'Evaluación desempeño equipos', '2025-08-14 02:18:27', 2, 1),
(10, 'Reunión con proveedores principales', '2025-08-16 04:03:27', 2, 1),
(11, 'Análisis mercado zona centro', '2025-08-09 03:03:38', 3, 2),
(12, 'Presentación nueva línea productos', '2025-08-11 09:23:38', 3, 1),
(13, 'Revisión KPI comerciales', '2025-08-13 02:33:38', 3, 1),
(14, 'Capacitación liderazgo', '2025-08-15 07:18:38', 3, 1),
(15, 'Sesión innovación estratégica', '2025-08-17 04:48:38', 3, 1),
(16, 'Expansión mercado sur', '2025-08-08 06:33:58', 4, 1),
(17, 'Alianza estratégica con partners', '2025-08-10 10:03:58', 4, 1),
(18, 'Revisión operativa plantel sur', '2025-08-13 01:48:58', 4, 1),
(19, 'Presentación informe gestión', '2025-08-14 03:33:58', 4, 1),
(20, 'Reunión con autoridades locales', '2025-08-16 05:18:58', 4, 1),
(21, 'Coordinación equipo ventas', '2025-08-11 01:04:08', 5, 1),
(22, 'Revisión cartera clientes', '2025-08-12 05:34:08', 5, 1),
(23, 'Capacitación productos nuevos', '2025-08-13 07:04:08', 5, 1),
(24, 'Seguimiento metas comerciales', '2025-08-15 02:49:08', 5, 1),
(25, 'Evaluación desempeño ejecutivos', '2025-08-16 09:24:08', 5, 1),
(26, 'Reunión operativa diaria', '2025-08-18 01:19:08', 5, 1),
(27, 'Planificación semanal actividades', '2025-08-10 02:34:17', 6, 2),
(28, 'Análisis competencia local', '2025-08-12 04:49:17', 6, 2),
(29, 'Revisión procesos internos', '2025-08-13 08:34:17', 6, 2),
(30, 'Entrenamiento habilidades blandas', '2025-08-14 03:19:17', 6, 2),
(31, 'Sesión feedback equipo', '2025-08-17 06:04:17', 6, 2),
(32, 'Coordinación logística', '2025-08-09 03:34:24', 7, 2),
(33, 'Presentación promociones', '2025-08-11 07:19:24', 7, 2),
(34, 'Revisión inventarios', '2025-08-13 02:04:24', 7, 2),
(35, 'Capacitación servicio al cliente', '2025-08-15 04:34:24', 7, 2),
(36, 'Análisis satisfacción clientes', '2025-08-16 09:49:24', 7, 2),
(37, 'Reunión cross-departamental', '2025-08-18 03:04:24', 7, 2),
(38, 'Planificación campaña marketing', '2025-08-08 06:04:33', 8, 2),
(39, 'Revisión métricas digitales', '2025-08-10 08:34:33', 8, 2),
(40, 'Coordinación eventos especiales', '2025-08-13 01:49:33', 8, 2),
(41, 'Sesión creativa publicitaria', '2025-08-14 07:24:33', 8, 2),
(42, 'Evaluación resultados campaña', '2025-08-17 04:19:33', 8, 2),
(43, 'Revisión expansión territorial', '2025-08-11 02:19:40', 9, 2),
(44, 'Presentación nuevo mercado', '2025-08-12 05:49:40', 9, 2),
(45, 'Capacitación normativa local', '2025-08-13 09:04:40', 9, 2),
(46, 'Seguimiento proyectos especiales', '2025-08-15 03:34:40', 9, 2),
(47, 'Evaluación riesgos operativos', '2025-08-16 07:19:40', 9, 2),
(48, 'Reunión con socios estratégicos', '2025-08-18 04:04:40', 9, 2),
(49, 'Planificación recursos humanos', '2025-08-10 01:34:46', 10, 2),
(50, 'Revisión clima laboral', '2025-08-12 04:24:46', 10, 2),
(51, 'Capacitación seguridad industrial', '2025-08-13 07:49:46', 10, 2),
(52, 'Sesión desarrollo profesional', '2025-08-14 02:19:46', 10, 2),
(53, 'Evaluación formación equipos', '2025-08-17 06:34:46', 10, 2),
(54, 'Visita cliente Corporación ABC', '2025-08-11 03:04:52', 11, 3),
(55, 'Presentación propuesta comercial', '2025-08-12 08:34:52', 11, 3),
(56, 'Seguimiento contrato 2025-001', '2025-08-13 04:49:52', 11, 3),
(57, 'Negociación condiciones pago', '2025-08-15 09:19:52', 11, 3),
(58, 'Reunión post-venta', '2025-08-16 02:34:52', 11, 3),
(59, 'Capacitación producto X', '2025-08-18 07:04:52', 11, 3),
(60, 'Visita prospecto Empresa XYZ', '2025-08-10 06:24:58', 12, 3),
(61, 'Presentación solución personalizada', '2025-08-12 02:49:58', 12, 3),
(62, 'Renovación contrato anual', '2025-08-13 09:34:58', 12, 3),
(63, 'Reunión servicio técnico', '2025-08-14 03:19:58', 12, 3),
(64, 'Seguimiento quejas cliente', '2025-08-17 05:04:58', 12, 3),
(65, 'Demostración producto nuevo', '2025-08-09 04:05:05', 13, 3),
(66, 'Negociación contrato corporativo', '2025-08-11 07:50:05', 13, 3),
(67, 'Visita instalaciones cliente', '2025-08-13 02:20:05', 13, 3),
(68, 'Capacitación características técnicas', '2025-08-15 08:35:05', 13, 3),
(69, 'Reunión evaluación satisfacción', '2025-08-16 03:50:05', 13, 3),
(70, 'Seguimiento implementación', '2025-08-18 06:20:05', 13, 3),
(71, 'Presentación informe mensual', '2025-08-08 01:50:13', 14, 3),
(72, 'Reunión con departamento compras', '2025-08-10 05:35:13', 14, 3),
(73, 'Visita seguimiento proyecto', '2025-08-13 07:20:13', 14, 3),
(74, 'Negociación condiciones especiales', '2025-08-14 04:05:13', 14, 3),
(75, 'Capacitación uso plataforma', '2025-08-17 09:50:13', 14, 3),
(76, 'Reunión inicial con nuevo cliente', '2025-08-11 03:35:19', 15, 3),
(77, 'Presentación portafolio completo', '2025-08-12 08:20:19', 15, 3),
(78, 'Visita diagnóstico necesidades', '2025-08-13 02:50:19', 15, 3),
(79, 'Seguimiento cotización pendiente', '2025-08-15 07:35:19', 15, 3),
(80, 'Reunión cierre de venta', '2025-08-16 04:20:19', 15, 3),
(81, 'Entrega producto/service', '2025-08-18 03:05:19', 15, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejecutivo`
--

CREATE TABLE `ejecutivo` (
  `id_eje` int(11) NOT NULL,
  `nom_eje` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tel_eje` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `eli_eje` int(11) DEFAULT '1',
  `id_padre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ejecutivo`
--

INSERT INTO `ejecutivo` (`id_eje`, `nom_eje`, `tel_eje`, `eli_eje`, `id_padre`) VALUES
(1, 'Raul Callejas', '555-1000', 1, NULL),
(2, 'Erick Valenzuela', '555-1001', 1, 1),
(3, 'Supervisor Norte A', '555-1002', 1, 2),
(4, 'Ejecutivo Norte 1', '555-1003', 1, 3),
(5, 'Ejecutivo Norte 2', '555-1004', 1, 3),
(6, 'Gerente Centro', '555-2001', 1, NULL),
(7, 'Supervisor Centro A', '555-2002', 1, 6),
(8, 'Ejecutivo Centro 1', '555-2003', 1, 7),
(9, 'Gerente Sur', '555-3001', 1, NULL),
(10, 'Supervisor Sur A', '555-3002', 1, 9),
(11, 'Ejecutivo Sur 1', '555-3003', 1, NULL),
(12, 'Supervisor Sur A', '555-3002', 1, 11),
(13, 'Ejecutivo Sur 1', '555-3003', 1, 15),
(14, 'Supervisor Norte A', '555-1002', 1, 15),
(15, 'Ejecutivo Norte 1', '555-1003', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejecutivo_plantel`
--

CREATE TABLE `ejecutivo_plantel` (
  `id_eje_pla` int(11) NOT NULL,
  `id_eje` int(11) NOT NULL,
  `id_pla` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ejecutivo_plantel`
--

INSERT INTO `ejecutivo_plantel` (`id_eje_pla`, `id_eje`, `id_pla`) VALUES
(1, 2, 1),
(2, 3, 1),
(3, 4, 1),
(4, 5, 1),
(5, 6, 2),
(6, 7, 2),
(7, 8, 2),
(8, 9, 2),
(9, 10, 2),
(10, 11, 3),
(11, 1, 1),
(12, 12, 3),
(13, 13, 3),
(14, 14, 3),
(15, 15, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantel`
--

CREATE TABLE `plantel` (
  `id_pla` int(11) NOT NULL,
  `nom_pla` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `plantel`
--

INSERT INTO `plantel` (`id_pla`, `nom_pla`) VALUES
(1, 'Ecatepec'),
(2, 'Naucalpan'),
(3, 'Cuatitlan');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cit`),
  ADD KEY `id_eje` (`id_eje`),
  ADD KEY `id_pla` (`id_pla`);

--
-- Indices de la tabla `ejecutivo`
--
ALTER TABLE `ejecutivo`
  ADD PRIMARY KEY (`id_eje`),
  ADD KEY `fk_ejecutivo_padre` (`id_padre`);

--
-- Indices de la tabla `ejecutivo_plantel`
--
ALTER TABLE `ejecutivo_plantel`
  ADD PRIMARY KEY (`id_eje_pla`),
  ADD KEY `id_eje` (`id_eje`),
  ADD KEY `id_pla` (`id_pla`);

--
-- Indices de la tabla `plantel`
--
ALTER TABLE `plantel`
  ADD PRIMARY KEY (`id_pla`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT de la tabla `ejecutivo`
--
ALTER TABLE `ejecutivo`
  MODIFY `id_eje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `ejecutivo_plantel`
--
ALTER TABLE `ejecutivo_plantel`
  MODIFY `id_eje_pla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `plantel`
--
ALTER TABLE `plantel`
  MODIFY `id_pla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_eje`) REFERENCES `ejecutivo` (`id_eje`),
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`id_pla`) REFERENCES `plantel` (`id_pla`);

--
-- Filtros para la tabla `ejecutivo`
--
ALTER TABLE `ejecutivo`
  ADD CONSTRAINT `ejecutivo_ibfk_1` FOREIGN KEY (`id_padre`) REFERENCES `ejecutivo` (`id_eje`),
  ADD CONSTRAINT `fk_ejecutivo_padre` FOREIGN KEY (`id_padre`) REFERENCES `ejecutivo` (`id_eje`);

--
-- Filtros para la tabla `ejecutivo_plantel`
--
ALTER TABLE `ejecutivo_plantel`
  ADD CONSTRAINT `ejecutivo_plantel_ibfk_1` FOREIGN KEY (`id_eje`) REFERENCES `ejecutivo` (`id_eje`),
  ADD CONSTRAINT `ejecutivo_plantel_ibfk_2` FOREIGN KEY (`id_pla`) REFERENCES `plantel` (`id_pla`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
