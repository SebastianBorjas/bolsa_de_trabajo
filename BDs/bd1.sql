-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 18-03-2025 a las 16:43:24
-- Versión del servidor: 10.6.18-MariaDB-0ubuntu0.22.04.1
-- Versión de PHP: 8.3.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `prueba`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `id_actividad` int(11) NOT NULL,
  `id_tema` int(11) NOT NULL,
  `id_plan` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ruta` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos`
--

CREATE TABLE `alumnos` (
  `id_alumno` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_plantel` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `id_especialidad` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `celular` varchar(15) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `numero_pase` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones`
--

CREATE TABLE `asignaciones` (
  `id_asignacion` int(11) NOT NULL,
  `id_plan` int(11) NOT NULL,
  `id_maestro` int(11) NOT NULL,
  `id_empresa_alumno` int(11) NOT NULL,
  `id_alumno` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id_empresa` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_plantel` int(11) NOT NULL,
  `id_moderador` int(11) NOT NULL,
  `razon_social` varchar(150) NOT NULL,
  `nombre_comercial` varchar(100) NOT NULL,
  `rfc` varchar(13) NOT NULL,
  `colonia` varchar(100) NOT NULL,
  `num_ext` varchar(10) NOT NULL,
  `cp` varchar(5) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `telefono_fijo` varchar(15) DEFAULT NULL,
  `celular` varchar(15) NOT NULL,
  `redes_sociales` varchar(255) DEFAULT NULL,
  `correo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa_alumno`
--

CREATE TABLE `empresa_alumno` (
  `id_empresa_alumno` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `id_alumno` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `id_plantel` int(11) NOT NULL,
  `id_maestro` int(11) DEFAULT NULL,
  `id_plan` int(11) DEFAULT NULL,
  `estado` enum('asignado','desasignado') NOT NULL DEFAULT 'asignado',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_termino` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas_actividades`
--

CREATE TABLE `entregas_actividades` (
  `id_entrega` int(11) NOT NULL,
  `id_actividad` int(11) NOT NULL,
  `id_tema` int(11) NOT NULL,
  `id_alumno` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `id_maestro` int(11) NOT NULL,
  `id_empresa_alumno` int(11) NOT NULL,
  `id_plan` int(11) NOT NULL,
  `contenido` text DEFAULT NULL,
  `ruta_entrega` varchar(255) DEFAULT NULL,
  `estado` enum('pendiente_empresa','pendiente_maestro','completado','rechazado') NOT NULL DEFAULT 'pendiente_empresa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id_especialidad` int(11) NOT NULL,
  `id_plantel` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instituciones`
--

CREATE TABLE `instituciones` (
  `id_institucion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_plantel` int(11) NOT NULL,
  `id_moderador` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `colonia` varchar(100) NOT NULL,
  `num_ext` varchar(10) NOT NULL,
  `cp` varchar(5) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `telefono_fijo` varchar(15) DEFAULT NULL,
  `celular` varchar(15) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `tipo_pase` enum('anual','semestre','bimestre','cuatrimestre') NOT NULL,
  `numero_pase` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maestros`
--

CREATE TABLE `maestros` (
  `id_maestro` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_plantel` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `celular` varchar(15) NOT NULL,
  `correo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pasar_lista`
--

CREATE TABLE `pasar_lista` (
  `id_pasar_lista` int(11) NOT NULL,
  `id_alumno` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `id_empresa_alumno` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `presente` enum('sí','no') NOT NULL DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes`
--

CREATE TABLE `planes` (
  `id_plan` int(11) NOT NULL,
  `id_maestro` int(11) NOT NULL,
  `id_especialidad` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planteles`
--

CREATE TABLE `planteles` (
  `id_plantel` int(11) NOT NULL,
  `nombre_plantel` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temas`
--

CREATE TABLE `temas` (
  `id_tema` int(11) NOT NULL,
  `id_plan` int(11) NOT NULL,
  `nombre_tema` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(30) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `tipo_usuario` enum('administrador','moderador','empresa','institucion','maestro','alumno') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id_actividad`),
  ADD KEY `id_tema` (`id_tema`),
  ADD KEY `id_plan` (`id_plan`);

--
-- Indices de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`id_alumno`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_plantel` (`id_plantel`),
  ADD KEY `id_institucion` (`id_institucion`),
  ADD KEY `id_especialidad` (`id_especialidad`);

--
-- Indices de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD KEY `id_plan` (`id_plan`),
  ADD KEY `id_maestro` (`id_maestro`),
  ADD KEY `id_empresa_alumno` (`id_empresa_alumno`),
  ADD KEY `id_alumno` (`id_alumno`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `rfc` (`rfc`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_plantel` (`id_plantel`),
  ADD KEY `id_moderador` (`id_moderador`);

--
-- Indices de la tabla `empresa_alumno`
--
ALTER TABLE `empresa_alumno`
  ADD PRIMARY KEY (`id_empresa_alumno`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `id_alumno` (`id_alumno`),
  ADD KEY `id_institucion` (`id_institucion`),
  ADD KEY `id_plantel` (`id_plantel`),
  ADD KEY `id_maestro` (`id_maestro`),
  ADD KEY `id_plan` (`id_plan`);

--
-- Indices de la tabla `entregas_actividades`
--
ALTER TABLE `entregas_actividades`
  ADD PRIMARY KEY (`id_entrega`),
  ADD KEY `id_actividad` (`id_actividad`),
  ADD KEY `id_tema` (`id_tema`),
  ADD KEY `id_alumno` (`id_alumno`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `id_maestro` (`id_maestro`),
  ADD KEY `id_empresa_alumno` (`id_empresa_alumno`),
  ADD KEY `id_plan` (`id_plan`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id_especialidad`),
  ADD KEY `id_plantel` (`id_plantel`),
  ADD KEY `id_institucion` (`id_institucion`);

--
-- Indices de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD PRIMARY KEY (`id_institucion`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_plantel` (`id_plantel`),
  ADD KEY `id_moderador` (`id_moderador`);

--
-- Indices de la tabla `maestros`
--
ALTER TABLE `maestros`
  ADD PRIMARY KEY (`id_maestro`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_plantel` (`id_plantel`),
  ADD KEY `id_institucion` (`id_institucion`);

--
-- Indices de la tabla `moderadores`
--
ALTER TABLE `moderadores`
  ADD PRIMARY KEY (`id_moderador`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_plantel` (`id_plantel`);

--
-- Indices de la tabla `pasar_lista`
--
ALTER TABLE `pasar_lista`
  ADD PRIMARY KEY (`id_pasar_lista`),
  ADD KEY `id_alumno` (`id_alumno`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `id_empresa_alumno` (`id_empresa_alumno`);

--
-- Indices de la tabla `planes`
--
ALTER TABLE `planes`
  ADD PRIMARY KEY (`id_plan`),
  ADD KEY `id_maestro` (`id_maestro`),
  ADD KEY `id_especialidad` (`id_especialidad`);

--
-- Indices de la tabla `planteles`
--
ALTER TABLE `planteles`
  ADD PRIMARY KEY (`id_plantel`),
  ADD UNIQUE KEY `nombre_plantel` (`nombre_plantel`);

--
-- Indices de la tabla `temas`
--
ALTER TABLE `temas`
  ADD PRIMARY KEY (`id_tema`),
  ADD KEY `id_plan` (`id_plan`);

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
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  MODIFY `id_alumno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresa_alumno`
--
ALTER TABLE `empresa_alumno`
  MODIFY `id_empresa_alumno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `entregas_actividades`
--
ALTER TABLE `entregas_actividades`
  MODIFY `id_entrega` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id_especialidad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  MODIFY `id_institucion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `maestros`
--
ALTER TABLE `maestros`
  MODIFY `id_maestro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `moderadores`
--
ALTER TABLE `moderadores`
  MODIFY `id_moderador` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pasar_lista`
--
ALTER TABLE `pasar_lista`
  MODIFY `id_pasar_lista` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `planes`
--
ALTER TABLE `planes`
  MODIFY `id_plan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `planteles`
--
ALTER TABLE `planteles`
  MODIFY `id_plantel` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `temas`
--
ALTER TABLE `temas`
  MODIFY `id_tema` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD CONSTRAINT `actividades_ibfk_1` FOREIGN KEY (`id_tema`) REFERENCES `temas` (`id_tema`),
  ADD CONSTRAINT `actividades_ibfk_2` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id_plan`);

--
-- Filtros para la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD CONSTRAINT `alumnos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `alumnos_ibfk_2` FOREIGN KEY (`id_plantel`) REFERENCES `planteles` (`id_plantel`),
  ADD CONSTRAINT `alumnos_ibfk_3` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id_institucion`),
  ADD CONSTRAINT `alumnos_ibfk_4` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`);

--
-- Filtros para la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD CONSTRAINT `asignaciones_ibfk_1` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id_plan`),
  ADD CONSTRAINT `asignaciones_ibfk_2` FOREIGN KEY (`id_maestro`) REFERENCES `maestros` (`id_maestro`),
  ADD CONSTRAINT `asignaciones_ibfk_3` FOREIGN KEY (`id_empresa_alumno`) REFERENCES `empresa_alumno` (`id_empresa_alumno`),
  ADD CONSTRAINT `asignaciones_ibfk_4` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`);

--
-- Filtros para la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD CONSTRAINT `empresas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `empresas_ibfk_2` FOREIGN KEY (`id_plantel`) REFERENCES `planteles` (`id_plantel`),
  ADD CONSTRAINT `empresas_ibfk_3` FOREIGN KEY (`id_moderador`) REFERENCES `moderadores` (`id_moderador`);

--
-- Filtros para la tabla `empresa_alumno`
--
ALTER TABLE `empresa_alumno`
  ADD CONSTRAINT `empresa_alumno_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`),
  ADD CONSTRAINT `empresa_alumno_ibfk_2` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`),
  ADD CONSTRAINT `empresa_alumno_ibfk_3` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id_institucion`),
  ADD CONSTRAINT `empresa_alumno_ibfk_4` FOREIGN KEY (`id_plantel`) REFERENCES `planteles` (`id_plantel`),
  ADD CONSTRAINT `empresa_alumno_ibfk_5` FOREIGN KEY (`id_maestro`) REFERENCES `maestros` (`id_maestro`),
  ADD CONSTRAINT `empresa_alumno_ibfk_6` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id_plan`);

--
-- Filtros para la tabla `entregas_actividades`
--
ALTER TABLE `entregas_actividades`
  ADD CONSTRAINT `entregas_actividades_ibfk_1` FOREIGN KEY (`id_actividad`) REFERENCES `actividades` (`id_actividad`),
  ADD CONSTRAINT `entregas_actividades_ibfk_2` FOREIGN KEY (`id_tema`) REFERENCES `temas` (`id_tema`),
  ADD CONSTRAINT `entregas_actividades_ibfk_3` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`),
  ADD CONSTRAINT `entregas_actividades_ibfk_4` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`),
  ADD CONSTRAINT `entregas_actividades_ibfk_5` FOREIGN KEY (`id_maestro`) REFERENCES `maestros` (`id_maestro`),
  ADD CONSTRAINT `entregas_actividades_ibfk_6` FOREIGN KEY (`id_empresa_alumno`) REFERENCES `empresa_alumno` (`id_empresa_alumno`),
  ADD CONSTRAINT `entregas_actividades_ibfk_7` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id_plan`);

--
-- Filtros para la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD CONSTRAINT `especialidades_ibfk_1` FOREIGN KEY (`id_plantel`) REFERENCES `planteles` (`id_plantel`),
  ADD CONSTRAINT `especialidades_ibfk_2` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id_institucion`);

--
-- Filtros para la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD CONSTRAINT `instituciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `instituciones_ibfk_2` FOREIGN KEY (`id_plantel`) REFERENCES `planteles` (`id_plantel`),
  ADD CONSTRAINT `instituciones_ibfk_3` FOREIGN KEY (`id_moderador`) REFERENCES `moderadores` (`id_moderador`);

--
-- Filtros para la tabla `maestros`
--
ALTER TABLE `maestros`
  ADD CONSTRAINT `maestros_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `maestros_ibfk_2` FOREIGN KEY (`id_plantel`) REFERENCES `planteles` (`id_plantel`),
  ADD CONSTRAINT `maestros_ibfk_3` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id_institucion`);

--
-- Filtros para la tabla `moderadores`
--
ALTER TABLE `moderadores`
  ADD CONSTRAINT `moderadores_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `moderadores_ibfk_2` FOREIGN KEY (`id_plantel`) REFERENCES `planteles` (`id_plantel`);

--
-- Filtros para la tabla `pasar_lista`
--
ALTER TABLE `pasar_lista`
  ADD CONSTRAINT `pasar_lista_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`),
  ADD CONSTRAINT `pasar_lista_ibfk_2` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`),
  ADD CONSTRAINT `pasar_lista_ibfk_3` FOREIGN KEY (`id_empresa_alumno`) REFERENCES `empresa_alumno` (`id_empresa_alumno`);

--
-- Filtros para la tabla `planes`
--
ALTER TABLE `planes`
  ADD CONSTRAINT `planes_ibfk_1` FOREIGN KEY (`id_maestro`) REFERENCES `maestros` (`id_maestro`),
  ADD CONSTRAINT `planes_ibfk_2` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`);

--
-- Filtros para la tabla `temas`
--
ALTER TABLE `temas`
  ADD CONSTRAINT `temas_ibfk_1` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id_plan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
