-- ============================================================================
-- SOLAR SYSTEM BUILDER — Database Migration
-- SolarPower Energy Corporation
-- Run this AFTER the main solar_power.sql schema is loaded.
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+08:00";

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. NEW TABLES
-- ─────────────────────────────────────────────────────────────────────────────

-- Product specifications (key-value per product)
CREATE TABLE IF NOT EXISTS `product_specifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `spec_key` VARCHAR(50) NOT NULL,
  `spec_value` VARCHAR(255) NOT NULL,
  `spec_unit` VARCHAR(20) DEFAULT NULL,
  `display_order` INT DEFAULT 0,
  INDEX `idx_prod_spec` (`product_id`, `spec_key`),
  CONSTRAINT `fk_spec_product` FOREIGN KEY (`product_id`) REFERENCES `product`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Builder peripherals / add-on services
CREATE TABLE IF NOT EXISTS `solar_builder_peripherals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `icon` VARCHAR(10) DEFAULT '⚙️',
  `is_active` TINYINT(1) DEFAULT 1,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Saved builds
CREATE TABLE IF NOT EXISTS `solar_builds` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `build_reference` VARCHAR(50) NOT NULL UNIQUE,
  `session_id` VARCHAR(128) DEFAULT NULL,
  `customer_name` VARCHAR(255) DEFAULT NULL,
  `customer_email` VARCHAR(255) DEFAULT NULL,
  `customer_phone` VARCHAR(50) DEFAULT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `status` ENUM('draft','submitted','quoted','approved','completed') DEFAULT 'draft',
  `performance_data` TEXT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Build line items (components)
CREATE TABLE IF NOT EXISTS `solar_build_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `build_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `category_slug` VARCHAR(50) NOT NULL,
  `quantity` INT DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  CONSTRAINT `fk_builditem_build` FOREIGN KEY (`build_id`) REFERENCES `solar_builds`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Build peripheral selections
CREATE TABLE IF NOT EXISTS `solar_build_peripherals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `build_id` INT NOT NULL,
  `peripheral_id` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  CONSTRAINT `fk_buildperiph_build` FOREIGN KEY (`build_id`) REFERENCES `solar_builds`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_buildperiph_periph` FOREIGN KEY (`peripheral_id`) REFERENCES `solar_builder_peripherals`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ─────────────────────────────────────────────────────────────────────────────
-- 2. ADD NEW CATEGORIES (if not already present)
-- ─────────────────────────────────────────────────────────────────────────────

INSERT IGNORE INTO `categories` (`category_id`, `category_name`) VALUES
(6, 'Wiring & Protection'),
(7, 'Monitoring System');


-- ─────────────────────────────────────────────────────────────────────────────
-- 3. ADD NEW BRANDS
-- ─────────────────────────────────────────────────────────────────────────────

INSERT IGNORE INTO `brands` (`brand_id`, `brand_name`, `category_id`) VALUES
(20, 'Schneider Electric', 6),
(21, 'ABB', 6),
(22, 'Generic Solar', 6),
(23, 'IronRidge', 4),
(24, 'Unirac', 4),
(25, 'K2 Systems', 4),
(26, 'Schletter', 4),
(27, 'Renusol', 4),
(28, 'Victron Energy', 7),
(29, 'Fronius', 7),
(30, 'SolarEdge', 7);


-- ─────────────────────────────────────────────────────────────────────────────
-- 4. ADD NEW PRODUCTS — Complete Mounting Systems
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `product` (`id`, `displayName`, `brandName`, `price`, `category`, `stockQuantity`, `warranty`, `description`, `imagePath`, `postedByStaffId`) VALUES
(500, 'Complete Roof Mount Kit – Standard (Up to 15 Panels)', 'IronRidge', 18500.00, 'Mounting & Accessories', 200, '10 years', 'Complete aluminum rail mounting system for pitched roofs. Includes rails, L-feet brackets, mid clamps, end clamps, and all hardware for up to 15 panels. Anodized aluminum construction with stainless steel fasteners. Wind rated to 200 km/h.', 'path/to/uploaded/image.jpg', NULL),
(501, 'Complete Roof Mount Kit – Large (Up to 25 Panels)', 'IronRidge', 28000.00, 'Mounting & Accessories', 150, '10 years', 'Heavy-duty aluminum rail mounting system for pitched roofs. Full kit for up to 25 panels including extended rails, reinforced L-feet, all clamps and hardware. Suitable for residential and light commercial installations.', 'path/to/uploaded/image.jpg', NULL),
(502, 'Ground Mount Array System (Up to 20 Panels)', 'Unirac', 32000.00, 'Mounting & Accessories', 100, '15 years', 'Industrial galvanized steel ground mount array. Adjustable tilt angle 10°–45°. Complete foundation kit with concrete pier mounts, cross-rails, and all hardware for up to 20 panels. Hot-dip galvanized for 25+ year outdoor life.', 'path/to/uploaded/image.jpg', NULL),
(503, 'Flat Roof Ballast Mount System (Up to 15 Panels)', 'Renusol', 22000.00, 'Mounting & Accessories', 120, '10 years', 'No-penetration ballasted mounting system for flat roofs. High-density polyethylene trays with 10° fixed tilt. No drilling required — weighted with ballast blocks. Includes all trays, connectors, and wind deflectors.', 'path/to/uploaded/image.jpg', NULL),
(504, 'Carport Solar Mount Structure (Up to 20 Panels)', 'Schletter', 45000.00, 'Mounting & Accessories', 50, '20 years', 'Premium dual-purpose carport + solar mount. Galvanized steel with powder coating. Covers one standard parking bay. Includes all structural members, panel rails, and ground anchors. Engineered for typhoon-prone regions.', 'path/to/uploaded/image.jpg', NULL),
(505, 'Heavy Duty Ground Mount (Up to 40 Panels)', 'K2 Systems', 58000.00, 'Mounting & Accessories', 40, '20 years', 'Commercial-grade ground mount system. Galvanized steel with adjustable tilt. Dual-row design for up to 40 panels. Includes screw pile foundations, all structural members, cable management trays.', 'path/to/uploaded/image.jpg', NULL);


-- ─────────────────────────────────────────────────────────────────────────────
-- 5. ADD NEW PRODUCTS — Wiring & Protection Kits
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `product` (`id`, `displayName`, `brandName`, `price`, `category`, `stockQuantity`, `warranty`, `description`, `imagePath`, `postedByStaffId`) VALUES
(510, 'Basic DC/AC Wiring & Protection Kit – Up to 5kW', 'Generic Solar', 8500.00, 'Wiring & Protection', 300, '2 years', 'Complete wiring and protection package for systems up to 5kW. Includes: 30m 4mm² PV DC cable (red+black), MC4 connector pairs, DC isolator switch, AC circuit breaker 32A, basic surge protector (SPD Type II), PVC conduit, cable ties and glands.', 'path/to/uploaded/image.jpg', NULL),
(511, 'Standard DC/AC Protection Kit – Up to 10kW', 'Schneider Electric', 15000.00, 'Wiring & Protection', 250, '5 years', 'Professional-grade protection for systems up to 10kW. Includes: 50m 6mm² PV DC cable, premium MC4 connectors, DC combiner box (2-string), DC isolator 1000V, AC RCCB 63A, MCB 40A, SPD Type I+II, DIN rail enclosure IP65, labeling kit.', 'path/to/uploaded/image.jpg', NULL),
(512, 'Premium Protection Bundle – Up to 15kW', 'ABB', 25000.00, 'Wiring & Protection', 150, '5 years', 'Industrial-quality protection for up to 15kW systems. Includes: 80m 6mm² PV DC cable, Stäubli MC4 connectors, 4-string DC combiner with fuses, dual DC isolator, AC RCCB 80A, MCB 63A, SPD Type I+II (DC + AC), IP65 enclosure, earth rod kit, full cable management.', 'path/to/uploaded/image.jpg', NULL),
(513, 'Industrial Grade Protection Kit – 20kW+ Systems', 'Schneider Electric', 38000.00, 'Wiring & Protection', 80, '5 years', 'Heavy-duty protection for large residential and commercial systems 20kW+. Includes: 120m 10mm² PV DC cable, professional MC4 sets, 6-string DC combiner with monitoring, dual DC isolators 1000V, 3-phase AC distribution board, RCCB 100A, MCBs, dual SPD arrays, full earthing system, cable trays, labeling and documentation.', 'path/to/uploaded/image.jpg', NULL);


-- ─────────────────────────────────────────────────────────────────────────────
-- 6. ADD NEW PRODUCTS — Monitoring Systems
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `product` (`id`, `displayName`, `brandName`, `price`, `category`, `stockQuantity`, `warranty`, `description`, `imagePath`, `postedByStaffId`) VALUES
(520, 'WiFi Smart Monitor – Basic', 'Growatt', 3500.00, 'Monitoring System', 400, '2 years', 'Basic WiFi monitoring dongle. Plug-and-play with compatible inverters. Real-time generation tracking via Growatt ShinePhone app. Daily/monthly/yearly statistics. Push notifications for faults. Cloud data storage.', 'path/to/uploaded/image.jpg', NULL),
(521, 'WiFi + Cloud Monitor – Standard', 'Growatt', 8500.00, 'Monitoring System', 300, '3 years', 'Advanced WiFi monitoring with external current sensors. Works with any inverter brand. Real-time and historical data on the ShineServer cloud platform. Export reports (PDF/CSV). Multiple plant management. Email alerts for anomalies.', 'path/to/uploaded/image.jpg', NULL),
(522, 'Professional Monitoring System with Display', 'Victron Energy', 15000.00, 'Monitoring System', 100, '5 years', 'Victron GX Touch 50 — 5-inch color touchscreen display + VRM cloud portal. Shows real-time solar, battery, grid, and load flows. Historical graphing. Remote firmware updates. MQTT/Modbus integration. Tank level and temperature inputs. Built-in WiFi + Ethernet.', 'path/to/uploaded/image.jpg', NULL),
(523, 'Enterprise Monitoring Suite with Energy Meter', 'SolarEdge', 22000.00, 'Monitoring System', 60, '5 years', 'Full SolarEdge monitoring ecosystem. Module-level monitoring with power optimizers. Smart energy meter (import/export). SolarEdge cloud portal with API access. Revenue-grade metering. Free lifetime cloud monitoring. Ideal for net metering documentation.', 'path/to/uploaded/image.jpg', NULL),
(524, 'Smart Monitoring Kit with Weather Station', 'Fronius', 18500.00, 'Monitoring System', 80, '5 years', 'Fronius Solar.web PRO monitoring package. Includes Fronius Smart Meter, WiFi card, and optional weather station sensor. Compare actual vs. expected yield. Automatic fault detection. API access for third-party integration. Free Fronius Solar.web portal.', 'path/to/uploaded/image.jpg', NULL);


-- ─────────────────────────────────────────────────────────────────────────────
-- 7. PRODUCT SPECIFICATIONS — Existing Panels
-- ─────────────────────────────────────────────────────────────────────────────

-- ID 91: Lvtopsun 550W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(91, 'wattage', '550', 'W', 1),
(91, 'efficiency', '21.3', '%', 2),
(91, 'voltage_voc', '49.8', 'V', 3),
(91, 'current_isc', '13.23', 'A', 4),
(91, 'panel_type', 'Bifacial', NULL, 5),
(91, 'cell_type', 'PERC Half-Cut', NULL, 6),
(91, 'builder_score', '55', NULL, 99);

-- ID 95: Lvtopsun 580W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(95, 'wattage', '580', 'W', 1),
(95, 'efficiency', '22.5', '%', 2),
(95, 'voltage_voc', '51.5', 'V', 3),
(95, 'current_isc', '13.8', 'A', 4),
(95, 'panel_type', 'Bifacial', NULL, 5),
(95, 'cell_type', 'N-Type TOPCon', NULL, 6),
(95, 'builder_score', '62', NULL, 99);

-- ID 98: Aiko 645W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(98, 'wattage', '645', 'W', 1),
(98, 'efficiency', '23.6', '%', 2),
(98, 'voltage_voc', '54.2', 'V', 3),
(98, 'current_isc', '14.9', 'A', 4),
(98, 'panel_type', 'Bifacial', NULL, 5),
(98, 'cell_type', 'N-Type ABC', NULL, 6),
(98, 'builder_score', '88', NULL, 99);

-- ID 99: Aiko 650W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(99, 'wattage', '650', 'W', 1),
(99, 'efficiency', '23.8', '%', 2),
(99, 'voltage_voc', '54.5', 'V', 3),
(99, 'current_isc', '15.0', 'A', 4),
(99, 'panel_type', 'Bifacial', NULL, 5),
(99, 'cell_type', 'N-Type ABC', NULL, 6),
(99, 'builder_score', '90', NULL, 99);

-- ID 102: Aiko 635W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(102, 'wattage', '635', 'W', 1),
(102, 'efficiency', '23.2', '%', 2),
(102, 'voltage_voc', '53.8', 'V', 3),
(102, 'current_isc', '14.7', 'A', 4),
(102, 'panel_type', 'Bifacial', NULL, 5),
(102, 'cell_type', 'N-Type ABC', NULL, 6),
(102, 'builder_score', '85', NULL, 99);

-- ID 103: Trina Solar 615W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(103, 'wattage', '615', 'W', 1),
(103, 'efficiency', '22.9', '%', 2),
(103, 'voltage_voc', '52.8', 'V', 3),
(103, 'current_isc', '14.5', 'A', 4),
(103, 'panel_type', 'Bifacial', NULL, 5),
(103, 'cell_type', 'N-Type i-TOPCon', NULL, 6),
(103, 'builder_score', '80', NULL, 99);

-- ID 104: Trina Solar 705W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(104, 'wattage', '705', 'W', 1),
(104, 'efficiency', '22.7', '%', 2),
(104, 'voltage_voc', '55.8', 'V', 3),
(104, 'current_isc', '15.6', 'A', 4),
(104, 'panel_type', 'Bifacial', NULL, 5),
(104, 'cell_type', 'N-Type i-TOPCon', NULL, 6),
(104, 'builder_score', '95', NULL, 99);

-- ID 108: AE Solar 580W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(108, 'wattage', '580', 'W', 1),
(108, 'efficiency', '22.0', '%', 2),
(108, 'voltage_voc', '51.0', 'V', 3),
(108, 'current_isc', '13.7', 'A', 4),
(108, 'panel_type', 'Bifacial', NULL, 5),
(108, 'cell_type', 'N-Type TOPCon', NULL, 6),
(108, 'builder_score', '60', NULL, 99);

-- ID 109: Jinko Solar 590W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(109, 'wattage', '590', 'W', 1),
(109, 'efficiency', '22.84', '%', 2),
(109, 'voltage_voc', '52.0', 'V', 3),
(109, 'current_isc', '14.0', 'A', 4),
(109, 'panel_type', 'Mono-facial', NULL, 5),
(109, 'cell_type', 'N-Type TOPCon HOT 3.0', NULL, 6),
(109, 'builder_score', '68', NULL, 99);

-- ID 118: Nuuko 580W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(118, 'wattage', '580', 'W', 1),
(118, 'efficiency', '22.6', '%', 2),
(118, 'voltage_voc', '51.2', 'V', 3),
(118, 'current_isc', '13.9', 'A', 4),
(118, 'panel_type', 'Bifacial', NULL, 5),
(118, 'cell_type', 'N-Type TOPCon', NULL, 6),
(118, 'builder_score', '63', NULL, 99);

-- ID 147: Jinko Solar 625W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(147, 'wattage', '625', 'W', 1),
(147, 'efficiency', '23.2', '%', 2),
(147, 'voltage_voc', '53.5', 'V', 3),
(147, 'current_isc', '14.8', 'A', 4),
(147, 'panel_type', 'Bifacial', NULL, 5),
(147, 'cell_type', 'N-Type TOPCon', NULL, 6),
(147, 'builder_score', '82', NULL, 99);

-- ID 148: Jinko Solar 630W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(148, 'wattage', '630', 'W', 1),
(148, 'efficiency', '23.4', '%', 2),
(148, 'voltage_voc', '53.8', 'V', 3),
(148, 'current_isc', '14.9', 'A', 4),
(148, 'panel_type', 'Bifacial', NULL, 5),
(148, 'cell_type', 'N-Type TOPCon', NULL, 6),
(148, 'builder_score', '84', NULL, 99);

-- ID 150: Nuuko 650W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(150, 'wattage', '650', 'W', 1),
(150, 'efficiency', '23.0', '%', 2),
(150, 'voltage_voc', '54.0', 'V', 3),
(150, 'current_isc', '15.1', 'A', 4),
(150, 'panel_type', 'Bifacial', NULL, 5),
(150, 'cell_type', 'N-Type TOPCon', NULL, 6),
(150, 'builder_score', '78', NULL, 99);

-- ID 153: Austra 650W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(153, 'wattage', '650', 'W', 1),
(153, 'efficiency', '22.5', '%', 2),
(153, 'voltage_voc', '53.5', 'V', 3),
(153, 'current_isc', '15.2', 'A', 4),
(153, 'panel_type', 'Bifacial', NULL, 5),
(153, 'cell_type', 'N-Type TOPCon', NULL, 6),
(153, 'builder_score', '76', NULL, 99);

-- ID 90: AE SOLAR 580W
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(90, 'wattage', '580', 'W', 1),
(90, 'efficiency', '21.3', '%', 2),
(90, 'voltage_voc', '49.8', 'V', 3),
(90, 'current_isc', '13.23', 'A', 4),
(90, 'panel_type', 'Bifacial', NULL, 5),
(90, 'cell_type', 'PERC Half-Cut', NULL, 6),
(90, 'builder_score', '55', NULL, 99);

-- ID 103: Trina Solar 615W (already inserted above)
-- ID 104: Trina Solar 705W (already inserted above)


-- ─────────────────────────────────────────────────────────────────────────────
-- 8. PRODUCT SPECIFICATIONS — Existing Inverters
-- ─────────────────────────────────────────────────────────────────────────────

-- ID 71: Deye GTI-SinglePhase 10kW
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(71, 'rated_power_kw', '10', 'kW', 1),
(71, 'inverter_type', 'Grid-Tie', NULL, 2),
(71, 'phase', 'Single', NULL, 3),
(71, 'efficiency', '97.5', '%', 4),
(71, 'mppt_count', '2', NULL, 5),
(71, 'max_pv_input_kw', '13', 'kW', 6),
(71, 'battery_voltage_min', '0', 'V', 7),
(71, 'battery_voltage_max', '0', 'V', 8),
(71, 'builder_score', '75', NULL, 99);

-- ID 79: LuxPower 6kW Hybrid
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(79, 'rated_power_kw', '6', 'kW', 1),
(79, 'inverter_type', 'Hybrid', NULL, 2),
(79, 'phase', 'Single', NULL, 3),
(79, 'efficiency', '97.0', '%', 4),
(79, 'mppt_count', '2', NULL, 5),
(79, 'max_pv_input_kw', '8', 'kW', 6),
(79, 'battery_voltage_min', '40', 'V', 7),
(79, 'battery_voltage_max', '60', 'V', 8),
(79, 'builder_score', '50', NULL, 99);

-- ID 119: Solax 6kW X1-HYB-6.0-LV
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(119, 'rated_power_kw', '6', 'kW', 1),
(119, 'inverter_type', 'Hybrid', NULL, 2),
(119, 'phase', 'Single', NULL, 3),
(119, 'efficiency', '97.8', '%', 4),
(119, 'mppt_count', '2', NULL, 5),
(119, 'max_pv_input_kw', '9', 'kW', 6),
(119, 'battery_voltage_min', '40', 'V', 7),
(119, 'battery_voltage_max', '60', 'V', 8),
(119, 'builder_score', '68', NULL, 99);

-- ID 120: Solax 5kW X1-HYB-5.0-LV
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(120, 'rated_power_kw', '5', 'kW', 1),
(120, 'inverter_type', 'Hybrid', NULL, 2),
(120, 'phase', 'Single', NULL, 3),
(120, 'efficiency', '97.6', '%', 4),
(120, 'mppt_count', '2', NULL, 5),
(120, 'max_pv_input_kw', '7.5', 'kW', 6),
(120, 'battery_voltage_min', '40', 'V', 7),
(120, 'battery_voltage_max', '60', 'V', 8),
(120, 'builder_score', '52', NULL, 99);

-- ID 135: SRNE 6kW Hybrid
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(135, 'rated_power_kw', '6', 'kW', 1),
(135, 'inverter_type', 'Hybrid', NULL, 2),
(135, 'phase', 'Single', NULL, 3),
(135, 'efficiency', '97.0', '%', 4),
(135, 'mppt_count', '1', NULL, 5),
(135, 'max_pv_input_kw', '8', 'kW', 6),
(135, 'battery_voltage_min', '40', 'V', 7),
(135, 'battery_voltage_max', '60', 'V', 8),
(135, 'builder_score', '55', NULL, 99);

-- ID 136: SRNE 8kW Hybrid
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(136, 'rated_power_kw', '8', 'kW', 1),
(136, 'inverter_type', 'Hybrid', NULL, 2),
(136, 'phase', 'Single', NULL, 3),
(136, 'efficiency', '97.2', '%', 4),
(136, 'mppt_count', '2', NULL, 5),
(136, 'max_pv_input_kw', '11', 'kW', 6),
(136, 'battery_voltage_min', '40', 'V', 7),
(136, 'battery_voltage_max', '60', 'V', 8),
(136, 'builder_score', '65', NULL, 99);

-- ID 138: SRNE 12kW Hybrid
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(138, 'rated_power_kw', '12', 'kW', 1),
(138, 'inverter_type', 'Hybrid', NULL, 2),
(138, 'phase', 'Single', NULL, 3),
(138, 'efficiency', '97.5', '%', 4),
(138, 'mppt_count', '2', NULL, 5),
(138, 'max_pv_input_kw', '16', 'kW', 6),
(138, 'battery_voltage_min', '40', 'V', 7),
(138, 'battery_voltage_max', '60', 'V', 8),
(138, 'builder_score', '80', NULL, 99);

-- ID 140: LuxPower SNA-6K
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(140, 'rated_power_kw', '6', 'kW', 1),
(140, 'inverter_type', 'Hybrid', NULL, 2),
(140, 'phase', 'Single', NULL, 3),
(140, 'efficiency', '96.5', '%', 4),
(140, 'mppt_count', '1', NULL, 5),
(140, 'max_pv_input_kw', '8', 'kW', 6),
(140, 'battery_voltage_min', '40', 'V', 7),
(140, 'battery_voltage_max', '60', 'V', 8),
(140, 'builder_score', '52', NULL, 99);

-- ID 142: LuxPower SNA-14K
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(142, 'rated_power_kw', '14', 'kW', 1),
(142, 'inverter_type', 'Hybrid', NULL, 2),
(142, 'phase', 'Single', NULL, 3),
(142, 'efficiency', '97.0', '%', 4),
(142, 'mppt_count', '2', NULL, 5),
(142, 'max_pv_input_kw', '24', 'kW', 6),
(142, 'battery_voltage_min', '40', 'V', 7),
(142, 'battery_voltage_max', '60', 'V', 8),
(142, 'builder_score', '88', NULL, 99);

-- ID 143: Solax X1-Lite-10.0-LV
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(143, 'rated_power_kw', '10', 'kW', 1),
(143, 'inverter_type', 'Hybrid', NULL, 2),
(143, 'phase', 'Single', NULL, 3),
(143, 'efficiency', '97.8', '%', 4),
(143, 'mppt_count', '2', NULL, 5),
(143, 'max_pv_input_kw', '15', 'kW', 6),
(143, 'battery_voltage_min', '40', 'V', 7),
(143, 'battery_voltage_max', '60', 'V', 8),
(143, 'builder_score', '78', NULL, 99);

-- ID 144: Solax X1-Lite-12.0-LV
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(144, 'rated_power_kw', '12', 'kW', 1),
(144, 'inverter_type', 'Hybrid', NULL, 2),
(144, 'phase', 'Single', NULL, 3),
(144, 'efficiency', '97.9', '%', 4),
(144, 'mppt_count', '3', NULL, 5),
(144, 'max_pv_input_kw', '18', 'kW', 6),
(144, 'battery_voltage_min', '40', 'V', 7),
(144, 'battery_voltage_max', '60', 'V', 8),
(144, 'builder_score', '85', NULL, 99);

-- ID 145: Solax X3-NEO-15K-LV (3-Phase)
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(145, 'rated_power_kw', '15', 'kW', 1),
(145, 'inverter_type', 'Hybrid', NULL, 2),
(145, 'phase', 'Three', NULL, 3),
(145, 'efficiency', '98.0', '%', 4),
(145, 'mppt_count', '3', NULL, 5),
(145, 'max_pv_input_kw', '22', 'kW', 6),
(145, 'battery_voltage_min', '40', 'V', 7),
(145, 'battery_voltage_max', '60', 'V', 8),
(145, 'builder_score', '95', NULL, 99);


-- ─────────────────────────────────────────────────────────────────────────────
-- 9. PRODUCT SPECIFICATIONS — Existing Batteries
-- ─────────────────────────────────────────────────────────────────────────────

-- ID 73: Hoymiles LB-5D-G2 5.12kWh
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(73, 'capacity_kwh', '5.12', 'kWh', 1),
(73, 'voltage', '51.2', 'V', 2),
(73, 'ampere_hour', '100', 'Ah', 3),
(73, 'chemistry', 'LiFePO4', NULL, 4),
(73, 'cycle_life', '6000', 'cycles', 5),
(73, 'dod', '90', '%', 6),
(73, 'builder_score', '55', NULL, 99);

-- ID 128: SRNE 10.24kWh 200Ah
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(128, 'capacity_kwh', '10.24', 'kWh', 1),
(128, 'voltage', '51.2', 'V', 2),
(128, 'ampere_hour', '200', 'Ah', 3),
(128, 'chemistry', 'LiFePO4', NULL, 4),
(128, 'cycle_life', '6000', 'cycles', 5),
(128, 'dod', '90', '%', 6),
(128, 'builder_score', '80', NULL, 99);

-- ID 130: SRNE 5.12kWh 100Ah
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(130, 'capacity_kwh', '5.12', 'kWh', 1),
(130, 'voltage', '51.2', 'V', 2),
(130, 'ampere_hour', '100', 'Ah', 3),
(130, 'chemistry', 'LiFePO4', NULL, 4),
(130, 'cycle_life', '6000', 'cycles', 5),
(130, 'dod', '90', '%', 6),
(130, 'builder_score', '55', NULL, 99);

-- ID 132: SRNE 16.07kWh 314Ah
INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(132, 'capacity_kwh', '16.07', 'kWh', 1),
(132, 'voltage', '51.2', 'V', 2),
(132, 'ampere_hour', '314', 'Ah', 3),
(132, 'chemistry', 'LiFePO4', NULL, 4),
(132, 'cycle_life', '6000', 'cycles', 5),
(132, 'dod', '90', '%', 6),
(132, 'builder_score', '92', NULL, 99);


-- ─────────────────────────────────────────────────────────────────────────────
-- 10. PRODUCT SPECIFICATIONS — New Mounting Systems
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(500, 'mount_type', 'Roof', NULL, 1),
(500, 'material', 'Aluminum', NULL, 2),
(500, 'max_panels', '15', 'panels', 3),
(500, 'wind_rating', '200', 'km/h', 4),
(500, 'builder_score', '55', NULL, 99),

(501, 'mount_type', 'Roof', NULL, 1),
(501, 'material', 'Aluminum', NULL, 2),
(501, 'max_panels', '25', 'panels', 3),
(501, 'wind_rating', '200', 'km/h', 4),
(501, 'builder_score', '70', NULL, 99),

(502, 'mount_type', 'Ground', NULL, 1),
(502, 'material', 'Galvanized Steel', NULL, 2),
(502, 'max_panels', '20', 'panels', 3),
(502, 'tilt_range', '10-45', '°', 4),
(502, 'builder_score', '75', NULL, 99),

(503, 'mount_type', 'Flat Roof', NULL, 1),
(503, 'material', 'HDPE + Aluminum', NULL, 2),
(503, 'max_panels', '15', 'panels', 3),
(503, 'tilt_angle', '10', '°', 4),
(503, 'builder_score', '60', NULL, 99),

(504, 'mount_type', 'Carport', NULL, 1),
(504, 'material', 'Galvanized Steel', NULL, 2),
(504, 'max_panels', '20', 'panels', 3),
(504, 'wind_rating', '250', 'km/h', 4),
(504, 'builder_score', '85', NULL, 99),

(505, 'mount_type', 'Ground', NULL, 1),
(505, 'material', 'Galvanized Steel', NULL, 2),
(505, 'max_panels', '40', 'panels', 3),
(505, 'tilt_range', '10-45', '°', 4),
(505, 'builder_score', '95', NULL, 99);


-- ─────────────────────────────────────────────────────────────────────────────
-- 11. PRODUCT SPECIFICATIONS — New Wiring & Protection
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(510, 'kit_type', 'Basic', NULL, 1),
(510, 'max_system_kw', '5', 'kW', 2),
(510, 'cable_size', '4', 'mm²', 3),
(510, 'spd_type', 'Type II', NULL, 4),
(510, 'max_voltage', '600', 'V', 5),
(510, 'builder_score', '40', NULL, 99),

(511, 'kit_type', 'Standard', NULL, 1),
(511, 'max_system_kw', '10', 'kW', 2),
(511, 'cable_size', '6', 'mm²', 3),
(511, 'spd_type', 'Type I+II', NULL, 4),
(511, 'max_voltage', '1000', 'V', 5),
(511, 'builder_score', '65', NULL, 99),

(512, 'kit_type', 'Premium', NULL, 1),
(512, 'max_system_kw', '15', 'kW', 2),
(512, 'cable_size', '6', 'mm²', 3),
(512, 'spd_type', 'Type I+II', NULL, 4),
(512, 'max_voltage', '1000', 'V', 5),
(512, 'builder_score', '82', NULL, 99),

(513, 'kit_type', 'Industrial', NULL, 1),
(513, 'max_system_kw', '25', 'kW', 2),
(513, 'cable_size', '10', 'mm²', 3),
(513, 'spd_type', 'Type I+II', NULL, 4),
(513, 'max_voltage', '1000', 'V', 5),
(513, 'builder_score', '95', NULL, 99);


-- ─────────────────────────────────────────────────────────────────────────────
-- 12. PRODUCT SPECIFICATIONS — New Monitoring Systems
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `product_specifications` (`product_id`, `spec_key`, `spec_value`, `spec_unit`, `display_order`) VALUES
(520, 'connectivity', 'WiFi', NULL, 1),
(520, 'display_type', 'App Only', NULL, 2),
(520, 'cloud_portal', 'Yes', NULL, 3),
(520, 'energy_meter', 'No', NULL, 4),
(520, 'builder_score', '35', NULL, 99),

(521, 'connectivity', 'WiFi + Cloud', NULL, 1),
(521, 'display_type', 'App + Web', NULL, 2),
(521, 'cloud_portal', 'Yes', NULL, 3),
(521, 'energy_meter', 'External Sensor', NULL, 4),
(521, 'builder_score', '55', NULL, 99),

(522, 'connectivity', 'WiFi + LAN', NULL, 1),
(522, 'display_type', '5\" Color Touch', NULL, 2),
(522, 'cloud_portal', 'VRM Portal', NULL, 3),
(522, 'energy_meter', 'Built-in', NULL, 4),
(522, 'builder_score', '80', NULL, 99),

(523, 'connectivity', 'WiFi + Cell + LAN', NULL, 1),
(523, 'display_type', 'Web Portal', NULL, 2),
(523, 'cloud_portal', 'SolarEdge Portal', NULL, 3),
(523, 'energy_meter', 'Revenue-Grade Meter', NULL, 4),
(523, 'builder_score', '95', NULL, 99),

(524, 'connectivity', 'WiFi + LAN', NULL, 1),
(524, 'display_type', 'Web Portal', NULL, 2),
(524, 'cloud_portal', 'Solar.web PRO', NULL, 3),
(524, 'energy_meter', 'Smart Meter', NULL, 4),
(524, 'builder_score', '85', NULL, 99);


-- ─────────────────────────────────────────────────────────────────────────────
-- 13. PERIPHERALS / ADD-ON SERVICES
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `solar_builder_peripherals` (`name`, `type`, `description`, `price`, `icon`, `is_active`, `display_order`) VALUES
('Site Assessment & Shade Analysis', 'Service', 'Professional roof inspection, structural assessment, and solar shading analysis report with system design recommendation.', 3500.00, '🏠', 1, 1),
('MERALCO Net Metering Application', 'Service', 'Complete net metering application processing with MERALCO, including all documentary requirements, inspection coordination, and meter installation follow-up.', 8000.00, '📋', 1, 2),
('Extended 10-Year Labor Warranty', 'Warranty', 'Extended workmanship and labor warranty covering installation defects, re-wiring, and panel re-seating for 10 years from installation date.', 15000.00, '🛡️', 1, 3),
('Smart Home Energy Controller', 'Accessory', 'Intelligent load management system. Automates solar self-consumption, load shifting, EV charging schedules, and integrates with smart home platforms.', 22000.00, '🏡', 1, 4),
('Annual Preventive Maintenance Plan', 'Service', 'One-year plan including 2 professional panel cleanings, electrical inspection, inverter health check, and detailed performance report.', 12000.00, '🔧', 1, 5),
('Panel Cleaning Kit + Anti-Dust Coating', 'Accessory', 'Professional-grade solar panel cleaning kit with extendable brush, biodegradable soap, squeegee, and nano anti-dust coating for 6-month protection.', 4500.00, '🧹', 1, 6),
('Lightning Arrester & Earthing System', 'Safety', 'Complete lightning protection system including arrester rod, down conductor, earth electrode, and surge protection devices for the solar array.', 9500.00, '⚡', 1, 7),
('CCTV Camera for Equipment Area', 'Security', 'Weatherproof IP camera with night vision for remote monitoring of inverter room / equipment area. Includes 32GB storage and mobile app access.', 7800.00, '📷', 1, 8);

-- ─────────────────────────────────────────────────────────────────────────────
-- Done! Solar Builder schema is ready.
-- ─────────────────────────────────────────────────────────────────────────────
