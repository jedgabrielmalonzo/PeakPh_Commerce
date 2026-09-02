-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2026 at 01:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

CREATE DATABASE IF NOT EXISTS `peakph_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `peakph_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `peakph_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_trail`
--

CREATE TABLE `audit_trail` (
  `id` int(11) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) NOT NULL,
  `action` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_email` varchar(150) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_trail`
--

INSERT INTO `audit_trail` (`id`, `table_name`, `record_id`, `action`, `old_values`, `new_values`, `user_id`, `user_email`, `timestamp`, `ip_address`) VALUES
(1, 'users', 3, '', NULL, '{\"registration_time\":\"2025-10-02 03:36:10\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-02 01:36:10', '::1'),
(2, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-02 03:39:02\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-02 01:39:02', '::1'),
(3, 'users', 4, '', NULL, '{\"registration_time\":\"2025-10-02 07:40:45\",\"ip_address\":\"::1\"}', 4, 'helloworld@gmail.com', '2025-10-02 05:40:45', '::1'),
(4, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-02 07:55:52\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-02 05:55:52', '::1'),
(5, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-02 08:08:02\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-02 06:08:02', '::1'),
(6, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-02 08:22:34\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-02 06:22:34', '::1'),
(7, 'users', 5, '', NULL, '{\"registration_time\":\"2025-10-02 11:26:30\",\"ip_address\":\"::1\"}', 5, 'qktipedu@gmail.com', '2025-10-02 09:26:30', '::1'),
(8, 'users', 5, '', NULL, '{\"logout_time\":\"2025-10-09 18:27:37\",\"ip_address\":\"::1\"}', 5, 'qktipedu@gmail.com', '2025-10-09 16:27:37', '::1'),
(9, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-09 19:19:08\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-09 17:19:08', '::1'),
(10, 'users', 3, '', NULL, '{\"logout_time\":\"2025-10-09 19:19:18\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-09 17:19:18', '::1'),
(11, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-09 19:19:36\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-09 17:19:36', '::1'),
(12, 'users', 3, '', NULL, '{\"logout_time\":\"2025-10-09 19:19:47\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-09 17:19:47', '::1'),
(13, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-09 19:20:00\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-09 17:20:00', '::1'),
(14, 'users', 3, '', NULL, '{\"logout_time\":\"2025-10-12 16:51:05\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-12 14:51:05', '::1'),
(15, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-12 16:51:20\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-12 14:51:20', '::1'),
(16, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-12 17:05:23\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-12 15:05:23', '::1'),
(17, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-13 03:03:02\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-13 01:03:02', '::1'),
(18, 'users', 3, '', NULL, '{\"logout_time\":\"2025-10-13 03:19:41\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-13 01:19:41', '::1'),
(19, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-13 03:20:30\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-13 01:20:30', '::1'),
(20, 'users', 3, '', NULL, '{\"logout_time\":\"2025-10-13 03:24:43\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-13 01:24:43', '::1'),
(21, 'users', 6, '', NULL, '{\"registration_time\":\"2025-10-13 03:25:36\",\"ip_address\":\"::1\"}', 6, 'test@example.com', '2025-10-13 01:25:36', '::1'),
(22, 'users', 6, '', NULL, '{\"logout_time\":\"2025-10-13 03:28:16\",\"ip_address\":\"::1\"}', 6, 'test@example.com', '2025-10-13 01:28:16', '::1'),
(23, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-13 03:36:00\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-13 01:36:00', '::1'),
(24, 'users', 3, '', NULL, '{\"logout_time\":\"2025-10-14 04:32:49\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-14 02:32:49', '::1'),
(25, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-14 05:00:37\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-14 03:00:37', '::1'),
(26, 'users', 3, '', NULL, '{\"logout_time\":\"2025-10-30 07:33:39\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-30 06:33:39', '::1'),
(27, 'users', 3, '', NULL, '{\"login_time\":\"2025-10-30 07:36:08\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-10-30 06:36:08', '::1'),
(28, 'users', 3, '', NULL, '{\"logout_time\":\"2025-11-03 14:25:54\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-11-03 13:25:55', '::1'),
(29, 'users', 7, '', NULL, '{\"registration_time\":\"2025-11-03 14:27:11\",\"ip_address\":\"::1\"}', 7, 'soyabean@gmail.com', '2025-11-03 13:27:11', '::1'),
(30, 'users', 7, '', NULL, '{\"logout_time\":\"2025-11-06 03:38:52\",\"ip_address\":\"::1\"}', 7, 'soyabean@gmail.com', '2025-11-06 02:38:52', '::1'),
(31, 'users', 3, '', NULL, '{\"login_time\":\"2025-11-06 04:05:58\",\"ip_address\":\"::1\"}', 3, 'kinrequim@gmail.com', '2025-11-06 03:05:58', '::1'),
(32, 'users', 8, '', NULL, '{\"registration_time\":\"2026-06-02 15:34:41\",\"ip_address\":\"::1\"}', 8, 'kingabrazado04@gmail.com', '2026-06-02 13:34:41', '::1'),
(33, 'users', 8, '', NULL, '{\"logout_time\":\"2026-06-02 15:36:41\",\"ip_address\":\"::1\"}', 8, 'kingabrazado04@gmail.com', '2026-06-02 13:36:41', '::1'),
(34, 'users', 1, '', NULL, '{\"login_time\":\"2026-06-02 15:38:12\",\"ip_address\":\"::1\"}', 1, 'admin@peakph.com', '2026-06-02 13:38:12', '::1'),
(35, 'users', 1, '', NULL, '{\"login_time\":\"2026-06-02 15:38:28\",\"ip_address\":\"::1\"}', 1, 'admin@peakph.com', '2026-06-02 13:38:28', '::1'),
(36, 'users', 1, '', NULL, '{\"login_time\":\"2026-06-02 15:38:41\",\"ip_address\":\"::1\"}', 1, 'admin@peakph.com', '2026-06-02 13:38:41', '::1'),
(37, 'users', 8, '', NULL, '{\"login_time\":\"2026-06-02 15:41:19\",\"ip_address\":\"::1\"}', 8, 'kingabrazado04@gmail.com', '2026-06-02 13:41:19', '::1'),
(38, 'users', 8, '', NULL, '{\"logout_time\":\"2026-06-02 15:41:44\",\"ip_address\":\"::1\"}', 8, 'kingabrazado04@gmail.com', '2026-06-02 13:41:44', '::1'),
(39, 'users', 8, '', NULL, '{\"login_time\":\"2026-06-02 15:42:00\",\"ip_address\":\"::1\"}', 8, 'kingabrazado04@gmail.com', '2026-06-02 13:42:00', '::1'),
(40, 'users', 8, '', NULL, '{\"logout_time\":\"2026-06-02 15:42:08\",\"ip_address\":\"::1\"}', 8, 'kingabrazado04@gmail.com', '2026-06-02 13:42:08', '::1'),
(41, 'users', 8, '', NULL, '{\"login_time\":\"2026-06-02 15:42:24\",\"ip_address\":\"::1\"}', 8, 'kingabrazado04@gmail.com', '2026-06-02 13:42:24', '::1'),
(42, 'users', 8, '', NULL, '{\"logout_time\":\"2026-06-02 15:44:45\",\"ip_address\":\"::1\"}', 8, 'kingabrazado04@gmail.com', '2026-06-02 13:44:45', '::1'),
(43, 'users', 5, '', NULL, '{\"login_time\":\"2026-06-02 15:44:59\",\"ip_address\":\"::1\"}', 5, 'qktipedu@gmail.com', '2026-06-02 13:44:59', '::1'),
(44, 'users', 1, '', NULL, '{\"login_time\":\"2026-06-05 02:01:55\",\"ip_address\":\"::1\"}', 1, 'admin@peakph.com', '2026-06-05 00:01:55', '::1'),
(45, 'users', 8, '', NULL, '{\"login_time\":\"2026-06-05 02:02:19\",\"ip_address\":\"::1\"}', 8, 'kingabrazado04@gmail.com', '2026-06-05 00:02:19', '::1'),
(46, 'users', 8, '', NULL, '{\"logout_time\":\"2026-06-05 02:02:30\",\"ip_address\":\"::1\"}', 8, 'kingabrazado04@gmail.com', '2026-06-05 00:02:30', '::1'),
(47, 'users', 6, '', NULL, '{\"login_time\":\"2026-06-05 02:02:51\",\"ip_address\":\"::1\"}', 6, 'test@example.com', '2026-06-05 00:02:51', '::1'),
(48, 'users', 6, '', NULL, '{\"logout_time\":\"2026-06-05 02:43:27\",\"ip_address\":\"::1\"}', 6, 'test@example.com', '2026-06-05 00:43:27', '::1'),
(49, 'users', 9, '', NULL, '{\"registration_time\":\"2026-06-05 02:46:27\",\"ip_address\":\"::1\"}', 9, 'q@gmail.com', '2026-06-05 00:46:27', '::1'),
(50, 'users', 9, '', NULL, '{\"logout_time\":\"2026-06-05 02:46:54\",\"ip_address\":\"::1\"}', 9, 'q@gmail.com', '2026-06-05 00:46:54', '::1'),
(51, 'users', 9, '', NULL, '{\"login_time\":\"2026-06-05 02:47:12\",\"ip_address\":\"::1\"}', 9, 'q@gmail.com', '2026-06-05 00:47:12', '::1'),
(52, 'users', 9, '', NULL, '{\"logout_time\":\"2026-06-05 02:47:39\",\"ip_address\":\"::1\"}', 9, 'q@gmail.com', '2026-06-05 00:47:39', '::1'),
(53, 'users', 8, '', NULL, '{\"login_time\":\"2026-06-05 17:30:38\",\"ip_address\":\"::1\"}', 8, 'kingabrazado04@gmail.com', '2026-06-05 15:30:38', '::1'),
(54, 'users', 8, '', NULL, '{\"logout_time\":\"2026-06-05 17:30:56\",\"ip_address\":\"::1\"}', 8, 'kingabrazado04@gmail.com', '2026-06-05 15:30:56', '::1'),
(55, 'users', 10, '', NULL, '{\"logout_time\":\"2026-06-07 05:23:09\",\"ip_address\":\"::1\"}', 10, 'dwad@gmail.com', '2026-06-07 03:23:09', '::1'),
(56, 'users', 11, '', NULL, '{\"logout_time\":\"2026-08-29 10:32:54\",\"ip_address\":\"::1\"}', 11, 'hello@gmail.com', '2026-08-29 08:32:54', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `bestsellers`
--

CREATE TABLE `bestsellers` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carousel`
--

CREATE TABLE `carousel` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carousel`
--

INSERT INTO `carousel` (`id`, `title`, `description`, `image_path`, `link_url`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Welcome to PeakPH', 'Your ultimate destination for camping gear', 'Assets/Carousel_Picts/DeaksV2.png', NULL, 1, 1, '2025-09-27 05:59:50', '2025-09-27 05:59:50'),
(2, 'Best Deals Available', 'Check out our amazing deals on camping equipment', 'Assets/Carousel_Picts/Deals.png', NULL, 1, 2, '2025-09-27 05:59:50', '2025-09-27 05:59:50'),
(3, 'Special Vouchers', 'Get exclusive vouchers for your next adventure', 'Assets/Carousel_Picts/Vouchers.png', NULL, 1, 3, '2025-09-27 05:59:50', '2025-09-27 05:59:50'),
(4, 'Welcome to PeakPH', 'Your ultimate destination for camping gear', 'Assets/Carousel_Picts/DeaksV2.png', NULL, 1, 1, '2025-09-28 03:29:15', '2025-09-28 03:29:15'),
(5, 'Best Deals Available', 'Check out our amazing deals on camping equipment', 'Assets/Carousel_Picts/Deals.png', NULL, 1, 2, '2025-09-28 03:29:15', '2025-09-28 03:29:15'),
(6, 'Special Vouchers', 'Get exclusive vouchers for your next adventure', 'Assets/Carousel_Picts/Vouchers.png', NULL, 1, 3, '2025-09-28 03:29:15', '2025-09-28 03:29:15');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `tag` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `additional_images` text DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `dimensions` varchar(255) DEFAULT NULL,
  `weight` varchar(100) DEFAULT NULL,
  `category_details` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `label` varchar(50) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_name`, `price`, `stock`, `tag`, `image`, `description`, `specifications`, `additional_images`, `video_url`, `dimensions`, `weight`, `category_details`, `created_at`, `label`, `updated_at`) VALUES
(4, 'Chopping Board', 120.00, 48, 'cooking', 'uploads/1759201670_Chopping Board.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-18 07:25:42', 'Best Seller', '2025-10-21 12:15:40'),
(10, 'Camping Stove Portable', 650.00, 46, 'cooking', 'uploads/1759201538_NorthHike_CampingStove.png', 'A hightech and portable burner', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-27 05:59:50', 'Best Seller', '2025-11-04 05:00:03'),
(18, 'Outdoor Knife', 400.00, 52, 'cooking', 'uploads/1759204649_Outdoor Knife.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-30 03:57:29', 'Best Seller', '2025-10-21 12:37:52'),
(19, 'Camping Tent Big Tent 6-12 Person Large Tents For Camping Waterproof 8 Person Tent Outdoor Heavy Duty For Family', 200.00, 50, 'tents', 'uploads/1760281418_Camping Tent Big Tent 6-12 Person Large Tents For Camping Waterproof 8 Person Tent Outdoor Heavy Duty For Family.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-12 15:03:38', 'Popular', '2025-10-13 02:30:09'),
(20, 'Outdoor Big Tent', 1250.00, 96, 'tents', 'uploads/1760281503_Hot Selling Outdoor Big Tent Outdoor Camping Waterproof Sun Protection Large Automatic Portable Family Tent.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-12 15:05:03', 'New Arrival', '2026-08-29 08:29:50'),
(21, 'Camping tent - MH100 - 3-person - Fresh', 750.00, 98, 'tents', 'uploads/1760283193_Camping tent - MH100 - 3-person - Fresh.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-12 15:33:13', '', '2025-10-13 23:54:37'),
(22, 'RetroCampingMug', 70.00, 12, 'cooking', 'uploads/1760322770_RetroCampingMug.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-13 02:32:50', '', '2025-10-25 15:27:27'),
(23, 'High-Capacity Alkaline Battery – Emergency & Everyday Use', 87.75, 100, 'emergency', 'uploads/1761374550_battery.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 06:42:30', '', '2025-10-25 06:42:30'),
(24, 'Mini Lensatic Compass – Pocket Navigation Tool', 75.50, 80, 'emergency', 'uploads/1761374626_compass.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 06:43:46', 'New Arrival', '2025-10-25 15:29:04'),
(25, 'Heavy-Duty Silver Duct Tape – Waterproof Repair Roll (48 mm × 10 m)', 250.00, 200, 'emergency', 'uploads/1761374704_duct tape.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 06:45:04', '', '2025-10-25 06:50:25'),
(26, 'Magnesium Fire Starter Rod with Striker & Whistle', 350.00, 120, 'emergency', 'uploads/1761374822_fire starter.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 06:47:02', '', '2025-10-25 06:50:36'),
(27, 'Compact First Aid Kit – 45 Pieces in Red Waterproof Case', 325.00, 250, 'emergency', 'uploads/1761374950_first aid kit.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 06:49:10', '', '2025-10-25 06:51:41'),
(28, '2-Pack Emergency Firestarter Matches Kit', 480.75, 50, 'emergency', 'uploads/1761375087_Firestarter.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 06:51:27', '', '2025-10-25 06:51:27'),
(29, 'LED Tactical Flashlight – High Lumen Aluminum Torch (Rechargeable)', 625.50, 50, 'emergency', 'uploads/1761375139_flashlight.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 06:52:19', 'New Arrival', '2025-10-25 15:29:14'),
(30, 'Hand-Crank Emergency Radio with Flashlight & USB Charger', 1350.00, 24, 'emergency', 'uploads/1761375169_hand crank radio.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 06:52:49', 'Best Seller', '2026-08-29 08:29:50'),
(31, '10,000 mAh Power Bank – Dual USB Portable Charge', 899.00, 80, 'emergency', 'uploads/1761401505_power bank.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 14:11:45', 'New Arrival', '2025-10-25 15:28:56'),
(32, '5 m Outdoor Nylon Rope – Utility Paracord (8 mm Thick)', 280.00, 150, 'emergency', 'uploads/1761401807_rope.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 14:16:47', '', '2025-10-25 14:16:47'),
(33, '12-in-1 Swiss Army Knife Multi-Tool – Stainless Steel12-in-1 Swiss Army Knife Multi-Tool – Stainless Steel', 950.00, 75, 'emergency', 'uploads/1761406249_swiss army knife.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 15:30:49', '', '2025-10-25 15:30:49'),
(35, '10 L Waterproof Dry Bag with Shoulder Strap – Roll-Top Design', 390.00, 87, 'emergency', 'uploads/1761406395_waterproof dry bag.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 15:33:15', '', '2025-10-25 15:33:15'),
(36, 'Survival Whistle with Keychain – 120 dB Dual-Tone', 99.00, 249, 'emergency', 'uploads/1761406425_whistle.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 15:33:45', '', '2025-10-25 15:33:45'),
(37, 'Portable Straw Water Filter – Up to 1,000 L Filtration Capacity', 450.00, 122, 'emergency', 'uploads/1761406511_straw water filter.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 15:35:11', '', '2025-11-04 07:13:44'),
(38, 'Folding Spatulas', 60.00, 197, 'cooking', 'uploads/1761409355_Folding Spatulas.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 16:22:35', '', '2025-11-04 07:13:44'),
(40, 'Camping Tent Big Tent 6-12 Person Large Tents For Camping Waterproof 8 Person Tent Outdoor Heavy Duty For Family', 1250.50, 10, 'tents', 'uploads/1762391959_Camping Tent Big Tent 6-12 Person Large Tents For Camping Waterproof 8 Person Tent Outdoor Heavy Duty For Family.png', 'a cute tent', 'polyester, military grade', '[\"uploads\\/1762391959_0_Camping Tent Big Tent 6-12 Person Large Tents For Camping Waterproof 8 Person Tent Outdoor Heavy Duty For Family.png\"]', NULL, NULL, NULL, NULL, '2025-11-06 01:19:19', '', '2025-11-06 01:19:19');

-- --------------------------------------------------------

--
-- Table structure for table `new_arrivals`
--

CREATE TABLE `new_arrivals` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `shipping_address` text NOT NULL,
  `billing_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_status` enum('Unpaid','Pending','Paid','Failed','Refunded') DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `shipping_address`, `billing_address`, `payment_method`, `order_date`, `updated_at`, `payment_status`) VALUES
(9, 'ORD-2025-05759', 3, 'King Doe', 'kinrequim@gmail.com', '09568809139', 990.80, 'Pending', 'Honey Street 65, Makati City, Antipolo, Batangas 1870, Philippines', 'Honey Street 65, Makati City, Antipolo, Batangas 1870, Philippines', 'cod', '2025-10-21 12:15:40', '2025-10-21 12:15:40', 'Pending'),
(10, 'ORD-2025-55922', 3, 'King Doe', 'kinrequim@gmail.com', '09568809139', 1450.00, 'Pending', 'Honey Street 65, Makati City, Antipolo, Batangas 1870, Philippines', 'Honey Street 65, Makati City, Antipolo, Batangas 1870, Philippines', 'cod', '2025-10-21 12:34:11', '2025-10-21 12:34:11', 'Pending'),
(11, 'ORD-2025-94204', 3, 'Jed Malonzo', 'jedmalonzo@gmail.com', '093545638886', 498.00, 'Pending', '69 DashFord, Infinity Castle Antipolo, Antipolo, Cavite 1870, Philippines', '69 DashFord, Infinity Castle Antipolo, Antipolo, Cavite 1870, Philippines', 'cod', '2025-10-21 12:37:52', '2025-10-21 12:37:52', 'Pending'),
(14, 'ORD-2025-90810', 3, 'King Abrazado', 'kinrequim@gmail.com', '09568809139', 778.00, 'Pending', 'Hello wortld street, Antipolo, Laguna 1870, Philippines', 'Hello wortld street, Antipolo, Laguna 1870, Philippines', 'cod', '2025-10-21 20:12:51', '2025-10-21 20:12:51', 'Pending'),
(15, 'ORD-2025-05684', 3, 'King Doe', 'kinrequim@gmail.com', '09568809139', 128.40, 'Pending', 'Honey Street 65, Makati City, Antipolo, Rizal 1870, Philippines', 'Honey Street 65, Makati City, Antipolo, Rizal 1870, Philippines', 'paymongo_gcash', '2025-10-23 15:49:26', '2025-10-23 15:49:26', 'Unpaid'),
(16, 'ORD-2025-26207', 3, 'John Day', 'kingabrazado04@gmail.com', '0945737293476', 1808.40, 'Pending', '69 DashFord, Infinity Castle Antipolo, Antipolo, Rizal 1870, Philippines', '69 DashFord, Infinity Castle Antipolo, Antipolo, Rizal 1870, Philippines', 'cod', '2025-10-27 06:07:58', '2025-10-27 06:07:58', 'Pending'),
(17, 'ORD-2025-67283', 7, 'Jed Malonzo', 'jedmalonzo8@gmail.com', '09605646344', 621.20, 'Pending', 'Block 4 Lot 8, Antipolo, Rizal 1870, Philippines', 'Block 4 Lot 8, Antipolo, Rizal 1870, Philippines', 'paymongo_gcash', '2025-11-04 07:13:44', '2025-11-04 07:13:44', 'Unpaid'),
(18, 'ORD-2026-88748', 11, 'King Albuen Abrazado', 'kingabrazado04@gmail.com', '09568809139', 2962.00, 'Pending', 'Cluster C, 572, Rizal 1870, Philippines', 'Cluster C, 572, Rizal 1870, Philippines', 'cod', '2026-08-29 08:29:50', '2026-08-29 08:29:51', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `total`) VALUES
(1, 9, 22, 'RetroCampingMug', 1, 70.00, 70.00),
(2, 9, 4, 'Chopping Board', 1, 120.00, 120.00),
(3, 9, 10, 'Camping Stove Portable', 1, 650.00, 650.00),
(4, 10, 20, 'Outdoor Big Tent', 1, 1250.00, 1250.00),
(5, 11, 18, 'Outdoor Knife', 1, 400.00, 400.00),
(8, 14, 10, 'Camping Stove Portable', 1, 650.00, 650.00),
(9, 15, 22, 'RetroCampingMug', 1, 70.00, 70.00),
(10, 16, 37, 'Portable Straw Water Filter – Up to 1,000 L Filtration Capacity', 2, 450.00, 900.00),
(11, 16, 38, 'Folding Spatulas', 2, 60.00, 120.00),
(12, 16, NULL, 'Heavy-Duty Blue Waterproof Tarp – 2 × 3 m Shelter Sheet', 1, 550.00, 550.00),
(13, 17, 38, 'Folding Spatulas', 1, 60.00, 60.00),
(14, 17, 37, 'Portable Straw Water Filter – Up to 1,000 L Filtration Capacity', 1, 450.00, 450.00),
(15, 18, 20, 'Outdoor Big Tent', 1, 1250.00, 1250.00),
(16, 18, 30, 'Hand-Crank Emergency Radio with Flashlight & USB Charger', 1, 1350.00, 1350.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `payment_method` enum('cod','gcash','paymaya','bank_transfer','card','paymongo_gcash','paymongo_card') NOT NULL DEFAULT 'cod',
  `amount` decimal(10,2) NOT NULL,
  `gateway_fee` decimal(10,2) DEFAULT 0.00,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `paymongo_payment_intent_id` varchar(100) DEFAULT NULL,
  `paymongo_source_id` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Processing','Completed','Failed','Refunded','Cancelled') NOT NULL DEFAULT 'Pending',
  `payment_details` text DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `user_id`, `payment_method`, `amount`, `gateway_fee`, `transaction_reference`, `paymongo_payment_intent_id`, `paymongo_source_id`, `status`, `payment_details`, `paid_at`, `created_at`, `updated_at`) VALUES
(3, 9, 3, 'cod', 990.80, 0.00, NULL, NULL, NULL, 'Pending', NULL, NULL, '2025-10-21 12:15:40', '2025-10-21 12:15:40'),
(4, 10, 3, 'cod', 1450.00, 0.00, NULL, NULL, NULL, 'Pending', NULL, NULL, '2025-10-21 12:34:11', '2025-10-21 12:34:11'),
(5, 11, 3, 'cod', 498.00, 0.00, NULL, NULL, NULL, 'Pending', NULL, NULL, '2025-10-21 12:37:52', '2025-10-21 12:37:52'),
(6, 14, 3, 'cod', 778.00, 0.00, NULL, NULL, NULL, 'Pending', NULL, NULL, '2025-10-21 20:12:51', '2025-10-21 20:12:51'),
(7, 16, 3, 'cod', 1808.40, 0.00, NULL, NULL, NULL, 'Pending', NULL, NULL, '2025-10-27 06:07:58', '2025-10-27 06:07:58'),
(8, 18, 11, 'cod', 2962.00, 0.00, NULL, NULL, NULL, 'Pending', NULL, NULL, '2026-08-29 08:29:51', '2026-08-29 08:29:51');

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `status` varchar(30) NOT NULL,
  `gateway_response` text DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paymongo_webhooks`
--

CREATE TABLE `paymongo_webhooks` (
  `id` int(11) NOT NULL,
  `webhook_id` varchar(100) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `payment_intent_id` varchar(100) DEFAULT NULL,
  `source_id` varchar(100) DEFAULT NULL,
  `status` varchar(30) NOT NULL,
  `payload` text NOT NULL,
  `processed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image_path`, `category`, `description`, `stock`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Professional Camping Tent', 2500.00, 'Assets/Gallery_Images/TentSample.jpg', 'tent', 'Professional grade camping tent for extreme weather conditions', 25, 'Active', '2025-09-27 05:59:50', '2025-09-27 05:59:50'),
(2, 'Portable Cooking Set', 750.00, 'Assets/Gallery_Images/CookingGearSample.png', 'cooking', 'Complete portable cooking set for outdoor adventures', 40, 'Active', '2025-09-27 05:59:50', '2025-09-27 05:59:50'),
(3, 'Hiking Backpack Pro', 1800.00, 'Assets/Gallery_Images/HikingBackpackSample.png', 'equipment', 'Professional hiking backpack with advanced features', 15, 'Active', '2025-09-27 05:59:50', '2025-09-27 05:59:50'),
(4, 'Professional Camping Tent', 2500.00, 'Assets/Gallery_Images/TentSample.jpg', 'tent', 'Professional grade camping tent for extreme weather conditions', 25, 'Active', '2025-09-28 03:29:14', '2025-09-28 03:29:14'),
(5, 'Portable Cooking Set', 750.00, 'Assets/Gallery_Images/CookingGearSample.png', 'cooking', 'Complete portable cooking set for outdoor adventures', 40, 'Active', '2025-09-28 03:29:14', '2025-09-28 03:29:14'),
(6, 'Hiking Backpack Pro', 1800.00, 'Assets/Gallery_Images/HikingBackpackSample.png', 'equipment', 'Professional hiking backpack with advanced features', 15, 'Active', '2025-09-28 03:29:14', '2025-09-28 03:29:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('User','Admin') NOT NULL DEFAULT 'User',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@peakph.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Active', '2025-09-28 03:29:14', '2025-09-28 03:29:14'),
(2, 'admin2', 'admin2@peakph.com', '$2y$10$IAsfjpAscOQKNbWXgoGxoOzG2rbzbTPkkWXKJ2LupWJvkTyQRqUQ2', 'Admin', 'Active', '2025-09-28 20:18:01', '2025-09-28 20:18:01'),
(3, 'King Albuen C. Abrazado', 'kinrequim@gmail.com', '$2y$10$sSrETYsq7xEQNK8xuh0gDuSjdnxnh5wNwYczxwYOzAGNystcPf6YW', 'User', 'Active', '2025-10-02 01:36:10', '2025-10-02 01:36:10'),
(4, 'Isaac', 'helloworld@gmail.com', '$2y$10$fpeIwHDuQ/ZoBTOzJZPvsOmcCeeCCxTnEFZSOiqAIA1HPWkueREte', 'User', 'Active', '2025-10-02 05:40:45', '2025-10-02 05:40:45'),
(5, 'Jed Day', 'qktipedu@gmail.com', '$2y$10$sebzRm/jhF7n0akOkMvU3eWdiD4U3VDnXC13Hum34uCb0IBCsuQM2', 'User', 'Active', '2025-10-02 09:26:30', '2025-10-02 09:26:30'),
(6, 'Testuser', 'test@example.com', '$2y$10$jTgYsS4chafp8u00ZRSN3O7JF/j0VY47s2efRxd3fEOwbEmUYQQSe', 'User', 'Active', '2025-10-13 01:25:36', '2025-10-13 01:25:36'),
(7, 'king soya', 'soyabean@gmail.com', '$2y$10$tdY7nI8Em6e/A5GgY6aOcuPf2FORargTq8qKYZMmSh7p9x6RzhO3G', 'User', 'Active', '2025-11-03 13:27:11', '2025-11-03 13:27:11'),
(8, 'King', 'kingabrazado04@gmail.com', '$2y$10$A7J2WTEdU7HbnGTGjfELxuyo2cv7Qad2OR2Bmiv9/zN2hut.c04ki', 'User', 'Active', '2026-06-02 13:34:41', '2026-06-02 13:34:41'),
(9, 'Isaac', 'q@gmail.com', '$2y$10$6FLYjFgrmW7eZOnnK82nKOMdbn26NBKuT96hlDRB0Q9.OqMS0V48q', 'User', 'Active', '2026-06-05 00:46:27', '2026-06-05 00:46:27'),
(10, 'dwad@gmail.com', 'dwad@gmail.com', 'dwad@gmail.com', 'User', 'Active', '2026-06-07 03:22:26', '2026-06-07 03:22:26'),
(11, 'hello@gmail.com', 'hello@gmail.com', 'hello@gmail.com', 'User', 'Active', '2026-08-29 08:26:21', '2026-08-29 08:26:21');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `shipping_address_2` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_province` varchar(100) DEFAULT NULL,
  `shipping_postal_code` varchar(10) DEFAULT NULL,
  `shipping_country` varchar(100) DEFAULT 'Philippines',
  `map_latitude` decimal(10,8) DEFAULT NULL,
  `map_longitude` decimal(11,8) DEFAULT NULL,
  `map_address` text DEFAULT NULL,
  `billing_same_as_shipping` tinyint(1) DEFAULT 1,
  `billing_address` text DEFAULT NULL,
  `billing_address_2` varchar(255) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_province` varchar(100) DEFAULT NULL,
  `billing_postal_code` varchar(10) DEFAULT NULL,
  `billing_country` varchar(100) DEFAULT 'Philippines',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `first_name`, `last_name`, `phone`, `shipping_address`, `shipping_address_2`, `shipping_city`, `shipping_province`, `shipping_postal_code`, `shipping_country`, `map_latitude`, `map_longitude`, `map_address`, `billing_same_as_shipping`, `billing_address`, `billing_address_2`, `billing_city`, `billing_province`, `billing_postal_code`, `billing_country`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, NULL, '09568809139', 'Cluster C, Lot-14 , Bagong Nayon, Antipolo City', 'Alma\'s Store', 'Antipolo', 'Rizal', '1870', 'Philippines', NULL, NULL, '', 1, '', '', '', '', '', 'Philippines', '2025-10-22 01:37:09', '2025-10-22 01:37:09'),
(2, 7, NULL, NULL, '09568809139', 'Honey Street 65, Makati City', 'Alma\'s Store', 'Antipolo', 'Rizal', '1870', 'Philippines', NULL, NULL, '', 1, '', '', '', '', '', 'Philippines', '2025-11-05 23:23:49', '2025-11-05 23:23:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_table_record` (`table_name`,`record_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `bestsellers`
--
ALTER TABLE `bestsellers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `carousel`
--
ALTER TABLE `carousel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inventory_tag` (`tag`),
  ADD KEY `idx_inventory_stock` (`stock`),
  ADD KEY `idx_inventory_created` (`created_at`);

--
-- Indexes for table `new_arrivals`
--
ALTER TABLE `new_arrivals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_date` (`order_date`),
  ADD KEY `idx_orders_payment_status` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_items_product_fk` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_payments_order` (`order_id`),
  ADD KEY `idx_payments_status` (`status`),
  ADD KEY `idx_payments_method` (`payment_method`),
  ADD KEY `idx_payments_reference` (`transaction_reference`),
  ADD KEY `idx_payments_intent` (`paymongo_payment_intent_id`),
  ADD KEY `idx_payments_source` (`paymongo_source_id`);

--
-- Indexes for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_logs_payment` (`payment_id`),
  ADD KEY `idx_payment_logs_order` (`order_id`),
  ADD KEY `idx_payment_logs_status` (`status`),
  ADD KEY `idx_payment_logs_created` (`created_at`);

--
-- Indexes for table `paymongo_webhooks`
--
ALTER TABLE `paymongo_webhooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_webhook_intent` (`payment_intent_id`),
  ADD KEY `idx_webhook_status` (`status`),
  ADD KEY `idx_webhook_processed` (`processed`),
  ADD KEY `idx_webhook_created` (`created_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category` (`category`),
  ADD KEY `idx_products_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user_profiles_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_trail`
--
ALTER TABLE `audit_trail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `bestsellers`
--
ALTER TABLE `bestsellers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carousel`
--
ALTER TABLE `carousel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `new_arrivals`
--
ALTER TABLE `new_arrivals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paymongo_webhooks`
--
ALTER TABLE `paymongo_webhooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bestsellers`
--
ALTER TABLE `bestsellers`
  ADD CONSTRAINT `bestsellers_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `new_arrivals`
--
ALTER TABLE `new_arrivals`
  ADD CONSTRAINT `new_arrivals_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_fk` FOREIGN KEY (`product_id`) REFERENCES `inventory` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `fk_user_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
