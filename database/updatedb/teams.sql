-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 31, 2024 at 04:38 PM
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
-- Database: `asdasd`
--

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `acronym` varchar(3) NOT NULL,
  `league_id` int(11) NOT NULL DEFAULT 0,
  `conference_id` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `acronym`, `league_id`, `conference_id`, `created_at`, `updated_at`) VALUES
(129, 'Lions', 'LIO', 1, 1, NULL, NULL),
(130, 'Tigers', 'TIG', 1, 1, NULL, NULL),
(131, 'Bears', 'BEA', 1, 1, NULL, NULL),
(132, 'Wolves', 'WOL', 1, 1, NULL, NULL),
(133, 'Eagles', 'EAG', 1, 1, NULL, NULL),
(134, 'Falcons', 'FAL', 1, 1, NULL, NULL),
(135, 'Hawks', 'HAW', 1, 1, NULL, NULL),
(136, 'Panthers', 'PAN', 1, 1, NULL, '2024-05-25 23:05:57'),
(137, 'Cobras', 'COB', 1, 2, NULL, NULL),
(138, 'Vipers', 'VIP', 1, 2, NULL, NULL),
(139, 'Greybacks', 'GB', 1, 2, NULL, '2024-04-04 05:36:05'),
(140, 'Rattlers', 'RAT', 1, 2, NULL, NULL),
(141, 'Rockets', 'RCK', 1, 2, NULL, '2024-04-04 05:32:25'),
(142, 'Braves', 'BRV', 1, 2, NULL, '2024-04-04 05:36:27'),
(143, 'Mad Ants', 'MA', 1, 2, NULL, '2024-04-04 05:31:53'),
(144, 'Mambas', 'MAM', 1, 2, NULL, NULL),
(145, 'Titans', 'TIT', 1, 3, NULL, NULL),
(146, 'Spartans', 'SPA', 1, 3, NULL, NULL),
(147, 'Trojans', 'TRO', 1, 3, NULL, NULL),
(148, 'Gladiators', 'GLA', 1, 3, NULL, NULL),
(149, 'Centurions', 'CNT', 1, 3, NULL, NULL),
(150, 'Leopards', 'LEO', 1, 3, NULL, '2024-04-05 02:02:18'),
(151, 'SILVERTOOTHS', 'St', 1, 3, NULL, '2024-04-20 07:52:34'),
(152, 'Shadows', 'SHD', 1, 3, NULL, '2024-07-06 20:38:05'),
(257, 'Islanders', 'ISL', 1, 1, '2024-05-27 05:06:46', '2024-05-27 05:06:46'),
(258, 'Pterodactyls', 'PTE', 1, 2, '2024-05-27 05:07:08', '2024-05-27 05:07:08'),
(259, 'Patriots', 'PTR', 1, 3, '2024-05-27 05:07:25', '2024-05-27 05:07:25');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=323;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
