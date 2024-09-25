-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2024 at 02:16 PM
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
-- Database: `league`
--

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `acronym` varchar(10) NOT NULL,
  `league_id` int(11) NOT NULL,
  `conference_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `acronym`, `league_id`, `conference_id`, `created_at`, `updated_at`) VALUES
(1, 'Lions', 'LIO', 1, 1, NULL, NULL),
(2, 'Tigers', 'TIG', 1, 1, NULL, NULL),
(3, 'Bears', 'BEA', 1, 1, NULL, NULL),
(4, 'Wolves', 'WOL', 1, 1, NULL, NULL),
(5, 'Eagles', 'EAG', 1, 1, NULL, NULL),
(6, 'Falcons', 'FAL', 1, 1, NULL, NULL),
(7, 'Hawks', 'HAW', 1, 1, NULL, NULL),
(8, 'Panthers', 'PAN', 1, 1, NULL, '2024-05-25 15:05:57'),
(9, 'Athletics', 'ATH', 1, 1, NULL, NULL),
(10, 'Vipers', 'VIP', 1, 1, NULL, NULL),
(11, 'Jaguars', 'JAG', 1, 1, NULL, '2024-04-03 21:36:05'),
(12, 'Dolphins', 'DOL', 1, 1, NULL, NULL),
(13, 'Rockets', 'RCK', 1, 1, NULL, '2024-04-03 21:32:25'),
(14, 'Braves', 'BRV', 1, 1, NULL, '2024-04-03 21:36:27'),
(15, 'Blazers', 'BLZ', 1, 1, NULL, '2024-04-03 21:31:53'),
(16, 'Kings', 'KIN', 1, 1, NULL, NULL),
(17, 'Titans', 'TIT', 1, 2, NULL, NULL),
(18, 'Spartans', 'SPA', 1, 2, NULL, NULL),
(19, 'Trojans', 'TRO', 1, 2, NULL, NULL),
(20, 'Saints', 'SNT', 1, 2, NULL, '2024-08-04 08:15:59'),
(21, 'Aliens', 'ALN', 1, 2, NULL, '2024-08-04 08:16:38'),
(22, 'Leopards', 'LEO', 1, 2, NULL, '2024-04-04 18:02:18'),
(23, 'Sabertooths', 'SAB', 1, 2, NULL, '2024-04-19 23:52:34'),
(24, 'Spiders', 'SPD', 1, 2, NULL, '2024-07-06 12:38:05'),
(25, 'Vikings', 'VIK', 1, 2, NULL, NULL),
(26, 'Crows', 'CRW', 1, 2, NULL, '2024-04-03 21:08:24'),
(27, 'Royals', 'RYL', 1, 2, NULL, '2024-04-03 21:34:47'),
(28, 'Thunders', 'THN', 1, 2, NULL, '2024-04-03 21:35:00'),
(29, 'Warriors', 'WRM', 1, 2, NULL, '2024-05-25 15:05:47'),
(30, 'Hellhounds', 'HH', 1, 2, NULL, '2024-04-03 21:30:33'),
(31, 'Red Fox', 'RF', 1, 2, NULL, '2024-04-03 21:33:29'),
(32, 'Cougars', 'CGR', 1, 2, NULL, '2024-04-03 21:31:05'),
(33, 'Waves', 'WAV', 1, 3, NULL, '2024-04-03 21:35:23'),
(34, 'Predators', 'PRD', 1, 3, NULL, '2024-04-03 21:36:56'),
(35, 'Trilogy', 'TRI', 1, 3, NULL, '2024-04-03 21:31:38'),
(36, 'Monarchs', 'MON', 1, 3, NULL, '2024-08-04 08:17:44'),
(37, 'Krakens', 'KRK', 1, 3, NULL, '2024-04-03 21:12:27'),
(38, 'Jets', 'JET', 1, 3, NULL, NULL),
(39, 'Northern Stars', 'NS', 1, 3, NULL, '2024-08-04 08:18:10'),
(40, 'Ninjas', 'NIN', 1, 3, NULL, '2024-04-03 21:13:24'),
(41, 'Dragons', 'DRA', 1, 3, NULL, NULL),
(42, 'Phoenix', 'PHO', 1, 3, NULL, NULL),
(43, 'Sharks', 'SHA', 1, 3, NULL, '2024-08-04 08:18:49'),
(44, 'Giants', 'GNT', 1, 3, NULL, '2024-04-03 21:12:44'),
(45, 'Fire', 'FRE', 1, 3, NULL, '2024-08-04 08:19:21'),
(46, 'Patriots', 'PAT', 1, 3, NULL, '2024-08-04 08:19:45'),
(47, 'Aces', 'ACE', 1, 3, NULL, '2024-07-09 19:07:39'),
(48, 'Monsters', 'MNT', 1, 3, NULL, '2024-07-22 21:06:34'),
(49, 'Pirates', 'PIR', 1, 4, NULL, NULL),
(50, 'Scorpions', 'SCR', 1, 4, NULL, '2024-04-03 21:16:24'),
(51, 'Enemies', 'ENM', 1, 4, NULL, '2024-04-03 21:23:33'),
(52, 'Reapers', 'RPR', 1, 4, NULL, '2024-04-03 21:18:44'),
(53, 'Raiders', 'RAI', 1, 4, NULL, NULL),
(54, 'Whales', 'WH', 1, 4, NULL, '2024-04-03 21:17:10'),
(55, 'Poseidons', 'POS', 1, 4, NULL, '2024-04-03 21:17:26'),
(56, 'Cyclones', 'CYC', 1, 4, NULL, '2024-08-04 08:20:57'),
(57, 'Force', 'FRC', 1, 4, NULL, '2024-04-03 21:18:19'),
(58, 'Astronauts', 'AST', 1, 4, NULL, '2024-04-03 21:17:50'),
(59, 'Demons', 'DMN', 1, 4, NULL, '2024-04-03 21:19:49'),
(60, 'Devils', 'DVL', 1, 4, NULL, '2024-07-09 19:08:05'),
(61, 'Bulldogs', 'BD', 1, 4, NULL, '2024-04-03 21:20:50'),
(62, 'Hornets', 'HRN', 1, 4, NULL, '2024-04-03 21:22:01'),
(63, 'Rebels', 'RBL', 1, 4, NULL, '2024-08-04 08:21:56'),
(64, 'Owls', 'OWL', 1, 4, NULL, '2024-04-03 21:22:40'),
(65, 'Knights', 'KNI', 1, 1, '2024-09-01 00:00:04', '2024-09-01 00:00:04'),
(66, 'Strikers', 'STR', 1, 1, '2024-09-01 00:00:17', '2024-09-01 00:00:17'),
(67, 'Sealions', 'SEa', 1, 3, '2024-09-01 00:00:31', '2024-09-01 00:01:17'),
(68, 'Dreamers', 'DRM', 1, 1, '2024-09-01 00:00:43', '2024-09-01 00:00:43'),
(69, 'Bolts', 'BLT', 1, 1, '2024-09-01 00:02:06', '2024-09-01 00:02:06'),
(70, 'Sonics', 'SNC', 1, 2, '2024-09-01 00:02:27', '2024-09-01 00:02:27'),
(71, 'Octopus', 'OCT', 1, 2, '2024-09-01 00:03:40', '2024-09-01 00:03:40'),
(72, 'Ghosts', 'GHO', 1, 2, '2024-09-01 00:03:49', '2024-09-01 00:03:49'),
(73, 'Blue Frogs', 'BF', 1, 4, '2024-09-01 00:04:42', '2024-09-01 00:04:42'),
(74, 'Ravens', 'RVN', 1, 4, '2024-09-01 00:04:53', '2024-09-01 00:04:53'),
(75, 'Electric Eels', 'EEE', 1, 4, '2024-09-01 00:05:03', '2024-09-01 00:05:03'),
(76, 'Peacemakers', 'PM', 1, 4, '2024-09-01 00:05:14', '2024-09-01 00:05:14'),
(77, 'Tamaraws', 'TAM', 1, 3, '2024-09-01 00:05:31', '2024-09-01 00:06:34'),
(78, 'Earthquakes', 'EQ', 1, 3, '2024-09-01 00:06:54', '2024-09-01 00:06:54'),
(79, 'Hurricanes', 'HUR', 1, 3, '2024-09-01 00:07:10', '2024-09-01 00:07:10'),
(80, 'Mad Ants', 'MA', 1, 2, '2024-09-01 00:09:29', '2024-09-01 00:09:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
