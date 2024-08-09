-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 09, 2024 at 04:15 AM
-- Server version: 8.0.31
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `liga2`
--

-- --------------------------------------------------------

--
-- Table structure for table `seasons`
--

DROP TABLE IF EXISTS `seasons`;
CREATE TABLE IF NOT EXISTS `seasons` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `league_id` int NOT NULL,
  `type` int NOT NULL,
  `match_type` tinyint NOT NULL,
  `start_playoffs` int NOT NULL DEFAULT '0',
  `is_conference` tinyint(1) NOT NULL DEFAULT '0',
  `status` int NOT NULL,
  `finals_mvp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `finals_winner_id` bigint UNSIGNED DEFAULT NULL,
  `finals_winner_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `finals_winner_score` int DEFAULT NULL,
  `finals_loser_id` bigint UNSIGNED DEFAULT NULL,
  `finals_loser_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `finals_loser_score` int DEFAULT NULL,
  `champion_id` int UNSIGNED DEFAULT NULL,
  `champion_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `weakest_id` int UNSIGNED DEFAULT NULL,
  `weakest_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
