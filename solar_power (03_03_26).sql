-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 03, 2026 at 01:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `solar_power`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `firstName`, `lastName`, `email`, `password`, `created_at`) VALUES
(5, 'Gab', 'Don', 'gabdon@gmail.com', '12345678', '2025-12-12 01:33:29');

-- --------------------------------------------------------

--
-- Table structure for table `archived_products`
--

CREATE TABLE `archived_products` (
  `archive_id` int(11) NOT NULL,
  `original_id` int(11) NOT NULL,
  `displayName` varchar(255) NOT NULL,
  `brandName` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `stockQuantity` int(11) NOT NULL DEFAULT 0,
  `warranty` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `imagePath` varchar(255) NOT NULL,
  `postedByStaffId` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archived_products`
--

INSERT INTO `archived_products` (`archive_id`, `original_id`, `displayName`, `brandName`, `price`, `category`, `stockQuantity`, `warranty`, `description`, `imagePath`, `postedByStaffId`, `deleted_by`, `deleted_at`) VALUES
(1, 155, '650W', 'Nuuko', 5750.00, 'Panel', 1000, '12years', 'The Nuuko 620W solar panel (part of the NKM-132BDR12 series) is a high-efficiency module designed for both large-scale residential and commercial systems. Nuuko utilizes N-Type TOPCon technology, similar to the Jinko Tiger Neo, which ensures better performance in high-heat and low-light conditions.', 'path/to/uploaded/image.jpg', 10, 12, '2026-02-18 08:25:30'),
(3, 166, 'asd', 'TrinaSolar', 123.00, 'Battery', 21, '5 years', 'asd', 'path/to/uploaded/image.jpg', 12, 12, '2026-02-18 16:20:18'),
(4, 164, 'asd', 'TrinaSolar', 123.00, 'Battery', 21, '5 years', 'asd', 'path/to/uploaded/image.jpg', 12, 12, '2026-02-18 16:23:42'),
(5, 190, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-02-25 13:04:48'),
(6, 191, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-02-25 13:04:48');

-- --------------------------------------------------------

--
-- Table structure for table `archived_quotations`
--

CREATE TABLE `archived_quotations` (
  `archive_id` int(11) NOT NULL,
  `original_id` int(11) NOT NULL,
  `quotation_number` varchar(10) DEFAULT NULL,
  `client_name` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contact` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `system_type` varchar(50) DEFAULT NULL,
  `kw` decimal(10,2) DEFAULT NULL,
  `officer` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `original_created_at` timestamp NULL DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archived_quotations`
--

INSERT INTO `archived_quotations` (`archive_id`, `original_id`, `quotation_number`, `client_name`, `email`, `contact`, `location`, `system_type`, `kw`, `officer`, `status`, `remarks`, `created_by`, `original_created_at`, `deleted_by`, `deleted_at`) VALUES
(2, 10, 'Q20258958', 'Lebron James', 'markangelo@gmail.com', 912345678, 'laguna', 'HYBRID', 2.20, 'GAB', 'APPROVED', 'Sample', 5, '2025-12-27 07:46:06', 12, '2026-02-18 08:41:46'),
(5, 14, 'Q20261850', 'meg formelos', 'meg@gmail.com', 2147483647, 'lipa', 'SUPPLY-ONLY', 150.00, 'PRINCESS', 'LOSS', 'nako nawala', 12, '2026-02-18 03:57:10', 12, '2026-02-18 14:29:41'),
(6, 13, 'Q20267431', 'meg formelos', 'meg@gmail.com', 2147483647, 'lipa', 'SUPPLY-ONLY', 150.00, 'PRINCESS', 'LOSS', 'nako nawala', 12, '2026-02-18 03:57:10', 12, '2026-02-18 14:29:53');

-- --------------------------------------------------------

--
-- Table structure for table `archived_suppliers`
--

CREATE TABLE `archived_suppliers` (
  `archive_id` int(11) NOT NULL,
  `original_id` int(11) NOT NULL,
  `supplierName` varchar(255) NOT NULL,
  `contactPerson` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `registrationDate` timestamp NULL DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) UNSIGNED NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `category_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `category_id`) VALUES
(1, 'Trina', 1),
(2, 'JA Solar', 1),
(3, 'Aiko', 1),
(4, 'lvtopsun', 1),
(5, 'BYD', 2),
(6, 'longi', 2),
(7, 'Pylontech', 2),
(8, 'Huawei', 3),
(9, 'Solis', 3),
(10, 'Growatt', 3),
(11, 'Holymiles', 3),
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

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) UNSIGNED NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(2, 'Battery'),
(3, 'Inverter'),
(7, 'Monitoring System'),
(4, 'Mounting & Accessories'),
(5, 'Package'),
(1, 'Panel'),
(6, 'Wiring & Protection');

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `contact_number` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`id`, `email`, `firstName`, `lastName`, `password`, `contact_number`, `address`, `created_at`) VALUES
(1, 'janvierericksonaraque@gmail.com', 'janvier', '', '$2y$10$Mie2sM.pOS3w/pWL48NBUey9SBAbEq3NpI9IHPNAgV9CgK6fHcP/q', 0, '', '2025-12-10 02:11:15'),
(2, 'demo@gmail.com', 'demo', 'only', 'haha', 0, 'wala', '2026-02-16 05:03:43');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('new','read','replied') DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `message`, `created_at`, `status`) VALUES
(5, 'janvier', 'janvierericksonaraque@gmail.com', '09706911766', 'Hello! I`m interested in getting a solar installation for our home. Please provide an estimated cost and schedule for inspection. Thank you.', '2025-12-17 00:50:20', 'read'),
(6, 'Kent Jocel', 'kent@gmail.com', '0929292929292929', 'this is demo inquiries', '2026-02-23 05:51:46', 'new');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_locations`
--

CREATE TABLE `delivery_locations` (
  `id` int(11) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `location_type` enum('warehouse','hub','transit','destination') NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_reference` varchar(50) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` varchar(50) DEFAULT 'pending',
  `order_status` varchar(50) DEFAULT 'pending',
  `tracking_number` varchar(100) DEFAULT NULL,
  `staff_notes` text DEFAULT NULL,
  `current_location` varchar(255) DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_reference`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `tracking_number`, `staff_notes`, `current_location`, `estimated_delivery`, `delivered_at`, `created_at`) VALUES
(38, 'ORD-20260201055130-0E1949', 'Janvier', 'janviererickson@Gmail.com', '+639706911766', 'Punta Sta. Ana, 133914096, City of Manila, Metro Manila (NCR)', 320000.00, 'maya_full', 'partial', 'out_for_delivery', '566', NULL, 'sitio coral nabato', '2026-02-28', NULL, '2026-02-01 05:51:31'),
(39, 'ORD-202602010551 30-0E20000', 'demo name order', 'demoorder@gmail.com', '09202656254', 'demo orders address ', 399.00, 'gcash', 'pending', 'preparing', '55', NULL, 'In Transit', '2026-02-28', NULL, '2026-02-16 06:00:26'),
(40, 'ORD-202602010551 30-0E8966', 'demo jocel name', 'demo@gmail.com', '09556674484', 'sitio coral nabato', 50.00, 'union bank', 'pending', 'pending', '8953345', 'eto ay order notes', 'batangas', NULL, NULL, '2026-02-16 07:36:37'),
(41, 'ORD-202602010551 30-0E26981', 'pop up demo', 'popupdemo@gmail.com', '09556674568', 'this is customer address', 100000.00, 'full_maya', 'pending', 'confirmed', '584946', 'this is for demo only', 'Main Warehouse - Alabang', '2026-02-27', NULL, '2026-03-04 07:46:49'),
(42, 'ORD-202602010551 30-0E88787', 'mema lagay hehe', 'mema@gmail.com', '09264554774', 'mema address', 852.00, 'cash', 'pending', 'pending', '9292929', 'mema notes', 'bomb my location', NULL, NULL, '2026-02-18 07:25:22'),
(43, 'ORD-202602010551 30-0E26883', 'pang pito name', 'pangpito@gmail.com', '095959595', 'pang pito address', 7777.00, 'pang pito payment method', 'paid', 'delivered', '14213123', 'pang pito notes', 'pang pito location', '2026-02-21', NULL, '2026-02-19 01:07:46');

--
-- Triggers `orders`
--
DELIMITER $$
CREATE TRIGGER `after_order_insert` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
    INSERT INTO order_tracking_history (order_id, status, description)
    VALUES (NEW.id, 'pending', 'Order has been placed and is awaiting confirmation');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `subtotal`) VALUES
(21, 38, 0, '8kW - Grid- Tie Package', 1, 320000.00, 320000.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_tracking_history`
--

CREATE TABLE `order_tracking_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_by_staff_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_tracking_history`
--

INSERT INTO `order_tracking_history` (`id`, `order_id`, `status`, `location`, `description`, `updated_by_staff_id`, `created_at`) VALUES
(46, 38, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-02-01 05:51:31'),
(47, 39, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-02-16 06:00:26'),
(49, 40, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-02-16 07:36:37'),
(50, 41, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-02-16 07:49:38'),
(53, 42, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-02-18 07:25:22'),
(54, 43, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-02-19 01:07:46'),
(55, 41, 'confirmed', 'Main Warehouse - Alabang', 'none', 12, '2026-02-19 01:37:03'),
(56, 39, 'preparing', 'In Transit', 'This is for demo purposes only', 12, '2026-02-24 01:32:10'),
(57, 39, 'preparing', 'In Transit', 'This is for demo purposes only', 12, '2026-02-24 01:32:13'),
(58, 38, 'out_for_delivery', 'sitio coral nabato', 'this is demo for janvier tracking order', 12, '2026-02-24 01:39:00'),
(59, 38, 'out_for_delivery', 'sitio coral nabato', 'this is demo for janvier tracking order', 12, '2026-02-24 01:39:02');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `reset_code` varchar(6) NOT NULL,
  `user_role` enum('staff','client') NOT NULL,
  `expiry_date` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `displayName` varchar(255) NOT NULL,
  `brandName` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `stockQuantity` int(11) NOT NULL DEFAULT 0,
  `warranty` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `imagePath` varchar(255) NOT NULL,
  `postedByStaffId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `displayName`, `brandName`, `price`, `category`, `stockQuantity`, `warranty`, `description`, `imagePath`, `postedByStaffId`) VALUES
(59, ' Railings Roof Solar 2.4M', 'Universal Brand', 420.00, 'Mounting & Accessories', 1000, '0', '2.4-meter railings are commonly available in various materials such as aluminum and steel and are frequently used for applications like solar panel mounting, security fencing, and handrails.', 'path/to/uploaded/image.jpg', 5),
(60, 'Rail Connector', 'Universal Brand', 50.00, 'Mounting & Accessories', 1000, '0', 'Rail connectors, also known as splices or brackets, are essential hardware for joining rail sections, changing angles, or attaching railings to support structures. ', 'path/to/uploaded/image.jpg', 5),
(61, 'METAL BRACKETS (L FOOT)', 'Universal Brand', 50.00, 'Mounting & Accessories', 1000, '0', 'L foot\\\\\\\" metal brackets are predominantly used for securing solar panel mounting rails to roofs, but the term also refers to general-purpose L-shaped angle brackets used for shelving, handrails, and furniture reinforcement. These brackets are typically made of aluminum alloy or stainless steel for durability and corrosion resistance.', 'path/to/uploaded/image.jpg', 5),
(62, 'MID Clamps', 'Universal Brand', 30.00, 'Mounting & Accessories', 1000, '5 years', 'Essential components in solar panel mounting systems used to secure the adjacent edges of two solar panels to the mounting rails. They are typically made from durable, corrosion-resistant anodized aluminum and are available for various panel frame thicknesses. ', 'path/to/uploaded/image.jpg', 5),
(63, 'End Clamps', 'Universal Brand', 37.00, 'Mounting & Accessories', 1000, '0', 'Key components in photovoltaic (PV) solar panel installations, designed to securely fasten the outermost panels to the mounting rails. They are made from durable, corrosion-resistant materials like anodized aluminum or stainless steel and are available for various panel thicknesses.', 'path/to/uploaded/image.jpg', 5),
(64, 'SINGLE CORE PV WIRE RED 4mm2', 'Universal Brand', 37.00, 'Mounting & Accessories', 1500, '0', 'A box of 100 meters of single core red 4mmÂ² PV wire typically costs between â‚±3,000 and â‚±6,500, with a price per meter of around â‚±32 to â‚±65. The price of â‚±37.00 per meter falls within the typical range for this product when purchased in bulk. \\\\r\\\\n', 'path/to/uploaded/image.jpg', 5),
(65, 'flexible PVC BLACK 3/4', 'Universal Brand', 150.00, 'Mounting & Accessories', 1500, '0', 'A price of â‚±88 for 3/4\\\\\\\" black flexible PVC conduit is a competitive price for a single meter or possibly a short length.', 'path/to/uploaded/image.jpg', 5),
(71, 'GTI-SinglePhase 10kW', 'Deye', 46000.00, 'Inverter', 1000, '5 years', 'âœ¨ Product Features: ðŸŒž High Conversion Efficiency â€“ Up to 97.5% efficiency for maximum solar utilization. ðŸ”‹ Hybrid Technology â€“ Works with or without batteries, supports lithium & lead-acid storage. ðŸ”„ Seamless Switching â€“ Automatic changeover between solar, grid, and battery. ðŸ“¶ Smart Monitoring â€“ WiFi / GPRS connectivity for real-time system monitoring. ðŸŒ Safe & Durable â€“ Built-in protections (overload, short circuit, over-temperature). ðŸ› ï¸ Flexible System Design â€“ Supports multiple energy inputs (solar, grid, generator). ðŸ”‡ Low Noise Operation â€“ Designed for residential and commercial use. ðŸ’¡ Why Choose DEYE Inverters? DEYE is a global leader in hybrid solar inverter technology, known for durability, efficiency, and flexibility. Perfect for homes and businesses aiming for reliable, 24/7 clean energy.', 'path/to/uploaded/image.jpg', 5),
(73, 'Hoymiles Solar Battery LB-5D-G2 (5.12kWh) 100V/piece', 'HoyMiles', 50500.00, 'Battery', 1000, '5 years', 'âš¡ HOYMILES SOLAR BATTERY LB-5D-G2 (5.12kWh) â€“ Reliable, Safe & Efficient Energy Storage âš¡ âœ… Brand: Hoymiles âœ… Model: LB-5D-G2 âœ… Type: Low Voltage Detached Lithium Battery âœ… Capacity: 5.12kWh âœ… Protection Rating: IP20 (Indoor Installation)\\\\r\\\\n\\\\r\\\\nâœ… Warranty: Up to 10 years (depending on usage & configuration) âœ… Application: Residential, Commercial, and Off-Grid Solar Energy Systems ', 'path/to/uploaded/image.jpg', 5),
(74, '2.2kW Grid Tie Packages', 'Grid-tie', 120000.00, 'Package', 100, '5 years', '4 PC 635 BIFICIAL SOLAR PANEL , 2kW GRID TIE INVERTER', 'path/to/uploaded/image.jpg', 5),
(75, '4.4kW Grid-Tie', 'Grid-tie', 200000.00, 'Package', 150, '5 years', '7 PCS 635 BIFACIAL SOLAR PANEL , 4 kW GRID-TIE INVERTER', 'path/to/uploaded/image.jpg', 5),
(78, '6kW - Grid-Tie Package', 'Grid-tie', 260000.00, 'Package', 1000, '5 years', '10 PCS Bifacial Solar Panel, 6 kW Grid-Tie Inverter', 'path/to/uploaded/image.jpg', 5),
(79, 'LuxPower 6kW Hybrid Inverter', 'LuxPower', 34000.00, 'Inverter', 1000, '5 years', 'Single phase/Unbalanced 3-Phase\\r\\nSupports up to 10pcs in parallel for on/off grid\\r\\nTime of Use\\r\\n24/7 real-time monitoring via free LUX app and web\\r\\nOn/off grid seamless switching under 20ms\\r\\nPlug & Play (AC and PV port)\\r\\nIP65 for indoor/outdoor installation\\r\\nAuto Gen control', 'path/to/uploaded/image.jpg', 5),
(80, '8kW - Grid- Tie Package', 'Grid-tie', 320000.00, 'Package', 50, '5 years', '12 PCS Bifacial Solar Panel\\r\\n6 kW Grid Tie Inverter', 'path/to/uploaded/image.jpg', 1),
(81, '10kW - Grid-Tie Package', 'Grid-tie', 390000.00, 'Package', 50, '5 years', '16 PCS Bifacial Solar Panel\\r\\n6 kW Grid Tie Inverter', 'path/to/uploaded/image.jpg', 1),
(90, 'AE SOLAR', '580W', 5000.00, 'Panel', 1000, '12 years', 'The LVTOPSUN 550W Bifacial Solar Panel (Model LVTS144M-550) uses PERC half-cut cells, features a 21.2-21.3% efficiency, and offers 550W power at STC, with key specs like 49.8V Voc, 13.23A Isc, and a durable anodized aluminum frame, boasting a 25-year performance warranty and IP68 protection, ideal for maximizing energy capture from both sides.', 'path/to/uploaded/image.jpg', 7),
(91, '550 W', 'Lvtopsun', 4750.00, 'Panel', 1000, '12 years', 'The LVTOPSUN 550W Bifacial Solar Panel (Model LVTS144M-550) uses PERC half-cut cells, features a 21.2-21.3% efficiency, and offers 550W power at STC, with key specs like 49.8V Voc, 13.23A Isc, and a durable anodized aluminum frame, boasting a 25-year performance warranty and IP68 protection, ideal for maximizing energy capture from both sides.', 'path/to/uploaded/image.jpg', 7),
(95, '580W', 'Lvtopsun', 5125.00, 'Panel', 1000, '12 years', 'A specific model of high-power, bifacial N-type solar panel from the brand LVTOPSUN, offering 580 watts of peak power with 22.5% efficiency, designed to capture sunlight from both sides for increased energy yield, popular in the Philippines for solar installations. Key specs include a high open-circuit voltage (Voc) of 51.5V and a 25-year product warranty.', 'path/to/uploaded/image.jpg', 5),
(98, '645W', 'Aiko', 6250.00, 'Panel', 1000, '12 years', 'The Aiko 645W Bifacial Solar Panel is a high-efficiency N-type panel using All-Back Contact (ABC) technology for superior power generation from both sides, featuring a durable dual-glass design, excellent low-light performance, and high temperature resistance, backed by a strong warranty for residential, commercial, and industrial use', 'path/to/uploaded/image.jpg', 7),
(99, '650W', 'Aiko', 6300.00, 'Panel', 1000, '12 years', 'The Aiko 650W Bifacial Solar Panel is a high-efficiency N-type panel using All-Back Contact (ABC) technology for superior power generation from both sides, featuring a durable dual-glass design, excellent low-light performance, and high temperature resistance, backed by a strong warranty for residential, commercial, and industrial use', 'path/to/uploaded/image.jpg', 7),
(102, '635W', 'Aiko', 5625.00, 'Panel', 1000, '12 years', 'The Aiko 635W Bifacial Solar Panel is a high-efficiency N-type panel using All-Back Contact (ABC) technology for superior power generation from both sides, featuring a durable dual-glass design, excellent low-light performance, and high temperature resistance, backed by a strong warranty for residential, commercial, and industrial use', 'path/to/uploaded/image.jpg', 7),
(103, '615W', 'Trina Solar', 5590.00, 'Panel', 1000, '12 years', 'Trina Solar\\\'s 615W panels, typically the Vertex N i-TOPCon TSM-NEG19RC.20 model, are high-efficiency N-type bifacial glass-glass modules for utility/commercial use, featuring 22.8-23.1% efficiency, 132 half-cut cells, and excellent performance with 12-year product/30-year performance warranties. Key specs include 1500V system voltage, high mechanical load resistance (5400 Pa front), and lower degradation rates (1% first year, 0.4% annually)', 'path/to/uploaded/image.jpg', 7),
(104, '705W', 'Trina Solar', 6630.00, 'Panel', 1000, '12 years', 'Trina Solar\\\'s 705W bifacial panels, part of the Vertex N series (TSM-NEG21C.20), are high-power, N-type i-TOPCon glass-glass modules featuring ~22.7% efficiency, 132 cells, MC4 connectors, and robust mechanicals, offering significant power gains from the backside (up to 10-20%) for utility-scale projects, with key specs including 1500V max voltage, 12-year product warranty, and 30-year performance warranty.', 'path/to/uploaded/image.jpg', 7),
(108, '580w', 'Aerosolar', 5625.00, 'Panel', 1000, '12 years', 'AE Solar produces high-efficiency 580W bifacial solar panels under several product lines, primarily the Meteor (N-type TOPCon) and Aurora (PERC) series. These panels are designed with German engineering for high durability and performance in varying light conditions.', 'path/to/uploaded/image.jpg', 7),
(109, '590W', 'Jinko Solar', 5250.00, 'Panel', 1000, '12 years', 'The Jinko Solar Tiger Neo 590W N-type monocrystalline mono-facial panel (JKM590N-72HL4-V) uses advanced TOPCon/HOT 3.0 technology and SMBB to deliver high efficiency (up to 22.84%), low light-induced degradation, and superior performance in low-light conditions. It features 144 half-cut cells, a robust 35mm frame, and is ideal for utility/commercial, offering a -0.29%/Â°C temperature coefficient.', 'path/to/uploaded/image.jpg', 7),
(118, '580W', 'Nuuko', 5125.00, 'Panel', 1000, '12 years', 'The 580W Nuuko solar panel is a high-efficiency monocrystalline module, often featuring N-Type TOPCon or PERC technology with 144 half-cut cells (182mm M10). These bifacial, double-glass panels are designed for maximum power output (up to 22.6% efficiency) and durability, featuring 25-30 year warranties. They are ideal for residential and commercial systems. ', 'path/to/uploaded/image.jpg', 7),
(119, '6kW Single Phase Hybrid Inverter X1-HYB-6.0-LV', 'Solax', 64000.00, 'Inverter', 100, '5 years', 'The X1-Hybrid LV series, available from 6kW, combines a user-friendly LCD screen to meet modern residential energy needs. The inverter also features easy generator and microgrid integration, making it an ideal choice for a wide range of hybrid solar applications.', 'path/to/uploaded/image.jpg', 7),
(120, '5kW Single Phase Hybrid Inverter X1-HYB-5.0-LV', 'Solax', 49000.00, 'Inverter', 100, '5 years', 'The X1-Hybrid LV series, available from 5kW, combines a user-friendly LCD screen to meet modern residential energy needs. The inverter also features easy generator and microgrid integration, making it an ideal choice for a wide range of hybrid solar applications.', 'path/to/uploaded/image.jpg', 7),
(122, '2.2kW - Hybrid', 'Hybrid', 180000.00, 'Package', 50, '2 years', 'Product Included:\\r\\n- 4PCS 635 Bifacial Solar Panel\\r\\n- 2kW Hybrid Inverter\\r\\n- 51.2V 100AH LifePO Battery\\r\\n\\r\\nWarranties: \\r\\n- Solar Panel: 12 yrs\\r\\n- Inverter: 5 yrs\\r\\n- Battery: 5 yrs\\r\\n', 'path/to/uploaded/image.jpg', 5),
(123, '3.3kW - Hybrid', 'Hybrid', 210000.00, 'Package', 50, '2 years', 'Product Included:\\r\\n- 6 Pcs 635 Bifacial Solar Panel\\r\\n- 2kW Hybrid Inverter\\r\\n- 51.2V 100AH LifePO Battery\\r\\n\\r\\nWarranties:\\r\\n- Solar Panel: 12yrs\\r\\n- Inverter: 5 yrs\\r\\n- Battery: 5 yrs', 'path/to/uploaded/image.jpg', 5),
(124, '4.0kW - Hybrid Package', 'Hybrid', 240000.00, 'Package', 50, '2 years', 'Product Included:\\r\\n- 7 Pcs 635 Bifacial Solar Panel\\r\\n- 4kW Hybrid Inverter\\r\\n- 51.2V 100AH LifePO Battery\\r\\n\\r\\nWarranties:\\r\\n- Solar Panel: 12yrs\\r\\n- Inverter: 5 yrs\\r\\n- Battery: 5 yrs', 'path/to/uploaded/image.jpg', 5),
(128, '10.24kwh 200ah EOS  Low Voltage Battery', 'SRNE', 88200.00, 'Battery', 500, '5 years', 'The SRNE SR-EOS10B is a high-capacity 10.24kWh lithium iron phosphate (LiFePO4) battery designed for modern residential, commercial, and off-grid solar energy storage. It is engineered as a space-saving, intelligent storage solution that can be mounted on walls or floors.', 'path/to/uploaded/image.jpg', 11),
(130, '5.12KWH 100AH EOS  Low Voltage Battery', 'SRNE', 49000.00, 'Battery', 500, '5 years', 'SRNE offers several 5.12kWh lithium iron phosphate (LiFePO4) battery models designed for residential and commercial solar storage, with standard 6,000-cycle lifespans.\\r\\n\\r\\nThese standalone modules are designed for integration into existing solar setups and are often scalable by connecting multiple units in parallel.', 'path/to/uploaded/image.jpg', 11),
(132, '16.07kwh 314ah EOS  Low Voltage Battery', 'SRNE', 114800.00, 'Battery', 500, '5 years', 'The SRNE 314Ah refers to a specific capacity of a lithium iron phosphate (LFP) battery used in the company\\\'s modular energy storage systems, primarily in the  SR-SE16B-Pro models.', 'path/to/uploaded/image.jpg', 11),
(135, '6kW Single Phase Low Voltage Hybrid Inverter', 'SRNE', 49000.00, 'Inverter', 500, '5 years', 'The SRNE 6kW Single Phase Low Voltage Hybrid Inverter (primarily the HESP4860S100-H model) is an all-in-one power management system designed for residential and light commercial use. It is a \\\"hybrid\\\" because it can simultaneously manage power from solar panels, the utility grid, and a generator', 'path/to/uploaded/image.jpg', 11),
(136, '8kW Single Phase Low Voltage Hybrid Inverter', 'SRNE', 63000.00, 'Inverter', 500, '5 years', 'The SRNE 8kW Single Phase Low Voltage Hybrid Inverter is primarily the HESP4860S100-H models is an all-in-one power management system designed for residential and light commercial use. It is a hybrid because it can simultaneously manage power from solar panels, the utility grid, and a generator', 'path/to/uploaded/image.jpg', 11),
(138, '12kW Single Phase Low Voltage Hybrid Inverter', 'SRNE', 84600.00, 'Inverter', 500, '5 years', 'The SRNE 12kW single-phase low-voltage hybrid inverter is a powerful 48V system (specifically models like the SRNE HESP48120S200-H or SRNE SEI-12K-SP) designed to manage solar, battery, and grid power for high-capacity residential use.', 'path/to/uploaded/image.jpg', 10),
(140, '6KW SNA-6K Single-Phase Hybrid Inverter', 'LuxPower', 48000.00, 'Inverter', 500, '5 years', 'The most common model, the Luxpower SNA 6000 W (or SNA 6K), is a cost-effective, single-phase, off-grid and hybrid-ready inverter that is popular in residential settings. It features a built-in 80A/100A MPPT solar charger (some sources list 140A for a newer variant), allowing up to 8kW of solar panel input, a maximum charging current of 140A for 48V lithium-ion batteries, and the ability to operate in parallel with up to 16 units for scalable systems.', 'path/to/uploaded/image.jpg', 10),
(142, '14KW SNA-14K Single-Phase Hybrid Inverter', 'LuxPower', 93000.00, 'Inverter', 500, '5 years', 'The Luxpower 14kW hybrid inverter (model SNA14000 or SNA-EU 14000) is a single-phase unit with a 14,000W rated output power, capable of accepting up to 24kW of PV input. It features dual MPPTs, integrated breakers, and supports parallel operation.', 'path/to/uploaded/image.jpg', 10),
(143, '10kW Single Phase Hybrid Inverter X1-Lite-10.0-LV', 'Solax', 100000.00, 'Inverter', 500, '5 years', 'The SolaX X1-Lite-10.0-LV is a high-performance, single-phase hybrid inverter designed specifically for residential systems using Low Voltage (LV) batteries. It strikes a balance between high power output and the flexibility of 48V battery', 'path/to/uploaded/image.jpg', 10),
(144, '12kW Single Phase Hybrid Inverter X1-Lite-12.0-LV', 'Solax', 128000.00, 'Inverter', 500, '5 years', 'The SolaX X1-Lite-12.0-LV is the most powerful variant in the Lite-LV series, offering a significant jump in solar handling and MPPT flexibility. It is specifically built for large-scale residential energy storage using 48V Low Voltage batteries.', 'path/to/uploaded/image.jpg', 10),
(145, '15kW Three Phase Hybrid Inverter X3-NEO-15K-LV', 'Solax', 150000.00, 'Inverter', 500, '5 years', 'The SolaX X3-NEO-15K-LV is part of SolaX\\\'s latest generation of low-voltage three-phase inverters. It is designed to bridge the gap between heavy residential use and light commercial applications, maintaining compatibility with 48V battery systems while delivering high power output.', 'path/to/uploaded/image.jpg', 10),
(147, '625W', 'Jinko Solar', 7500.00, 'Panel', 1000, '5 years', 'The Jinko 625W Tiger Neo (specifically models like the JKM625N-66HL4M-BDV) is an ultra-high-power module designed for large residential, commercial, and utility-scale projects. It utilizes N-Type TOPCon technology, which is the current industry gold standard for efficiency and longevity.', 'path/to/uploaded/image.jpg', 10),
(148, '630W', 'Jinko Solar', 7600.00, 'Panel', 1000, '5 years', 'The Jinko 630W Tiger Neo (specifically the JKM630N-78HL4-BDV) is a flagship ultra-high-power module. It sits at the top end of the Tiger Neo series, utilizing Jinko\\\'s N-Type TOPCon (Tunnel Oxide Passivated Contact) technology to achieve elite-level efficiency and durability.', 'path/to/uploaded/image.jpg', 10),
(150, '650W', 'Nuuko', 5750.00, 'Panel', 1000, '12years', 'The Nuuko 620W solar panel (part of the NKM-132BDR12 series) is a high-efficiency module designed for both large-scale residential and commercial systems. Nuuko utilizes N-Type TOPCon technology, similar to the Jinko Tiger Neo, which ensures better performance in high-heat and low-light conditions.', 'path/to/uploaded/image.jpg', 10),
(153, '650W', 'Austra', 7000.00, 'Panel', 1000, '12 years', 'The Austa 650W (specifically model AU650-33V-MH) is an ultra-high-power module designed for large-scale installations. Unlike some other brands that use standard widths, this Austa model is significantly wider, utilizing 210mm large-format cells to push the wattage boundary.', 'path/to/uploaded/image.jpg', 10),
(154, '650W', 'Austra', 7000.00, 'Panel', 1000, '12 years', 'The Austa 650W (specifically model AU650-33V-MH) is an ultra-high-power module designed for large-scale installations. Unlike some other brands that use standard widths, this Austa model is significantly wider, utilizing 210mm large-format cells to push the wattage boundary.', 'path/to/uploaded/image.jpg', 10),
(156, 'convert demo', 'Trina Solar', 500.00, 'Panel', 5, '5 years', 'connvert', 'path/to/uploaded/image.jpg', 12),
(157, 'convert demo', 'Trina Solar', 500.00, 'Panel', 5, '5 years', 'connvert', 'path/to/uploaded/image.jpg', 12),
(158, 'convert demo', 'Trina Solar', 500.00, 'Panel', 5, '5 years', 'connvert', 'path/to/uploaded/image.jpg', 12),
(159, 'last demo', 'Hybrid', 800.00, 'Package', 6, '5 years', 'asdsad', 'path/to/uploaded/image.jpg', 12),
(160, 'last demo', 'Hybrid', 800.00, 'Package', 6, '5 years', 'asdsad', 'path/to/uploaded/image.jpg', 12),
(161, 'last demo', 'Hybrid', 800.00, 'Package', 6, '5 years', 'asdsad', 'path/to/uploaded/image.jpg', 12),
(162, 'asd', 'TrinaSolar', 123.00, 'Battery', 21, '5 years', 'asd', 'path/to/uploaded/image.jpg', 12),
(163, 'asd', 'TrinaSolar', 123.00, 'Battery', 21, '5 years', 'asd', 'path/to/uploaded/image.jpg', 12),
(167, 'last product name ', 'Grid-tie', 10000.00, 'Package', 10, '10 years', 'eto ay description ng last product na aking ginagawa', 'path/to/uploaded/image.jpg', 12),
(168, 'last product name ', 'Grid-tie', 10000.00, 'Package', 10, '10 years', 'eto ay description ng last product na aking ginagawa', 'path/to/uploaded/image.jpg', 12),
(169, 'last product name ', 'Grid-tie', 10000.00, 'Package', 10, '10 years', 'eto ay description ng last product na aking ginagawa', 'path/to/uploaded/image.jpg', 12),
(170, 'last product name ', 'Grid-tie', 10000.00, 'Package', 10, '10 years', 'eto ay description ng last product na aking ginagawa', 'path/to/uploaded/image.jpg', 12),
(171, 'last product name ', 'Grid-tie', 10000.00, 'Package', 10, '10 years', 'eto ay description ng last product na aking ginagawa', 'path/to/uploaded/image.jpg', 12),
(172, 'convert demo', 'Huawei', 500.00, 'Inverter', 600, '5 years', 'demo', 'path/to/uploaded/image.jpg', 12),
(173, 'convert demo', 'Huawei', 500.00, 'Inverter', 600, '5 years', 'demo', 'path/to/uploaded/image.jpg', 12),
(174, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(175, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(176, 'motolite updated', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(177, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(178, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(179, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(180, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(181, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(182, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(183, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(184, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(185, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(186, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(187, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(188, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(189, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(192, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(193, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12),
(194, 'convert demo hehe', 'Trina Solar hehe', 65555555.00, 'Panel', 210, '5 years', 'rggdsf', 'path/to/uploaded/image.jpg', 12),
(195, 'find me', 'Huawei', 502.00, 'Inverter', 6, '5 years', 'hahu', 'path/to/uploaded/image.jpg', 12),
(196, 'find me', 'Huawei', 502.00, 'Inverter', 6, '5 years', 'hahu', 'path/to/uploaded/image.jpg', 12),
(197, 'motolite', 'TrinaSolar', 99999999.99, 'Inverter', 2147483647, '5 years', 'oipio', 'path/to/uploaded/image.jpg', 12),
(198, 'motolite', 'TrinaSolar', 99999999.99, 'Inverter', 2147483647, '5 years', 'oipio', 'path/to/uploaded/image.jpg', 12),
(199, 'ang lala', 'Solis', 99999999.99, 'Inverter', 65, '5 years', 'ang lala men', 'path/to/uploaded/image.jpg', 12),
(200, 'ang lala', 'Solis', 99999999.99, 'Inverter', 65, '5 years', 'ang lala men', 'path/to/uploaded/image.jpg', 12),
(201, 'debug', 'Deye', 500.00, 'Inverter', 3, '5 years', 'asd', 'path/to/uploaded/image.jpg', 12),
(202, 'debug', 'Deye', 500.00, 'Inverter', 3, '5 years', 'asd', 'path/to/uploaded/image.jpg', 12),
(203, 'carousel demo', 'Trina Solar', 65.00, 'Panel', 50, '5 years', 'carousel image dapat laman', 'path/to/uploaded/image.jpg', 12),
(204, 'carousel demo', 'Trina Solar', 65.00, 'Panel', 50, '5 years', 'carousel image dapat laman', 'path/to/uploaded/image.jpg', 12),
(205, 'carousel demo', 'Trina Solar', 65.00, 'Panel', 50, '5 years', 'carousel image dapat laman', 'path/to/uploaded/image.jpg', 12),
(206, 'carousel demo', 'Trina Solar', 65.00, 'Panel', 50, '5 years', 'carousel image dapat laman', 'path/to/uploaded/image.jpg', 12),
(207, 'carousel demo', 'Trina Solar', 65.00, 'Panel', 50, '5 years', 'carousel image dapat laman', 'path/to/uploaded/image.jpg', 12),
(208, 'carousel demo', 'Trina Solar', 65.00, 'Panel', 50, '5 years', 'carousel image dapat laman', 'path/to/uploaded/image.jpg', 12),
(209, 'carousel demo', 'Trina Solar', 65.00, 'Panel', 50, '5 years', 'carousel image dapat laman', 'path/to/uploaded/image.jpg', 12),
(210, 'carousel demo', 'Trina Solar', 65.00, 'Panel', 50, '5 years', 'carousel image dapat laman', 'path/to/uploaded/image.jpg', 12),
(211, 'second demo', 'Grid-tie', 85.00, 'Package', 46, '5 years', 'second demo ulit', 'path/to/uploaded/image.jpg', 12),
(212, 'second demo', 'Grid-tie', 85.00, 'Package', 46, '5 years', 'second demo ulit', 'path/to/uploaded/image.jpg', 12),
(213, 'poste', 'Huawei', 855.00, 'Inverter', 64, '9 years', 'lala', 'path/to/uploaded/image.jpg', 12),
(214, 'poste', 'Huawei', 855.00, 'Inverter', 64, '9 years', 'lala', 'path/to/uploaded/image.jpg', 12),
(215, 'poste', 'Huawei', 855.00, 'Inverter', 64, '9 years', 'lala', 'path/to/uploaded/image.jpg', 12),
(216, 'poste', 'Huawei', 855.00, 'Inverter', 64, '9 years', 'lala', 'path/to/uploaded/image.jpg', 12),
(217, 'ultra demo', 'HoyMiles', 852852.00, 'Battery', 96, '5 years', '456', 'path/to/uploaded/image.jpg', 12),
(218, 'ultra demo', 'HoyMiles', 852852.00, 'Battery', 96, '5 years', '456', 'path/to/uploaded/image.jpg', 12),
(219, 'ultra demo', 'HoyMiles', 852852.00, 'Battery', 96, '5 years', '456', 'path/to/uploaded/image.jpg', 12),
(220, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(221, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(222, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(223, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(224, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(225, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(226, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(227, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(228, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(229, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(230, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(231, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(232, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(233, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(234, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(235, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(236, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(237, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(238, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(239, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(240, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(241, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(242, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(243, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(244, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(245, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(246, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(247, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(248, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(249, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(250, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(251, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(252, 'poste', 'Huawei', 855.00, 'Inverter', 64, '9 years', 'lala', 'path/to/uploaded/image.jpg', 12),
(253, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(254, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(255, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(256, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(257, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(258, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(259, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(260, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(261, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(262, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(263, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(264, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(265, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(266, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(267, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(268, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(269, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12),
(270, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12),
(271, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12),
(272, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12),
(273, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12),
(274, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12),
(275, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12),
(276, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12),
(277, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12),
(278, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12),
(279, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12),
(500, 'Complete Roof Mount Kit ??? Standard (Up to 15 Panels)', 'IronRidge', 18500.00, 'Mounting & Accessories', 200, '10 years', 'Complete aluminum rail mounting system for pitched roofs. Includes rails, L-feet brackets, mid clamps, end clamps, and all hardware for up to 15 panels. Anodized aluminum construction with stainless steel fasteners. Wind rated to 200 km/h.', 'path/to/uploaded/image.jpg', NULL),
(501, 'Complete Roof Mount Kit ??? Large (Up to 25 Panels)', 'IronRidge', 28000.00, 'Mounting & Accessories', 150, '10 years', 'Heavy-duty aluminum rail mounting system for pitched roofs. Full kit for up to 25 panels including extended rails, reinforced L-feet, all clamps and hardware. Suitable for residential and light commercial installations.', 'path/to/uploaded/image.jpg', NULL),
(502, 'Ground Mount Array System (Up to 20 Panels)', 'Unirac', 32000.00, 'Mounting & Accessories', 100, '15 years', 'Industrial galvanized steel ground mount array. Adjustable tilt angle 10?????45??. Complete foundation kit with concrete pier mounts, cross-rails, and all hardware for up to 20 panels. Hot-dip galvanized for 25+ year outdoor life.', 'path/to/uploaded/image.jpg', NULL),
(503, 'Flat Roof Ballast Mount System (Up to 15 Panels)', 'Renusol', 22000.00, 'Mounting & Accessories', 120, '10 years', 'No-penetration ballasted mounting system for flat roofs. High-density polyethylene trays with 10?? fixed tilt. No drilling required ??? weighted with ballast blocks. Includes all trays, connectors, and wind deflectors.', 'path/to/uploaded/image.jpg', NULL),
(504, 'Carport Solar Mount Structure (Up to 20 Panels)', 'Schletter', 45000.00, 'Mounting & Accessories', 50, '20 years', 'Premium dual-purpose carport + solar mount. Galvanized steel with powder coating. Covers one standard parking bay. Includes all structural members, panel rails, and ground anchors. Engineered for typhoon-prone regions.', 'path/to/uploaded/image.jpg', NULL),
(505, 'Heavy Duty Ground Mount (Up to 40 Panels)', 'K2 Systems', 58000.00, 'Mounting & Accessories', 40, '20 years', 'Commercial-grade ground mount system. Galvanized steel with adjustable tilt. Dual-row design for up to 40 panels. Includes screw pile foundations, all structural members, cable management trays.', 'path/to/uploaded/image.jpg', NULL),
(510, 'Basic DC/AC Wiring & Protection Kit ??? Up to 5kW', 'Generic Solar', 8500.00, 'Wiring & Protection', 300, '2 years', 'Complete wiring and protection package for systems up to 5kW. Includes: 30m 4mm?? PV DC cable (red+black), MC4 connector pairs, DC isolator switch, AC circuit breaker 32A, basic surge protector (SPD Type II), PVC conduit, cable ties and glands.', 'path/to/uploaded/image.jpg', NULL),
(511, 'Standard DC/AC Protection Kit ??? Up to 10kW', 'Schneider Electric', 15000.00, 'Wiring & Protection', 250, '5 years', 'Professional-grade protection for systems up to 10kW. Includes: 50m 6mm?? PV DC cable, premium MC4 connectors, DC combiner box (2-string), DC isolator 1000V, AC RCCB 63A, MCB 40A, SPD Type I+II, DIN rail enclosure IP65, labeling kit.', 'path/to/uploaded/image.jpg', NULL),
(512, 'Premium Protection Bundle ??? Up to 15kW', 'ABB', 25000.00, 'Wiring & Protection', 150, '5 years', 'Industrial-quality protection for up to 15kW systems. Includes: 80m 6mm?? PV DC cable, St??ubli MC4 connectors, 4-string DC combiner with fuses, dual DC isolator, AC RCCB 80A, MCB 63A, SPD Type I+II (DC + AC), IP65 enclosure, earth rod kit, full cable management.', 'path/to/uploaded/image.jpg', NULL),
(513, 'Industrial Grade Protection Kit ??? 20kW+ Systems', 'Schneider Electric', 38000.00, 'Wiring & Protection', 80, '5 years', 'Heavy-duty protection for large residential and commercial systems 20kW+. Includes: 120m 10mm?? PV DC cable, professional MC4 sets, 6-string DC combiner with monitoring, dual DC isolators 1000V, 3-phase AC distribution board, RCCB 100A, MCBs, dual SPD arrays, full earthing system, cable trays, labeling and documentation.', 'path/to/uploaded/image.jpg', NULL),
(520, 'WiFi Smart Monitor ??? Basic', 'Growatt', 3500.00, 'Monitoring System', 400, '2 years', 'Basic WiFi monitoring dongle. Plug-and-play with compatible inverters. Real-time generation tracking via Growatt ShinePhone app. Daily/monthly/yearly statistics. Push notifications for faults. Cloud data storage.', 'path/to/uploaded/image.jpg', NULL),
(521, 'WiFi + Cloud Monitor ??? Standard', 'Growatt', 8500.00, 'Monitoring System', 300, '3 years', 'Advanced WiFi monitoring with external current sensors. Works with any inverter brand. Real-time and historical data on the ShineServer cloud platform. Export reports (PDF/CSV). Multiple plant management. Email alerts for anomalies.', 'path/to/uploaded/image.jpg', NULL),
(522, 'Professional Monitoring System with Display', 'Victron Energy', 15000.00, 'Monitoring System', 100, '5 years', 'Victron GX Touch 50 ??? 5-inch color touchscreen display + VRM cloud portal. Shows real-time solar, battery, grid, and load flows. Historical graphing. Remote firmware updates. MQTT/Modbus integration. Tank level and temperature inputs. Built-in WiFi + Ethernet.', 'path/to/uploaded/image.jpg', NULL),
(523, 'Enterprise Monitoring Suite with Energy Meter', 'SolarEdge', 22000.00, 'Monitoring System', 60, '5 years', 'Full SolarEdge monitoring ecosystem. Module-level monitoring with power optimizers. Smart energy meter (import/export). SolarEdge cloud portal with API access. Revenue-grade metering. Free lifetime cloud monitoring. Ideal for net metering documentation.', 'path/to/uploaded/image.jpg', NULL),
(524, 'Smart Monitoring Kit with Weather Station', 'Fronius', 18500.00, 'Monitoring System', 80, '5 years', 'Fronius Solar.web PRO monitoring package. Includes Fronius Smart Meter, WiFi card, and optional weather station sensor. Compare actual vs. expected yield. Automatic fault detection. API access for third-party integration. Free Fronius Solar.web portal.', 'path/to/uploaded/image.jpg', NULL),
(525, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12),
(526, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12),
(527, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12),
(528, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12),
(529, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12),
(530, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12),
(531, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12),
(532, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12),
(533, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12),
(534, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12),
(535, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `created_at`) VALUES
(46, 59, 'uploads/products/59/img_6958664fc383c.jpg', '2026-01-03 00:43:59'),
(47, 60, 'uploads/products/60/img_695867bcb5396.jpg', '2026-01-03 00:50:04'),
(48, 61, 'uploads/products/61/img_69586a3f288e6.webp', '2026-01-03 01:00:47'),
(49, 62, 'uploads/products/62/img_69586a782a343.jpg', '2026-01-03 01:01:44'),
(50, 63, 'uploads/products/63/img_69586b088be48.jpg', '2026-01-03 01:04:08'),
(51, 64, 'uploads/products/64/img_69586cbf500fe.png', '2026-01-03 01:11:27'),
(52, 65, 'uploads/products/65/img_69586d3b66f92.webp', '2026-01-03 01:13:31'),
(56, 71, 'uploads/products/71/img_695870b62fdf1.webp', '2026-01-03 01:28:22'),
(57, 71, 'uploads/products/71/img_695870b630f37.webp', '2026-01-03 01:28:22'),
(59, 73, 'uploads/products/73/img_69587eb78d8d8.webp', '2026-01-03 02:28:07'),
(60, 74, 'uploads/products/74/img_6958918b5af26.jpg', '2026-01-03 03:48:27'),
(61, 75, 'uploads/products/75/img_6958923aef776.jpg', '2026-01-03 03:51:22'),
(63, 78, 'uploads/products/78/img_6965dae288237.jpg', '2026-01-13 05:40:50'),
(64, 79, 'uploads/products/79/img_6965e53f4b671.png', '2026-01-13 06:25:03'),
(65, 80, 'uploads/products/80/img_6966e56d20efd.jpg', '2026-01-14 00:38:05'),
(66, 81, 'uploads/products/81/img_6966e5d91fdf5.jpg', '2026-01-14 00:39:53'),
(90, 90, 'uploads/products/90/img_6969f59f0c9db.png', '2026-01-16 08:23:59'),
(92, 91, 'uploads/products/91/img_6969f89584292.png', '2026-01-16 08:36:37'),
(93, 91, 'uploads/products/91/img_6969f8c06a36b.png', '2026-01-16 08:37:20'),
(94, 91, 'uploads/products/91/img_6969f8c06aecc.png', '2026-01-16 08:37:20'),
(97, 95, 'uploads/products/95/img_696ef6d9e9705.png', '2026-01-20 03:30:33'),
(98, 95, 'uploads/products/95/img_696ef94c0b720.png', '2026-01-20 03:41:00'),
(99, 95, 'uploads/products/95/img_696ef9886c89f.png', '2026-01-20 03:42:00'),
(102, 98, 'uploads/products/98/img_696efc0356e81.png', '2026-01-20 03:52:35'),
(103, 99, 'uploads/products/99/img_696efcc955f40.png', '2026-01-20 03:55:53'),
(118, 98, 'uploads/products/98/img_696f3149633ee.jpg', '2026-01-20 07:39:53'),
(119, 98, 'uploads/products/98/img_696f31496ff3c.jpg', '2026-01-20 07:39:53'),
(124, 102, 'uploads/products/102/img_696f330152850.png', '2026-01-20 07:47:13'),
(125, 99, 'uploads/products/99/img_696f330803931.jpg', '2026-01-20 07:47:20'),
(126, 99, 'uploads/products/99/img_696f3308062af.jpg', '2026-01-20 07:47:20'),
(127, 102, 'uploads/products/102/img_696f34406dda5.jpg', '2026-01-20 07:52:32'),
(128, 102, 'uploads/products/102/img_696f34406f23d.jpg', '2026-01-20 07:52:32'),
(129, 103, 'uploads/products/103/img_696f357080e65.png', '2026-01-20 07:57:36'),
(130, 104, 'uploads/products/104/img_696f35fcecec8.png', '2026-01-20 07:59:57'),
(132, 103, 'uploads/products/103/img_696f37e4e30c4.jpg', '2026-01-20 08:08:04'),
(133, 103, 'uploads/products/103/img_696f37e4e4056.jpg', '2026-01-20 08:08:04'),
(136, 104, 'uploads/products/104/img_696f397bce310.jpg', '2026-01-20 08:14:51'),
(137, 104, 'uploads/products/104/img_696f397bcf373.jpg', '2026-01-20 08:14:51'),
(138, 108, 'uploads/products/108/img_696f3b751d15e.png', '2026-01-20 08:23:17'),
(139, 109, 'uploads/products/109/img_6972c19b3ef0f.png', '2026-01-23 00:32:27'),
(141, 109, 'uploads/products/109/img_6972c2bb8b4fb.png', '2026-01-23 00:37:15'),
(142, 109, 'uploads/products/109/img_6972c2bb8be4d.png', '2026-01-23 00:37:15'),
(150, 118, 'uploads/products/118/img_69784fa345380.png', '2026-01-27 05:39:47'),
(151, 119, 'uploads/products/119/img_69785c02b7ff3.png', '2026-01-27 06:32:34'),
(152, 120, 'uploads/products/120/img_69785d3c03bde.png', '2026-01-27 06:37:48'),
(154, 122, 'uploads/products/122/img_697ad9370c153.jpg', '2026-01-29 03:51:19'),
(155, 123, 'uploads/products/123/img_697ada136d7a3.jpg', '2026-01-29 03:54:59'),
(156, 124, 'uploads/products/124/img_697c617490e4e.jpg', '2026-01-30 07:44:52'),
(157, 124, 'uploads/products/124/img_697ff7cabc5a5.png', '2026-02-02 01:03:06'),
(162, 128, 'uploads/products/128/img_69892fd722c9e.png', '2026-02-09 00:52:39'),
(164, 130, 'uploads/products/130/img_6989309e992c5.png', '2026-02-09 00:55:58'),
(169, 135, 'uploads/products/135/img_6989353d9a833.png', '2026-02-09 01:15:41'),
(170, 136, 'uploads/products/136/img_69894a0ccbfae.png', '2026-02-09 02:44:28'),
(172, 138, 'uploads/products/138/img_69898695e52f9.png', '2026-02-09 07:02:45'),
(174, 140, 'uploads/products/140/img_6989891e5272d.png', '2026-02-09 07:13:34'),
(176, 142, 'uploads/products/142/img_69898a7b27150.png', '2026-02-09 07:19:23'),
(177, 143, 'uploads/products/143/img_698a9efc68c02.png', '2026-02-10 02:59:08'),
(178, 144, 'uploads/products/144/img_698aca11cc86a.png', '2026-02-10 06:02:57'),
(179, 145, 'uploads/products/145/img_698ae014473a1.png', '2026-02-10 07:36:52'),
(181, 147, 'uploads/products/147/img_698ae301d642a.png', '2026-02-10 07:49:21'),
(182, 148, 'uploads/products/148/img_698ae63a26f83.png', '2026-02-10 08:03:06'),
(184, 150, 'uploads/products/150/img_698ae9440ceac.png', '2026-02-10 08:16:04'),
(187, 153, 'uploads/products/153/img_698aea5312c1e.png', '2026-02-10 08:20:35'),
(188, 154, 'uploads/products/154/img_698aea6755cff.png', '2026-02-10 08:20:55'),
(190, 156, 'uploads/products/156/img_6992d3b61a3c9.png', '2026-02-16 08:22:14'),
(191, 157, 'uploads/products/157/img_6992d42e84c58.png', '2026-02-16 08:24:14'),
(192, 158, 'uploads/products/158/img_6992d56bd210c.png', '2026-02-16 08:29:31'),
(193, 159, 'uploads/products/159/img_6992d5932e68d.png', '2026-02-16 08:30:11'),
(194, 160, 'uploads/products/160/img_6992d597c7b63.png', '2026-02-16 08:30:15'),
(196, 167, 'uploads/products/167/img_6996a5c77db04.png', '2026-02-19 05:55:19'),
(197, 168, 'uploads/products/168/img_6996a5cdbd4f7.png', '2026-02-19 05:55:25'),
(198, 169, 'uploads/products/169/img_6996a633e1f11.png', '2026-02-19 05:57:07'),
(199, 170, 'uploads/products/170/img_6996a68f8b391.png', '2026-02-19 05:58:39'),
(200, 171, 'uploads/products/171/img_6996a86932c0c.png', '2026-02-19 06:06:33'),
(201, 172, 'uploads/products/172/img_699e498c6f8c4.png', '2026-02-25 00:59:56'),
(202, 172, 'uploads/products/172/img_699e498c704d1.png', '2026-02-25 00:59:56'),
(203, 172, 'uploads/products/172/img_699e498c70dd7.png', '2026-02-25 00:59:56'),
(204, 173, 'uploads/products/173/img_699e4991cf90a.png', '2026-02-25 01:00:01'),
(205, 173, 'uploads/products/173/img_699e4991d05dd.png', '2026-02-25 01:00:01'),
(206, 173, 'uploads/products/173/img_699e4991d0f90.png', '2026-02-25 01:00:01'),
(207, 174, 'uploads/products/174/img_699e4ff219b9f.png', '2026-02-25 01:27:14'),
(208, 175, 'uploads/products/175/img_699e55927f5f6.png', '2026-02-25 01:51:14'),
(209, 176, 'uploads/products/176/img_699e560edaa84.png', '2026-02-25 01:53:18'),
(210, 177, 'uploads/products/177/img_699e5637578df.png', '2026-02-25 01:53:59'),
(211, 178, 'uploads/products/178/img_699e5725abb9e.png', '2026-02-25 01:57:57'),
(212, 179, 'uploads/products/179/img_699e57285fec7.png', '2026-02-25 01:58:00'),
(213, 180, 'uploads/products/180/img_699e59a6c49ac.png', '2026-02-25 02:08:38'),
(214, 181, 'uploads/products/181/img_699e5a0ece0e2.png', '2026-02-25 02:10:22'),
(215, 182, 'uploads/products/182/img_699e5b731de1e.png', '2026-02-25 02:16:19'),
(216, 183, 'uploads/products/183/img_699e5c6ce4997.png', '2026-02-25 02:20:28'),
(217, 184, 'uploads/products/184/img_699e68ebddd36.png', '2026-02-25 03:13:47'),
(218, 185, 'uploads/products/185/img_699e69de37440.png', '2026-02-25 03:17:50'),
(219, 186, 'uploads/products/186/img_699e6a803ff1d.png', '2026-02-25 03:20:32'),
(220, 187, 'uploads/products/187/img_699e6ae540300.png', '2026-02-25 03:22:13'),
(221, 188, 'uploads/products/188/img_699e6b6894c4d.png', '2026-02-25 03:24:24'),
(222, 189, 'uploads/products/189/img_699e6bea17741.png', '2026-02-25 03:26:34'),
(225, 192, 'uploads/products/192/img_699e6c1aea09d.png', '2026-02-25 03:27:22'),
(226, 193, 'uploads/products/193/img_699e6e266e0a1.png', '2026-02-25 03:36:06'),
(227, 194, 'uploads/products/194/img_699e921628853.jpg', '2026-02-25 06:09:26'),
(228, 194, 'uploads/products/194/img_699e92162958a.jpg', '2026-02-25 06:09:26'),
(229, 195, 'uploads/products/195/img_699ead4e0f031.png', '2026-02-25 08:05:34'),
(230, 196, 'uploads/products/196/img_699ead53bdcec.png', '2026-02-25 08:05:39'),
(231, 197, 'uploads/products/197/img_699eada45588f.jpg', '2026-02-25 08:07:00'),
(232, 198, 'uploads/products/198/img_699eada746bbf.jpg', '2026-02-25 08:07:03'),
(233, 199, 'uploads/products/199/img_699eb1c00e443.jpg', '2026-02-25 08:24:32'),
(234, 200, 'uploads/products/200/img_699eb1cc3e2ab.jpg', '2026-02-25 08:24:44'),
(235, 201, 'uploads/products/201/img_699eb2c89b955.png', '2026-02-25 08:28:56'),
(236, 202, 'uploads/products/202/img_699eb2cc9b43c.png', '2026-02-25 08:29:00'),
(237, 203, 'uploads/products/203/img_69a4da42deecf.jpg', '2026-03-02 00:30:58'),
(238, 203, 'uploads/products/203/img_69a4da42dfafd.png', '2026-03-02 00:30:58'),
(239, 203, 'uploads/products/203/img_69a4da42e043b.jpg', '2026-03-02 00:30:58'),
(240, 204, 'uploads/products/204/img_69a4da49c6fc4.jpg', '2026-03-02 00:31:05'),
(241, 204, 'uploads/products/204/img_69a4da49c7d12.png', '2026-03-02 00:31:05'),
(242, 204, 'uploads/products/204/img_69a4da49c8a07.jpg', '2026-03-02 00:31:05'),
(243, 205, 'uploads/products/205/img_69a4dc23db341.jpg', '2026-03-02 00:38:59'),
(244, 205, 'uploads/products/205/img_69a4dc23dbf43.png', '2026-03-02 00:38:59'),
(245, 205, 'uploads/products/205/img_69a4dc23dcab9.jpg', '2026-03-02 00:38:59'),
(246, 203, 'uploads/products/203/img_69a4dc37c8665.jpg', '2026-03-02 00:39:19'),
(247, 206, 'uploads/products/206/img_69a4dc393faa4.jpg', '2026-03-02 00:39:21'),
(248, 206, 'uploads/products/206/img_69a4dc39409ba.png', '2026-03-02 00:39:21'),
(249, 206, 'uploads/products/206/img_69a4dc3941a04.jpg', '2026-03-02 00:39:21'),
(250, 207, 'uploads/products/207/img_69a4dc47e5983.jpg', '2026-03-02 00:39:35'),
(251, 207, 'uploads/products/207/img_69a4dc47e6519.png', '2026-03-02 00:39:35'),
(252, 207, 'uploads/products/207/img_69a4dc47e6ed7.jpg', '2026-03-02 00:39:35'),
(253, 208, 'uploads/products/208/img_69a4dc8bde9c0.jpg', '2026-03-02 00:40:43'),
(254, 208, 'uploads/products/208/img_69a4dc8bdf6af.png', '2026-03-02 00:40:43'),
(255, 208, 'uploads/products/208/img_69a4dc8be01c3.jpg', '2026-03-02 00:40:43'),
(256, 209, 'uploads/products/209/img_69a4ddce60bcc.jpg', '2026-03-02 00:46:06'),
(257, 209, 'uploads/products/209/img_69a4ddce619df.png', '2026-03-02 00:46:06'),
(258, 209, 'uploads/products/209/img_69a4ddce6264f.jpg', '2026-03-02 00:46:06'),
(259, 210, 'uploads/products/210/img_69a4e4d0d124d.jpg', '2026-03-02 01:16:00'),
(260, 210, 'uploads/products/210/img_69a4e4d0d1ebb.png', '2026-03-02 01:16:00'),
(261, 210, 'uploads/products/210/img_69a4e4d0d2f47.jpg', '2026-03-02 01:16:00'),
(262, 211, 'uploads/products/211/img_69a4e58536ef0.png', '2026-03-02 01:19:01'),
(263, 211, 'uploads/products/211/img_69a4e58537958.jpg', '2026-03-02 01:19:01'),
(265, 212, 'uploads/products/212/img_69a4e5872c0c1.png', '2026-03-02 01:19:03'),
(266, 212, 'uploads/products/212/img_69a4e5872caad.jpg', '2026-03-02 01:19:03'),
(268, 213, 'uploads/products/213/img_69a4e5c01966f.jpg', '2026-03-02 01:20:00'),
(269, 214, 'uploads/products/214/img_69a4e5c363ca2.jpg', '2026-03-02 01:20:03'),
(270, 215, 'uploads/products/215/img_69a4e71ac3a16.jpg', '2026-03-02 01:25:46'),
(271, 216, 'uploads/products/216/img_69a4e761ef630.jpg', '2026-03-02 01:26:57'),
(272, 220, 'uploads/products/220/img_69a4e9f9d8147.png', '2026-03-02 01:38:01'),
(273, 220, 'uploads/products/220/img_69a4e9f9d8ae0.jpg', '2026-03-02 01:38:01'),
(274, 220, 'uploads/products/220/img_69a4e9f9d95bb.jpg', '2026-03-02 01:38:01'),
(275, 221, 'uploads/products/221/img_69a4e9fdce674.png', '2026-03-02 01:38:05'),
(276, 221, 'uploads/products/221/img_69a4e9fdcf144.jpg', '2026-03-02 01:38:05'),
(277, 221, 'uploads/products/221/img_69a4e9fdcfbb8.jpg', '2026-03-02 01:38:05'),
(278, 220, 'uploads/products/220/img_69a4ea484169d.jpg', '2026-03-02 01:39:20'),
(279, 222, 'uploads/products/222/img_69a4ea4a1b09c.png', '2026-03-02 01:39:22'),
(280, 222, 'uploads/products/222/img_69a4ea4a1bbad.jpg', '2026-03-02 01:39:22'),
(281, 222, 'uploads/products/222/img_69a4ea4a1c4e4.jpg', '2026-03-02 01:39:22'),
(282, 223, 'uploads/products/223/img_69a4ea4c581d5.png', '2026-03-02 01:39:24'),
(283, 223, 'uploads/products/223/img_69a4ea4c5910e.jpg', '2026-03-02 01:39:24'),
(284, 223, 'uploads/products/223/img_69a4ea4c59b6f.jpg', '2026-03-02 01:39:24'),
(285, 224, 'uploads/products/224/img_69a4eadf49413.png', '2026-03-02 01:41:51'),
(286, 224, 'uploads/products/224/img_69a4eadf4a1fa.jpg', '2026-03-02 01:41:51'),
(287, 224, 'uploads/products/224/img_69a4eadf4ad3c.jpg', '2026-03-02 01:41:51'),
(288, 225, 'uploads/products/225/img_69a4eb0742154.png', '2026-03-02 01:42:31'),
(289, 225, 'uploads/products/225/img_69a4eb0742d0b.jpg', '2026-03-02 01:42:31'),
(290, 225, 'uploads/products/225/img_69a4eb07437fe.jpg', '2026-03-02 01:42:31'),
(291, 226, 'uploads/products/226/img_69a4ec2fa3b2e.png', '2026-03-02 01:47:27'),
(292, 226, 'uploads/products/226/img_69a4ec2fa4977.jpg', '2026-03-02 01:47:27'),
(293, 226, 'uploads/products/226/img_69a4ec2fa55cd.jpg', '2026-03-02 01:47:27'),
(294, 225, 'uploads/products/225/img_69a4ec4142ef9.jpg', '2026-03-02 01:47:45'),
(295, 225, 'uploads/products/225/img_69a4ec41434cc.jpg', '2026-03-02 01:47:45'),
(296, 227, 'uploads/products/227/img_69a4ec42409fc.png', '2026-03-02 01:47:46'),
(297, 227, 'uploads/products/227/img_69a4ec4241382.jpg', '2026-03-02 01:47:46'),
(298, 227, 'uploads/products/227/img_69a4ec4242257.jpg', '2026-03-02 01:47:46'),
(299, 228, 'uploads/products/228/img_69a4ec43e8fc2.png', '2026-03-02 01:47:47'),
(300, 228, 'uploads/products/228/img_69a4ec43e9949.jpg', '2026-03-02 01:47:47'),
(301, 228, 'uploads/products/228/img_69a4ec43ea41d.jpg', '2026-03-02 01:47:47'),
(302, 229, 'uploads/products/229/img_69a4ec59c2a43.png', '2026-03-02 01:48:09'),
(303, 229, 'uploads/products/229/img_69a4ec59c35d8.jpg', '2026-03-02 01:48:09'),
(304, 229, 'uploads/products/229/img_69a4ec59c3fc3.jpg', '2026-03-02 01:48:09'),
(305, 230, 'uploads/products/230/img_69a4ec6a1ab80.png', '2026-03-02 01:48:26'),
(306, 230, 'uploads/products/230/img_69a4ec6a1b6db.jpg', '2026-03-02 01:48:26'),
(307, 230, 'uploads/products/230/img_69a4ec6a1c2a7.jpg', '2026-03-02 01:48:26'),
(308, 231, 'uploads/products/231/img_69a4ed5d95a01.png', '2026-03-02 01:52:29'),
(309, 231, 'uploads/products/231/img_69a4ed5d9655c.jpg', '2026-03-02 01:52:29'),
(310, 231, 'uploads/products/231/img_69a4ed5d970a6.jpg', '2026-03-02 01:52:29'),
(311, 230, 'uploads/products/230/img_69a4ed6ef0a55.jpg', '2026-03-02 01:52:46'),
(312, 232, 'uploads/products/232/img_69a4ed7136591.png', '2026-03-02 01:52:49'),
(313, 232, 'uploads/products/232/img_69a4ed7136fe8.jpg', '2026-03-02 01:52:49'),
(314, 232, 'uploads/products/232/img_69a4ed7137afa.jpg', '2026-03-02 01:52:49'),
(315, 233, 'uploads/products/233/img_69a4ed7314e25.png', '2026-03-02 01:52:51'),
(316, 233, 'uploads/products/233/img_69a4ed73157e0.jpg', '2026-03-02 01:52:51'),
(317, 233, 'uploads/products/233/img_69a4ed7316135.jpg', '2026-03-02 01:52:51'),
(318, 234, 'uploads/products/234/img_69a4ee86bb1ae.png', '2026-03-02 01:57:26'),
(319, 234, 'uploads/products/234/img_69a4ee86bbb90.jpg', '2026-03-02 01:57:26'),
(320, 234, 'uploads/products/234/img_69a4ee86bc649.jpg', '2026-03-02 01:57:26'),
(321, 233, 'uploads/products/233/img_69a4ee9b68322.png', '2026-03-02 01:57:47'),
(322, 233, 'uploads/products/233/img_69a4ee9b68ad2.png', '2026-03-02 01:57:47'),
(323, 235, 'uploads/products/235/img_69a4ee9cb5ce4.png', '2026-03-02 01:57:48'),
(324, 235, 'uploads/products/235/img_69a4ee9cb6687.jpg', '2026-03-02 01:57:48'),
(325, 235, 'uploads/products/235/img_69a4ee9cb6f34.jpg', '2026-03-02 01:57:48'),
(326, 236, 'uploads/products/236/img_69a4ee9e9253d.png', '2026-03-02 01:57:50'),
(327, 236, 'uploads/products/236/img_69a4ee9e930f5.jpg', '2026-03-02 01:57:50'),
(328, 236, 'uploads/products/236/img_69a4ee9e93a28.jpg', '2026-03-02 01:57:50'),
(329, 235, 'uploads/products/235/img_69a4eec657254.jpg', '2026-03-02 01:58:30'),
(331, 237, 'uploads/products/237/img_69a4eec77afc9.jpg', '2026-03-02 01:58:31'),
(332, 237, 'uploads/products/237/img_69a4eec77b98c.jpg', '2026-03-02 01:58:31'),
(333, 238, 'uploads/products/238/img_69a4eec9433ad.png', '2026-03-02 01:58:33'),
(334, 238, 'uploads/products/238/img_69a4eec943e0f.jpg', '2026-03-02 01:58:33'),
(335, 238, 'uploads/products/238/img_69a4eec94478f.jpg', '2026-03-02 01:58:33'),
(336, 239, 'uploads/products/239/img_69a4eedb25950.png', '2026-03-02 01:58:51'),
(337, 239, 'uploads/products/239/img_69a4eedb26498.jpg', '2026-03-02 01:58:51'),
(339, 240, 'uploads/products/240/img_69a4eee5c4d7e.png', '2026-03-02 01:59:01'),
(340, 240, 'uploads/products/240/img_69a4eee5c58e7.jpg', '2026-03-02 01:59:01'),
(341, 240, 'uploads/products/240/img_69a4eee5c64fd.jpg', '2026-03-02 01:59:01'),
(342, 241, 'uploads/products/241/img_69a4eef561adc.png', '2026-03-02 01:59:17'),
(343, 241, 'uploads/products/241/img_69a4eef562718.jpg', '2026-03-02 01:59:17'),
(345, 242, 'uploads/products/242/img_69a4eefcd779d.png', '2026-03-02 01:59:24'),
(346, 242, 'uploads/products/242/img_69a4eefcd846f.jpg', '2026-03-02 01:59:24'),
(347, 242, 'uploads/products/242/img_69a4eefcd8dbb.jpg', '2026-03-02 01:59:24'),
(349, 243, 'uploads/products/243/img_69a4ef0bbb9ac.jpg', '2026-03-02 01:59:39'),
(350, 243, 'uploads/products/243/img_69a4ef0bbc5e8.jpg', '2026-03-02 01:59:39'),
(351, 244, 'uploads/products/244/img_69a4ef13ebb13.png', '2026-03-02 01:59:47'),
(352, 244, 'uploads/products/244/img_69a4ef13ec41c.jpg', '2026-03-02 01:59:47'),
(353, 244, 'uploads/products/244/img_69a4ef13ece85.jpg', '2026-03-02 01:59:47'),
(354, 245, 'uploads/products/245/img_69a4ef2bd7831.png', '2026-03-02 02:00:11'),
(355, 245, 'uploads/products/245/img_69a4ef2bd8269.jpg', '2026-03-02 02:00:11'),
(356, 245, 'uploads/products/245/img_69a4ef2bd8c92.jpg', '2026-03-02 02:00:11'),
(358, 246, 'uploads/products/246/img_69a4ef2eb1148.jpg', '2026-03-02 02:00:14'),
(359, 246, 'uploads/products/246/img_69a4ef2eb19ab.jpg', '2026-03-02 02:00:14'),
(360, 247, 'uploads/products/247/img_69a4ef51647b0.png', '2026-03-02 02:00:49'),
(361, 247, 'uploads/products/247/img_69a4ef5164ffd.jpg', '2026-03-02 02:00:49'),
(362, 247, 'uploads/products/247/img_69a4ef5165a8c.jpg', '2026-03-02 02:00:49'),
(363, 248, 'uploads/products/248/img_69a4ef5841df4.png', '2026-03-02 02:00:56'),
(364, 248, 'uploads/products/248/img_69a4ef5842729.jpg', '2026-03-02 02:00:56'),
(365, 248, 'uploads/products/248/img_69a4ef5843027.jpg', '2026-03-02 02:00:56'),
(366, 249, 'uploads/products/249/img_69a4ef59e0f3b.png', '2026-03-02 02:00:57'),
(367, 249, 'uploads/products/249/img_69a4ef59e1a26.jpg', '2026-03-02 02:00:57'),
(368, 249, 'uploads/products/249/img_69a4ef59e22be.jpg', '2026-03-02 02:00:57'),
(369, 248, 'uploads/products/248/img_69a4ef6871675.jpg', '2026-03-02 02:01:12'),
(370, 248, 'uploads/products/248/img_69a4ef6871d63.jpg', '2026-03-02 02:01:12'),
(371, 250, 'uploads/products/250/img_69a4ef69df5d2.png', '2026-03-02 02:01:13'),
(372, 250, 'uploads/products/250/img_69a4ef69dffc4.jpg', '2026-03-02 02:01:13'),
(373, 250, 'uploads/products/250/img_69a4ef69e08b7.jpg', '2026-03-02 02:01:13'),
(374, 251, 'uploads/products/251/img_69a4ef6c7eeb6.png', '2026-03-02 02:01:16'),
(375, 251, 'uploads/products/251/img_69a4ef6c7f832.jpg', '2026-03-02 02:01:16'),
(376, 251, 'uploads/products/251/img_69a4ef6c8016d.jpg', '2026-03-02 02:01:16'),
(378, 253, 'uploads/products/253/img_69a4f15651fc4.png', '2026-03-02 02:09:26'),
(379, 253, 'uploads/products/253/img_69a4f15652a82.jpg', '2026-03-02 02:09:26'),
(380, 253, 'uploads/products/253/img_69a4f1565360d.jpg', '2026-03-02 02:09:26'),
(383, 254, 'uploads/products/254/img_69a4f16d3dd97.png', '2026-03-02 02:09:49'),
(384, 254, 'uploads/products/254/img_69a4f16d3e7f1.jpg', '2026-03-02 02:09:49'),
(385, 254, 'uploads/products/254/img_69a4f16d3f57b.jpg', '2026-03-02 02:09:49'),
(386, 255, 'uploads/products/255/img_69a4f17031a2f.png', '2026-03-02 02:09:52'),
(387, 255, 'uploads/products/255/img_69a4f170323c8.jpg', '2026-03-02 02:09:52'),
(388, 255, 'uploads/products/255/img_69a4f17032c28.jpg', '2026-03-02 02:09:52'),
(389, 256, 'uploads/products/256/img_69a4f17984fd4.png', '2026-03-02 02:10:01'),
(390, 256, 'uploads/products/256/img_69a4f17985b3f.jpg', '2026-03-02 02:10:01'),
(391, 256, 'uploads/products/256/img_69a4f179865ba.jpg', '2026-03-02 02:10:01'),
(392, 257, 'uploads/products/257/img_69a4f17b236f1.png', '2026-03-02 02:10:03'),
(393, 257, 'uploads/products/257/img_69a4f17b24013.jpg', '2026-03-02 02:10:03'),
(394, 257, 'uploads/products/257/img_69a4f17b24cff.jpg', '2026-03-02 02:10:03'),
(395, 258, 'uploads/products/258/img_69a4f19200f23.png', '2026-03-02 02:10:26'),
(396, 258, 'uploads/products/258/img_69a4f19201a56.jpg', '2026-03-02 02:10:26'),
(397, 258, 'uploads/products/258/img_69a4f192024df.jpg', '2026-03-02 02:10:26'),
(398, 259, 'uploads/products/259/img_69a4f1949d818.png', '2026-03-02 02:10:28'),
(399, 259, 'uploads/products/259/img_69a4f1949e29a.jpg', '2026-03-02 02:10:28'),
(400, 259, 'uploads/products/259/img_69a4f1949ef00.jpg', '2026-03-02 02:10:28'),
(401, 260, 'uploads/products/260/img_69a4fdfe7a04f.png', '2026-03-02 03:03:26'),
(402, 260, 'uploads/products/260/img_69a4fdfe7a9f6.jpg', '2026-03-02 03:03:26'),
(403, 260, 'uploads/products/260/img_69a4fdfe7b3d3.jpg', '2026-03-02 03:03:26'),
(404, 261, 'uploads/products/261/img_69a4fe011fd04.png', '2026-03-02 03:03:29'),
(405, 261, 'uploads/products/261/img_69a4fe0120875.jpg', '2026-03-02 03:03:29'),
(406, 261, 'uploads/products/261/img_69a4fe01212d2.jpg', '2026-03-02 03:03:29'),
(407, 260, 'uploads/products/260/img_69a4fe0d732ad.jpg', '2026-03-02 03:03:41'),
(408, 260, 'uploads/products/260/img_69a4fe0d73e2f.jpg', '2026-03-02 03:03:41'),
(409, 262, 'uploads/products/262/img_69a4fe0f78fe9.png', '2026-03-02 03:03:43'),
(410, 262, 'uploads/products/262/img_69a4fe0f79999.jpg', '2026-03-02 03:03:43'),
(411, 262, 'uploads/products/262/img_69a4fe0f7ad8b.jpg', '2026-03-02 03:03:43'),
(412, 263, 'uploads/products/263/img_69a4fe13d6df2.png', '2026-03-02 03:03:47'),
(413, 263, 'uploads/products/263/img_69a4fe13d762b.jpg', '2026-03-02 03:03:47'),
(414, 263, 'uploads/products/263/img_69a4fe13d7db5.jpg', '2026-03-02 03:03:47'),
(415, 262, 'uploads/products/262/img_69a4fe286176c.jpg', '2026-03-02 03:04:08'),
(416, 262, 'uploads/products/262/img_69a4fe2862dc4.jpg', '2026-03-02 03:04:08'),
(417, 264, 'uploads/products/264/img_69a4fe3ce3e72.png', '2026-03-02 03:04:28'),
(418, 264, 'uploads/products/264/img_69a4fe3ce4898.jpg', '2026-03-02 03:04:28'),
(419, 264, 'uploads/products/264/img_69a4fe3ce51af.jpg', '2026-03-02 03:04:28'),
(420, 265, 'uploads/products/265/img_69a4fe95a6ebd.png', '2026-03-02 03:05:57'),
(421, 265, 'uploads/products/265/img_69a4fe95a78f4.jpg', '2026-03-02 03:05:57'),
(422, 265, 'uploads/products/265/img_69a4fe95a82db.jpg', '2026-03-02 03:05:57'),
(423, 266, 'uploads/products/266/img_69a4fea6c7346.png', '2026-03-02 03:06:14'),
(424, 266, 'uploads/products/266/img_69a4fea6c7d5b.jpg', '2026-03-02 03:06:14'),
(425, 266, 'uploads/products/266/img_69a4fea6c877a.jpg', '2026-03-02 03:06:14'),
(426, 267, 'uploads/products/267/img_69a4ff51227a3.png', '2026-03-02 03:09:05'),
(427, 267, 'uploads/products/267/img_69a4ff5123323.jpg', '2026-03-02 03:09:05'),
(428, 267, 'uploads/products/267/img_69a4ff512401d.jpg', '2026-03-02 03:09:05'),
(429, 266, 'uploads/products/266/img_69a4ff57f02d6.jpg', '2026-03-02 03:09:11'),
(430, 266, 'uploads/products/266/img_69a4ff57f195d.jpg', '2026-03-02 03:09:11'),
(431, 268, 'uploads/products/268/img_69a4ff6581d60.png', '2026-03-02 03:09:25'),
(432, 268, 'uploads/products/268/img_69a4ff65826d9.jpg', '2026-03-02 03:09:25'),
(433, 268, 'uploads/products/268/img_69a4ff6583071.jpg', '2026-03-02 03:09:25'),
(435, 269, 'uploads/products/269/img_69a4ff69725b8.jpg', '2026-03-02 03:09:29'),
(436, 269, 'uploads/products/269/img_69a4ff6972fa4.jpg', '2026-03-02 03:09:29'),
(437, 269, 'uploads/products/269/img_69a4ff9fdb9b2.png', '2026-03-02 03:10:23'),
(439, 270, 'uploads/products/270/img_69a4ffe1e176a.jpg', '2026-03-02 03:11:29'),
(440, 271, 'uploads/products/271/img_69a4ffe586dc1.png', '2026-03-02 03:11:33'),
(441, 271, 'uploads/products/271/img_69a4ffe587a73.jpg', '2026-03-02 03:11:33'),
(443, 272, 'uploads/products/272/img_69a4ffeb9ac97.jpg', '2026-03-02 03:11:39'),
(444, 273, 'uploads/products/273/img_69a4fff167e01.png', '2026-03-02 03:11:45'),
(445, 273, 'uploads/products/273/img_69a4fff168807.jpg', '2026-03-02 03:11:45'),
(446, 274, 'uploads/products/274/img_69a4fff9556ef.png', '2026-03-02 03:11:53'),
(447, 274, 'uploads/products/274/img_69a4fff9562a1.jpg', '2026-03-02 03:11:53'),
(449, 275, 'uploads/products/275/img_69a4fffbbac55.jpg', '2026-03-02 03:11:55'),
(451, 275, 'uploads/products/275/img_69a5005bf1c8c.png', '2026-03-02 03:13:31'),
(452, 276, 'uploads/products/276/img_69a50157dc493.png', '2026-03-02 03:17:43'),
(453, 276, 'uploads/products/276/img_69a50157dcf8b.jpg', '2026-03-02 03:17:43'),
(454, 277, 'uploads/products/277/img_69a5015b10441.png', '2026-03-02 03:17:47'),
(455, 277, 'uploads/products/277/img_69a5015b10df9.jpg', '2026-03-02 03:17:47'),
(456, 184, 'uploads/products/184/img_69a5016c2acdc.jpg', '2026-03-02 03:18:04'),
(457, 278, 'uploads/products/278/img_69a5016ea59d9.png', '2026-03-02 03:18:06'),
(458, 278, 'uploads/products/278/img_69a5016ea6685.jpg', '2026-03-02 03:18:06'),
(459, 279, 'uploads/products/279/img_69a501724d140.png', '2026-03-02 03:18:10'),
(460, 279, 'uploads/products/279/img_69a501724dbee.jpg', '2026-03-02 03:18:10'),
(461, 525, 'uploads/products/525/img_69a52280bd24d.jpg', '2026-03-02 05:39:12'),
(462, 525, 'uploads/products/525/img_69a52280be31a.jpg', '2026-03-02 05:39:12'),
(464, 526, 'uploads/products/526/img_69a522841f143.jpg', '2026-03-02 05:39:16'),
(465, 526, 'uploads/products/526/img_69a522841fb03.jpg', '2026-03-02 05:39:16'),
(466, 526, 'uploads/products/526/img_69a52284206f1.png', '2026-03-02 05:39:16'),
(467, 527, 'uploads/products/527/img_69a5228f7b1bf.jpg', '2026-03-02 05:39:27'),
(468, 527, 'uploads/products/527/img_69a5228f7bc8b.jpg', '2026-03-02 05:39:27'),
(469, 527, 'uploads/products/527/img_69a5228f7c6e3.png', '2026-03-02 05:39:27'),
(470, 528, 'uploads/products/528/img_69a52292145b5.jpg', '2026-03-02 05:39:30'),
(471, 528, 'uploads/products/528/img_69a52292150e4.jpg', '2026-03-02 05:39:30'),
(473, 529, 'uploads/products/529/img_69a5229c50c6c.jpg', '2026-03-02 05:39:40'),
(474, 529, 'uploads/products/529/img_69a5229c51a31.jpg', '2026-03-02 05:39:40'),
(475, 529, 'uploads/products/529/img_69a5229c52449.png', '2026-03-02 05:39:40'),
(476, 530, 'uploads/products/530/img_69a522a567cc9.jpg', '2026-03-02 05:39:49'),
(477, 530, 'uploads/products/530/img_69a522a568646.jpg', '2026-03-02 05:39:49'),
(478, 530, 'uploads/products/530/img_69a522a56913e.png', '2026-03-02 05:39:49'),
(479, 531, 'uploads/products/531/img_69a522a860830.jpg', '2026-03-02 05:39:52'),
(480, 531, 'uploads/products/531/img_69a522a86112b.jpg', '2026-03-02 05:39:52'),
(481, 531, 'uploads/products/531/img_69a522a861b82.png', '2026-03-02 05:39:52'),
(482, 530, 'uploads/products/530/img_69a522b8e08fe.jpg', '2026-03-02 05:40:08'),
(483, 530, 'uploads/products/530/img_69a522b8e12ce.jpg', '2026-03-02 05:40:08'),
(484, 530, 'uploads/products/530/img_69a522b8e1f7b.jpg', '2026-03-02 05:40:08'),
(485, 532, 'uploads/products/532/img_69a522bc15113.jpg', '2026-03-02 05:40:12'),
(486, 532, 'uploads/products/532/img_69a522bc15bb6.jpg', '2026-03-02 05:40:12'),
(487, 532, 'uploads/products/532/img_69a522bc164fc.png', '2026-03-02 05:40:12'),
(488, 533, 'uploads/products/533/img_69a522bf9568f.jpg', '2026-03-02 05:40:15'),
(489, 533, 'uploads/products/533/img_69a522bf96282.jpg', '2026-03-02 05:40:15'),
(490, 533, 'uploads/products/533/img_69a522bf96ef2.png', '2026-03-02 05:40:15'),
(491, 534, 'uploads/products/534/img_69a522cb0b8b0.png', '2026-03-02 05:40:27'),
(492, 534, 'uploads/products/534/img_69a522cb0c1c2.jpg', '2026-03-02 05:40:27'),
(493, 535, 'uploads/products/535/img_69a5242369bb2.jpg', '2026-03-02 05:46:11'),
(494, 535, 'uploads/products/535/img_69a524236a6bc.jpg', '2026-03-02 05:46:11'),
(495, 535, 'uploads/products/535/img_69a524236af4e.png', '2026-03-02 05:46:11');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(11) NOT NULL,
  `quotation_number` varchar(10) DEFAULT NULL,
  `client_name` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contact` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `system_type` enum('HYBRID','SUPPLY-ONLY','GRID-TIE-HYBRID') DEFAULT NULL,
  `kw` decimal(10,2) DEFAULT NULL,
  `officer` enum('PRINCESS','ANNE','GAB','JOY') DEFAULT NULL,
  `status` enum('SENT','ONGOING','APPROVED','CLOSED','LOSS') DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `quotation_number`, `client_name`, `email`, `contact`, `location`, `system_type`, `kw`, `officer`, `status`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(11, 'Q20268147', 'client name', 'haha@gmail.com', 48, 'Santa Rosa City Laguna', 'HYBRID', 85.00, '', 'APPROVED', 'hahaha', 12, '2026-02-18 03:55:55', '2026-02-18 03:55:55'),
(12, 'Q20269851', 'client name', 'haha@gmail.com', 48, 'Santa Rosa City Laguna', 'HYBRID', 85.00, '', 'APPROVED', 'hahaha', 12, '2026-02-18 03:55:55', '2026-02-18 03:55:55'),
(17, 'Q20269424', '1', '2@gmail.com', 911111111, 'mindanao', 'GRID-TIE-HYBRID', 62.00, '', 'ONGOING', 'asd', 12, '2026-02-18 03:58:07', '2026-02-18 06:30:28');

-- --------------------------------------------------------

--
-- Table structure for table `solar_builds`
--

CREATE TABLE `solar_builds` (
  `id` int(11) NOT NULL,
  `build_reference` varchar(50) NOT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','submitted','quoted','approved','completed') DEFAULT 'draft',
  `performance_data` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `solar_build_items`
--

CREATE TABLE `solar_build_items` (
  `id` int(11) NOT NULL,
  `build_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_slug` varchar(50) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `firstName` varchar(55) NOT NULL,
  `lastName` varchar(55) NOT NULL,
  `email` varchar(55) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Hashed password using password_hash()',
  `contact_number` varchar(20) DEFAULT NULL COMMENT 'Contact number in any format (09XX, +639XX, etc)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `firstName`, `lastName`, `email`, `password`, `contact_number`, `created_at`) VALUES
(8, 'Princess', 'Tumala', 'princesstumala5@gmail.com', '$2y$10$3Ci', '09184148517', '2026-01-12 08:17:14'),
(9, 'Aico', 'Raymundo', 'raymundoaicomarie@gmail.com', '$2y$10$had', '2147483647', '2026-01-30 08:20:50'),
(10, 'Janvier', 'Erickson', 'janvieraraque@gmail.com', '$2y$10$FhVcoq8GXnPJDXDzIg9Mquw7u1FL.8QbSXkgAjC70EY74d1xCTwOG', '+639706911766', '2026-02-02 00:53:23'),
(11, 'Joy', 'Madrigal', 'joymadrigal01@gmail.com', '$2y$10$U0WHtT1yiYU1nmEtHxA.c.gNJSYi3G04A8CFfWC9sVtoqrTSQbPzK', '099999999999', '2026-02-03 08:46:50'),
(12, 'kent jocel', 'lusdoc', 'kentjocellusdoc@gmail.com', 'kentjocel', '09201195508', '2026-02-16 02:26:03');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive','unsubscribed') DEFAULT 'active',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id` int(11) NOT NULL,
  `supplierName` varchar(255) NOT NULL,
  `contactPerson` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `registrationDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id`, `supplierName`, `contactPerson`, `email`, `phone`, `address`, `city`, `country`, `registrationDate`) VALUES
(1, 'PowerAi', 'Marilou', 'marilou@gmail.com', '0912345678', 'Alabang pbb', 'muntinlupa', 'Philippines', '2025-12-24 02:47:39'),
(2, 'demo 2', 'demo contact', 'contactdemo@gmail.com', '09595656585', 'contact demo haha', 'city contact demo', 'country demdo', '2026-02-19 00:28:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `archived_products`
--
ALTER TABLE `archived_products`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `archived_quotations`
--
ALTER TABLE `archived_quotations`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `archived_suppliers`
--
ALTER TABLE `archived_suppliers`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `brand_name` (`brand_name`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_locations`
--
ALTER TABLE `delivery_locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_reference` (`order_reference`),
  ADD KEY `idx_order_reference` (`order_reference`),
  ADD KEY `idx_customer_email` (`customer_email`),
  ADD KEY `idx_order_status` (`order_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_tracking_history`
--
ALTER TABLE `order_tracking_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by_staff_id` (`updated_by_staff_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quotation_number` (`quotation_number`);

--
-- Indexes for table `solar_builds`
--
ALTER TABLE `solar_builds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `build_reference` (`build_reference`);

--
-- Indexes for table `solar_build_items`
--
ALTER TABLE `solar_build_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_builditem_build` (`build_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_subscribed_at` (`subscribed_at`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `archived_products`
--
ALTER TABLE `archived_products`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `archived_quotations`
--
ALTER TABLE `archived_quotations`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `archived_suppliers`
--
ALTER TABLE `archived_suppliers`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `delivery_locations`
--
ALTER TABLE `delivery_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `order_tracking_history`
--
ALTER TABLE `order_tracking_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=536;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=496;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `solar_builds`
--
ALTER TABLE `solar_builds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `solar_build_items`
--
ALTER TABLE `solar_build_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `brands`
--
ALTER TABLE `brands`
  ADD CONSTRAINT `brands_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_tracking_history`
--
ALTER TABLE `order_tracking_history`
  ADD CONSTRAINT `order_tracking_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_tracking_history_ibfk_2` FOREIGN KEY (`updated_by_staff_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `solar_build_items`
--
ALTER TABLE `solar_build_items`
  ADD CONSTRAINT `fk_builditem_build` FOREIGN KEY (`build_id`) REFERENCES `solar_builds` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
