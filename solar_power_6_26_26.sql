-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 26, 2026 at 10:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
(6, 191, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-02-25 13:04:48'),
(7, 525, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(8, 526, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(9, 527, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(10, 528, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(11, 529, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(12, 530, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(13, 531, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(14, 532, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(15, 533, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(16, 534, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(17, 535, 'convert demo', 'TrinaSolar', 5656.00, 'Battery', 56, '5 years', '5656', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(18, 536, 'dapat lumabas ka', 'Huawei', 555.00, 'Inverter', 555, '5 years', 'dapat lalabas at inverter at huawei', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(19, 537, 'dapat lumabas ka', 'Huawei', 555.00, 'Inverter', 555, '5 years', 'dapat lalabas at inverter at huawei', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(20, 538, 'dota', 'Trina Solar', 456.00, 'Panel', 456, '5 years', '456', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(21, 539, 'dota', 'Trina Solar', 456.00, 'Panel', 456, '5 years', '456', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(22, 540, 'wohooo', 'Jinko Solar', 4556.00, 'Panel', 56, '5 years', 'asdad', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(23, 541, 'wohooo', 'Jinko Solar', 4556.00, 'Panel', 56, '5 years', 'asdad', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(24, 542, 'wohooo', 'Jinko Solar', 4556.00, 'Panel', 56, '5 years', 'asdad', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(25, 543, 'wohooo', 'Jinko Solar', 4556.00, 'Panel', 56, '5 years', 'asdad', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(26, 544, 'TRIPLE AAA', 'IanSolar', 999.00, 'Battery', 89, '5 years', 'this is double A battery', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(27, 545, 'double AA', 'IanSolar', 999.00, 'Battery', 89, '5 years', 'this is double A battery', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(28, 546, 'double AA', 'IanSolar', 999.00, 'Battery', 89, '5 years', 'this is double A battery', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(29, 547, 'double AA', 'IanSolar', 999.00, 'Battery', 89, '5 years', 'this is double A battery', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(30, 548, 'double AA', 'IanSolar', 999.00, 'Battery', 89, '5 years', 'this is double A battery', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(31, 549, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(32, 550, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(33, 551, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(34, 552, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(35, 553, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(36, 554, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(37, 555, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(38, 556, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(39, 557, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(40, 558, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(41, 559, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(42, 560, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(43, 561, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(44, 562, 'for demo on march', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(45, 563, 'for demo on march lupet ko', 'IanSolar', 9898.00, 'Battery', 85, '5 years', 'for demo on march only', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:16:49'),
(46, 172, 'convert demo', 'Huawei', 500.00, 'Inverter', 600, '5 years', 'demo', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(47, 173, 'convert demo', 'Huawei', 500.00, 'Inverter', 600, '5 years', 'demo', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(48, 195, 'find me', 'Huawei', 502.00, 'Inverter', 6, '5 years', 'hahu', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(49, 196, 'find me', 'Huawei', 502.00, 'Inverter', 6, '5 years', 'hahu', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(50, 197, 'motolite', 'TrinaSolar', 99999999.99, 'Inverter', 2147483647, '5 years', 'oipio', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(51, 198, 'motolite', 'TrinaSolar', 99999999.99, 'Inverter', 2147483647, '5 years', 'oipio', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(52, 199, 'ang lala', 'Solis', 99999999.99, 'Inverter', 65, '5 years', 'ang lala men', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(53, 200, 'ang lala', 'Solis', 99999999.99, 'Inverter', 65, '5 years', 'ang lala men', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(54, 201, 'debug', 'Deye', 500.00, 'Inverter', 3, '5 years', 'asd', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(55, 202, 'debug', 'Deye', 500.00, 'Inverter', 3, '5 years', 'asd', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(56, 213, 'poste', 'Huawei', 855.00, 'Inverter', 64, '9 years', 'lala', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(57, 214, 'poste', 'Huawei', 855.00, 'Inverter', 64, '9 years', 'lala', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(58, 215, 'poste', 'Huawei', 855.00, 'Inverter', 64, '9 years', 'lala', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(59, 216, 'poste', 'Huawei', 855.00, 'Inverter', 64, '9 years', 'lala', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(60, 252, 'poste', 'Huawei', 855.00, 'Inverter', 64, '9 years', 'lala', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:19:19'),
(61, 162, 'asd', 'TrinaSolar', 123.00, 'Battery', 21, '5 years', 'asd', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(62, 163, 'asd', 'TrinaSolar', 123.00, 'Battery', 21, '5 years', 'asd', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(63, 174, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(64, 175, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(65, 176, 'motolite updated', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(66, 177, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(67, 178, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(68, 179, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(69, 180, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(70, 181, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(71, 182, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(72, 183, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(73, 184, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(74, 185, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(75, 186, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(76, 187, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(77, 188, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(78, 189, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(79, 192, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(80, 193, 'motolite', 'HoyMiles', 500.00, 'Battery', 5, '5 years', 'motolite description', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(81, 210, 'carousel demo', 'Trina Solar', 65.00, 'Panel', 50, '5 years', 'carousel image dapat laman', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(82, 217, 'ultra demo', 'HoyMiles', 852852.00, 'Battery', 96, '5 years', '456', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(83, 218, 'ultra demo', 'HoyMiles', 852852.00, 'Battery', 96, '5 years', '456', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(84, 219, 'ultra demo', 'HoyMiles', 852852.00, 'Battery', 96, '5 years', '456', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(85, 220, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(86, 221, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(87, 222, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(88, 223, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(89, 224, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(90, 225, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(91, 226, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(92, 227, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(93, 228, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(94, 229, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(95, 230, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(96, 231, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(97, 232, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(98, 233, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(99, 234, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(100, 235, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(101, 236, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(102, 237, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(103, 238, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(104, 239, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(105, 240, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(106, 241, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(107, 242, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(108, 243, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(109, 244, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(110, 245, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(111, 246, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(112, 247, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(113, 248, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(114, 249, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(115, 250, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(116, 251, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(117, 253, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(118, 254, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(119, 255, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(120, 256, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(121, 257, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(122, 258, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(123, 259, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(124, 260, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(125, 261, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(126, 262, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(127, 263, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(128, 264, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(129, 265, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(130, 266, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(131, 267, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(132, 268, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(133, 269, 'last demo', 'IanSolar', 969.00, 'Battery', 52, '5 years', 'benepisyo sa katawan haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(134, 270, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(135, 271, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(136, 272, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(137, 273, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(138, 274, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(139, 275, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(140, 276, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(141, 277, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(142, 278, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(143, 279, 'ayaw gumana', 'IanSolar', 89.00, 'Battery', 87, '5 years', 'haha', 'path/to/uploaded/image.jpg', 12, 12, '2026-03-06 09:21:36'),
(147, 91, '550 W', 'Lvtopsun', 7150.00, 'Panel', 1000, '12 years', 'The LVTOPSUN 550W Bifacial Solar Panel (Model LVTS144M-550) uses PERC half-cut cells, features a 21.2-21.3% efficiency, and offers 550W power at STC, with key specs like 49.8V Voc, 13.23A Isc, and a durable anodized aluminum frame, boasting a 25-year performance warranty and IP68 protection, ideal for maximizing energy capture from both sides.', 'path/to/uploaded/image.jpg', 7, 10, '2026-06-24 14:24:51'),
(148, 95, '580W', 'Lvtopsun', 7800.00, 'Panel', 1000, '12 years', 'A specific model of high-power, bifacial N-type solar panel from the brand LVTOPSUN, offering 580 watts of peak power with 22.5% efficiency, designed to capture sunlight from both sides for increased energy yield, popular in the Philippines for solar installations. Key specs include a high open-circuit voltage (Voc) of 51.5V and a 25-year product warranty.', 'path/to/uploaded/image.jpg', 5, 10, '2026-06-24 14:24:51'),
(149, 98, '645W', 'Aiko', 7100.00, 'Panel', 1000, '12 years', 'The Aiko 645W Bifacial Solar Panel is a high-efficiency N-type panel using All-Back Contact (ABC) technology for superior power generation from both sides, featuring a durable dual-glass design, excellent low-light performance, and high temperature resistance, backed by a strong warranty for residential, commercial, and industrial use', 'path/to/uploaded/image.jpg', 7, 10, '2026-06-24 14:24:51'),
(150, 99, '650W', 'Aiko', 7200.00, 'Panel', 1000, '12 years', 'The Aiko 650W Bifacial Solar Panel is a high-efficiency N-type panel using All-Back Contact (ABC) technology for superior power generation from both sides, featuring a durable dual-glass design, excellent low-light performance, and high temperature resistance, backed by a strong warranty for residential, commercial, and industrial use', 'path/to/uploaded/image.jpg', 7, 10, '2026-06-24 14:24:51'),
(151, 102, '635W', 'Aiko', 6900.00, 'Panel', 1000, '12 years', 'The Aiko 635W Bifacial Solar Panel is a high-efficiency N-type panel using All-Back Contact (ABC) technology for superior power generation from both sides, featuring a durable dual-glass design, excellent low-light performance, and high temperature resistance, backed by a strong warranty for residential, commercial, and industrial use', 'path/to/uploaded/image.jpg', 7, 10, '2026-06-24 14:24:51'),
(152, 103, '615W', 'Trina Solar', 6500.00, 'Panel', 1000, '12 years', 'Trina Solar\\\'s 615W panels, typically the Vertex N i-TOPCon TSM-NEG19RC.20 model, are high-efficiency N-type bifacial glass-glass modules for utility/commercial use, featuring 22.8-23.1% efficiency, 132 half-cut cells, and excellent performance with 12-year product/30-year performance warranties. Key specs include 1500V system voltage, high mechanical load resistance (5400 Pa front), and lower degradation rates (1% first year, 0.4% annually)', 'path/to/uploaded/image.jpg', 7, 10, '2026-06-24 14:24:51'),
(153, 104, '705W', 'Trina Solar', 6630.00, 'Panel', 1000, '12 years', 'Trina Solar\\\'s 705W bifacial panels, part of the Vertex N series (TSM-NEG21C.20), are high-power, N-type i-TOPCon glass-glass modules featuring ~22.7% efficiency, 132 cells, MC4 connectors, and robust mechanicals, offering significant power gains from the backside (up to 10-20%) for utility-scale projects, with key specs including 1500V max voltage, 12-year product warranty, and 30-year performance warranty.', 'path/to/uploaded/image.jpg', 7, 10, '2026-06-24 14:24:51'),
(154, 108, '580w', 'AE Solar', 9900.00, 'Panel', 1000, '12 years', 'AE Solar produces high-efficiency 580W bifacial solar panels under several product lines, primarily the Meteor (N-type TOPCon) and Aurora (PERC) series. These panels are designed with German engineering for high durability and performance in varying light conditions.', 'path/to/uploaded/image.jpg', 7, 10, '2026-06-24 14:24:51'),
(155, 109, '590W', 'Jinko Solar', 7420.00, 'Panel', 1000, '12 years', 'The Jinko Solar Tiger Neo 590W N-type monocrystalline mono-facial panel (JKM590N-72HL4-V) uses advanced TOPCon/HOT 3.0 technology and SMBB to deliver high efficiency (up to 22.84%), low light-induced degradation, and superior performance in low-light conditions. It features 144 half-cut cells, a robust 35mm frame, and is ideal for utility/commercial, offering a -0.29%/Â°C temperature coefficient.', 'path/to/uploaded/image.jpg', 7, 10, '2026-06-24 14:24:51'),
(156, 147, '625W', 'Jinko Solar', 7500.00, 'Panel', 1000, '5 years', 'The Jinko 625W Tiger Neo (specifically models like the JKM625N-66HL4M-BDV) is an ultra-high-power module designed for large residential, commercial, and utility-scale projects. It utilizes N-Type TOPCon technology, which is the current industry gold standard for efficiency and longevity.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(157, 148, '630W', 'Jinko Solar', 7600.00, 'Panel', 1000, '5 years', 'The Jinko 630W Tiger Neo (specifically the JKM630N-78HL4-BDV) is a flagship ultra-high-power module. It sits at the top end of the Tiger Neo series, utilizing Jinko\\\'s N-Type TOPCon (Tunnel Oxide Passivated Contact) technology to achieve elite-level efficiency and durability.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(158, 150, '620W', 'Nuuko', 9100.00, 'Panel', 1000, '12years', 'The Nuuko 620W solar panel (part of the NKM-132BDR12 series) is a high-efficiency module designed for both large-scale residential and commercial systems. Nuuko utilizes N-Type TOPCon technology, similar to the Jinko Tiger Neo, which ensures better performance in high-heat and low-light conditions.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(159, 153, '650W', 'Austra', 7000.00, 'Panel', 1000, '12 years', 'The Austa 650W (specifically model AU650-33V-MH) is an ultra-high-power module designed for large-scale installations. Unlike some other brands that use standard widths, this Austa model is significantly wider, utilizing 210mm large-format cells to push the wattage boundary.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(160, 156, '595W', 'Nuuko', 8450.00, 'Panel', 1000, '12 years', 'The Nuuko 595W solar panel (specifically the NKM595N-144BDM10 model) is a high-efficiency module utilizing advanced N-type TOPCon technology. It is designed for both large-scale commercial projects and high-performance residential installations, offering a significant power density and excellent longevity.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(161, 158, '630W', 'AE Solar', 10000.00, 'Panel', 1000, '12 years', 'The AE Solar 630W (specifically the Meteor AE CMER-132BDS) is a top-tier bifacial module from the German-engineered Aurora/Meteor series. It utilizes N-type TOPCon technology, making it a direct competitor to the Nuuko 595W but with a larger footprint and higher total power output.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(162, 160, '595W', 'TongWei (TW)', 6890.00, 'Panel', 1000, '12 years', 'The Tongwei (TW Solar) 595W (Model: TWMND-72HD595W) is a high-efficiency N-type TOPCon bifacial module. Tongwei is one of the world\\\'s largest silicon and solar cell manufacturers, and this panel represents their premium tier for large-scale and industrial applications.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(163, 163, '585W', 'Dahai', 6400.00, 'Panel', 1000, '12 years', 'The Dahai Solar 585W (specifically the DHM72T31-585/TP model) is a high-efficiency N-type TOPCon module. Dahai Solar is a large-scale manufacturer known for its significant silicon and module production capacity, and this panel is part of their premium high-efficiency line.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(164, 164, '550W', 'IanSolar', 6600.00, 'Panel', 1000, '12 years', 'The IAN 550W Mono (Model: IAN550-144-MH) is a high-output monocrystalline module widely distributed in the Philippines. It is produced through OEM partnerships (often featuring the same architecture as major Tier 1 brands like Jinko or Longi) and uses PERC Half-Cut cell technology.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(165, 165, '610W', 'JA Solar', 7540.00, 'Panel', 1000, '12 years', 'The JA Solar 610W is a high-performance module typically found in two main variations: the N-type TOPCon (DeepBlue 4.0 Pro) and the Mono PERC (DeepBlue 3.0 Pro). Both are designed for large-scale commercial and industrial applications due to their high power density.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(166, 168, '585W', 'Hanersun', 5900.00, 'Panel', 1000, '12 years', 'The Hanersun 620W (specifically from the HiTouch 6N series) is a high-power module designed for utility-scale projects and high-capacity industrial installations. It utilizes the latest N-type TOPCon technology on a 210mm large-wafer platform.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(167, 170, '710w', 'JA Solar', 8320.00, 'Panel', 1000, '12 years', 'The JA Solar 710W (specifically the JAM66D46-710/LB) is a high-power bifacial module from the DeepBlue 4.0 Pro series. It represents the \\\"ultra-high power\\\" category, utilizing the large-format 210mm (G12) wafer platform and N-type TOPCon technology.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(168, 240, 'Aiko Solar Power', 'Aiko', 5000.00, 'Panel', 9999, '5 years', 'sample', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:24:51'),
(169, 74, '5kW - Hybrid ', 'Hybrid', 400000.00, 'Package', 100, '5 years', '4 PC 635 BIFICIAL SOLAR PANEL , 2kW GRID TIE INVERTER', 'path/to/uploaded/image.jpg', 5, 10, '2026-06-24 14:25:13'),
(170, 75, '5kW - Hybrid', 'Hybrid', 280000.00, 'Package', 150, '5 years', '7 PCS 635 BIFACIAL SOLAR PANEL , 4 kW GRID-TIE INVERTER', 'path/to/uploaded/image.jpg', 5, 10, '2026-06-24 14:25:13'),
(171, 78, '6kW - Hybrid', 'Hybrid', 390000.00, 'Package', 1000, '5 years', '10 PCS Bifacial Solar Panel, 6 kW Grid-Tie Inverter', 'path/to/uploaded/image.jpg', 5, 10, '2026-06-24 14:25:13'),
(172, 80, '6kW - Hybrid', 'Hybrid', 540000.00, 'Package', 50, '5 years', '12 PCS Bifacial Solar Panel\\r\\n6 kW Grid Tie Inverter', 'path/to/uploaded/image.jpg', 1, 10, '2026-06-24 14:25:13'),
(173, 81, '6kW - Hybrid', 'Hybrid', 480000.00, 'Package', 50, '5 years', '16 PCS Bifacial Solar Panel\\r\\n6 kW Grid Tie Inverter', 'path/to/uploaded/image.jpg', 1, 10, '2026-06-24 14:25:13'),
(174, 122, '8kW - Hybrid', 'Hybrid', 520000.00, 'Package', 50, '2 years', 'Product Included:\\r\\n- 4PCS 635 Bifacial Solar Panel\\r\\n- 2kW Hybrid Inverter\\r\\n- 51.2V 100AH LifePO Battery\\r\\n\\r\\nWarranties: \\r\\n- Solar Panel: 12 yrs\\r\\n- Inverter: 5 yrs\\r\\n- Battery: 5 yrs\\r\\n', 'path/to/uploaded/image.jpg', 5, 10, '2026-06-24 14:25:13'),
(175, 123, '8kW - Hybrid', 'Hybrid', 640000.00, 'Package', 50, '2 years', 'Product Included:\\r\\n- 6 Pcs 635 Bifacial Solar Panel\\r\\n- 2kW Hybrid Inverter\\r\\n- 51.2V 100AH LifePO Battery\\r\\n\\r\\nWarranties:\\r\\n- Solar Panel: 12yrs\\r\\n- Inverter: 5 yrs\\r\\n- Battery: 5 yrs', 'path/to/uploaded/image.jpg', 5, 10, '2026-06-24 14:25:13'),
(176, 124, '5kW - Hybrid ', 'Hybrid', 325000.00, 'Package', 50, '2 years', 'Product Included:\\r\\n- 7 Pcs 635 Bifacial Solar Panel\\r\\n- 4kW Hybrid Inverter\\r\\n- 51.2V 100AH LifePO Battery Warranties:\\r\\n- Solar Panel: 12yrs\\r\\n- Inverter: 5 yrs\\r\\n- Battery: 5 yrs', 'path/to/uploaded/image.jpg', 5, 10, '2026-06-24 14:25:13'),
(177, 119, '6kW Single Phase Hybrid Inverter X1-HYB-6.0-LV', 'Solax', 64000.00, 'Inverter', 100, '5 years', 'The X1-Hybrid LV series, available from 6kW, combines a user-friendly LCD screen to meet modern residential energy needs. The inverter also features easy generator and microgrid integration, making it an ideal choice for a wide range of hybrid solar applications.', 'path/to/uploaded/image.jpg', 7, 10, '2026-06-24 14:25:53'),
(178, 120, '5kW Single Phase Hybrid Inverter X1-HYB-5.0-LV', 'Solax', 49000.00, 'Inverter', 100, '5 years', 'The X1-Hybrid LV series, available from 5kW, combines a user-friendly LCD screen to meet modern residential energy needs. The inverter also features easy generator and microgrid integration, making it an ideal choice for a wide range of hybrid solar applications.', 'path/to/uploaded/image.jpg', 7, 10, '2026-06-24 14:25:53'),
(179, 135, '6kW Single Phase Low Voltage Hybrid Inverter', 'SRNE', 49000.00, 'Inverter', 500, '5 years', 'The SRNE 6kW Single Phase Low Voltage Hybrid Inverter (primarily the HESP4860S100-H model) is an all-in-one power management system designed for residential and light commercial use. It is a \\\"hybrid\\\" because it can simultaneously manage power from solar panels, the utility grid, and a generator', 'path/to/uploaded/image.jpg', 11, 10, '2026-06-24 14:25:53'),
(180, 136, '8kW Single Phase Low Voltage Hybrid Inverter', 'SRNE', 63000.00, 'Inverter', 500, '5 years', 'The SRNE 8kW Single Phase Low Voltage Hybrid Inverter is primarily the HESP4860S100-H models is an all-in-one power management system designed for residential and light commercial use. It is a hybrid because it can simultaneously manage power from solar panels, the utility grid, and a generator', 'path/to/uploaded/image.jpg', 11, 10, '2026-06-24 14:25:53'),
(181, 138, '12kW Single Phase Low Voltage Hybrid Inverter', 'SRNE', 84600.00, 'Inverter', 500, '5 years', 'The SRNE 12kW single-phase low-voltage hybrid inverter is a powerful 48V system (specifically models like the SRNE HESP48120S200-H or SRNE SEI-12K-SP) designed to manage solar, battery, and grid power for high-capacity residential use.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(182, 140, '6KW SNA-6K Single-Phase Hybrid Inverter', 'LuxPower', 48000.00, 'Inverter', 500, '5 years', 'The most common model, the Luxpower SNA 6000 W (or SNA 6K), is a cost-effective, single-phase, off-grid and hybrid-ready inverter that is popular in residential settings. It features a built-in 80A/100A MPPT solar charger (some sources list 140A for a newer variant), allowing up to 8kW of solar panel input, a maximum charging current of 140A for 48V lithium-ion batteries, and the ability to operate in parallel with up to 16 units for scalable systems.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(183, 142, '14KW SNA-14K Single-Phase Hybrid Inverter', 'LuxPower', 93000.00, 'Inverter', 500, '5 years', 'The Luxpower 14kW hybrid inverter (model SNA14000 or SNA-EU 14000) is a single-phase unit with a 14,000W rated output power, capable of accepting up to 24kW of PV input. It features dual MPPTs, integrated breakers, and supports parallel operation.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(184, 143, '10kW Single Phase Hybrid Inverter X1-Lite-10.0-LV', 'Solax', 100000.00, 'Inverter', 500, '5 years', 'The SolaX X1-Lite-10.0-LV is a high-performance, single-phase hybrid inverter designed specifically for residential systems using Low Voltage (LV) batteries. It strikes a balance between high power output and the flexibility of 48V battery', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(185, 144, '12kW Single Phase Hybrid Inverter X1-Lite-12.0-LV', 'Solax', 128000.00, 'Inverter', 500, '5 years', 'The SolaX X1-Lite-12.0-LV is the most powerful variant in the Lite-LV series, offering a significant jump in solar handling and MPPT flexibility. It is specifically built for large-scale residential energy storage using 48V Low Voltage batteries.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(186, 145, '15kW Three Phase Hybrid Inverter X3-NEO-15K-LV', 'Solax', 150000.00, 'Inverter', 500, '5 years', 'The SolaX X3-NEO-15K-LV is part of SolaX\\\'s latest generation of low-voltage three-phase inverters. It is designed to bridge the gap between heavy residential use and light commercial applications, maintaining compatibility with 48V battery systems while delivering high power output.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(187, 198, '6 KW Single Phase Grid Tied Inverter SUN-6-G05P1- EU-AM2', 'Deye', 35000.00, 'Inverter', 500, '5 years', 'The Deye SUN-6K-G05P1-EU-AM2 is a compact, high-efficiency single-phase grid-tied string inverter. Like the 10kW model, this is an on-grid only inverter, meaning it focuses on maximizing solar harvesting and grid export but does not support batteries or provide power during a brownout.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(188, 200, '10 KW Single Phase Grid Tied Inverter SUN-10K-G02P1-EU-AM2', 'Deye', 45000.00, 'Inverter', 500, '5 years', 'The  Deye SUN-10K-G02P1-EU-AM2 is a high-power, single-phase grid-tied string inverter. Unlike the hybrid inverters often paired with the batteries we discussed earlier, this is a pure grid-tie unit. Its primary job is to convert solar energy into AC power for your home or for selling back to the grid (Net Metering), but it does not support battery storage directly.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(189, 202, '6kW Single Phase Hybrid Inverter SUN-6K- SG04LP1-EU-SM1/SM2', 'Deye', 59000.00, 'Inverter', 500, '5 years', 'The Deye SUN-6K-SG04LP1-EU (SM1/SM2) is a highly versatile single-phase hybrid inverter that has become a benchmark in the Philippines for residential solar storage. Unlike the \\\"grid-tied\\\" models we discussed earlier, this Hybrid unit can manage solar panels, the utility grid, a generator, and a battery bank simultaneously.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(190, 203, '8kW Three Phase Hybrid Inverter SUN-8K- SG05LP3-EU-SM2', 'Deye', 77000.00, 'Inverter', 500, '5 years', 'The Deye SUN-8K-SG05LP3-EU-SM2 is a sophisticated three-phase hybrid inverter designed for homes or small businesses with three-phase electrical systems. Unlike standard three-phase units that often require high-voltage batteries, this specific model (SG05 series) is a Low Voltage (LV) hybrid, meaning it remains compatible with the 48V/51.2V battery systems.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(191, 205, '12KW Three Phase Hybrid Inverter SUN-12K- SG05LP3-EU-SM2', 'Deye', 99000.00, 'Inverter', 500, '5 years', 'The Deye SUN-12K-SG05LP3-EU-SM2 is the most powerful model in Deyeâ€™s low-voltage (48V) three-phase hybrid series. It is highly sought after for large residences and small commercial facilities in the Philippines because it provides massive 12kW backup power while remaining compatible with affordable 48V/51.2V battery banks.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(192, 207, '16KW Three Phase Hybrid Inverter SUN-16K- SG05LP3-EU-SM2', 'Deye', 128000.00, 'Inverter', 500, '5 years', 'The Deye SUN-16K-SG05LP3-EU-SM2 is currently the most powerful low-voltage (48V) three-phase hybrid inverter in Deye\\\'s lineup. It is a high-performance solution for large estates, commercial complexes, and light industrial sites that need significant power but want to use safer and more affordable 48V battery systems.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(193, 209, '18KW Three Phase Hybrid Inverter SUN-18K- SG05LP3-EU-SM2', 'Deye', 140000.00, 'Inverter', 500, '5 years', 'The Deye SUN-18K-SG05LP3-EU-SM2 is the high-capacity elite of Deye\\\'s low-voltage (48V) three-phase hybrid family. It is a rare powerhouse that bridges the gap between residential storage and light industrial demand, managing a massive 18kW of power while remaining on a safer, 48V battery architecture.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(194, 211, '20KW Three Phase Hybrid Inverter SUN-20K- SG05LP3-EU-SM2', 'Deye', 175000.00, 'Inverter', 500, '5 years', 'The Deye SUN-20K-SG05LP3-EU-SM2 is the powerhouse of the Deye low-voltage (48V) three-phase hybrid family. It is a rare and highly engineered inverter that allows for a massive 20kW of power while still using a safe, low-voltage battery architecture. This makes it ideal for large luxury estates, agricultural facilities, or small commercial buildings in the Philippines.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(195, 213, '3KW On Grid Single Phase String Inverter with AFCI HYX-S3K-S', 'Hyxipower', 29000.00, 'Inverter', 500, '5 years', 'The HyxiPower HYX-S3K-S is a compact, high-efficiency single-phase on-grid string inverter. It is specifically designed for residential systems where safety is a priority, featuring integrated AFCI (Arc Fault Circuit Interrupter) to detect and mitigate electrical fire risks caused by DC arc faults.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53'),
(196, 215, '4KW On Grid Single Phase String Inverter with AFCI HYX-S4K-S', 'Hyxipower', 32000.00, 'Inverter', 500, '5 years', 'The HyxiPower HYX-S4K-S is the 4kW variant of the \\\"Halo\\\" series single-phase on-grid string inverters. It is a highly efficient, safety-focused unit designed for residential rooftops where the homeowner wants to maximize solar harvest while ensuring protection against electrical fires via integrated AFCI technology.', 'path/to/uploaded/image.jpg', 10, 10, '2026-06-24 14:25:53');

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
(6, 13, 'Q20267431', 'meg formelos', 'meg@gmail.com', 2147483647, 'lipa', 'SUPPLY-ONLY', 150.00, 'PRINCESS', 'LOSS', 'nako nawala', 12, '2026-02-18 03:57:10', 12, '2026-02-18 14:29:53'),
(7, 17, 'Q20269424', '1', '2@gmail.com', 911111111, 'mindanao', 'GRID-TIE-HYBRID', 62.00, '', 'ONGOING', 'asd', 12, '2026-02-18 03:58:07', 10, '2026-05-18 23:16:03'),
(8, 12, 'Q20269851', 'client name', 'haha@gmail.com', 48, 'Santa Rosa City Laguna', 'HYBRID', 85.00, '', 'APPROVED', 'hahaha', 12, '2026-02-18 03:55:55', 10, '2026-05-18 23:16:07'),
(9, 11, 'Q20268147', 'client name', 'haha@gmail.com', 48, 'Santa Rosa City Laguna', 'HYBRID', 85.00, '', 'APPROVED', 'hahaha', 12, '2026-02-18 03:55:55', 10, '2026-05-18 23:16:10'),
(10, 18, 'Q20262457', 'Janvier', 'janviererikcon@gmail.com', 2147483647, 'imus cavute', '', 3.50, '', 'ONGOING', '', 10, '2026-05-19 06:44:17', 10, '2026-05-19 00:53:04'),
(11, 19, 'Q20263289', 'Janvier Erickson', 'janvieraraque@gmail.com', 2147483647, 'Blk 11 lot 38', '', 3.50, '', 'ONGOING', '', 10, '2026-05-19 07:55:17', 10, '2026-05-19 06:42:45');

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
  `category_id` int(11) UNSIGNED DEFAULT NULL,
  `logo_image` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `location_country` varchar(255) DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `category_id`, `logo_image`, `contact_person`, `phone`, `location_country`, `is_visible`) VALUES
(1, 'Trina', 1, NULL, NULL, NULL, NULL, 0),
(2, 'JA Solar', 1, NULL, NULL, NULL, NULL, 0),
(3, 'Aiko', 1, NULL, NULL, NULL, NULL, 0),
(4, 'lvtopsun', 1, NULL, NULL, NULL, NULL, 0),
(6, 'longi', 2, NULL, NULL, NULL, NULL, 0),
(7, 'Pylontech', 2, 'logo_1780458046_6a1fa23e44401.png', 'Janvier Erickson Araque', '092353364411', 'Cavite', 1),
(8, 'Huawei', 3, 'logo_1780458074_6a1fa25ac8b57.png', '', '', '', 1),
(9, 'Solis', 3, 'logo_1780458092_6a1fa26c13dbd.png', '', '', '', 1),
(10, 'Growatt', 3, NULL, NULL, NULL, NULL, 1),
(11, 'Holymiles', 3, NULL, NULL, NULL, NULL, 1),
(14, 'Suntree', 6, NULL, NULL, NULL, NULL, 0),
(15, 'Universal Brand', 4, NULL, NULL, NULL, NULL, 0),
(16, 'YESEIZN', 6, NULL, NULL, NULL, NULL, 0),
(17, 'REYUN', 6, NULL, NULL, NULL, NULL, 0),
(34, 'Hopewind', 3, 'logo_1780455235_6a1f974308cf9.png', '', '', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `calculator_logs`
--

CREATE TABLE `calculator_logs` (
  `id` int(11) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_type` varchar(100) DEFAULT 'Guest',
  `lead_name` varchar(150) DEFAULT NULL,
  `lead_phone` varchar(50) DEFAULT NULL,
  `lead_email` varchar(150) DEFAULT NULL,
  `bill` decimal(10,2) NOT NULL,
  `system_size` varchar(50) NOT NULL,
  `action` varchar(50) DEFAULT 'calculated',
  `action_label` varchar(100) DEFAULT 'Calculated Only'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calculator_settings`
--

CREATE TABLE `calculator_settings` (
  `id` int(11) NOT NULL,
  `solar_panel_wattage` int(11) NOT NULL DEFAULT 400,
  `kwh_rate` decimal(10,2) NOT NULL DEFAULT 12.00,
  `average_sun_hours` decimal(5,2) NOT NULL DEFAULT 4.50,
  `card1_title` varchar(255) NOT NULL DEFAULT 'REQUIRED SYSTEM (KWP)',
  `card1_icon` varchar(255) NOT NULL DEFAULT 'assets/img/system-size.png',
  `card2_title` varchar(255) NOT NULL DEFAULT 'SOLAR PANELS',
  `card2_icon` varchar(255) NOT NULL DEFAULT 'assets/img/panels.png',
  `card3_title` varchar(255) NOT NULL DEFAULT 'EST. MONTHLY SAVINGS',
  `card3_icon` varchar(255) NOT NULL DEFAULT 'assets/img/monthly-savings.png',
  `card4_title` varchar(255) NOT NULL DEFAULT 'EST. YEARLY SAVINGS',
  `card4_icon` varchar(255) NOT NULL DEFAULT 'assets/img/yearly-savings.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calculator_settings`
--

INSERT INTO `calculator_settings` (`id`, `solar_panel_wattage`, `kwh_rate`, `average_sun_hours`, `card1_title`, `card1_icon`, `card2_title`, `card2_icon`, `card3_title`, `card3_icon`, `card4_title`, `card4_icon`) VALUES
(1, 620, 14.00, 4.00, 'REQUIRED SYSTEM (KWP)', 'uploads/calculator/card1_1780716992_6a2395c02bb50.png', 'SOLAR PANELS', 'assets/img/panels.png', 'EST. MONTHLY SAVINGS', 'assets/img/monthly-savings.png', 'EST. YEARLY SAVINGS', 'assets/img/yearly-savings.png');

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
  `status` enum('new','read','replied') DEFAULT 'new',
  `source` varchar(50) NOT NULL DEFAULT 'Website',
  `monthly_bill` decimal(10,2) DEFAULT NULL,
  `property_type` varchar(50) DEFAULT NULL,
  `roof_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `message`, `created_at`, `status`, `source`, `monthly_bill`, `property_type`, `roof_type`) VALUES
(1, 'Jeciel Duyag', 'jecielmee@gmail.com', '09916676792', 'Hi, I would like to inquire about rent to own or rental prices or packages', '2026-03-06 08:07:08', 'replied', 'Website', NULL, NULL, NULL),
(2, 'Filzen Dela cruz', 'filzen17@gmail.com', '09171211978', 'GOOD DAY!  Request for quotation:\r\n\r\nLocation:\r\nDiliman, QC\r\n\r\nMeralco bill: 8-9K\r\n\r\nResidential grid tie solar panel package', '2026-03-27 08:03:38', 'replied', 'Website', NULL, NULL, NULL),
(3, 'Raflyn Guillermo', 'waynani@yahoo.com', '09328874453', 'Please send me details on 6k hybrid set up on rent to own basis.', '2026-04-04 03:07:23', 'replied', 'Website', NULL, NULL, NULL),
(4, 'John Gabriel Celestial', 'johngabriel.celestial@deped.gov.ph', '09454093122', 'Goodday, my electric bill is up to 3000k, how much is the qoutation on your most affordable price po for solar panel installations po. Need visit or can i just get free qoutations po or the overall total cost receipt ? This is my info\r\n\r\nName: JOHN GABRIEL CELESTIAL\r\nEmail: johngabriel.celestial@deped.gov.ph\r\nTelephone Number: 09454093122\r\nArea/Location: Cluster 1, G.K. European Village, MalacaÃ±ang-dulo, Brgy. Don Bosco, ParaÃ±aque city 1700\r\nRooftype: GI steel\r\n\r\nLandmarks: Near Puregold jr. Betterliving along Dominic Savio street and Immaculate Heart of Mary College-ParaÃ±aque city\r\nTarget Installation Date: MAY 11, 2026', '2026-04-04 04:58:21', 'replied', 'Website', NULL, NULL, NULL),
(5, 'JOHN MICO ALMORADIE', 'micoalmoradie@gmail.com', '0995-267-8859 (If possible, please reach me out on', 'Hi, a blessed day!\r\n\r\nI am inquiring because my family is planning to install a solar power system for our home in the future.\r\n\r\nInstead of choosing one of the packages you offer, we are considering selecting a specific inverter, battery, and solar panels ourselves in order to reduce costs and hopefully achieve a faster ROI. The other materials and installation requirements that are not included in our chosen items may be provided and installed by your solar company.\r\n\r\nWe are also interested in your fully deductible solar site visit/ocular inspection, which is one of the reasons we would like to purchase the supplies and installation from your company. We understand that our final setup may still change depending on the results of the site visit, so this is still open for adjustments.\r\n\r\nFor now, we are initially considering the following items based on what we saw on your website:\r\n\r\n1. SRNE 12kW Single Phase Low Voltage Hybrid Inverter â€” â‚±84,600.00\r\n2. Eenovance 16.07kWh 314Ah MANA 16-D LFP Battery â€” â‚±88,000.00\r\n3. 15 pcs or more Trina Solar Panels 705W â€” â‚±6,630.00 each (Total for 15 pcs: â‚±99,450.00)\r\n\r\nWe are currently located in DasmariÃ±as, Cavite. We look forward to your reply so we can discuss our future solar-powered home with my family. For now, we will wait for your response and any additional recommendations before proceeding to book a site inspection.\r\n\r\nThank you very much, and God bless!', '2026-04-04 06:45:26', 'replied', 'Website', NULL, NULL, NULL),
(6, 'MARILYN V. FERNANDO', 'timmulco@gmail.com', '+63 917 6515 993', 'SOLAR POWER PHILIPPINES\r\n4/F PBB Corporate Center, 1906 Finance Drive, Madrigal Business Park 1, Ayala Alabang, Muntinlupa City, 1780, Philippines\r\n+63 995 394 7379\r\nsolar@solarpower.com.ph\r\nSubject: Solar System Proposal Request (Business Load)\r\n\r\nDear Sir: \r\n\r\nGood day high esteem supplier!\r\n\r\nI am looking for a solar solution for our business with the following details:\r\n\r\nMonthly bill: Ranging from â‚±12,000.00 to â‚±15,000 (~1,250 kWh/month) \r\n\r\nEquipment: \r\nâ€¢	14 desktop computers \r\nâ€¢	1 computer server \r\nâ€¢	4 floor-mounted aircons \r\nâ€¢	printers, ATM, lighting, etc. \r\n\r\nGoal: \r\nâ€¢	Reduce electric bill significantly \r\nâ€¢	Have battery backup for night operation\r\n\r\nI am interested in the following configurations:\r\nâ€¢	10 kW solar + 10â€“12 kWh battery (entry) \r\nâ€¢	15 kW solar + 20â€“24 kWh battery (recommended) \r\nâ€¢	20â€“25 kW solar + 30â€“40 kWh battery (full backup) \r\n\r\nPreferred brands:\r\nâ€¢	Inverter: Deye / Solis / Growatt \r\nâ€¢	Battery: Dyness / Pylontech / BYD \r\nâ€¢	Panels: Jinko / Longi \r\n\r\nPlease provide:\r\nâ€¢	Full system quote \r\nâ€¢	Installation cost \r\nâ€¢	Warranty details \r\nâ€¢	Payback estimate \r\n\r\nLocation: Public Market Center, Poblacion, Bayog, Zamboanga del Sur, Mindanao, Philippines \r\n\r\nHoping for your immediate action.\r\n\r\nMarilyn V. Fernando\r\nManager', '2026-04-09 04:47:43', 'replied', 'Website', NULL, NULL, NULL),
(7, 'Kristine Gaza', 'cookiegaza@gmail.com', '0998 552 3935', 'Inquiring for solar panels for residential home in pasig. Ave 500kwh monthly consumption. Options with and without net metering', '2026-04-12 02:54:10', 'replied', 'Website', NULL, NULL, NULL),
(8, 'Dilbert Dela Cruz', 'bmwworkshop242@gmail.com', '+61416010776', 'Hello mag inquire po Ako Kung available po ang 630watts solar  panel  Jinko.  10-12 PC\'s at  railings . And battery \r\nPwede po ba kami na Lang  ang nagpick up Sa ware house nyo Kung available po .?\r\nYou contact my number po through Wattsap messenger.\r\nSalamat po', '2026-04-13 08:57:26', 'replied', 'Website', NULL, NULL, NULL);

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
-- Table structure for table `estimates`
--

CREATE TABLE `estimates` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `property_type` varchar(50) NOT NULL,
  `complete_address` text NOT NULL,
  `inspection_date` date NOT NULL,
  `monthly_bill` decimal(10,2) NOT NULL,
  `roof_type` varchar(100) NOT NULL,
  `additional_notes` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_applications`
--

CREATE TABLE `loan_applications` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `monthly_bill` decimal(10,2) NOT NULL,
  `meralco_bill_path` varchar(255) NOT NULL,
  `land_title_path` varchar(255) NOT NULL,
  `membership_proof_path` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_applications`
--

INSERT INTO `loan_applications` (`id`, `full_name`, `email_address`, `contact_number`, `monthly_bill`, `meralco_bill_path`, `land_title_path`, `membership_proof_path`, `status`, `created_at`) VALUES
(1, 'janvier', 'janvierericksonaraque@gmail.com', '09706911766', 8000.00, 'uploads/loans/meralco_bill_bb33acc11d35d34f.png', 'uploads/loans/land_title_d11ef628c9a8c4bd.jpg', 'uploads/loans/membership_proof_a392fe43a2204b10.jpg', 'Under Review', '2026-06-06 03:44:31');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `order_reference` varchar(50) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_address` text NOT NULL,
  `customer_city` varchar(150) DEFAULT NULL,
  `items_subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_location` varchar(150) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` varchar(50) DEFAULT 'pending',
  `order_status` varchar(50) DEFAULT 'pending',
  `receipt_path` varchar(255) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `staff_notes` text DEFAULT NULL,
  `current_location` varchar(255) DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sales_channel` varchar(50) DEFAULT 'Website',
  `service_type` varchar(100) DEFAULT 'Site Assessment',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `client_id`, `order_reference`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `customer_city`, `items_subtotal`, `delivery_fee`, `delivery_location`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `receipt_path`, `tracking_number`, `staff_notes`, `current_location`, `estimated_delivery`, `delivered_at`, `created_at`, `sales_channel`, `service_type`, `remarks`) VALUES
(48, NULL, 'ORD-20260606-7936C7', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Poblacion I, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 331200.00, 'instapay', 'pending', 'pending', 'uploads/receipts/RCP-1780725036-6a23b52cbf4fc.png', NULL, NULL, NULL, NULL, NULL, '2026-06-06 05:50:36', 'Website', 'Site Assessment', NULL),
(49, NULL, 'SP-20260625-2CF206', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Panungyan I, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 07:54:15', 'Website', 'Site Assessment', NULL),
(50, NULL, 'SP-20260625-FE1BFE', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Panungyan I, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 07:55:24', 'Website', 'Site Assessment', NULL),
(51, NULL, 'SP-20260625-FDB5E3', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Pinagsanhan I B, Maragondon, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 07:56:56', 'Website', 'Site Assessment', NULL),
(52, NULL, 'SP-20260625-DE954A', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Malainen Bago, Naic, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 07:59:31', 'Website', 'Site Assessment', NULL),
(53, NULL, 'SP-20260625-B11FEC', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Barangay 1 (Pob.), Magallanes, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 08:10:55', 'Website', 'Site Assessment', NULL),
(54, NULL, 'SP-20260625-FD9096', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Barangay II (Pob.), Amadeo, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 08:11:11', 'Website', 'Site Assessment', NULL),
(55, NULL, 'SP-20260625-12FE1A', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Panungyan II, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 08:12:53', 'Website', 'Site Assessment', NULL),
(56, NULL, 'SP-20260625-04C2F6', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Poblacion I, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 08:17:03', 'Website', 'Site Assessment', NULL),
(57, NULL, 'SP-20260625-B0AD90', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Palocpoc II, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 08:18:01', 'Website', 'Site Assessment', NULL),
(58, NULL, 'SP-20260625-C64F13', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Makina, Naic, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 08:22:03', 'Website', 'Site Assessment', NULL),
(59, NULL, 'SP-20260625-70C498', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Poblacion II, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-25 08:39:22', 'Website', 'Site Assessment', NULL),
(60, NULL, 'SP-20260626-123B8E', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Panungyan II, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 00:00:24', 'Website', 'Site Assessment', NULL),
(61, NULL, 'SP-20260626-F1329B', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Patungan, Maragondon, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 00:01:41', 'Website', 'Site Assessment', NULL),
(62, NULL, 'SP-20260626-BAC1B7', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Patungan, Maragondon, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 00:04:02', 'Website', 'Site Assessment', NULL),
(63, NULL, 'SP-20260626-51F04E', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, San Agustin, Magallanes, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 00:04:51', 'Website', 'Site Assessment', NULL),
(64, NULL, 'SP-20260626-536D9C', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, San Agustin, Magallanes, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 00:05:56', 'Website', 'Site Assessment', NULL),
(65, NULL, 'ORD-20260626021334-547076', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Malainen Bago, Naic, Cavite', NULL, 0.00, 0.00, NULL, 25004200.00, 'maya_full', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 00:13:34', 'Website', 'Site Assessment', NULL),
(66, NULL, 'ORD-20260626021341-D1B25E', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Malainen Bago, Naic, Cavite', NULL, 0.00, 0.00, NULL, 25004200.00, 'maya_full', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 00:13:41', 'Website', 'Site Assessment', NULL),
(67, NULL, 'ORD-20260626021442-78DB73', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Malinta, Lasam, Cagayan', NULL, 0.00, 0.00, NULL, 27000.00, 'maya_full', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 00:14:42', 'Website', 'Site Assessment', NULL),
(68, NULL, 'SP-20260626-0C2ABB', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Poblacion I, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 00:15:00', 'Website', 'Site Assessment', NULL),
(69, NULL, 'ORD-20260626030701-3B8626', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Barangay II (Pob.), Amadeo, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya_full', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 01:07:01', 'Website', 'Site Assessment', NULL),
(70, NULL, 'SP-20260626-8EEA9A', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Poblacion I, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 01:07:19', 'Website', 'Site Assessment', NULL),
(71, NULL, 'SP-20260626-A1F493', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Malainen Bago, Naic, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 01:25:44', 'Website', 'Site Assessment', NULL),
(72, NULL, 'ORD-20260626032559-79D245', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Poblacion I, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya_full', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 01:25:59', 'Website', 'Site Assessment', NULL),
(73, NULL, 'ORD-20260626032603-13CBC0', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Poblacion I, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya_full', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 01:26:03', 'Website', 'Site Assessment', NULL),
(74, NULL, 'ORD-20260626032653-9E4A67', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Panungyan II, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya_full', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 01:26:53', 'Website', 'Site Assessment', NULL),
(75, NULL, 'ORD-20260626032713-C341AF', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Panungyan II, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya_full', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 01:27:13', 'Website', 'Site Assessment', NULL),
(76, NULL, 'ORD-20260626032759-7B2CD3', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Makina, Naic, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya_full', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 01:27:59', 'Website', 'Site Assessment', NULL),
(77, NULL, 'ORD-20260626032801-88AF0B', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Makina, Naic, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya_full', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 01:28:01', 'Website', 'Site Assessment', NULL),
(78, NULL, 'ORD-20260626065800-26034B', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Panungyan II, Mendez, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya_full', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 04:58:00', 'Website', 'Site Assessment', NULL),
(79, NULL, 'SP-20260626-3F68F3', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Barangay 2 (Pob.), Magallanes, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 04:58:18', 'Website', 'Site Assessment', NULL),
(80, NULL, 'SP-20260626-C30928', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Barangay 2 (Pob.), Magallanes, Cavite', NULL, 0.00, 0.00, NULL, 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:00:02', 'Website', 'Site Assessment', NULL),
(81, NULL, 'SP-20260626073908-43027E', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, San Agustin, Magallanes, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:39:08', 'Website', 'Site Assessment', NULL),
(82, NULL, 'SP-20260626074321-A45B02', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Busali, Biliran, Biliran', NULL, 25000.00, 2000.00, 'Biliran', 27000.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:43:21', 'Website', 'Site Assessment', NULL),
(83, NULL, 'SP-20260626074709-C93988', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Patungan, Maragondon, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:47:09', 'Website', 'Site Assessment', NULL),
(84, NULL, 'SP-20260626074722-4B5C47', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Panungyan II, Mendez, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:47:22', 'Website', 'Site Assessment', NULL),
(85, NULL, 'SP-20260626074819-ED926B', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Panungyan II, Mendez, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:48:19', 'Website', 'Site Assessment', NULL),
(86, NULL, 'SP-20260626075204-A902C2', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Panungyan I, Mendez, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:52:04', 'Website', 'Site Assessment', NULL),
(87, NULL, 'SP-20260626075226-EF9E9D', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Real de Cacarong, Pandi, Bulacan', NULL, 25000.00, 7000.00, 'Bulacan', 32000.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:52:26', 'Website', 'Site Assessment', NULL),
(88, NULL, 'SP-20260626075227-372A11', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Real de Cacarong, Pandi, Bulacan', NULL, 25000.00, 7000.00, 'Bulacan', 32000.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:52:27', 'Website', 'Site Assessment', NULL),
(89, NULL, 'SP-20260626075228-79D84C', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Real de Cacarong, Pandi, Bulacan', NULL, 25000.00, 7000.00, 'Bulacan', 32000.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:52:28', 'Website', 'Site Assessment', NULL),
(90, NULL, 'SP-20260626075229-7D5AA5', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Real de Cacarong, Pandi, Bulacan', NULL, 25000.00, 7000.00, 'Bulacan', 32000.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:52:29', 'Website', 'Site Assessment', NULL),
(91, NULL, 'SP-20260626075230-158BF3', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Real de Cacarong, Pandi, Bulacan', NULL, 25000.00, 7000.00, 'Bulacan', 32000.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:52:30', 'Website', 'Site Assessment', NULL),
(92, NULL, 'SP-20260626075230-93478C', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Real de Cacarong, Pandi, Bulacan', NULL, 25000.00, 7000.00, 'Bulacan', 32000.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:52:30', 'Website', 'Site Assessment', NULL),
(93, NULL, 'SP-20260626075231-012769', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Real de Cacarong, Pandi, Bulacan', NULL, 25000.00, 7000.00, 'Bulacan', 32000.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:52:31', 'Website', 'Site Assessment', NULL),
(94, NULL, 'SP-20260626075244-87B0E1', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Makina, Naic, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 05:52:44', 'Website', 'Site Assessment', NULL),
(95, NULL, 'SP-20260626080637-B23B97', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Malainen Bago, Naic, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 06:06:37', 'Website', 'Site Assessment', NULL),
(96, NULL, 'SP-20260626080657-C17CC7', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Palocpoc II, Mendez, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 06:06:57', 'Website', 'Site Assessment', NULL),
(97, NULL, 'SP-20260626081128-F66C55', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Malainen Bago, Naic, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 06:11:28', 'Website', 'Site Assessment', NULL),
(98, NULL, 'SP-20260626081144-C01281', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Poblacion I, Mendez, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 06:11:44', 'Website', 'Site Assessment', NULL),
(99, NULL, 'SP-20260626081151-B39519', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Poblacion I, Mendez, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 06:11:51', 'Website', 'Site Assessment', NULL),
(100, NULL, 'SP-20260626081833-4A9C7D', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Makina, Naic, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'Pending Manual Maya Payment', 'Pending Payment', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 06:18:33', 'Website', 'Site Assessment', NULL),
(101, NULL, 'SP-20260626083318-82B6DF', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Panungyan I, Mendez, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 06:33:18', 'Website', 'Site Assessment', NULL),
(102, NULL, 'SP-20260626083324-3A3002', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Panungyan I, Mendez, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 06:33:24', 'Website', 'Site Assessment', NULL),
(103, NULL, 'SP-20260626083357-B233F5', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38, Panungyan II, Mendez, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 06:33:57', 'Website', 'Site Assessment', NULL),
(104, NULL, 'SP-20260626091039-528459', 'Janvier Erickson', 'janvieraraque@gmail.com', '+639706911766', 'Blk 11 lot 38, Poblacion I, Mendez, Cavite', NULL, 25000.00, 4200.00, 'Cavite', 29200.00, 'maya', 'maya_error', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 07:10:39', 'Website', 'Site Assessment', NULL),
(105, NULL, 'SP-20260626103247-8DD895', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38', 'Metro Manila 21-30km', 25000.00, 6000.00, 'Metro Manila 21-30km', 31000.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 02:32:47', 'Website', 'Product Checkout', 'Items subtotal: PHP 25000.00; Delivery: PHP 6000.00; Location: Metro Manila 21-30km'),
(106, NULL, 'SP-20260626104209-E99456', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38', 'Metro Manila 1-5km', 25000.00, 2000.00, 'Metro Manila 1-5km', 27000.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 02:42:09', 'Website', 'Product Checkout', 'Items subtotal: PHP 25000.00; Delivery: PHP 2000.00; Location: Metro Manila 1-5km'),
(107, NULL, 'SP-20260626104428-44B25B', 'Janvier Erickson', 'janvieraraque@gmail.com', '09706911766', 'Blk 11 lot 38', 'Metro Manila 11-20km', 55000.00, 4000.00, 'Metro Manila 11-20km', 59000.00, 'maya', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 02:44:28', 'Website', 'Product Checkout', 'Items subtotal: PHP 55000.00; Delivery: PHP 4000.00; Location: Metro Manila 11-20km');

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
(26, 48, 0, '5kW - Hybrid ', 1, 325000.00, 325000.00),
(27, 49, 241, '750kw', 5, 5000.00, 25000.00),
(28, 50, 241, '750kw', 5, 5000.00, 25000.00),
(29, 51, 241, '750kw', 5, 5000.00, 25000.00),
(30, 52, 241, '750kw', 5, 5000.00, 25000.00),
(31, 53, 241, '750kw', 5, 5000.00, 25000.00),
(32, 54, 241, '750kw', 5, 5000.00, 25000.00),
(33, 55, 241, '750kw', 5, 5000.00, 25000.00),
(34, 56, 241, '750kw', 5, 5000.00, 25000.00),
(35, 57, 241, '750kw', 5, 5000.00, 25000.00),
(36, 58, 241, '750kw', 5, 5000.00, 25000.00),
(37, 59, 241, '750kw', 5, 5000.00, 25000.00),
(38, 60, 241, '750kw', 5, 5000.00, 25000.00),
(39, 61, 241, '750kw', 5, 5000.00, 25000.00),
(40, 62, 241, '750kw', 5, 5000.00, 25000.00),
(41, 63, 241, '750kw', 5, 5000.00, 25000.00),
(42, 64, 241, '750kw', 5, 5000.00, 25000.00),
(43, 65, 242, '580W LVTOPSUN ', 1, 25000000.00, 25000000.00),
(44, 66, 242, '580W LVTOPSUN ', 1, 25000000.00, 25000000.00),
(45, 67, 241, '750kw', 5, 5000.00, 25000.00),
(46, 68, 241, '750kw', 5, 5000.00, 25000.00),
(47, 69, 241, '750kw', 5, 5000.00, 25000.00),
(48, 70, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(49, 71, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(50, 72, 241, '750kw', 5, 5000.00, 25000.00),
(51, 73, 241, '750kw', 5, 5000.00, 25000.00),
(52, 74, 241, '750kw', 5, 5000.00, 25000.00),
(53, 75, 241, '750kw', 5, 5000.00, 25000.00),
(54, 76, 241, '750kw', 5, 5000.00, 25000.00),
(55, 77, 241, '750kw', 5, 5000.00, 25000.00),
(56, 78, 241, '750kw', 5, 5000.00, 25000.00),
(57, 79, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(58, 80, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(59, 81, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(60, 82, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(61, 83, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(62, 84, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(63, 85, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(64, 86, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(65, 87, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(66, 88, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(67, 89, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(68, 90, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(69, 91, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(70, 92, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(71, 93, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(72, 94, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(73, 95, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(74, 96, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(75, 97, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(76, 98, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(77, 99, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(78, 100, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(79, 101, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(80, 102, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(81, 103, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(82, 104, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(83, 105, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(84, 106, 241, 'JA Solar 750kw', 5, 5000.00, 25000.00),
(85, 107, 241, 'JA Solar 750kw', 11, 5000.00, 55000.00);

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
(65, 48, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-06 05:50:36'),
(66, 49, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-25 07:54:15'),
(67, 50, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-25 07:55:24'),
(68, 51, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-25 07:56:56'),
(69, 52, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-25 07:59:31'),
(70, 53, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-25 08:10:55'),
(71, 54, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-25 08:11:11'),
(72, 55, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-25 08:12:53'),
(73, 56, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-25 08:17:03'),
(74, 57, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-25 08:18:01'),
(75, 58, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-25 08:22:03'),
(76, 59, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-25 08:39:22'),
(77, 60, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 00:00:24'),
(78, 61, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 00:01:41'),
(79, 62, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 00:04:02'),
(80, 63, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 00:04:51'),
(81, 64, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 00:05:56'),
(82, 65, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 00:13:34'),
(83, 66, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 00:13:41'),
(84, 67, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 00:14:42'),
(85, 68, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 00:15:00'),
(86, 69, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 01:07:01'),
(87, 70, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 01:07:19'),
(88, 71, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 01:25:44'),
(89, 72, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 01:25:59'),
(90, 73, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 01:26:03'),
(91, 74, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 01:26:53'),
(92, 75, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 01:27:13'),
(93, 76, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 01:27:59'),
(94, 77, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 01:28:01'),
(95, 78, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 04:58:00'),
(96, 79, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 04:58:18'),
(97, 80, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:00:02'),
(98, 81, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:39:08'),
(99, 82, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:43:21'),
(100, 83, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:47:09'),
(101, 84, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:47:22'),
(102, 85, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:48:19'),
(103, 86, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:52:04'),
(104, 87, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:52:26'),
(105, 88, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:52:27'),
(106, 89, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:52:28'),
(107, 90, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:52:29'),
(108, 91, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:52:30'),
(109, 92, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:52:30'),
(110, 93, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:52:31'),
(111, 94, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 05:52:44'),
(112, 95, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 06:06:37'),
(113, 96, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 06:06:57'),
(114, 97, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 06:11:28'),
(115, 98, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 06:11:44'),
(116, 99, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 06:11:51'),
(117, 100, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 06:18:33'),
(118, 101, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 06:33:18'),
(119, 102, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 06:33:24'),
(120, 103, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 06:33:57'),
(121, 104, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 07:10:39'),
(122, 105, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 08:32:47'),
(123, 106, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 08:42:09'),
(124, 107, 'pending', NULL, 'Order has been placed and is awaiting confirmation', NULL, '2026-06-26 08:44:28');

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
-- Table structure for table `portfolio_projects`
--

CREATE TABLE `portfolio_projects` (
  `id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `subtitle` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `system_type` varchar(255) NOT NULL,
  `co2_reduction` varchar(100) NOT NULL,
  `efficiency_rate` varchar(100) NOT NULL,
  `service_type` varchar(100) DEFAULT 'Supply and Install',
  `image_url` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portfolio_projects`
--

INSERT INTO `portfolio_projects` (`id`, `project_name`, `subtitle`, `location`, `system_type`, `co2_reduction`, `efficiency_rate`, `service_type`, `image_url`, `created_at`) VALUES
(2, 'Mr. & Mrs. Banqued Residence', 'asda', 'sadad', '', '', '', 'Preventive Maintenance', '[\"uploads\\/portfolio\\/2\\/main_1779425878.png\",\"uploads\\/portfolio\\/2\\/gallery_1_1779425878.png\",\"uploads\\/portfolio\\/2\\/gallery_2_1779425878.jpg\",\"uploads\\/portfolio\\/2\\/gallery_3_1779425878.png\",\"uploads\\/portfolio\\/2\\/gallery_4_1779425878.png\",\"uploads\\/portfolio\\/2\\/gallery_5_1779425878.jpg\",\"uploads\\/portfolio\\/2\\/gallery_6_1779425878.png\"]', '2026-05-22 04:57:58');

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
  `packageType` enum('On-Grid','Hybrid','Off-Grid') DEFAULT NULL,
  `stockQuantity` int(11) NOT NULL DEFAULT 0,
  `warranty` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `imagePath` varchar(255) NOT NULL,
  `postedByStaffId` int(11) DEFAULT NULL,
  `moq` int(11) NOT NULL DEFAULT 1 COMMENT 'Minimum Order Quantity. Only enforced for Solar Panel and Mounting & Accessories categories.',
  `status` enum('Active','Hidden') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `displayName`, `brandName`, `price`, `category`, `packageType`, `stockQuantity`, `warranty`, `description`, `imagePath`, `postedByStaffId`, `moq`, `status`) VALUES
(241, '750kw', 'JA Solar', 5000.00, 'Panel', NULL, 9999, '5 years', '<p><strong style=\"color: rgb(51, 51, 51);\">750kw bifacial</strong></p><p>VAT Included</p><p><br></p>', 'uploads/products/241/variant_2_6a3b948628568.png', 10, 5, 'Active'),
(242, '580W LVTOPSUN ', 'Package', 25000000.00, 'Package', 'On-Grid', 9999, '5 years', '<p><span style=\"color: rgb(51, 51, 51);\">10 pcs. 620w bifacial</span></p><p><span style=\"color: rgb(51, 51, 51);\">6kW On-Grid Inverter</span></p><p><span style=\"color: rgb(51, 51, 51);\">Supply | Deliver | VAT Included</span></p>', 'uploads/products/242/img_6a3b976b84c34.png', 10, 1, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `product_brand_variants`
--

CREATE TABLE `product_brand_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `variant_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_brand_variants`
--

INSERT INTO `product_brand_variants` (`id`, `product_id`, `brand_id`, `price`, `variant_image`) VALUES
(1, 241, 2, 5000.00, 'uploads/products/241/variant_2_6a3b948628568.png');

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
(327, 241, 'uploads/products/241/img_6a3b94864017e.jpg', '2026-06-24 08:25:42'),
(328, 242, 'uploads/products/242/img_6a3b976b84c34.png', '2026-06-24 08:38:03');

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
-- Table structure for table `solar_irradiance_cache`
--

CREATE TABLE `solar_irradiance_cache` (
  `id` int(11) NOT NULL,
  `location_id` varchar(100) NOT NULL,
  `location_name` varchar(255) DEFAULT NULL,
  `latitude` decimal(8,4) NOT NULL,
  `longitude` decimal(8,4) NOT NULL,
  `month` tinyint(4) NOT NULL,
  `avg_irradiance` decimal(5,2) DEFAULT NULL COMMENT 'kWh/m²/day',
  `peak_sun_hours` decimal(4,2) DEFAULT NULL,
  `production_multiplier` decimal(4,2) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `source` varchar(50) DEFAULT 'static' COMMENT 'nasa-power or static'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `solar_irradiance_cache`
--

INSERT INTO `solar_irradiance_cache` (`id`, `location_id`, `location_name`, `latitude`, `longitude`, `month`, `avg_irradiance`, `peak_sun_hours`, `production_multiplier`, `last_updated`, `source`) VALUES
(1, 'ph_14.60_120.98', 'Manila / NCR', 14.5995, 120.9842, 1, 4.50, 3.83, 0.88, '2026-03-04 07:38:33', 'nasa-power'),
(2, 'ph_14.60_120.98', 'Manila / NCR', 14.5995, 120.9842, 2, 5.24, 4.45, 1.03, '2026-03-04 07:38:33', 'nasa-power'),
(3, 'ph_14.60_120.98', 'Manila / NCR', 14.5995, 120.9842, 3, 6.22, 5.28, 1.22, '2026-03-04 07:38:33', 'nasa-power'),
(4, 'ph_14.60_120.98', 'Manila / NCR', 14.5995, 120.9842, 4, 6.52, 5.54, 1.28, '2026-03-04 07:38:33', 'nasa-power'),
(5, 'ph_14.60_120.98', 'Manila / NCR', 14.5995, 120.9842, 5, 6.28, 5.34, 1.23, '2026-03-04 07:38:33', 'nasa-power'),
(6, 'ph_14.60_120.98', 'Manila / NCR', 14.5995, 120.9842, 6, 5.40, 4.59, 1.06, '2026-03-04 07:38:33', 'nasa-power'),
(7, 'ph_14.60_120.98', 'Manila / NCR', 14.5995, 120.9842, 7, 4.24, 3.61, 0.83, '2026-03-04 07:38:33', 'nasa-power'),
(8, 'ph_14.60_120.98', 'Manila / NCR', 14.5995, 120.9842, 8, 4.97, 4.22, 0.98, '2026-03-04 07:38:33', 'nasa-power'),
(9, 'ph_14.60_120.98', 'Manila / NCR', 14.5995, 120.9842, 9, 4.41, 3.75, 0.87, '2026-03-04 07:38:33', 'nasa-power'),
(10, 'ph_14.60_120.98', 'Manila / NCR', 14.5995, 120.9842, 10, 4.51, 3.83, 0.88, '2026-03-04 07:38:33', 'nasa-power'),
(11, 'ph_14.60_120.98', 'Manila / NCR', 14.5995, 120.9842, 11, 4.77, 4.06, 0.94, '2026-03-04 07:38:33', 'nasa-power'),
(12, 'ph_14.60_120.98', 'Manila / NCR', 14.5995, 120.9842, 12, 4.08, 3.47, 0.80, '2026-03-04 07:38:33', 'nasa-power'),
(13, 'ph_14.27_121.41', 'Laguna', 14.2691, 121.4113, 1, 3.51, 2.98, 0.77, '2026-03-04 07:51:24', 'nasa-power'),
(14, 'ph_14.27_121.41', 'Laguna', 14.2691, 121.4113, 2, 4.30, 3.65, 0.95, '2026-03-04 07:51:24', 'nasa-power'),
(15, 'ph_14.27_121.41', 'Laguna', 14.2691, 121.4113, 3, 5.25, 4.46, 1.16, '2026-03-04 07:51:24', 'nasa-power'),
(16, 'ph_14.27_121.41', 'Laguna', 14.2691, 121.4113, 4, 5.86, 4.98, 1.29, '2026-03-04 07:51:24', 'nasa-power'),
(17, 'ph_14.27_121.41', 'Laguna', 14.2691, 121.4113, 5, 5.79, 4.92, 1.28, '2026-03-04 07:51:24', 'nasa-power'),
(18, 'ph_14.27_121.41', 'Laguna', 14.2691, 121.4113, 6, 5.27, 4.48, 1.16, '2026-03-04 07:51:24', 'nasa-power'),
(19, 'ph_14.27_121.41', 'Laguna', 14.2691, 121.4113, 7, 4.27, 3.63, 0.94, '2026-03-04 07:51:24', 'nasa-power'),
(20, 'ph_14.27_121.41', 'Laguna', 14.2691, 121.4113, 8, 4.83, 4.10, 1.07, '2026-03-04 07:51:24', 'nasa-power'),
(21, 'ph_14.27_121.41', 'Laguna', 14.2691, 121.4113, 9, 4.26, 3.62, 0.94, '2026-03-04 07:51:24', 'nasa-power'),
(22, 'ph_14.27_121.41', 'Laguna', 14.2691, 121.4113, 10, 3.92, 3.33, 0.86, '2026-03-04 07:51:24', 'nasa-power'),
(23, 'ph_14.27_121.41', 'Laguna', 14.2691, 121.4113, 11, 3.91, 3.33, 0.86, '2026-03-04 07:51:24', 'nasa-power'),
(24, 'ph_14.27_121.41', 'Laguna', 14.2691, 121.4113, 12, 3.17, 2.69, 0.70, '2026-03-04 07:51:24', 'nasa-power'),
(25, 'ph_10.32_123.89', 'Cebu City', 10.3157, 123.8854, 1, 4.05, 3.44, 0.81, '2026-03-05 00:55:47', 'nasa-power'),
(26, 'ph_10.32_123.89', 'Cebu City', 10.3157, 123.8854, 2, 4.62, 3.93, 0.93, '2026-03-05 00:55:47', 'nasa-power'),
(27, 'ph_10.32_123.89', 'Cebu City', 10.3157, 123.8854, 3, 5.57, 4.74, 1.12, '2026-03-05 00:55:47', 'nasa-power'),
(28, 'ph_10.32_123.89', 'Cebu City', 10.3157, 123.8854, 4, 5.97, 5.08, 1.20, '2026-03-05 00:55:47', 'nasa-power'),
(29, 'ph_10.32_123.89', 'Cebu City', 10.3157, 123.8854, 5, 5.74, 4.88, 1.15, '2026-03-05 00:55:47', 'nasa-power'),
(30, 'ph_10.32_123.89', 'Cebu City', 10.3157, 123.8854, 6, 5.19, 4.41, 1.04, '2026-03-05 00:55:47', 'nasa-power'),
(31, 'ph_10.32_123.89', 'Cebu City', 10.3157, 123.8854, 7, 4.92, 4.19, 0.99, '2026-03-05 00:55:47', 'nasa-power'),
(32, 'ph_10.32_123.89', 'Cebu City', 10.3157, 123.8854, 8, 5.21, 4.43, 1.05, '2026-03-05 00:55:47', 'nasa-power'),
(33, 'ph_10.32_123.89', 'Cebu City', 10.3157, 123.8854, 9, 4.94, 4.20, 0.99, '2026-03-05 00:55:47', 'nasa-power'),
(34, 'ph_10.32_123.89', 'Cebu City', 10.3157, 123.8854, 10, 4.69, 3.99, 0.94, '2026-03-05 00:55:47', 'nasa-power'),
(35, 'ph_10.32_123.89', 'Cebu City', 10.3157, 123.8854, 11, 4.77, 4.06, 0.96, '2026-03-05 00:55:47', 'nasa-power'),
(36, 'ph_10.32_123.89', 'Cebu City', 10.3157, 123.8854, 12, 3.96, 3.37, 0.80, '2026-03-05 00:55:47', 'nasa-power'),
(37, 'ph_15.19_120.55', 'Clark / Pampanga', 15.1851, 120.5464, 1, 4.64, 3.94, 0.92, '2026-03-05 00:55:48', 'nasa-power'),
(38, 'ph_15.19_120.55', 'Clark / Pampanga', 15.1851, 120.5464, 2, 5.34, 4.54, 1.05, '2026-03-05 00:55:48', 'nasa-power'),
(39, 'ph_15.19_120.55', 'Clark / Pampanga', 15.1851, 120.5464, 3, 6.12, 5.20, 1.21, '2026-03-05 00:55:48', 'nasa-power'),
(40, 'ph_15.19_120.55', 'Clark / Pampanga', 15.1851, 120.5464, 4, 6.28, 5.34, 1.24, '2026-03-05 00:55:48', 'nasa-power'),
(41, 'ph_15.19_120.55', 'Clark / Pampanga', 15.1851, 120.5464, 5, 5.93, 5.04, 1.17, '2026-03-05 00:55:48', 'nasa-power'),
(42, 'ph_15.19_120.55', 'Clark / Pampanga', 15.1851, 120.5464, 6, 5.32, 4.52, 1.05, '2026-03-05 00:55:48', 'nasa-power'),
(43, 'ph_15.19_120.55', 'Clark / Pampanga', 15.1851, 120.5464, 7, 4.34, 3.69, 0.86, '2026-03-05 00:55:48', 'nasa-power'),
(44, 'ph_15.19_120.55', 'Clark / Pampanga', 15.1851, 120.5464, 8, 4.88, 4.15, 0.96, '2026-03-05 00:55:48', 'nasa-power'),
(45, 'ph_15.19_120.55', 'Clark / Pampanga', 15.1851, 120.5464, 9, 4.44, 3.77, 0.88, '2026-03-05 00:55:48', 'nasa-power'),
(46, 'ph_15.19_120.55', 'Clark / Pampanga', 15.1851, 120.5464, 10, 4.54, 3.86, 0.90, '2026-03-05 00:55:48', 'nasa-power'),
(47, 'ph_15.19_120.55', 'Clark / Pampanga', 15.1851, 120.5464, 11, 4.74, 4.03, 0.94, '2026-03-05 00:55:48', 'nasa-power'),
(48, 'ph_15.19_120.55', 'Clark / Pampanga', 15.1851, 120.5464, 12, 4.23, 3.60, 0.84, '2026-03-05 00:55:48', 'nasa-power'),
(49, 'ph_14.60_121.30', 'Rizal', 14.6042, 121.3035, 1, 3.51, 2.98, 0.77, '2026-03-05 00:55:50', 'nasa-power'),
(50, 'ph_14.60_121.30', 'Rizal', 14.6042, 121.3035, 2, 4.30, 3.65, 0.95, '2026-03-05 00:55:50', 'nasa-power'),
(51, 'ph_14.60_121.30', 'Rizal', 14.6042, 121.3035, 3, 5.25, 4.46, 1.16, '2026-03-05 00:55:50', 'nasa-power'),
(52, 'ph_14.60_121.30', 'Rizal', 14.6042, 121.3035, 4, 5.86, 4.98, 1.29, '2026-03-05 00:55:50', 'nasa-power'),
(53, 'ph_14.60_121.30', 'Rizal', 14.6042, 121.3035, 5, 5.79, 4.92, 1.28, '2026-03-05 00:55:50', 'nasa-power'),
(54, 'ph_14.60_121.30', 'Rizal', 14.6042, 121.3035, 6, 5.27, 4.48, 1.16, '2026-03-05 00:55:50', 'nasa-power'),
(55, 'ph_14.60_121.30', 'Rizal', 14.6042, 121.3035, 7, 4.27, 3.63, 0.94, '2026-03-05 00:55:50', 'nasa-power'),
(56, 'ph_14.60_121.30', 'Rizal', 14.6042, 121.3035, 8, 4.83, 4.10, 1.07, '2026-03-05 00:55:50', 'nasa-power'),
(57, 'ph_14.60_121.30', 'Rizal', 14.6042, 121.3035, 9, 4.26, 3.62, 0.94, '2026-03-05 00:55:50', 'nasa-power'),
(58, 'ph_14.60_121.30', 'Rizal', 14.6042, 121.3035, 10, 3.92, 3.33, 0.86, '2026-03-05 00:55:50', 'nasa-power'),
(59, 'ph_14.60_121.30', 'Rizal', 14.6042, 121.3035, 11, 3.91, 3.33, 0.86, '2026-03-05 00:55:50', 'nasa-power'),
(60, 'ph_14.60_121.30', 'Rizal', 14.6042, 121.3035, 12, 3.17, 2.69, 0.70, '2026-03-05 00:55:50', 'nasa-power'),
(61, 'ph_6.12_125.17', 'General Santos', 6.1164, 125.1716, 1, 4.34, 3.69, 0.84, '2026-03-05 00:55:51', 'nasa-power'),
(62, 'ph_6.12_125.17', 'General Santos', 6.1164, 125.1716, 2, 4.81, 4.09, 0.94, '2026-03-05 00:55:51', 'nasa-power'),
(63, 'ph_6.12_125.17', 'General Santos', 6.1164, 125.1716, 3, 5.54, 4.71, 1.08, '2026-03-05 00:55:51', 'nasa-power'),
(64, 'ph_6.12_125.17', 'General Santos', 6.1164, 125.1716, 4, 5.64, 4.80, 1.10, '2026-03-05 00:55:51', 'nasa-power'),
(65, 'ph_6.12_125.17', 'General Santos', 6.1164, 125.1716, 5, 5.36, 4.56, 1.04, '2026-03-05 00:55:51', 'nasa-power'),
(66, 'ph_6.12_125.17', 'General Santos', 6.1164, 125.1716, 6, 5.21, 4.43, 1.01, '2026-03-05 00:55:51', 'nasa-power'),
(67, 'ph_6.12_125.17', 'General Santos', 6.1164, 125.1716, 7, 5.09, 4.33, 0.99, '2026-03-05 00:55:51', 'nasa-power'),
(68, 'ph_6.12_125.17', 'General Santos', 6.1164, 125.1716, 8, 5.38, 4.57, 1.05, '2026-03-05 00:55:51', 'nasa-power'),
(69, 'ph_6.12_125.17', 'General Santos', 6.1164, 125.1716, 9, 5.59, 4.75, 1.09, '2026-03-05 00:55:51', 'nasa-power'),
(70, 'ph_6.12_125.17', 'General Santos', 6.1164, 125.1716, 10, 5.30, 4.51, 1.03, '2026-03-05 00:55:51', 'nasa-power'),
(71, 'ph_6.12_125.17', 'General Santos', 6.1164, 125.1716, 11, 4.97, 4.23, 0.97, '2026-03-05 00:55:51', 'nasa-power'),
(72, 'ph_6.12_125.17', 'General Santos', 6.1164, 125.1716, 12, 4.48, 3.81, 0.87, '2026-03-05 00:55:51', 'nasa-power'),
(73, 'ph_13.62_123.19', 'Naga City', 13.6218, 123.1948, 1, 3.56, 3.03, 0.74, '2026-03-05 00:55:58', 'nasa-power'),
(74, 'ph_13.62_123.19', 'Naga City', 13.6218, 123.1948, 2, 4.53, 3.85, 0.94, '2026-03-05 00:55:58', 'nasa-power'),
(75, 'ph_13.62_123.19', 'Naga City', 13.6218, 123.1948, 3, 5.49, 4.66, 1.14, '2026-03-05 00:55:58', 'nasa-power'),
(76, 'ph_13.62_123.19', 'Naga City', 13.6218, 123.1948, 4, 5.91, 5.02, 1.22, '2026-03-05 00:55:58', 'nasa-power'),
(77, 'ph_13.62_123.19', 'Naga City', 13.6218, 123.1948, 5, 5.98, 5.08, 1.24, '2026-03-05 00:55:58', 'nasa-power'),
(78, 'ph_13.62_123.19', 'Naga City', 13.6218, 123.1948, 6, 5.53, 4.70, 1.15, '2026-03-05 00:55:58', 'nasa-power'),
(79, 'ph_13.62_123.19', 'Naga City', 13.6218, 123.1948, 7, 4.83, 4.10, 1.00, '2026-03-05 00:55:58', 'nasa-power'),
(80, 'ph_13.62_123.19', 'Naga City', 13.6218, 123.1948, 8, 5.16, 4.38, 1.07, '2026-03-05 00:55:58', 'nasa-power'),
(81, 'ph_13.62_123.19', 'Naga City', 13.6218, 123.1948, 9, 4.80, 4.08, 0.99, '2026-03-05 00:55:58', 'nasa-power'),
(82, 'ph_13.62_123.19', 'Naga City', 13.6218, 123.1948, 10, 4.43, 3.77, 0.92, '2026-03-05 00:55:58', 'nasa-power'),
(83, 'ph_13.62_123.19', 'Naga City', 13.6218, 123.1948, 11, 4.09, 3.47, 0.85, '2026-03-05 00:55:58', 'nasa-power'),
(84, 'ph_13.62_123.19', 'Naga City', 13.6218, 123.1948, 12, 3.60, 3.06, 0.75, '2026-03-05 00:55:58', 'nasa-power');

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
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `position` varchar(100) NOT NULL DEFAULT 'Staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `firstName`, `lastName`, `email`, `password`, `contact_number`, `profile_picture`, `created_at`, `status`, `position`) VALUES
(8, 'Princess', 'Tumala', 'princesstumala5@gmail.com', '$2y$10$3Ci', '09184148517', NULL, '2026-01-12 15:17:14', 'Active', 'Staff'),
(9, 'Aico', 'Raymundo', 'raymundoaicomarie@gmail.com', '$2y$10$had', '0947483647', NULL, '2026-01-30 15:20:50', 'Active', 'Staff'),
(10, 'Renz', 'Baniqued', 'janvierericksonaraque@gmail.com', '$2y$10$uIlZuPr0f9MD75qG9DxkhexWh2aMztpY6LHPsuhL211a5VITId9g2', '+639706911766', 'staff_10_1780450472.png', '2026-02-02 07:53:23', 'Active', 'Staff'),
(11, 'Joy', 'Madrigal', 'joymadrigal01@gmail.com', '$2y$10$U0WHtT1yiYU1nmEtHxA.c.gNJSYi3G04A8CFfWC9sVtoqrTSQbPzK', '099999999999', NULL, '2026-02-03 15:46:50', 'Active', 'Staff'),
(13, 'SolarPower', 'Corporation', 'solar@solarpower.com.ph', '$2y$10$/mv5TyiQLZSSBXtaL05AAe4fhiWlj7neF1cAQj/ORiW3KcMytwml.', '099573947379', NULL, '2026-06-02 08:29:48', 'Active', 'Staff');

-- --------------------------------------------------------

--
-- Table structure for table `staff_audit_logs`
--

CREATE TABLE `staff_audit_logs` (
  `id` int(11) NOT NULL,
  `actor_id` int(11) NOT NULL,
  `actor_name` varchar(150) NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `target_name` varchar(150) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_audit_logs`
--

INSERT INTO `staff_audit_logs` (`id`, `actor_id`, `actor_name`, `action`, `target_id`, `target_name`, `ip_address`, `details`, `created_at`) VALUES
(1, 10, 'Janvier Erickson', 'Create Staff', 13, 'SolarPower Corporation', '127.0.0.1', 'Created staff account with email: solar@solarpower.com.ph', '2026-06-02 08:29:48'),
(2, 10, 'Janvier Erickson', 'Edit Staff Info', 13, 'SolarPower Corporation', '127.0.0.1', 'Modified fields: Contact (from \'0288911170\' to \'099573947379\')', '2026-06-02 08:30:12');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'potential_client',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`id`, `email`, `created_at`, `status`, `ip_address`, `user_agent`) VALUES
(1, 'testing_new_subscriber@example.com', '2026-06-22 08:51:28', '', NULL, NULL);

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

-- --------------------------------------------------------

--
-- Table structure for table `supplier_brands`
--

CREATE TABLE `supplier_brands` (
  `id` int(11) NOT NULL,
  `brandName` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_brands`
--

INSERT INTO `supplier_brands` (`id`, `brandName`, `category`, `status`) VALUES
(1, 'longi', 'Battery', 'Active'),
(2, 'Pylontech', 'Battery', 'Active'),
(3, 'Huawei', 'Inverter', 'Active'),
(4, 'Solis', 'Inverter', 'Active'),
(5, 'Growatt', 'Inverter', 'Active'),
(6, 'Holymiles', 'Inverter', 'Active'),
(7, 'Hopewind', 'Inverter', 'Active'),
(8, 'Universal Brand', 'Mounting & Accessories', 'Active'),
(9, 'Trina', 'Panel', 'Active'),
(10, 'JA Solar', 'Panel', 'Active'),
(11, 'Aiko', 'Panel', 'Active'),
(12, 'lvtopsun', 'Panel', 'Active'),
(13, 'Suntree', 'Wiring & Protection', 'Active'),
(14, 'YESEIZN', 'Wiring & Protection', 'Active'),
(15, 'REYUN', 'Wiring & Protection', 'Active');

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
-- Indexes for table `calculator_logs`
--
ALTER TABLE `calculator_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `calculator_settings`
--
ALTER TABLE `calculator_settings`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `estimates`
--
ALTER TABLE `estimates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_applications`
--
ALTER TABLE `loan_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_reference` (`order_reference`),
  ADD KEY `idx_order_reference` (`order_reference`),
  ADD KEY `idx_customer_email` (`customer_email`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_orders_client_id` (`client_id`);

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
-- Indexes for table `portfolio_projects`
--
ALTER TABLE `portfolio_projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_brand_variants`
--
ALTER TABLE `product_brand_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `brand_id` (`brand_id`);

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
-- Indexes for table `solar_irradiance_cache`
--
ALTER TABLE `solar_irradiance_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loc_month` (`location_id`,`month`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff_audit_logs`
--
ALTER TABLE `staff_audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_subscribed_at` (`created_at`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_brands`
--
ALTER TABLE `supplier_brands`
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
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT for table `archived_quotations`
--
ALTER TABLE `archived_quotations`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `archived_suppliers`
--
ALTER TABLE `archived_suppliers`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `calculator_logs`
--
ALTER TABLE `calculator_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `calculator_settings`
--
ALTER TABLE `calculator_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `delivery_locations`
--
ALTER TABLE `delivery_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimates`
--
ALTER TABLE `estimates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_applications`
--
ALTER TABLE `loan_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `order_tracking_history`
--
ALTER TABLE `order_tracking_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `portfolio_projects`
--
ALTER TABLE `portfolio_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;

--
-- AUTO_INCREMENT for table `product_brand_variants`
--
ALTER TABLE `product_brand_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=329;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
-- AUTO_INCREMENT for table `solar_irradiance_cache`
--
ALTER TABLE `solar_irradiance_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `staff_audit_logs`
--
ALTER TABLE `staff_audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `supplier_brands`
--
ALTER TABLE `supplier_brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `brands`
--
ALTER TABLE `brands`
  ADD CONSTRAINT `brands_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_client_id` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `product_brand_variants`
--
ALTER TABLE `product_brand_variants`
  ADD CONSTRAINT `product_brand_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_brand_variants_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `supplier_brands` (`id`) ON DELETE CASCADE;

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
