-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-09-2026 a las 10:04:56
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
-- Base de datos: `revista_digital2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autores`
--

CREATE TABLE `autores` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `ap_paterno` varchar(100) DEFAULT NULL,
  `ap_materno` varchar(100) DEFAULT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `es_nickname` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `autores`
--

INSERT INTO `autores` (`id`, `nombres`, `ap_paterno`, `ap_materno`, `nickname`, `es_nickname`) VALUES
(1, 'fefe', 'fefe', 'fefe', NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletines`
--

CREATE TABLE `boletines` (
  `id` int(10) UNSIGNED NOT NULL,
  `numero_boletin` varchar(50) NOT NULL,
  `titulo_boletin` varchar(100) NOT NULL,
  `resumen` varchar(500) DEFAULT NULL,
  `foto_portada` varchar(255) DEFAULT NULL,
  `archivo_pdf` varchar(255) NOT NULL,
  `estado` enum('borrador','publicado') NOT NULL DEFAULT 'publicado',
  `fecha_publicacion` date NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `boletines`
--

INSERT INTO `boletines` (`id`, `numero_boletin`, `titulo_boletin`, `resumen`, `foto_portada`, `archivo_pdf`, `estado`, `fecha_publicacion`, `usuario_id`) VALUES
(1, '1', 'boletin xd', 'qwertyuiopasdfghj', 'config/image/boletin-210cd8e9aeefb014.jpg', 'config/Pdf/boletin-355e83ccf6425c2e.pdf', 'publicado', '2026-09-24', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `link_externo` varchar(500) DEFAULT NULL,
  `estado` enum('borrador','publicado') NOT NULL DEFAULT 'publicado',
  `fecha_publicacion` date NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id`, `titulo`, `foto`, `link_externo`, `estado`, `fecha_publicacion`, `usuario_id`) VALUES
(1, 'fefe', 'config/image/noticia-6f1309486890169d.jpg', 'https://rpp.pe/politica/elecciones/elecciones-municipales-2026-intencion-de-voto-para-la-alcaldia-de-lima-segun-encuesta-de-iep-noticia-1706998', 'publicado', '2026-09-12', 1),
(2, 'linterna', 'config/image/noticia-ed0fa859cdbd2b62.png', 'https://rpp.pe/politica/elecciones/elecciones-municipales-2026-intencion-de-voto-para-la-alcaldia-de-lima-segun-encuesta-de-iep-noticia-1706998', 'publicado', '2026-09-12', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `podcasts`
--

CREATE TABLE `podcasts` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `url_embed` varchar(500) NOT NULL,
  `portada` varchar(255) DEFAULT NULL,
  `estado` enum('borrador','publicado') NOT NULL DEFAULT 'publicado',
  `fecha_publicacion` date NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `podcasts`
--

INSERT INTO `podcasts` (`id`, `titulo`, `url_embed`, `portada`, `estado`, `fecha_publicacion`, `usuario_id`) VALUES
(1, 'Linterna', 'https://www.youtube.com/watch?v=hi3oZSjSfiw&t=10s', 'config/image/podcast-acfd50fea8bf270e.jpg', 'publicado', '2026-09-12', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recuperacion_contrasenas`
--

CREATE TABLE `recuperacion_contrasenas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado_en` datetime DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportajes`
--

CREATE TABLE `reportajes` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `resumen_corto` varchar(500) DEFAULT NULL,
  `desarrollo` longtext NOT NULL,
  `foto_principal` varchar(255) DEFAULT NULL,
  `pdf_adjunto` varchar(255) DEFAULT NULL,
  `estado` enum('borrador','publicado') NOT NULL DEFAULT 'publicado',
  `fecha_publicacion` date NOT NULL,
  `es_destacado` tinyint(1) NOT NULL DEFAULT 0,
  `autor_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reportajes`
--

INSERT INTO `reportajes` (`id`, `titulo`, `resumen_corto`, `desarrollo`, `foto_principal`, `pdf_adjunto`, `estado`, `fecha_publicacion`, `es_destacado`, `autor_id`, `usuario_id`, `created_at`, `updated_at`) VALUES
(1, 'fefef', 'fefefe', '[{\"titulo\":\"fefe\",\"contenido\":\"fefef\"},{\"titulo\":\"fe\",\"contenido\":\"fefe\"}]', 'config/image/reportaje-a87eb24b664e6f31.png', '', 'publicado', '2026-09-12', 1, 1, 1, '2026-09-12 10:03:02', '2026-09-24 02:37:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportajes_fotos`
--

CREATE TABLE `reportajes_fotos` (
  `id` int(10) UNSIGNED NOT NULL,
  `reportaje_id` int(10) UNSIGNED NOT NULL,
  `url_foto` varchar(255) NOT NULL,
  `orden` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `parrafo_despues` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reportajes_fotos`
--

INSERT INTO `reportajes_fotos` (`id`, `reportaje_id`, `url_foto`, `orden`, `parrafo_despues`, `descripcion`) VALUES
(1, 1, 'config/image/reportaje-42dec87d7502d6de.jpg', 1, 0, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `ap_paterno` varchar(100) NOT NULL,
  `ap_materno` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('admin','editor','redactor') NOT NULL DEFAULT 'redactor',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombres`, `ap_paterno`, `ap_materno`, `email`, `password_hash`, `rol`, `created_at`) VALUES
(1, 'Admin', 'DDP', '', 'admin@ddp.pe', '$2y$10$YU0.q4pTTpHyScZpKG9YsOLWv68eNtHQhdIb8QlVm.i1fdADGtw8y', 'admin', '2026-09-12 10:01:55'),
(2, 'Juan', 'Perez', 'Lopez', 'juan@ddp.pe', '$2y$10$w70tnT2/o1Kfss/MVBxTo.5p34g.acRbxncnwIWe.Jq4ucTKVNkm6', 'editor', '2026-09-12 12:53:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videos`
--

CREATE TABLE `videos` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `url_embed` varchar(500) NOT NULL,
  `portada` varchar(255) DEFAULT NULL,
  `estado` enum('borrador','publicado') NOT NULL DEFAULT 'publicado',
  `fecha_publicacion` date NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `videos`
--

INSERT INTO `videos` (`id`, `titulo`, `url_embed`, `portada`, `estado`, `fecha_publicacion`, `usuario_id`) VALUES
(1, 'Linterna', 'https://www.youtube.com/embed/hi3oZSjSfiw', 'config/image/video-0e2246afd3689287.jpg', 'publicado', '2026-09-12', 1),
(2, 'StarWars', 'https://www.youtube.com/embed/3CJUjxfQagA', 'config/image/video-81d50da9fd69717d.jpg', 'publicado', '2026-09-24', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `boletines`
--
ALTER TABLE `boletines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_boletines_numero` (`numero_boletin`),
  ADD KEY `fk_boletines_usuario` (`usuario_id`),
  ADD KEY `idx_boletines_fecha` (`fecha_publicacion`),
  ADD KEY `idx_boletines_estado` (`estado`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_noticias_usuario` (`usuario_id`),
  ADD KEY `idx_noticias_fecha` (`fecha_publicacion`),
  ADD KEY `idx_noticias_estado` (`estado`);

--
-- Indices de la tabla `podcasts`
--
ALTER TABLE `podcasts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_podcasts_usuario` (`usuario_id`),
  ADD KEY `idx_podcasts_fecha` (`fecha_publicacion`),
  ADD KEY `idx_podcasts_estado` (`estado`);

--
-- Indices de la tabla `recuperacion_contrasenas`
--
ALTER TABLE `recuperacion_contrasenas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_recuperacion_token` (`token_hash`),
  ADD KEY `idx_recuperacion_usuario` (`usuario_id`);

--
-- Indices de la tabla `reportajes`
--
ALTER TABLE `reportajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reportajes_autor` (`autor_id`),
  ADD KEY `fk_reportajes_usuario` (`usuario_id`),
  ADD KEY `idx_reportajes_fecha` (`fecha_publicacion`),
  ADD KEY `idx_reportajes_destacado` (`es_destacado`),
  ADD KEY `idx_reportajes_estado` (`estado`);

--
-- Indices de la tabla `reportajes_fotos`
--
ALTER TABLE `reportajes_fotos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reportajes_fotos_reportaje` (`reportaje_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuarios_email` (`email`);

--
-- Indices de la tabla `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_videos_usuario` (`usuario_id`),
  ADD KEY `idx_videos_fecha` (`fecha_publicacion`),
  ADD KEY `idx_videos_estado` (`estado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `autores`
--
ALTER TABLE `autores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `boletines`
--
ALTER TABLE `boletines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `podcasts`
--
ALTER TABLE `podcasts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `recuperacion_contrasenas`
--
ALTER TABLE `recuperacion_contrasenas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportajes`
--
ALTER TABLE `reportajes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `reportajes_fotos`
--
ALTER TABLE `reportajes_fotos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `boletines`
--
ALTER TABLE `boletines`
  ADD CONSTRAINT `fk_boletines_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `fk_noticias_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `podcasts`
--
ALTER TABLE `podcasts`
  ADD CONSTRAINT `fk_podcasts_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `recuperacion_contrasenas`
--
ALTER TABLE `recuperacion_contrasenas`
  ADD CONSTRAINT `fk_recuperacion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `reportajes`
--
ALTER TABLE `reportajes`
  ADD CONSTRAINT `fk_reportajes_autor` FOREIGN KEY (`autor_id`) REFERENCES `autores` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reportajes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `reportajes_fotos`
--
ALTER TABLE `reportajes_fotos`
  ADD CONSTRAINT `fk_reportajes_fotos_reportaje` FOREIGN KEY (`reportaje_id`) REFERENCES `reportajes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `fk_videos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
