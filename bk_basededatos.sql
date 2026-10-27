-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para hospedaje_db
CREATE DATABASE IF NOT EXISTS `hospedaje_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `hospedaje_db`;

-- Volcando estructura para tabla hospedaje_db.cargos_adicionales
CREATE TABLE IF NOT EXISTS `cargos_adicionales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reserva_id` bigint unsigned NOT NULL,
  `factura_id` bigint unsigned DEFAULT NULL,
  `concepto` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` enum('restaurante','minibar','lavanderia','telefono','transporte','tours','spa','otros') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'otros',
  `precio_unitario` decimal(10,2) NOT NULL,
  `cantidad` smallint unsigned NOT NULL DEFAULT '1',
  `subtotal` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cargos_adicionales_reserva_id_foreign` (`reserva_id`),
  KEY `cargos_adicionales_factura_id_foreign` (`factura_id`),
  CONSTRAINT `cargos_adicionales_factura_id_foreign` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cargos_adicionales_reserva_id_foreign` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.cargos_adicionales: ~10 rows (aproximadamente)
DELETE FROM `cargos_adicionales`;
INSERT INTO `cargos_adicionales` (`id`, `reserva_id`, `factura_id`, `concepto`, `categoria`, `precio_unitario`, `cantidad`, `subtotal`, `fecha`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 5, NULL, 'Desayuno buffet x2', 'restaurante', 35.00, 2, 70.00, '2025-12-23', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(2, 5, NULL, 'Minibar (bebidas)', 'minibar', 45.00, 1, 45.00, '2025-12-24', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(3, 6, NULL, 'Lavandería', 'lavanderia', 25.00, 1, 25.00, '2025-12-28', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(4, 10, NULL, 'Cena romántica', 'restaurante', 120.00, 1, 120.00, '2026-02-06', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(5, 10, NULL, 'Spa pareja', 'spa', 180.00, 1, 180.00, '2026-02-07', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(6, 13, NULL, 'Desayuno familiar x4', 'restaurante', 35.00, 4, 140.00, '2026-03-09', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(7, 13, NULL, 'Tour ciudad', 'tours', 80.00, 4, 320.00, '2026-03-10', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(8, 15, NULL, 'Transporte aeropuerto', 'transporte', 60.00, 1, 60.00, '2026-03-28', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(9, 19, NULL, 'Desayuno x2', 'restaurante', 30.00, 2, 60.00, '2026-04-30', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(10, 20, NULL, 'Minibar suite', 'minibar', 85.00, 1, 85.00, '2026-05-01', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36');

-- Volcando estructura para tabla hospedaje_db.configuraciones
CREATE TABLE IF NOT EXISTS `configuraciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `grupo` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `tipo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `descripcion` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `configuraciones_clave_unique` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.configuraciones: ~20 rows (aproximadamente)
DELETE FROM `configuraciones`;
INSERT INTO `configuraciones` (`id`, `clave`, `valor`, `grupo`, `tipo`, `descripcion`, `created_at`, `updated_at`) VALUES
	(1, 'empresa_nombre', 'Mi Hotel', 'empresa', 'text', 'Nombre comercial del hotel', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(2, 'empresa_razon_social', '', 'empresa', 'text', 'Razón social legal', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(3, 'empresa_ruc', '', 'empresa', 'text', 'RUC de la empresa (11 dígitos)', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(4, 'empresa_direccion', '', 'empresa', 'textarea', 'Dirección fiscal del hotel', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(5, 'empresa_telefono', '', 'empresa', 'text', 'Teléfono principal', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(6, 'empresa_email', '', 'empresa', 'text', 'Correo electrónico de contacto', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(7, 'empresa_web', '', 'empresa', 'text', 'Sitio web oficial', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(8, 'empresa_logo', NULL, 'empresa', 'image', 'Logotipo del hotel', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(9, 'empresa_eslogan', '', 'empresa', 'text', 'Eslogan o lema del hotel', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(10, 'facturacion_moneda_simbolo', 'S/', 'facturacion', 'text', 'Símbolo de la moneda (S/, $, €...)', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(11, 'facturacion_moneda_nombre', 'Soles', 'facturacion', 'text', 'Nombre de la moneda', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(12, 'facturacion_igv', '18', 'facturacion', 'number', 'Porcentaje de IGV / impuesto (%)', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(13, 'facturacion_serie_boleta', 'B001', 'facturacion', 'text', 'Serie para boletas de venta', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(14, 'facturacion_serie_factura', 'F001', 'facturacion', 'text', 'Serie para facturas', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(15, 'facturacion_serie_recibo', 'R001', 'facturacion', 'text', 'Serie para recibos de honorarios', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(16, 'facturacion_pie_factura', 'Gracias por su preferencia.', 'facturacion', 'textarea', 'Texto al pie de cada comprobante', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(17, 'sistema_zona_horaria', 'America/Lima', 'sistema', 'select', 'Zona horaria del servidor', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(18, 'sistema_formato_fecha', 'd/m/Y', 'sistema', 'select', 'Formato de visualización de fechas', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(19, 'sistema_color_sidebar', '#1a2035', 'sistema', 'color', 'Color del sidebar de navegación', '2026-05-01 16:09:10', '2026-05-01 16:09:10'),
	(20, 'sistema_color_brand', '#141d2e', 'sistema', 'color', 'Color de la barra de marca', '2026-05-01 16:09:10', '2026-05-01 16:09:10');

-- Volcando estructura para tabla hospedaje_db.facturas
CREATE TABLE IF NOT EXISTS `facturas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reserva_id` bigint unsigned NOT NULL,
  `huesped_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `fecha_emision` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `igv` decimal(10,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','pagada','anulada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `tipo_comprobante` enum('boleta','factura','recibo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'boleta',
  `ruc_cliente` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razon_social` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facturas_numero_unique` (`numero`),
  KEY `facturas_reserva_id_foreign` (`reserva_id`),
  KEY `facturas_huesped_id_foreign` (`huesped_id`),
  KEY `facturas_user_id_foreign` (`user_id`),
  CONSTRAINT `facturas_huesped_id_foreign` FOREIGN KEY (`huesped_id`) REFERENCES `huespedes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `facturas_reserva_id_foreign` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `facturas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.facturas: ~20 rows (aproximadamente)
DELETE FROM `facturas`;
INSERT INTO `facturas` (`id`, `numero`, `reserva_id`, `huesped_id`, `user_id`, `fecha_emision`, `subtotal`, `igv`, `descuento`, `total`, `estado`, `tipo_comprobante`, `ruc_cliente`, `razon_social`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 'FAC-2025-0001', 1, 1, 1, '2025-11-08', 240.00, 43.20, 0.00, 283.20, 'pagada', 'recibo', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(2, 'FAC-2025-0002', 2, 2, 1, '2025-11-19', 640.00, 115.20, 0.00, 755.20, 'pagada', 'boleta', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(3, 'FAC-2025-0003', 3, 3, 1, '2025-12-07', 480.00, 86.40, 0.00, 566.40, 'pagada', 'recibo', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(4, 'FAC-2025-0004', 4, 4, 1, '2025-12-15', 420.00, 75.60, 0.00, 495.60, 'pagada', 'boleta', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(5, 'FAC-2025-0005', 5, 5, 1, '2025-12-27', 1400.00, 252.00, 0.00, 1652.00, 'pagada', 'factura', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(6, 'FAC-2025-0006', 6, 6, 1, '2025-12-30', 420.00, 75.60, 0.00, 495.60, 'pagada', 'factura', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(7, 'FAC-2026-0007', 7, 7, 1, '2026-01-11', 360.00, 64.80, 0.00, 424.80, 'pagada', 'boleta', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(8, 'FAC-2026-0008', 8, 8, 1, '2026-01-22', 640.00, 115.20, 0.00, 755.20, 'pagada', 'factura', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(9, 'FAC-2026-0009', 9, 9, 1, '2026-01-28', 420.00, 75.60, 0.00, 495.60, 'pagada', 'recibo', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(10, 'FAC-2026-0010', 10, 10, 1, '2026-02-09', 1120.00, 201.60, 0.00, 1321.60, 'pagada', 'boleta', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(11, 'FAC-2026-0011', 11, 11, 1, '2026-02-18', 240.00, 43.20, 0.00, 283.20, 'pagada', 'recibo', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(12, 'FAC-2026-0012', 12, 12, 1, '2026-02-25', 360.00, 64.80, 0.00, 424.80, 'pagada', 'boleta', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(13, 'FAC-2026-0013', 13, 13, 1, '2026-03-12', 1280.00, 230.40, 0.00, 1510.40, 'pagada', 'boleta', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(14, 'FAC-2026-0014', 14, 14, 1, '2026-03-23', 420.00, 75.60, 0.00, 495.60, 'pagada', 'recibo', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(15, 'FAC-2026-0015', 15, 15, 1, '2026-04-01', 640.00, 115.20, 0.00, 755.20, 'pagada', 'boleta', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(16, 'FAC-2026-0016', 16, 16, 1, '2026-04-09', 480.00, 86.40, 0.00, 566.40, 'pagada', 'recibo', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(17, 'FAC-2026-0017', 17, 17, 1, '2026-04-22', 560.00, 100.80, 0.00, 660.80, 'pagada', 'recibo', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(18, 'FAC-2026-0018', 18, 18, 1, '2026-05-01', 400.00, 72.00, 0.00, 472.00, 'pagada', 'recibo', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(19, 'FAC-2026-0019', 19, 19, 1, '2026-05-01', 600.00, 108.00, 0.00, 708.00, 'pendiente', 'boleta', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(20, 'FAC-2026-0020', 20, 20, 1, '2026-05-01', 1400.00, 252.00, 0.00, 1652.00, 'pendiente', 'boleta', NULL, NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36');

-- Volcando estructura para tabla hospedaje_db.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.failed_jobs: ~0 rows (aproximadamente)
DELETE FROM `failed_jobs`;

-- Volcando estructura para tabla hospedaje_db.habitaciones
CREATE TABLE IF NOT EXISTS `habitaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `piso` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_habitacion_id` bigint unsigned NOT NULL,
  `estado` enum('disponible','ocupada','mantenimiento','reservada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disponible',
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `imagen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `habitaciones_numero_unique` (`numero`),
  KEY `habitaciones_tipo_habitacion_id_foreign` (`tipo_habitacion_id`),
  CONSTRAINT `habitaciones_tipo_habitacion_id_foreign` FOREIGN KEY (`tipo_habitacion_id`) REFERENCES `tipo_habitaciones` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.habitaciones: ~12 rows (aproximadamente)
DELETE FROM `habitaciones`;
INSERT INTO `habitaciones` (`id`, `numero`, `piso`, `tipo_habitacion_id`, `estado`, `descripcion`, `imagen`, `activa`, `created_at`, `updated_at`) VALUES
	(1, '101', '1', 1, 'disponible', NULL, NULL, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(2, '102', '1', 2, 'ocupada', NULL, NULL, 1, '2026-05-01 08:28:54', '2026-05-01 15:59:36'),
	(3, '103', '1', 3, 'disponible', NULL, NULL, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(4, '104', '1', 1, 'mantenimiento', NULL, NULL, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(5, '105', '1', 2, 'disponible', NULL, NULL, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(6, '201', '2', 3, 'disponible', NULL, NULL, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(7, '202', '2', 4, 'disponible', NULL, NULL, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(8, '203', '2', 2, 'disponible', NULL, NULL, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(9, '204', '2', 3, 'disponible', NULL, NULL, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(10, '301', '3', 5, 'ocupada', NULL, NULL, 1, '2026-05-01 08:28:54', '2026-05-01 15:59:36'),
	(11, '302', '3', 5, 'disponible', NULL, NULL, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(12, '303', '3', 6, 'disponible', NULL, NULL, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54');

-- Volcando estructura para tabla hospedaje_db.huespedes
CREATE TABLE IF NOT EXISTS `huespedes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` enum('DNI','Pasaporte','CE','RUC') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DNI',
  `num_documento` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nacionalidad` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('M','F','Otro') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `huespedes_num_documento_unique` (`num_documento`),
  KEY `huespedes_apellido_nombre_index` (`apellido`,`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.huespedes: ~20 rows (aproximadamente)
DELETE FROM `huespedes`;
INSERT INTO `huespedes` (`id`, `nombre`, `apellido`, `tipo_documento`, `num_documento`, `nacionalidad`, `fecha_nacimiento`, `genero`, `telefono`, `email`, `direccion`, `observaciones`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'Carlos', 'Mendoza García', 'DNI', '43521678', 'Peruana', '1985-03-15', 'M', '987654321', 'cmendoza@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(2, 'María', 'López Torres', 'DNI', '52341890', 'Peruana', '1990-07-22', 'F', '976543210', 'mlopez@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(3, 'José', 'Quispe Mamani', 'DNI', '61234567', 'Peruana', '1978-11-05', 'M', '965432109', 'jquispe@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(4, 'Ana', 'García Flores', 'DNI', '47891234', 'Peruana', '1993-04-18', 'F', '954321098', 'agarcia@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(5, 'Luis', 'Fernández Castro', 'DNI', '55678901', 'Peruana', '1982-09-30', 'M', '943210987', 'lfernandez@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(6, 'Rosa', 'Rodríguez Vargas', 'DNI', '48123456', 'Peruana', '1988-01-12', 'F', '932109876', 'rrodriguez@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(7, 'Jorge', 'Ramírez Silva', 'DNI', '63456789', 'Colombiana', '1975-06-25', 'M', '921098765', 'jramirez@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(8, 'Carmen', 'Gutiérrez Mora', 'DNI', '51234567', 'Peruana', '1995-12-08', 'F', '910987654', 'cgutierrez@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(9, 'Pedro', 'Huanca Ticona', 'DNI', '72345678', 'Boliviana', '1980-02-14', 'M', '909876543', 'phuanca@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(10, 'Elena', 'Morales Díaz', 'DNI', '45678901', 'Peruana', '1987-08-03', 'F', '998765432', 'emorales@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(11, 'Ricardo', 'Santos Peña', 'DNI', '67890123', 'Peruana', '1972-05-20', 'M', '997654321', 'rsantos@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(12, 'Lucía', 'Vega Chávez', 'DNI', '53901234', 'Chilena', '1991-10-17', 'F', '996543210', 'lvega@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(13, 'Miguel', 'Torres Herrera', 'DNI', '44012345', 'Peruana', '1983-03-28', 'M', '995432109', 'mtorres@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(14, 'Patricia', 'Ruiz Cano', 'DNI', '58123456', 'Peruana', '1997-07-09', 'F', '994321098', 'pruiz@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(15, 'Antonio', 'Flores Luna', 'DNI', '71234567', 'Ecuatoriana', '1969-12-01', 'M', '993210987', 'aflores@email.com', NULL, NULL, '2026-05-01 15:59:35', '2026-05-01 15:59:35', NULL),
	(16, 'Sofía', 'Castro Reyes', 'DNI', '46345678', 'Peruana', '1994-04-14', 'F', '992109876', 'scastro@email.com', NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(17, 'Fernando', 'Díaz Alva', 'DNI', '65456789', 'Argentina', '1976-09-11', 'M', '991098765', 'fdiaz@email.com', NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(18, 'Isabel', 'Rojas Paredes', 'DNI', '49567890', 'Peruana', '1989-06-23', 'F', '990987654', 'irojas@email.com', NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(19, 'Roberto', 'Chávez León', 'DNI', '68678901', 'Peruana', '1981-01-07', 'M', '989876543', 'rchavez@email.com', NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(20, 'Daniela', 'Medina Cruz', 'DNI', '57789012', 'Venezolana', '1996-11-30', 'F', '988765432', 'dmedina@email.com', NULL, NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL);

-- Volcando estructura para tabla hospedaje_db.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.migrations: ~14 rows (aproximadamente)
DELETE FROM `migrations`;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2014_10_12_000000_create_users_table', 1),
	(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
	(3, '2014_10_12_100000_create_password_resets_table', 1),
	(4, '2019_08_19_000000_create_failed_jobs_table', 1),
	(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
	(6, '2024_01_01_000001_create_tipo_habitaciones_table', 1),
	(7, '2024_01_01_000002_create_habitaciones_table', 1),
	(8, '2024_01_01_000003_create_huespedes_table', 1),
	(9, '2024_01_01_000004_create_reservas_table', 1),
	(10, '2024_01_01_000005_create_facturas_table', 1),
	(11, '2024_01_01_000006_create_pagos_table', 1),
	(12, '2024_01_01_000007_create_cargos_adicionales_table', 1),
	(13, '2024_01_01_000008_add_role_to_users_table', 2),
	(14, '2024_01_01_000009_create_configuraciones_table', 3);

-- Volcando estructura para tabla hospedaje_db.pagos
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `factura_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` enum('efectivo','tarjeta_credito','tarjeta_debito','transferencia','yape','plin','otro') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'efectivo',
  `referencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_pago` date NOT NULL,
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pagos_factura_id_foreign` (`factura_id`),
  KEY `pagos_user_id_foreign` (`user_id`),
  CONSTRAINT `pagos_factura_id_foreign` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `pagos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.pagos: ~18 rows (aproximadamente)
DELETE FROM `pagos`;
INSERT INTO `pagos` (`id`, `factura_id`, `user_id`, `monto`, `metodo_pago`, `referencia`, `fecha_pago`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 283.20, 'transferencia', 'OP-638009', '2025-11-08', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(2, 2, 1, 755.20, 'efectivo', 'OP-825777', '2025-11-19', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(3, 3, 1, 566.40, 'plin', 'OP-960502', '2025-12-07', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(4, 4, 1, 495.60, 'transferencia', 'OP-335498', '2025-12-15', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(5, 5, 1, 1652.00, 'tarjeta_debito', 'OP-205195', '2025-12-27', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(6, 6, 1, 495.60, 'tarjeta_debito', 'OP-979543', '2025-12-30', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(7, 7, 1, 424.80, 'efectivo', 'OP-340754', '2026-01-11', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(8, 8, 1, 755.20, 'plin', 'OP-880945', '2026-01-22', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(9, 9, 1, 495.60, 'transferencia', 'OP-758422', '2026-01-28', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(10, 10, 1, 1321.60, 'efectivo', 'OP-170197', '2026-02-09', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(11, 11, 1, 283.20, 'efectivo', 'OP-307591', '2026-02-18', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(12, 12, 1, 424.80, 'plin', 'OP-103496', '2026-02-25', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(13, 13, 1, 1510.40, 'tarjeta_debito', 'OP-210959', '2026-03-12', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(14, 14, 1, 495.60, 'transferencia', 'OP-118005', '2026-03-23', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(15, 15, 1, 755.20, 'yape', 'OP-558297', '2026-04-01', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(16, 16, 1, 566.40, 'transferencia', 'OP-977348', '2026-04-09', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(17, 17, 1, 660.80, 'yape', 'OP-234507', '2026-04-22', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36'),
	(18, 18, 1, 472.00, 'transferencia', 'OP-126491', '2026-05-01', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36');

-- Volcando estructura para tabla hospedaje_db.password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.password_resets: ~0 rows (aproximadamente)
DELETE FROM `password_resets`;

-- Volcando estructura para tabla hospedaje_db.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.password_reset_tokens: ~0 rows (aproximadamente)
DELETE FROM `password_reset_tokens`;

-- Volcando estructura para tabla hospedaje_db.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.personal_access_tokens: ~0 rows (aproximadamente)
DELETE FROM `personal_access_tokens`;

-- Volcando estructura para tabla hospedaje_db.reservas
CREATE TABLE IF NOT EXISTS `reservas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `huesped_id` bigint unsigned NOT NULL,
  `habitacion_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `fecha_entrada` date NOT NULL,
  `fecha_salida` date NOT NULL,
  `fecha_checkin` date DEFAULT NULL,
  `fecha_checkout` date DEFAULT NULL,
  `num_personas` smallint unsigned NOT NULL DEFAULT '1',
  `estado` enum('pendiente','confirmada','checkin','checkout','cancelada','no_show') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `precio_noche` decimal(10,2) NOT NULL,
  `num_noches` smallint unsigned NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `origen` enum('web','telefono','presencial','agencia') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'presencial',
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservas_codigo_unique` (`codigo`),
  KEY `reservas_huesped_id_foreign` (`huesped_id`),
  KEY `reservas_habitacion_id_foreign` (`habitacion_id`),
  KEY `reservas_user_id_foreign` (`user_id`),
  CONSTRAINT `reservas_habitacion_id_foreign` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `reservas_huesped_id_foreign` FOREIGN KEY (`huesped_id`) REFERENCES `huespedes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `reservas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.reservas: ~20 rows (aproximadamente)
DELETE FROM `reservas`;
INSERT INTO `reservas` (`id`, `codigo`, `huesped_id`, `habitacion_id`, `user_id`, `fecha_entrada`, `fecha_salida`, `fecha_checkin`, `fecha_checkout`, `num_personas`, `estado`, `precio_noche`, `num_noches`, `subtotal`, `descuento`, `total`, `origen`, `observaciones`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'RES-2025-0001', 1, 1, 1, '2025-11-05', '2025-11-08', '2025-11-05', '2025-11-08', 1, 'checkout', 80.00, 3, 240.00, 0.00, 240.00, 'presencial', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(2, 'RES-2025-0002', 2, 7, 1, '2025-11-15', '2025-11-19', '2025-11-15', '2025-11-19', 3, 'checkout', 160.00, 4, 640.00, 0.00, 640.00, 'telefono', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(3, 'RES-2025-0003', 3, 5, 1, '2025-12-03', '2025-12-07', '2025-12-03', '2025-12-07', 2, 'checkout', 120.00, 4, 480.00, 0.00, 480.00, 'agencia', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(4, 'RES-2025-0004', 4, 6, 1, '2025-12-12', '2025-12-15', '2025-12-12', '2025-12-15', 2, 'checkout', 140.00, 3, 420.00, 0.00, 420.00, 'presencial', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(5, 'RES-2025-0005', 5, 10, 1, '2025-12-22', '2025-12-27', '2025-12-22', '2025-12-27', 2, 'checkout', 280.00, 5, 1400.00, 0.00, 1400.00, 'web', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(6, 'RES-2025-0006', 6, 3, 1, '2025-12-27', '2025-12-30', '2025-12-27', '2025-12-30', 2, 'checkout', 140.00, 3, 420.00, 0.00, 420.00, 'telefono', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(7, 'RES-2026-0007', 7, 8, 1, '2026-01-08', '2026-01-11', '2026-01-08', '2026-01-11', 2, 'checkout', 120.00, 3, 360.00, 0.00, 360.00, 'presencial', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(8, 'RES-2026-0008', 8, 7, 1, '2026-01-18', '2026-01-22', '2026-01-18', '2026-01-22', 3, 'checkout', 160.00, 4, 640.00, 0.00, 640.00, 'agencia', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(9, 'RES-2026-0009', 9, 9, 1, '2026-01-25', '2026-01-28', '2026-01-25', '2026-01-28', 2, 'checkout', 140.00, 3, 420.00, 0.00, 420.00, 'web', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(10, 'RES-2026-0010', 10, 11, 1, '2026-02-05', '2026-02-09', '2026-02-05', '2026-02-09', 2, 'checkout', 280.00, 4, 1120.00, 0.00, 1120.00, 'agencia', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(11, 'RES-2026-0011', 11, 1, 1, '2026-02-15', '2026-02-18', '2026-02-15', '2026-02-18', 1, 'checkout', 80.00, 3, 240.00, 0.00, 240.00, 'presencial', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(12, 'RES-2026-0012', 12, 5, 1, '2026-02-22', '2026-02-25', '2026-02-22', '2026-02-25', 2, 'checkout', 120.00, 3, 360.00, 0.00, 360.00, 'telefono', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(13, 'RES-2026-0013', 13, 12, 1, '2026-03-08', '2026-03-12', '2026-03-08', '2026-03-12', 4, 'checkout', 320.00, 4, 1280.00, 0.00, 1280.00, 'web', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(14, 'RES-2026-0014', 14, 6, 1, '2026-03-20', '2026-03-23', '2026-03-20', '2026-03-23', 2, 'checkout', 140.00, 3, 420.00, 0.00, 420.00, 'presencial', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(15, 'RES-2026-0015', 15, 7, 1, '2026-03-28', '2026-04-01', '2026-03-28', '2026-04-01', 3, 'checkout', 160.00, 4, 640.00, 0.00, 640.00, 'agencia', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(16, 'RES-2026-0016', 16, 8, 1, '2026-04-05', '2026-04-09', '2026-04-05', '2026-04-09', 2, 'checkout', 120.00, 4, 480.00, 0.00, 480.00, 'telefono', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(17, 'RES-2026-0017', 17, 9, 1, '2026-04-18', '2026-04-22', '2026-04-18', '2026-04-22', 2, 'checkout', 140.00, 4, 560.00, 0.00, 560.00, 'web', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(18, 'RES-2026-0018', 18, 1, 1, '2026-04-26', '2026-05-01', '2026-04-26', '2026-05-01', 1, 'checkout', 80.00, 5, 400.00, 0.00, 400.00, 'presencial', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(19, 'RES-2026-0019', 19, 2, 1, '2026-04-29', '2026-05-04', '2026-04-29', NULL, 2, 'checkin', 120.00, 5, 600.00, 0.00, 600.00, 'presencial', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL),
	(20, 'RES-2026-0020', 20, 10, 1, '2026-05-01', '2026-05-06', '2026-05-01', NULL, 2, 'checkin', 280.00, 5, 1400.00, 0.00, 1400.00, 'web', NULL, '2026-05-01 15:59:36', '2026-05-01 15:59:36', NULL);

-- Volcando estructura para tabla hospedaje_db.tipo_habitaciones
CREATE TABLE IF NOT EXISTS `tipo_habitaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `capacidad` tinyint unsigned NOT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.tipo_habitaciones: ~12 rows (aproximadamente)
DELETE FROM `tipo_habitaciones`;
INSERT INTO `tipo_habitaciones` (`id`, `nombre`, `descripcion`, `capacidad`, `precio_base`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'Simple', 'Habitación con cama individual.', 1, 80.00, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(2, 'Doble', 'Habitación con dos camas individuales.', 2, 120.00, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(3, 'Matrimonial', 'Habitación con cama matrimonial.', 2, 140.00, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(4, 'Triple', 'Habitación con tres camas.', 3, 160.00, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(5, 'Suite', 'Suite de lujo con sala y jacuzzi.', 2, 280.00, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(6, 'Suite Familiar', 'Suite amplia para familias.', 5, 320.00, 1, '2026-05-01 08:28:54', '2026-05-01 08:28:54'),
	(7, 'Simple', 'Habitación con cama individual.', 1, 80.00, 1, '2026-05-01 15:47:05', '2026-05-01 15:47:05'),
	(8, 'Doble', 'Habitación con dos camas individuales.', 2, 120.00, 1, '2026-05-01 15:47:05', '2026-05-01 15:47:05'),
	(9, 'Matrimonial', 'Habitación con cama matrimonial.', 2, 140.00, 1, '2026-05-01 15:47:05', '2026-05-01 15:47:05'),
	(10, 'Triple', 'Habitación con tres camas.', 3, 160.00, 1, '2026-05-01 15:47:05', '2026-05-01 15:47:05'),
	(11, 'Suite', 'Suite de lujo con sala y jacuzzi.', 2, 280.00, 1, '2026-05-01 15:47:05', '2026-05-01 15:47:05'),
	(12, 'Suite Familiar', 'Suite amplia para familias.', 5, 320.00, 1, '2026-05-01 15:47:05', '2026-05-01 15:47:05');

-- Volcando estructura para tabla hospedaje_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','recepcionista','supervisor') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recepcionista',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla hospedaje_db.users: ~2 rows (aproximadamente)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `name`, `email`, `role`, `activo`, `telefono`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'Administrador', 'admin@hospedaje.com', 'admin', 1, NULL, NULL, '$2y$12$M8U5TEgXc6UapY.zI1fID.vYacoFG1MhfXDHKKqGgMNBGP2CycI22', '7soyWajwkoRqKieqmOWB59LUn5f4ynBzhfoNiSpNd34i543ILykjVcGzYiEA', '2026-05-01 08:28:54', '2026-05-01 16:16:15'),
	(2, 'Recepcionista', 'recepcion@hospedaje.com', 'recepcionista', 1, NULL, NULL, '$2y$12$VxUWuu.ttQ3EPM5vyGiBn.GlGDxi2x9rnzQ0Fmi/SZGivyLk2C/Sq', NULL, '2026-05-01 08:28:54', '2026-05-01 08:28:54');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
