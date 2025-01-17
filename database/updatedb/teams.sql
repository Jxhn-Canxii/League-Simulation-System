-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2024 at 12:29 AM
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
-- Database: `aasdasd`
--

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teamx` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `acronym` varchar(10) NOT NULL,
  `primary_color` varchar(8) NOT NULL,
  `secondary_color` varchar(8) NOT NULL,
  `league_id` int(11) NOT NULL,
  `conference_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teamx` (`id`, `name`, `acronym`, `primary_color`, `secondary_color`, `league_id`, `conference_id`, `created_at`, `updated_at`) VALUES
(1, 'Lions', 'LIO', '121883', '5F12ED', 1, 1, NULL, NULL),
(2, 'Tigers', 'TIG', 'B4F3F7', 'FADB59', 1, 1, NULL, NULL),
(3, 'Bears', 'BEA', 'A3A79F', '5D8D6C', 1, 1, NULL, NULL),
(4, 'Wolves', 'WOL', '0EA6F5', 'C70FAA', 1, 1, NULL, NULL),
(5, 'Eagles', 'EAG', '517ED2', '56FB1A', 1, 1, NULL, NULL),
(6, 'Falcons', 'FAL', 'EC062E', '2FD182', 1, 1, NULL, NULL),
(7, 'Hawks', 'HAW', '298EB8', 'C4FD33', 1, 1, NULL, NULL),
(8, 'Panthers', 'PAN', '17F2CC', '840783', 1, 1, NULL, '2024-05-25 15:05:57'),
(9, 'Athletics', 'ATH', '19D26F', '7BA4E0', 1, 1, NULL, NULL),
(10, 'Vipers', 'VIP', '8B277A', 'B6BB6F', 1, 1, NULL, NULL),
(11, 'Jaguars', 'JAG', 'C324DC', '102F74', 1, 1, NULL, '2024-04-03 21:36:05'),
(12, 'Dolphins', 'DOL', '4E5B3F', 'C90FB1', 1, 1, NULL, NULL),
(13, 'Rockets', 'RCK', 'E7A0D1', '1FE699', 1, 1, NULL, '2024-04-03 21:32:25'),
(14, 'Braves', 'BRV', '1EA1F8', 'C36858', 1, 1, NULL, '2024-04-03 21:36:27'),
(15, 'Blazers', 'BLZ', '24A795', 'B349AD', 1, 1, NULL, '2024-04-03 21:31:53'),
(16, 'Kings', 'KIN', '3949BF', '6B0548', 1, 1, NULL, NULL),
(17, 'Titans', 'TIT', '095F4F', '88E18A', 1, 2, NULL, NULL),
(18, 'Spartans', 'SPA', 'E301A1', '7CECD4', 1, 2, NULL, NULL),
(19, 'Trojans', 'TRO', 'F37584', '9A38FB', 1, 2, NULL, NULL),
(20, 'Saints', 'SNT', '238D98', '04D461', 1, 2, NULL, '2024-08-04 08:15:59'),
(21, 'Aliens', 'ALN', '6EDACD', '3E5BFD', 1, 2, NULL, '2024-08-04 08:16:38'),
(22, 'Leopards', 'LEO', 'E47150', 'FA591F', 1, 2, NULL, '2024-04-04 18:02:18'),
(23, 'Sabertooths', 'SAB', 'B255CA', 'A8268E', 1, 2, NULL, '2024-04-19 23:52:34'),
(24, 'Spiders', 'SPD', 'A2DC08', '24A66C', 1, 2, NULL, '2024-07-06 12:38:05'),
(25, 'Vikings', 'VIK', '15B69A', 'B7F0F2', 1, 2, NULL, NULL),
(26, 'Crows', 'CRW', '9BDB7F', '9DBEB9', 1, 2, NULL, '2024-04-03 21:08:24'),
(27, 'Royals', 'RYL', '6A55B4', 'E5309A', 1, 2, NULL, '2024-04-03 21:34:47'),
(28, 'Thunders', 'THN', 'CAA202', 'FC1326', 1, 2, NULL, '2024-04-03 21:35:00'),
(29, 'Warriors', 'WAR', '1B970F', '99DC06', 1, 2, NULL, '2024-05-25 15:05:47'),
(30, 'Hellhounds', 'HH', '401E9C', '7EBD10', 1, 2, NULL, '2024-04-03 21:30:33'),
(31, 'Red Fox', 'RF', '20A265', '60E0C4', 1, 2, NULL, '2024-04-03 21:33:29'),
(32, 'Cougars', 'CGR', '6F8577', 'E84CC4', 1, 2, NULL, '2024-04-03 21:31:05'),
(33, 'Waves', 'WAV', '6894BE', '6048E4', 1, 3, NULL, '2024-04-03 21:35:23'),
(34, 'Predators', 'PRD', 'EB682E', '96AEBA', 1, 3, NULL, '2024-04-03 21:36:56'),
(35, 'Trilogy', 'TRI', '88DEDF', 'EEEA57', 1, 3, NULL, '2024-04-03 21:31:38'),
(36, 'Monarchs', 'MON', 'F1A926', 'C4CE32', 1, 3, NULL, '2024-08-04 08:17:44'),
(37, 'Krakens', 'KRK', '155ACC', '4DB89D', 1, 3, NULL, '2024-04-03 21:12:27'),
(38, 'Jets', 'JET', '2F88DF', '87F1FC', 1, 3, NULL, NULL),
(39, 'Northern Stars', 'NS', '9A325F', '492678', 1, 3, NULL, '2024-08-04 08:18:10'),
(40, 'Ninjas', 'NIN', '2C4E99', '85802C', 1, 3, NULL, '2024-04-03 21:13:24'),
(41, 'Dragons', 'DRA', '954019', '00FF92', 1, 3, NULL, NULL),
(42, 'Phoenix', 'PHO', 'F7BDEF', '3B5918', 1, 3, NULL, NULL),
(43, 'Sharks', 'SHA', 'AD988A', '70E379', 1, 3, NULL, '2024-08-04 08:18:49'),
(44, 'Giants', 'GNT', '1EA31E', '04B07D', 1, 3, NULL, '2024-04-03 21:12:44'),
(45, 'Fire', 'FRE', '43E2F0', '8F2962', 1, 3, NULL, '2024-08-04 08:19:21'),
(46, 'Patriots', 'PAT', 'FBB736', '3E51EB', 1, 3, NULL, '2024-08-04 08:19:45'),
(47, 'Aces', 'ACE', '8051DE', '603C57', 1, 3, NULL, '2024-07-09 19:07:39'),
(48, 'Monsters', 'MNT', '8D42F1', '8037B0', 1, 3, NULL, '2024-07-22 21:06:34'),
(49, 'Pirates', 'PIR', '9C4956', 'EB0A23', 1, 4, NULL, NULL),
(50, 'Scorpions', 'SCR', 'F0DBCE', 'ED4BBB', 1, 4, NULL, '2024-04-03 21:16:24'),
(51, 'Enemies', 'ENM', 'C8F4C9', '5D994D', 1, 4, NULL, '2024-04-03 21:23:33'),
(52, 'Reapers', 'RPR', '655052', '38EB92', 1, 4, NULL, '2024-04-03 21:18:44'),
(53, 'Raiders', 'RAI', '7748BE', 'EA72D0', 1, 4, NULL, NULL),
(54, 'Whales', 'WH', '97430E', '430904', 1, 4, NULL, '2024-04-03 21:17:10'),
(55, 'Poseidons', 'POS', 'B04893', '38B57D', 1, 4, NULL, '2024-04-03 21:17:26'),
(56, 'Cyclones', 'CYC', '229BEC', 'A6C3F3', 1, 4, NULL, '2024-08-04 08:20:57'),
(57, 'Force', 'FRC', '391253', '2217F8', 1, 4, NULL, '2024-04-03 21:18:19'),
(58, 'Astronauts', 'AST', '2F487D', '10D336', 1, 4, NULL, '2024-04-03 21:17:50'),
(59, 'Demons', 'DMN', 'C1B875', 'A979F2', 1, 4, NULL, '2024-04-03 21:19:49'),
(60, 'Devils', 'DVL', '8CFFBF', '2FD545', 1, 4, NULL, '2024-07-09 19:08:05'),
(61, 'Bulldogs', 'BD', 'A177A6', '12C948', 1, 4, NULL, '2024-04-03 21:20:50'),
(62, 'Hornets', 'HRN', '966B11', 'D36853', 1, 4, NULL, '2024-04-03 21:22:01'),
(63, 'Rebels', 'RBL', 'F9D71D', '155FBA', 1, 4, NULL, '2024-08-04 08:21:56'),
(64, 'Owls', 'OWL', '35EEC9', '61E96A', 1, 4, NULL, '2024-04-03 21:22:40'),
(65, 'Knights', 'KNI', '80EED1', '57D8CD', 1, 1, '2024-09-01 00:00:04', '2024-09-01 00:00:04'),
(66, 'Strikers', 'STR', '9A7276', '202237', 1, 1, '2024-09-01 00:00:17', '2024-09-01 00:00:17'),
(67, 'Sealions', 'SEA', 'AA9550', 'C6D3C2', 1, 3, '2024-09-01 00:00:31', '2024-09-01 00:01:17'),
(68, 'Dreamers', 'DRM', '0496FA', 'BE911F', 1, 1, '2024-09-01 00:00:43', '2024-09-01 00:00:43'),
(69, 'Bolts', 'BLT', '716ECC', '9504B7', 1, 1, '2024-09-01 00:02:06', '2024-09-01 00:02:06'),
(70, 'Sonics', 'SNC', 'F6B3B9', '782B44', 1, 2, '2024-09-01 00:02:27', '2024-09-01 00:02:27'),
(71, 'Octopus', 'OCT', '217AE0', 'D6063E', 1, 2, '2024-09-01 00:03:40', '2024-09-01 00:03:40'),
(72, 'Ghosts', 'GHO', '717181', '356379', 1, 2, '2024-09-01 00:03:49', '2024-09-01 00:03:49'),
(73, 'Blue Frogs', 'BF', 'EB82F9', '1C6D68', 1, 4, '2024-09-01 00:04:42', '2024-09-01 00:04:42'),
(74, 'Ravens', 'RVN', 'A1F5A2', '40B2E8', 1, 4, '2024-09-01 00:04:53', '2024-09-01 00:04:53'),
(75, 'Electric Eels', 'EEE', 'C5F2A7', '385EDA', 1, 4, '2024-09-01 00:05:03', '2024-09-01 00:05:03'),
(76, 'Peacemakers', 'PM', 'C6EC93', 'CAC5EC', 1, 4, '2024-09-01 00:05:14', '2024-09-01 00:05:14'),
(77, 'Tamaraws', 'TAM', '4956A1', '6BD906', 1, 3, '2024-09-01 00:05:31', '2024-09-01 00:06:34'),
(78, 'Earthquakes', 'EQ', 'F01EFA', '50F238', 1, 3, '2024-09-01 00:06:54', '2024-09-01 00:06:54'),
(79, 'Hurricanes', 'HUR', '4D8B9C', 'EBCE2E', 1, 3, '2024-09-01 00:07:10', '2024-09-01 00:07:10'),
(80, 'Mad Ants', 'MA', 'E85CB3', '26E2C7', 1, 2, '2024-09-01 00:09:29', '2024-09-01 00:09:29');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
