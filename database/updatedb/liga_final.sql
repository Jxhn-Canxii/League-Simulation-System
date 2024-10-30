-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2024 at 10:41 AM
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
-- Database: `liga2`
--

-- --------------------------------------------------------

--
-- Table structure for table `conferences`
--

CREATE TABLE `conferences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `league_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conferences`
--

INSERT INTO `conferences` (`id`, `name`, `league_id`, `created_at`, `updated_at`) VALUES
(1, 'West', 1, '2024-04-02 02:44:27', '2024-04-02 02:44:27'),
(2, 'East', 1, '2024-04-02 02:50:23', '2024-04-02 02:50:23'),
(3, 'North', 1, NULL, NULL),
(4, 'South', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `drafts`
--

CREATE TABLE `drafts` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `round` int(11) NOT NULL,
  `pick_number` int(11) NOT NULL,
  `draft_status` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leagues`
--

CREATE TABLE `leagues` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_conference` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leagues`
--

INSERT INTO `leagues` (`id`, `name`, `is_conference`, `created_at`, `updated_at`) VALUES
(1, 'Liga Uno', 0, '2024-04-02 02:08:32', '2024-04-02 02:08:32');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2024_03_25_104443_create_seasons_table', 1),
(6, 'create_conferences_table', 1),
(7, 'create_leagues', 1),
(8, 'create_schedules_table', 2),
(9, 'create_teams', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `address` mediumtext NOT NULL,
  `team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `contract_years` int(11) DEFAULT 1,
  `contract_expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_rookie` tinyint(1) NOT NULL DEFAULT 1,
  `age` int(11) DEFAULT 0,
  `retirement_age` int(11) DEFAULT 0,
  `injury_prone_percentage` decimal(5,2) DEFAULT 0.00,
  `role` varchar(255) DEFAULT 'bench',
  `shooting_rating` decimal(5,2) DEFAULT 0.00,
  `defense_rating` decimal(5,2) DEFAULT 0.00,
  `passing_rating` decimal(5,2) DEFAULT 0.00,
  `rebounding_rating` decimal(5,2) DEFAULT 0.00,
  `type` varchar(255) DEFAULT NULL,
  `overall_rating` decimal(5,2) DEFAULT 0.00,
  `draft_id` int(11) NOT NULL,
  `draft_order` int(11) NOT NULL,
  `drafted_team_id` int(11) NOT NULL,
  `is_drafted` tinyint(1) NOT NULL DEFAULT 0,
  `draft_status` varchar(255) NOT NULL DEFAULT 'undrafted',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `player_game_stats`
--

CREATE TABLE `player_game_stats` (
  `id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `game_id` varchar(255) NOT NULL,
  `player_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `minutes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `points` int(10) UNSIGNED DEFAULT 0,
  `rebounds` int(10) UNSIGNED DEFAULT 0,
  `assists` int(10) UNSIGNED DEFAULT 0,
  `steals` int(10) UNSIGNED DEFAULT 0,
  `blocks` int(10) UNSIGNED DEFAULT 0,
  `turnovers` int(10) UNSIGNED DEFAULT 0,
  `fouls` int(10) UNSIGNED DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `player_playoff_appearances`
-- (See below for the actual view)
--
CREATE TABLE `player_playoff_appearances` (
`player_id` bigint(20) unsigned
,`player_name` varchar(255)
,`teams_played_for_in_playoffs` mediumtext
,`team_acronyms` mediumtext
,`current_team_name` varchar(255)
,`active_status` tinyint(1)
,`round_of_32_appearances` bigint(21)
,`round_of_16_appearances` bigint(21)
,`quarter_finals_appearances` bigint(21)
,`semi_finals_appearances` bigint(21)
,`interconference_semi_finals_appearances` bigint(21)
,`finals_appearances` bigint(21)
,`total_playoff_appearances` bigint(21)
,`seasons_played_in_playoffs` bigint(21)
,`total_seasons_played` bigint(21)
,`championships_won` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `player_ratings`
--

CREATE TABLE `player_ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `player_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` int(11) NOT NULL DEFAULT 0,
  `season_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(50) NOT NULL,
  `shooting_rating` tinyint(3) UNSIGNED NOT NULL,
  `defense_rating` tinyint(3) UNSIGNED NOT NULL,
  `passing_rating` tinyint(3) UNSIGNED NOT NULL,
  `rebounding_rating` tinyint(3) UNSIGNED NOT NULL,
  `overall_rating` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `player_season_stats`
--

CREATE TABLE `player_season_stats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `player_id` bigint(20) UNSIGNED NOT NULL,
  `season_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `avg_minutes_per_game` decimal(5,2) UNSIGNED DEFAULT 0.00,
  `avg_points_per_game` decimal(5,2) UNSIGNED DEFAULT 0.00,
  `avg_rebounds_per_game` decimal(5,2) UNSIGNED DEFAULT 0.00,
  `avg_assists_per_game` decimal(5,2) UNSIGNED DEFAULT 0.00,
  `avg_steals_per_game` decimal(5,2) UNSIGNED DEFAULT 0.00,
  `avg_blocks_per_game` decimal(5,2) UNSIGNED DEFAULT 0.00,
  `avg_turnovers_per_game` decimal(5,2) UNSIGNED DEFAULT 0.00,
  `avg_fouls_per_game` decimal(5,2) UNSIGNED DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `game_id` varchar(255) NOT NULL,
  `round` varchar(255) NOT NULL,
  `season_id` int(11) NOT NULL,
  `conference_id` int(11) NOT NULL,
  `home_id` int(11) NOT NULL,
  `home_score` int(11) DEFAULT 0,
  `away_id` int(11) NOT NULL,
  `away_score` int(11) DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `schedule_view`
-- (See below for the actual view)
--
CREATE TABLE `schedule_view` (
`id` bigint(20) unsigned
,`game_id` varchar(255)
,`round` varchar(255)
,`season_id` int(11)
,`conference_id` int(11)
,`home_id` int(11)
,`home_score` int(11)
,`away_id` int(11)
,`away_score` int(11)
,`status` int(11)
,`created_at` timestamp
,`updated_at` timestamp
,`home_team_name` varchar(255)
,`away_team_name` varchar(255)
,`season_name` varchar(255)
,`league_name` varchar(255)
,`league_type` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `seasons`
--

CREATE TABLE `seasons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `league_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `match_type` tinyint(4) NOT NULL,
  `start_playoffs` int(11) NOT NULL DEFAULT 0,
  `is_conference` tinyint(1) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL,
  `finals_mvp_id` int(11) NOT NULL DEFAULT 0,
  `finals_mvp` varchar(255) DEFAULT NULL,
  `finals_winner_id` bigint(20) UNSIGNED DEFAULT NULL,
  `finals_winner_name` varchar(255) DEFAULT NULL,
  `finals_winner_score` int(11) DEFAULT NULL,
  `finals_loser_id` bigint(20) UNSIGNED DEFAULT NULL,
  `finals_loser_name` varchar(255) DEFAULT NULL,
  `finals_loser_score` int(11) DEFAULT NULL,
  `west_champion_id` int(10) UNSIGNED DEFAULT NULL,
  `west_champion_name` varchar(255) DEFAULT NULL,
  `east_champion_id` int(10) UNSIGNED DEFAULT NULL,
  `east_champion_name` varchar(255) DEFAULT NULL,
  `north_champion_id` int(10) UNSIGNED DEFAULT NULL,
  `north_champion_name` varchar(255) DEFAULT NULL,
  `south_champion_id` int(10) UNSIGNED DEFAULT NULL,
  `south_champion_name` varchar(255) DEFAULT NULL,
  `champion_id` int(10) UNSIGNED DEFAULT NULL,
  `champion_name` varchar(255) DEFAULT NULL,
  `weakest_id` int(10) UNSIGNED DEFAULT NULL,
  `weakest_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `season_awards`
--

CREATE TABLE `season_awards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `player_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) NOT NULL DEFAULT 0,
  `season_id` bigint(20) UNSIGNED NOT NULL,
  `award_name` varchar(255) NOT NULL,
  `award_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `standings_view`
-- (See below for the actual view)
--
CREATE TABLE `standings_view` (
`team_id` int(11)
,`team_name` varchar(255)
,`team_acronym` varchar(10)
,`conference_id` int(11)
,`conference_name` varchar(255)
,`wins` decimal(22,0)
,`losses` decimal(22,0)
,`total_home_score` decimal(32,0)
,`total_away_score` decimal(32,0)
,`home_ppg` decimal(35,2)
,`away_ppg` decimal(35,2)
,`score_difference` decimal(33,0)
,`season_id` int(11)
,`conference_rank` bigint(21)
,`overall_rank` bigint(21)
,`playoff_appearances` bigint(21)
,`finals_appearances` bigint(21)
,`conference_championships` bigint(21)
,`championships` bigint(21)
,`streak_status` varchar(22)
,`overall_1_rank` decimal(22,0)
,`conference_1_rank` decimal(22,0)
,`is_grandslam` int(1)
);

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

-- --------------------------------------------------------

--
-- Stand-in structure for view `top_10_players_by_team_all_time`
-- (See below for the actual view)
--
CREATE TABLE `top_10_players_by_team_all_time` (
`player_id` bigint(20) unsigned
,`player_name` varchar(255)
,`team_id` int(11)
,`team_name` varchar(255)
,`total_points` decimal(32,0)
,`total_assists` decimal(32,0)
,`total_rebounds` decimal(32,0)
,`total_steals` decimal(32,0)
,`total_blocks` decimal(32,0)
,`total_turnovers` decimal(32,0)
,`championships_won` bigint(21)
,`finals_mvp_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `top_20_players_all_time`
-- (See below for the actual view)
--
CREATE TABLE `top_20_players_all_time` (
`player_id` bigint(20) unsigned
,`player_name` varchar(255)
,`team_name` varchar(255)
,`teams_played` mediumtext
,`total_points` decimal(54,0)
,`total_assists` decimal(54,0)
,`total_rebounds` decimal(54,0)
,`total_steals` decimal(54,0)
,`total_blocks` decimal(54,0)
,`total_turnovers` decimal(54,0)
,`finals_mvp_count` bigint(21)
,`championships_won` bigint(21)
,`is_active` tinyint(1)
);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `details` varchar(255) NOT NULL,
  `from_team_id` int(11) NOT NULL,
  `to_team_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, 'Test User', 'test@example.com', '2024-04-02 05:33:27', '$2y$12$gA2COiA70xvq42adHOh8FeFPVa/yWObn2K62NIEhH5gdvHU59rlrC', 'njYfyd13s0', '2024-04-02 05:33:28', '2024-04-02 05:33:28'),
(3, 'John Canciller', 'greygreygrey35@gmail.com', NULL, '$2y$12$37VsY6TE0e78hykKlw30z.rpGOxKXmRBiFMNJriY3Wc1OwNTcXGAy', 'crrjquUN0VrfH8bIIyum9b1aR7YRHo0saj7D0MBjXISqRNNk9F4n5NxTgIH7', '2024-04-02 05:35:10', '2024-04-02 05:35:10');

-- --------------------------------------------------------

--
-- Structure for view `player_playoff_appearances`
--
DROP TABLE IF EXISTS `player_playoff_appearances`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `player_playoff_appearances`  AS SELECT `p`.`id` AS `player_id`, `p`.`name` AS `player_name`, coalesce(group_concat(distinct `t`.`name` order by `t`.`name` ASC separator ', '),'Free Agent') AS `teams_played_for_in_playoffs`, coalesce(group_concat(distinct `t`.`acronym` order by `t`.`acronym` ASC separator ', '),'N/A') AS `team_acronyms`, coalesce(max(`t2`.`name`),'Free Agent') AS `current_team_name`, `p`.`is_active` AS `active_status`, count(distinct case when `s`.`round` = 'round_of_32' then `s`.`game_id` end) AS `round_of_32_appearances`, count(distinct case when `s`.`round` = 'round_of_16' then `s`.`game_id` end) AS `round_of_16_appearances`, count(distinct case when `s`.`round` = 'quarter_finals' then `s`.`game_id` end) AS `quarter_finals_appearances`, count(distinct case when `s`.`round` = 'semi_finals' then `s`.`game_id` end) AS `semi_finals_appearances`, count(distinct case when `s`.`round` = 'interconference_semi_finals' then `s`.`game_id` end) AS `interconference_semi_finals_appearances`, count(distinct case when `s`.`round` = 'finals' then `s`.`game_id` end) AS `finals_appearances`, count(distinct `s`.`game_id`) AS `total_playoff_appearances`, count(distinct case when `s`.`round` in ('round_of_32','round_of_16','quarter_finals','semi_finals','interconference_semi_finals','finals') then `s`.`season_id` end) AS `seasons_played_in_playoffs`, count(distinct `all_s`.`season_id`) AS `total_seasons_played`, count(distinct case when `s`.`round` = 'finals' and (`pg`.`team_id` = `s`.`home_id` and `s`.`home_score` > `s`.`away_score` or `pg`.`team_id` = `s`.`away_id` and `s`.`away_score` > `s`.`home_score`) then `s`.`game_id` end) AS `championships_won` FROM (((((`players` `p` left join `player_game_stats` `pg` on(`p`.`id` = `pg`.`player_id`)) left join `schedules` `s` on(`pg`.`game_id` = `s`.`game_id`)) left join `teams` `t` on(`pg`.`team_id` = `t`.`id`)) left join `teams` `t2` on(`p`.`team_id` = `t2`.`id`)) left join (select distinct `player_game_stats`.`player_id` AS `player_id`,`player_game_stats`.`season_id` AS `season_id` from `player_game_stats`) `all_s` on(`all_s`.`player_id` = `p`.`id`)) WHERE `s`.`round` in ('round_of_32','round_of_16','quarter_finals','semi_finals','interconference_semi_finals','finals') GROUP BY `p`.`id`, `p`.`name`, `p`.`is_active` ORDER BY count(distinct `s`.`game_id`) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `schedule_view`
--
DROP TABLE IF EXISTS `schedule_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `schedule_view`  AS SELECT `s`.`id` AS `id`, `s`.`game_id` AS `game_id`, `s`.`round` AS `round`, `s`.`season_id` AS `season_id`, `s`.`conference_id` AS `conference_id`, `s`.`home_id` AS `home_id`, `s`.`home_score` AS `home_score`, `s`.`away_id` AS `away_id`, `s`.`away_score` AS `away_score`, `s`.`status` AS `status`, `s`.`created_at` AS `created_at`, `s`.`updated_at` AS `updated_at`, `t_home`.`name` AS `home_team_name`, `t_away`.`name` AS `away_team_name`, `se`.`name` AS `season_name`, `l`.`name` AS `league_name`, `se`.`type` AS `league_type` FROM ((((`schedules` `s` join `teams` `t_home` on(`s`.`home_id` = `t_home`.`id`)) join `teams` `t_away` on(`s`.`away_id` = `t_away`.`id`)) join `seasons` `se` on(`s`.`season_id` = `se`.`id`)) join `leagues` `l` on(`se`.`league_id` = `l`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `standings_view`
--
DROP TABLE IF EXISTS `standings_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `standings_view`  AS WITH team_games AS (SELECT `teams`.`id` AS `team_id`, `teams`.`name` AS `team_name`, `teams`.`acronym` AS `team_acronym`, `teams`.`conference_id` AS `conference_id`, `conferences`.`name` AS `conference_name`, `schedules`.`id` AS `game_id`, `schedules`.`season_id` AS `season_id`, `schedules`.`round` AS `round`, CASE WHEN `schedules`.`home_id` = `teams`.`id` AND `schedules`.`home_score` > `schedules`.`away_score` THEN 'W' WHEN `schedules`.`away_id` = `teams`.`id` AND `schedules`.`away_score` > `schedules`.`home_score` THEN 'W' WHEN `schedules`.`home_id` = `teams`.`id` AND `schedules`.`home_score` < `schedules`.`away_score` THEN 'L' WHEN `schedules`.`away_id` = `teams`.`id` AND `schedules`.`away_score` < `schedules`.`home_score` THEN 'L' ELSE NULL END AS `game_result` FROM ((`teams` left join `schedules` on(`teams`.`id` = `schedules`.`home_id` or `teams`.`id` = `schedules`.`away_id`)) left join `conferences` on(`teams`.`conference_id` = `conferences`.`id`)) WHERE `schedules`.`round` not in ('round_of_32','round_of_16','quarter_finals','semi_finals','interconference_semi_finals','finals')), streaks AS (SELECT `streak_groups`.`team_id` AS `team_id`, `streak_groups`.`season_id` AS `season_id`, `streak_groups`.`game_result` AS `game_result`, `streak_groups`.`round` AS `round`, count(0) AS `streak_length` FROM (select `team_games`.`team_id` AS `team_id`,`team_games`.`season_id` AS `season_id`,`team_games`.`game_result` AS `game_result`,`team_games`.`round` AS `round`,row_number() over ( partition by `team_games`.`team_id`,`team_games`.`season_id` order by `team_games`.`game_id`) - row_number() over ( partition by `team_games`.`team_id`,`team_games`.`season_id`,`team_games`.`game_result` order by `team_games`.`game_id`) AS `streak_id` from `team_games`) AS `streak_groups` WHERE `streak_groups`.`game_result` is not null GROUP BY `streak_groups`.`team_id`, `streak_groups`.`season_id`, `streak_groups`.`game_result`, `streak_groups`.`streak_id`, `streak_groups`.`round`), latest_streak AS (SELECT `ranked_streaks`.`team_id` AS `team_id`, `ranked_streaks`.`season_id` AS `season_id`, `ranked_streaks`.`game_result` AS `game_result`, `ranked_streaks`.`streak_length` AS `streak_length` FROM (select `streaks`.`team_id` AS `team_id`,`streaks`.`season_id` AS `season_id`,`streaks`.`game_result` AS `game_result`,`streaks`.`streak_length` AS `streak_length`,row_number() over ( partition by `streaks`.`team_id`,`streaks`.`season_id` order by `streaks`.`streak_length` desc,`streaks`.`round` desc) AS `rn` from `streaks`) AS `ranked_streaks` WHERE `ranked_streaks`.`rn` = 1), team_rankings AS (SELECT `teams`.`id` AS `team_id`, `teams`.`name` AS `team_name`, `teams`.`acronym` AS `team_acronym`, `teams`.`conference_id` AS `conference_id`, `conferences`.`name` AS `conference_name`, coalesce(sum(case when `schedules`.`home_score` > `schedules`.`away_score` and `schedules`.`home_id` = `teams`.`id` then 1 when `schedules`.`away_score` > `schedules`.`home_score` and `schedules`.`away_id` = `teams`.`id` then 1 else 0 end),0) AS `wins`, coalesce(sum(case when `schedules`.`home_score` < `schedules`.`away_score` and `schedules`.`home_id` = `teams`.`id` then 1 when `schedules`.`away_score` < `schedules`.`home_score` and `schedules`.`away_id` = `teams`.`id` then 1 else 0 end),0) AS `losses`, coalesce(sum(case when `schedules`.`home_id` = `teams`.`id` then `schedules`.`home_score` else 0 end),0) AS `total_home_score`, coalesce(sum(case when `schedules`.`away_id` = `teams`.`id` then `schedules`.`away_score` else 0 end),0) AS `total_away_score`, round(coalesce(sum(case when `schedules`.`home_id` = `teams`.`id` then `schedules`.`home_score` else 0 end),0) / nullif(count(case when `schedules`.`home_id` = `teams`.`id` then 1 end),0),2) AS `home_ppg`, round(coalesce(sum(case when `schedules`.`away_id` = `teams`.`id` then `schedules`.`away_score` else 0 end),0) / nullif(count(case when `schedules`.`away_id` = `teams`.`id` then 1 end),0),2) AS `away_ppg`, abs(coalesce(sum(case when `schedules`.`home_id` = `teams`.`id` then `schedules`.`home_score` - `schedules`.`away_score` when `schedules`.`away_id` = `teams`.`id` then `schedules`.`away_score` - `schedules`.`home_score` else 0 end),0)) AS `score_difference`, `schedules`.`season_id` AS `season_id` FROM ((`teams` left join `schedules` on(`teams`.`id` = `schedules`.`home_id` or `teams`.`id` = `schedules`.`away_id`)) left join `conferences` on(`teams`.`conference_id` = `conferences`.`id`)) WHERE `schedules`.`round` not in ('round_of_32','round_of_16','quarter_finals','semi_finals','interconference_semi_finals','finals') GROUP BY `teams`.`id`, `teams`.`name`, `teams`.`acronym`, `teams`.`conference_id`, `conferences`.`name`, `schedules`.`season_id`), ranked_team_rankings AS (SELECT `team_rankings`.`team_id` AS `team_id`, `team_rankings`.`team_name` AS `team_name`, `team_rankings`.`team_acronym` AS `team_acronym`, `team_rankings`.`conference_id` AS `conference_id`, `team_rankings`.`conference_name` AS `conference_name`, `team_rankings`.`wins` AS `wins`, `team_rankings`.`losses` AS `losses`, `team_rankings`.`total_home_score` AS `total_home_score`, `team_rankings`.`total_away_score` AS `total_away_score`, `team_rankings`.`home_ppg` AS `home_ppg`, `team_rankings`.`away_ppg` AS `away_ppg`, `team_rankings`.`score_difference` AS `score_difference`, `team_rankings`.`season_id` AS `season_id`, rank() over ( partition by `team_rankings`.`season_id`,`team_rankings`.`conference_id` order by `team_rankings`.`wins` desc,`team_rankings`.`score_difference` desc) AS `conference_rank`, rank() over ( partition by `team_rankings`.`season_id` order by `team_rankings`.`wins` desc,`team_rankings`.`score_difference` desc) AS `overall_rank` FROM `team_rankings`), rank_counts AS (SELECT `ranked_team_rankings`.`team_id` AS `team_id`, sum(case when `ranked_team_rankings`.`overall_rank` = 1 then 1 else 0 end) AS `overall_rank`, sum(case when `ranked_team_rankings`.`conference_rank` = 1 then 1 else 0 end) AS `conference_rank` FROM `ranked_team_rankings` GROUP BY `ranked_team_rankings`.`team_id`), playoff_appearances AS (SELECT `teams`.`id` AS `team_id`, count(distinct `schedules`.`season_id`) AS `playoff_appearances` FROM (`teams` join `schedules` on(`teams`.`id` = `schedules`.`home_id` or `teams`.`id` = `schedules`.`away_id`)) WHERE `schedules`.`round` in ('round_of_32','round_of_16','quarter_finals','semi_finals','interconference_semi_finals','finals') GROUP BY `teams`.`id`), finals_appearances AS (SELECT `teams`.`id` AS `team_id`, count(distinct `schedules`.`season_id`) AS `finals_appearances` FROM (`teams` join `schedules` on(`teams`.`id` = `schedules`.`home_id` or `teams`.`id` = `schedules`.`away_id`)) WHERE `schedules`.`round` = 'finals' GROUP BY `teams`.`id`), conference_championships AS (SELECT `teams`.`id` AS `team_id`, count(distinct `schedules`.`season_id`) AS `championships` FROM (`teams` join `schedules` on(`teams`.`id` = `schedules`.`home_id` or `teams`.`id` = `schedules`.`away_id`)) WHERE `schedules`.`round` = 'semi_finals' AND (`schedules`.`home_score` > `schedules`.`away_score` AND `schedules`.`home_id` = `teams`.`id` OR `schedules`.`away_score` > `schedules`.`home_score` AND `schedules`.`away_id` = `teams`.`id`) GROUP BY `teams`.`id`), championships AS (SELECT `teams`.`id` AS `team_id`, count(distinct `schedules`.`season_id`) AS `championships` FROM (`teams` join `schedules` on(`teams`.`id` = `schedules`.`home_id` or `teams`.`id` = `schedules`.`away_id`)) WHERE `schedules`.`round` = 'finals' AND (`schedules`.`home_score` > `schedules`.`away_score` AND `schedules`.`home_id` = `teams`.`id` OR `schedules`.`away_score` > `schedules`.`home_score` AND `schedules`.`away_id` = `teams`.`id`) GROUP BY `teams`.`id`)  SELECT `standings`.`team_id` AS `team_id`, `standings`.`team_name` AS `team_name`, `standings`.`team_acronym` AS `team_acronym`, `standings`.`conference_id` AS `conference_id`, `standings`.`conference_name` AS `conference_name`, `standings`.`wins` AS `wins`, `standings`.`losses` AS `losses`, `standings`.`total_home_score` AS `total_home_score`, `standings`.`total_away_score` AS `total_away_score`, `standings`.`home_ppg` AS `home_ppg`, `standings`.`away_ppg` AS `away_ppg`, `standings`.`score_difference` AS `score_difference`, `standings`.`season_id` AS `season_id`, `standings`.`conference_rank` AS `conference_rank`, `standings`.`overall_rank` AS `overall_rank`, coalesce(`playoff_appearances`.`playoff_appearances`,0) AS `playoff_appearances`, coalesce(`finals_appearances`.`finals_appearances`,0) AS `finals_appearances`, coalesce(`conference_championships`.`championships`,0) AS `conference_championships`, coalesce(`championships`.`championships`,0) AS `championships`, CASE WHEN `latest_streak`.`game_result` = 'W' THEN concat('W',`latest_streak`.`streak_length`) WHEN `latest_streak`.`game_result` = 'L' THEN concat('L',`latest_streak`.`streak_length`) ELSE NULL END AS `streak_status`, coalesce(`rank_counts`.`overall_rank`,0) AS `overall_1_rank`, coalesce(`rank_counts`.`conference_rank`,0) AS `conference_1_rank`, CASE WHEN coalesce(`rank_counts`.`overall_rank`,0) = 1 AND coalesce(`rank_counts`.`conference_rank`,0) = 1 AND coalesce(`conference_championships`.`championships`,0) > 0 AND coalesce(`championships`.`championships`,0) > 0 THEN 1 ELSE 0 END AS `is_grandslam` FROM (((((((select `ranked_team_rankings`.`team_id` AS `team_id`,`ranked_team_rankings`.`team_name` AS `team_name`,`ranked_team_rankings`.`team_acronym` AS `team_acronym`,`ranked_team_rankings`.`conference_id` AS `conference_id`,`ranked_team_rankings`.`conference_name` AS `conference_name`,`ranked_team_rankings`.`wins` AS `wins`,`ranked_team_rankings`.`losses` AS `losses`,`ranked_team_rankings`.`total_home_score` AS `total_home_score`,`ranked_team_rankings`.`total_away_score` AS `total_away_score`,`ranked_team_rankings`.`home_ppg` AS `home_ppg`,`ranked_team_rankings`.`away_ppg` AS `away_ppg`,`ranked_team_rankings`.`score_difference` AS `score_difference`,`ranked_team_rankings`.`season_id` AS `season_id`,`ranked_team_rankings`.`conference_rank` AS `conference_rank`,`ranked_team_rankings`.`overall_rank` AS `overall_rank` from `ranked_team_rankings`) `standings` left join `latest_streak` on(`standings`.`team_id` = `latest_streak`.`team_id` and `standings`.`season_id` = `latest_streak`.`season_id`)) left join `playoff_appearances` on(`standings`.`team_id` = `playoff_appearances`.`team_id`)) left join `finals_appearances` on(`standings`.`team_id` = `finals_appearances`.`team_id`)) left join `conference_championships` on(`standings`.`team_id` = `conference_championships`.`team_id`)) left join `championships` on(`standings`.`team_id` = `championships`.`team_id`)) left join `rank_counts` on(`standings`.`team_id` = `rank_counts`.`team_id`)))  ;

-- --------------------------------------------------------

--
-- Structure for view `top_10_players_by_team_all_time`
--
DROP TABLE IF EXISTS `top_10_players_by_team_all_time`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `top_10_players_by_team_all_time`  AS WITH player_achievements AS (SELECT `p`.`id` AS `player_id`, `p`.`name` AS `player_name`, `t`.`id` AS `team_id`, `t`.`name` AS `team_name`, sum(`pg`.`points`) AS `total_points`, sum(`pg`.`assists`) AS `total_assists`, sum(`pg`.`rebounds`) AS `total_rebounds`, sum(`pg`.`steals`) AS `total_steals`, sum(`pg`.`blocks`) AS `total_blocks`, sum(`pg`.`turnovers`) AS `total_turnovers`, count(distinct case when `s`.`round` = 'finals' and (`s`.`home_id` = `t`.`id` and `s`.`home_score` > `s`.`away_score` or `s`.`away_id` = `t`.`id` and `s`.`away_score` > `s`.`home_score`) then `s`.`id` end) AS `championships_won`, count(distinct case when `se`.`finals_mvp_id` = `p`.`id` then `se`.`id` end) AS `finals_mvp_count` FROM ((((`player_game_stats` `pg` left join `players` `p` on(`pg`.`player_id` = `p`.`id`)) left join `teams` `t` on(`pg`.`team_id` = `t`.`id`)) left join `schedules` `s` on(`pg`.`game_id` = `s`.`game_id`)) left join `seasons` `se` on(`s`.`season_id` = `se`.`id`)) GROUP BY `p`.`id`, `p`.`name`, `t`.`id`, `t`.`name`), ranked_players AS (SELECT `pa`.`player_id` AS `player_id`, `pa`.`player_name` AS `player_name`, `pa`.`team_id` AS `team_id`, `pa`.`team_name` AS `team_name`, `pa`.`total_points` AS `total_points`, `pa`.`total_assists` AS `total_assists`, `pa`.`total_rebounds` AS `total_rebounds`, `pa`.`total_steals` AS `total_steals`, `pa`.`total_blocks` AS `total_blocks`, `pa`.`total_turnovers` AS `total_turnovers`, `pa`.`championships_won` AS `championships_won`, `pa`.`finals_mvp_count` AS `finals_mvp_count`, row_number() over ( partition by `pa`.`team_id` order by `pa`.`total_points` * 1.0 + `pa`.`total_assists` * 0.75 + `pa`.`total_rebounds` * 0.5 + `pa`.`total_steals` * 0.5 + `pa`.`total_blocks` * 0.5 - `pa`.`total_turnovers` * 0.25 desc,`pa`.`championships_won` desc,`pa`.`finals_mvp_count` desc) AS `rank_in_team` FROM `player_achievements` AS `pa`)  SELECT `ranked_players`.`player_id` AS `player_id`, `ranked_players`.`player_name` AS `player_name`, `ranked_players`.`team_id` AS `team_id`, `ranked_players`.`team_name` AS `team_name`, `ranked_players`.`total_points` AS `total_points`, `ranked_players`.`total_assists` AS `total_assists`, `ranked_players`.`total_rebounds` AS `total_rebounds`, `ranked_players`.`total_steals` AS `total_steals`, `ranked_players`.`total_blocks` AS `total_blocks`, `ranked_players`.`total_turnovers` AS `total_turnovers`, `ranked_players`.`championships_won` AS `championships_won`, `ranked_players`.`finals_mvp_count` AS `finals_mvp_count` FROM `ranked_players` WHERE `ranked_players`.`rank_in_team` <= 10 ORDER BY `ranked_players`.`team_id` ASC, `ranked_players`.`rank_in_team` ASC`rank_in_team`  ;

-- --------------------------------------------------------

--
-- Structure for view `top_20_players_all_time`
--
DROP TABLE IF EXISTS `top_20_players_all_time`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `top_20_players_all_time`  AS WITH player_stats AS (SELECT `p`.`id` AS `player_id`, `p`.`name` AS `player_name`, `t`.`name` AS `team_name`, sum(`pg`.`points`) AS `total_points`, sum(`pg`.`assists`) AS `total_assists`, sum(`pg`.`rebounds`) AS `total_rebounds`, sum(`pg`.`steals`) AS `total_steals`, sum(`pg`.`blocks`) AS `total_blocks`, sum(`pg`.`turnovers`) AS `total_turnovers` FROM ((`player_game_stats` `pg` left join `players` `p` on(`pg`.`player_id` = `p`.`id`)) left join `teams` `t` on(`pg`.`team_id` = `t`.`id`)) GROUP BY `p`.`id`, `p`.`name`, `t`.`name`), player_achievements AS (SELECT `p`.`id` AS `player_id`, `p`.`name` AS `player_name`, count(distinct case when `se`.`finals_mvp_id` = `p`.`id` then `se`.`id` end) AS `finals_mvp_count`, count(distinct case when `s`.`round` = 'finals' and (`s`.`home_id` = `t`.`id` and `s`.`home_score` > `s`.`away_score` or `s`.`away_id` = `t`.`id` and `s`.`away_score` > `s`.`home_score`) then `s`.`id` end) AS `championships_won` FROM ((((`player_game_stats` `pg` left join `players` `p` on(`pg`.`player_id` = `p`.`id`)) left join `schedules` `s` on(`pg`.`game_id` = `s`.`game_id`)) left join `seasons` `se` on(`s`.`season_id` = `se`.`id`)) left join `teams` `t` on(`pg`.`team_id` = `t`.`id`)) GROUP BY `p`.`id`, `p`.`name`), player_teams AS (SELECT `p`.`id` AS `player_id`, group_concat(distinct `t`.`name` order by `t`.`name` ASC separator ', ') AS `teams_played` FROM ((`player_game_stats` `pg` left join `players` `p` on(`pg`.`player_id` = `p`.`id`)) left join `teams` `t` on(`pg`.`team_id` = `t`.`id`)) GROUP BY `p`.`id`), player_active_status AS (SELECT `p`.`id` AS `player_id`, `p`.`is_active` AS `is_active` FROM `players` AS `p`), merged_player_data AS (SELECT `ps`.`player_id` AS `player_id`, `ps`.`player_name` AS `player_name`, max(`ps`.`team_name`) AS `team_name`, max(`pt`.`teams_played`) AS `teams_played`, sum(`ps`.`total_points`) AS `total_points`, sum(`ps`.`total_assists`) AS `total_assists`, sum(`ps`.`total_rebounds`) AS `total_rebounds`, sum(`ps`.`total_steals`) AS `total_steals`, sum(`ps`.`total_blocks`) AS `total_blocks`, sum(`ps`.`total_turnovers`) AS `total_turnovers`, max(`pa`.`finals_mvp_count`) AS `finals_mvp_count`, max(`pa`.`championships_won`) AS `championships_won`, max(`pas`.`is_active`) AS `is_active` FROM (((`player_stats` `ps` left join `player_achievements` `pa` on(`ps`.`player_id` = `pa`.`player_id`)) left join `player_teams` `pt` on(`ps`.`player_id` = `pt`.`player_id`)) left join `player_active_status` `pas` on(`ps`.`player_id` = `pas`.`player_id`)) GROUP BY `ps`.`player_id`, `ps`.`player_name`), ranked_players AS (SELECT `merged_player_data`.`player_id` AS `player_id`, `merged_player_data`.`player_name` AS `player_name`, `merged_player_data`.`team_name` AS `team_name`, `merged_player_data`.`teams_played` AS `teams_played`, `merged_player_data`.`total_points` AS `total_points`, `merged_player_data`.`total_assists` AS `total_assists`, `merged_player_data`.`total_rebounds` AS `total_rebounds`, `merged_player_data`.`total_steals` AS `total_steals`, `merged_player_data`.`total_blocks` AS `total_blocks`, `merged_player_data`.`total_turnovers` AS `total_turnovers`, `merged_player_data`.`finals_mvp_count` AS `finals_mvp_count`, `merged_player_data`.`championships_won` AS `championships_won`, `merged_player_data`.`is_active` AS `is_active`, rank() over ( order by `merged_player_data`.`total_points` * 1.0 + `merged_player_data`.`total_assists` * 0.75 + `merged_player_data`.`total_rebounds` * 0.5 + `merged_player_data`.`total_steals` * 0.5 + `merged_player_data`.`total_blocks` * 0.5 - `merged_player_data`.`total_turnovers` * 0.25 desc) AS `stat_rank`, rank() over ( order by `merged_player_data`.`finals_mvp_count` * 1.5 + `merged_player_data`.`championships_won` * 1.0 desc) AS `achievement_rank` FROM `merged_player_data`), combined_ranks AS (SELECT `ranked_players`.`player_id` AS `player_id`, `ranked_players`.`player_name` AS `player_name`, `ranked_players`.`team_name` AS `team_name`, `ranked_players`.`teams_played` AS `teams_played`, `ranked_players`.`total_points` AS `total_points`, `ranked_players`.`total_assists` AS `total_assists`, `ranked_players`.`total_rebounds` AS `total_rebounds`, `ranked_players`.`total_steals` AS `total_steals`, `ranked_players`.`total_blocks` AS `total_blocks`, `ranked_players`.`total_turnovers` AS `total_turnovers`, `ranked_players`.`finals_mvp_count` AS `finals_mvp_count`, `ranked_players`.`championships_won` AS `championships_won`, `ranked_players`.`is_active` AS `is_active`, `ranked_players`.`stat_rank`* 0.5 + `ranked_players`.`achievement_rank` * 0.5 AS `combined_rank` FROM `ranked_players`) SELECT `combined_ranks`.`player_id` AS `player_id`, `combined_ranks`.`player_name` AS `player_name`, `combined_ranks`.`team_name` AS `team_name`, `combined_ranks`.`teams_played` AS `teams_played`, `combined_ranks`.`total_points` AS `total_points`, `combined_ranks`.`total_assists` AS `total_assists`, `combined_ranks`.`total_rebounds` AS `total_rebounds`, `combined_ranks`.`total_steals` AS `total_steals`, `combined_ranks`.`total_blocks` AS `total_blocks`, `combined_ranks`.`total_turnovers` AS `total_turnovers`, `combined_ranks`.`finals_mvp_count` AS `finals_mvp_count`, `combined_ranks`.`championships_won` AS `championships_won`, `combined_ranks`.`is_active` AS `is_active` FROM `combined_ranks` ORDER BY `combined_ranks`.`combined_rank` ASC LIMIT 0, 2020  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `drafts`
--
ALTER TABLE `drafts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leagues`
--
ALTER TABLE `leagues`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_game_stats`
--
ALTER TABLE `player_game_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_ratings`
--
ALTER TABLE `player_ratings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_season_stats`
--
ALTER TABLE `player_season_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `seasons`
--
ALTER TABLE `seasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `season_awards`
--
ALTER TABLE `season_awards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `drafts`
--
ALTER TABLE `drafts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leagues`
--
ALTER TABLE `leagues`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_game_stats`
--
ALTER TABLE `player_game_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_ratings`
--
ALTER TABLE `player_ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_season_stats`
--
ALTER TABLE `player_season_stats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seasons`
--
ALTER TABLE `seasons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `season_awards`
--
ALTER TABLE `season_awards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
