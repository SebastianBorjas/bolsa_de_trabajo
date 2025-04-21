-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-03-2025 a las 20:19:08
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `curriculums2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas`
--

CREATE TABLE `areas` (
  `id_area` int(11) NOT NULL,
  `id_plantel` int(11) NOT NULL,
  `nombre_area` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `areas`
--

INSERT INTO `areas` (`id_area`, `id_plantel`, `nombre_area`) VALUES
(1, 1, 'Contabilidad'),
(2, 1, 'Mecatronica'),
(3, 1, 'Electronica'),
(4, 1, 'Software'),
(5, 1, 'Administración');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id_empleado` int(11) NOT NULL,
  `id_plantel` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `celular` varchar(15) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `estudios` enum('primaria','secundaria','preparatoria','universidad') NOT NULL,
  `edad` int(11) NOT NULL,
  `ruta_curriculum` varchar(255) DEFAULT NULL,
  `experiencia` enum('Sin experiencia','0-1 años de experiencia','1-5 años de experiencia','mas de 5 años de experiencia') NOT NULL,
  `area1` int(11) NOT NULL,
  `area2` int(11) DEFAULT NULL,
  `area3` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id_empleado`, `id_plantel`, `nombre_completo`, `celular`, `correo`, `estudios`, `edad`, `ruta_curriculum`, `experiencia`, `area1`, `area2`, `area3`) VALUES
(1, 1, 'sd asd as', 'asd', 'asd@gmail.com', 'secundaria', 28, '../curriculum/67daeabbc8903_Bases de datos modelo.pdf', '0-1 años de experiencia', 1, NULL, NULL),
(3, 1, 'dfsdf fsdlk lkjdsf', 'aslkdfdlfk', 'lkjdfs@gmail.com', 'secundaria', 23, '../curriculum/67daf1c831659_32805916536.pdf', 'Sin experiencia', 2, NULL, NULL),
(4, 1, 'ñlsadkñl lñkdf ñlkdsflk', '234987', 'sldfk@gmail.com', 'universidad', 44, '../curriculum/67daf328d3313_32805916536.pdf', 'Sin experiencia', 2, NULL, NULL),
(5, 1, 'sasdas asdasd asdasd', '134234', 'asdasd@gmail.com', 'primaria', 33, '../curriculum/67daf34833004_IMSS.pdf', 'Sin experiencia', 1, NULL, NULL),
(6, 1, 'Borrame adafdsd fdsssd', '34234', 'sdsfs@gmail.com', 'preparatoria', 34, '../curriculum/67db369022c35_67daf1c831659_32805916536.pdf', '1-5 años de experiencia', 3, 2, NULL),
(9, 1, 'kkk kkk kkk', '896867876', 'kkk@gmail.com', 'secundaria', 33, '../curriculum/67db378f6f75e_67daf1c831659_32805916536.pdf', 'mas de 5 años de experiencia', 1, 3, 2),
(10, 1, 'dsd sdsd sdsd', '5456', 'sds@gmail.com', 'primaria', 43, '../curriculum/67db37c95328e_67daf1c831659_32805916536.pdf', '1-5 años de experiencia', 4, NULL, NULL),
(11, 1, 'lll lll lll', '3240098', 'lll@gmail.com', 'universidad', 33, '../curriculum/67dc3962c9659_Bases de datos modelo.pdf', 'mas de 5 años de experiencia', 5, 1, 3),
(12, 1, '123 123 123', '123', '123@gmail.com', 'primaria', 33, '../curriculum/67dc3af5941bc_25650.pdf', 'Sin experiencia', 5, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `moderadores`
--

CREATE TABLE `moderadores` (
  `id_moderador` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_plantel` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `numero` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `moderadores`
--

INSERT INTO `moderadores` (`id_moderador`, `id_usuario`, `id_plantel`, `nombre_completo`, `correo`, `numero`) VALUES
(1, 2, 1, 'Moderador 1 1', 'mod1@gmail.com', '87654645');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planteles`
--

CREATE TABLE `planteles` (
  `id_plantel` int(11) NOT NULL,
  `nombre_plantel` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `planteles`
--

INSERT INTO `planteles` (`id_plantel`, `nombre_plantel`) VALUES
(1, 'Monclova');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(30) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `tipo_usuario` enum('administrador','moderador','empresa','empleado') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `usuario`, `contraseña`, `tipo_usuario`) VALUES
(1, 'admin', '$2y$10$LZDZXcA4GmmseVnR140DQOlQQzkuHyezwtWbfw.zhF.Lytu7yLY.e', 'administrador'),
(2, 'mod1', '$2y$10$JMT09oXw5gJuXAvk21B5GevYEgnmZVHLa/RfJvrhcQuDCTXVoNFpu', 'moderador'),
(3, 'sdasd884', '$2y$10$zJ5ot3GT3DRwnuMD7sXVD.8qnDgXN2cXe92qzUAsvpgQhKoCDB7MG', 'empleado'),
(4, 'sdasd132', '$2y$10$aSyRO7mElgfptxooup45WOJPO8p30qerfx7uPZNArGA.K4QuouwF2', 'empleado'),
(5, 'dfsfsd261', '$2y$10$4ZqTni8TMfHa6XbERdPf5eboOSRWMIhWDamyCxWXTPHlyfzde29oe', 'empleado'),
(6, 'ñllñ490', '$2y$10$bKVu79DMnPTtm3fRFJ4jcO7mdkFvimCn4uCD3ke98vzxcyYxm.iGC', 'empleado'),
(7, 'sasasd135', '$2y$10$Qvl4Tv9dJnBxLt0vPu1TCuR39eFcpY93Wlst8ieKH1LtlMUJ9WIx.', 'empleado'),
(8, 'borada510', '$2y$10$KylKvd6/RpNIMBs28Tj9L.VNrJ2d3uDYFhoFKxx6mrKDl0/SzOfFC', 'empleado'),
(9, 'borada242', '$2y$10$CAk6YakpR6EckLTPEvrtTOrg8GM/83ZgcpIV.9WD/Meqi0dPsmsKO', 'empleado'),
(10, 'borada166', '$2y$10$gp7Rg6dDzzUOHfpO6.60jeU8P63p2xeJvRVJpNlhj01o6WoUWtOB6', 'empleado'),
(11, 'kkkkkk111', '$2y$10$L8QGPMyBuo5q6wN0aIjQpOKyh1kc3iTKgEE7KQ2NLHe7DuM2LFlte', 'empleado'),
(12, 'dsdsds797', '$2y$10$FAJl0h.WUdog5nLjso6raedKd2AlmmXhPFWIGwpKihbREIDFWO7mC', 'empleado'),
(13, 'llllll508', '$2y$10$dYZPtgMNBlTBzovzf4SP7eD5gG64trVDzF1glhbbvHRKJeqYJ/Yju', 'empleado');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id_area`),
  ADD KEY `id_plantel` (`id_plantel`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id_empleado`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_plantel` (`id_plantel`),
  ADD KEY `empleados_ibfk_3` (`area1`),
  ADD KEY `empleados_ibfk_4` (`area2`),
  ADD KEY `empleados_ibfk_5` (`area3`);

--
-- Indices de la tabla `moderadores`
--
ALTER TABLE `moderadores`
  ADD PRIMARY KEY (`id_moderador`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_plantel` (`id_plantel`);

--
-- Indices de la tabla `planteles`
--
ALTER TABLE `planteles`
  ADD PRIMARY KEY (`id_plantel`),
  ADD UNIQUE KEY `nombre_plantel` (`nombre_plantel`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `areas`
--
ALTER TABLE `areas`
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `moderadores`
--
ALTER TABLE `moderadores`
  MODIFY `id_moderador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `planteles`
--
ALTER TABLE `planteles`
  MODIFY `id_plantel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `areas`
--
ALTER TABLE `areas`
  ADD CONSTRAINT `areas_ibfk_1` FOREIGN KEY (`id_plantel`) REFERENCES `planteles` (`id_plantel`);

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `empleados_ibfk_2` FOREIGN KEY (`id_plantel`) REFERENCES `planteles` (`id_plantel`),
  ADD CONSTRAINT `empleados_ibfk_3` FOREIGN KEY (`area1`) REFERENCES `areas` (`id_area`),
  ADD CONSTRAINT `empleados_ibfk_4` FOREIGN KEY (`area2`) REFERENCES `areas` (`id_area`),
  ADD CONSTRAINT `empleados_ibfk_5` FOREIGN KEY (`area3`) REFERENCES `areas` (`id_area`);

--
-- Filtros para la tabla `moderadores`
--
ALTER TABLE `moderadores`
  ADD CONSTRAINT `moderadores_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `moderadores_ibfk_2` FOREIGN KEY (`id_plantel`) REFERENCES `planteles` (`id_plantel`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
