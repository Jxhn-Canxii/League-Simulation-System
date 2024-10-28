-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2024 at 10:31 AM
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
-- Stand-in structure for view `mvp_leaders`
-- (See below for the actual view)
--
CREATE TABLE `mvp_leaders` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `player_playoff_appearances`
-- (See below for the actual view)
--
CREATE TABLE `player_playoff_appearances` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `player_playoff_stats`
-- (See below for the actual view)
--
CREATE TABLE `player_playoff_stats` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `rookie_leaders`
-- (See below for the actual view)
--
CREATE TABLE `rookie_leaders` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `schedule_view`
-- (See below for the actual view)
--
CREATE TABLE `schedule_view` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `standings_view`
-- (See below for the actual view)
--
CREATE TABLE `standings_view` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `top_10_players_by_decade`
-- (See below for the actual view)
--
CREATE TABLE `top_10_players_by_decade` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `top_10_players_by_team_all_time`
-- (See below for the actual view)
--
CREATE TABLE `top_10_players_by_team_all_time` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `top_20_players_all_time`
-- (See below for the actual view)
--
CREATE TABLE `top_20_players_all_time` (
);

-- --------------------------------------------------------

--
-- Structure for view `mvp_leaders`
--
DROP TABLE IF EXISTS `mvp_leaders`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `mvp_leaders`  AS SELECT `players`.`id` AS `player_id`, `players`.`name` AS `player_name`, `teams`.`name` AS `team_name`, count(case when `player_game_stats`.`minutes` > 0 then `player_game_stats`.`game_id` end) AS `games_played`, round(sum(`player_game_stats`.`points`) / nullif(count(case when `player_game_stats`.`minutes` > 0 then 1 end),0),2) AS `points_per_game`, round(sum(`player_game_stats`.`rebounds`) / nullif(count(case when `player_game_stats`.`minutes` > 0 then 1 end),0),2) AS `rebounds_per_game`, round(sum(`player_game_stats`.`assists`) / nullif(count(case when `player_game_stats`.`minutes` > 0 then 1 end),0),2) AS `assists_per_game`, round(sum(`player_game_stats`.`steals`) / nullif(count(case when `player_game_stats`.`minutes` > 0 then 1 end),0),2) AS `steals_per_game`, round(sum(`player_game_stats`.`blocks`) / nullif(count(case when `player_game_stats`.`minutes` > 0 then 1 end),0),2) AS `blocks_per_game`, sum(`player_game_stats`.`points`) AS `total_points`, sum(`player_game_stats`.`rebounds`) AS `total_rebounds`, sum(`player_game_stats`.`assists`) AS `total_assists`, sum(`player_game_stats`.`steals`) AS `total_steals`, sum(`player_game_stats`.`blocks`) AS `total_blocks`, sum(`player_game_stats`.`points`) + sum(`player_game_stats`.`rebounds`) * 1.2 + sum(`player_game_stats`.`assists`) * 1.5 + sum(`player_game_stats`.`steals`) * 2 + sum(`player_game_stats`.`blocks`) * 2 AS `performance_score` FROM ((`players` join `player_game_stats` on(`players`.`id` = `player_game_stats`.`player_id`)) join `teams` on(`players`.`team_id` = `teams`.`id`)) WHERE `player_game_stats`.`season_id` = (select max(`player_game_stats`.`season_id`) from `player_game_stats`) GROUP BY `players`.`id`, `players`.`name`, `teams`.`name` ORDER BY sum(`player_game_stats`.`points`) + sum(`player_game_stats`.`rebounds`) * 1.2 + sum(`player_game_stats`.`assists`) * 1.5 + sum(`player_game_stats`.`steals`) * 2 + sum(`player_game_stats`.`blocks`) * 2 DESC LIMIT 0, 10 ;

-- --------------------------------------------------------

--
-- Structure for view `player_playoff_appearances`
--
DROP TABLE IF EXISTS `player_playoff_appearances`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `player_playoff_appearances`  AS SELECT `p`.`id` AS `player_id`, `p`.`name` AS `player_name`, coalesce(group_concat(distinct `t`.`name` order by `t`.`name` ASC separator ', '),'Free Agent') AS `teams_played_for_in_playoffs`, coalesce(group_concat(distinct `t`.`acronym` order by `t`.`acronym` ASC separator ', '),'N/A') AS `team_acronyms`, coalesce(max(`t2`.`name`),'Free Agent') AS `current_team_name`, `p`.`is_active` AS `active_status`, count(distinct case when `s`.`round` = 'round_of_32' then `s`.`game_id` end) AS `round_of_32_appearances`, count(distinct case when `s`.`round` = 'round_of_16' then `s`.`game_id` end) AS `round_of_16_appearances`, count(distinct case when `s`.`round` = 'quarter_finals' then `s`.`game_id` end) AS `quarter_finals_appearances`, count(distinct case when `s`.`round` = 'semi_finals' then `s`.`game_id` end) AS `semi_finals_appearances`, count(distinct case when `s`.`round` = 'interconference_semi_finals' then `s`.`game_id` end) AS `interconference_semi_finals_appearances`, count(distinct case when `s`.`round` = 'finals' then `s`.`game_id` end) AS `finals_appearances`, count(distinct `s`.`game_id`) AS `total_playoff_appearances`, count(distinct case when `s`.`round` in ('round_of_32','round_of_16','quarter_finals','semi_finals','interconference_semi_finals','finals') then `s`.`season_id` end) AS `seasons_played_in_playoffs`, count(distinct `all_s`.`season_id`) AS `total_seasons_played`, count(distinct case when `s`.`round` = 'finals' and (`pg`.`team_id` = `s`.`home_id` and `s`.`home_score` > `s`.`away_score` or `pg`.`team_id` = `s`.`away_id` and `s`.`away_score` > `s`.`home_score`) then `s`.`game_id` end) AS `championships_won` FROM (((((`players` `p` left join `player_game_stats` `pg` on(`p`.`id` = `pg`.`player_id`)) left join `schedules` `s` on(`pg`.`game_id` = `s`.`game_id`)) left join `teams` `t` on(`pg`.`team_id` = `t`.`id`)) left join `teams` `t2` on(`p`.`team_id` = `t2`.`id`)) left join (select distinct `player_game_stats`.`player_id` AS `player_id`,`player_game_stats`.`season_id` AS `season_id` from `player_game_stats`) `all_s` on(`all_s`.`player_id` = `p`.`id`)) WHERE `s`.`round` in ('round_of_32','round_of_16','quarter_finals','semi_finals','interconference_semi_finals','finals') GROUP BY `p`.`id`, `p`.`name`, `p`.`is_active` ORDER BY count(distinct `s`.`game_id`) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `player_playoff_stats`
--
DROP TABLE IF EXISTS `player_playoff_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `player_playoff_stats`  AS SELECT `p`.`id` AS `player_id`, `p`.`name` AS `player_name`, count(case when `s`.`round` = 'round_of_16' then 1 end) AS `round_of_16_appearances`, count(case when `s`.`round` = 'quarter_finals' then 1 end) AS `quarter_finals_appearances`, count(case when `s`.`round` = 'semi_finals' then 1 end) AS `semi_finals_appearances`, count(case when `s`.`round` = 'interconference_semi_finals' then 1 end) AS `interconference_semi_finals_appearances`, count(case when `s`.`round` = 'finals' then 1 end) AS `finals_appearances`, count(distinct case when `se`.`finals_mvp_id` = `p`.`id` then `se`.`id` end) AS `finals_mvp_count`, group_concat(distinct `se`.`name` order by `se`.`name` ASC separator ', ') AS `seasons` FROM (((`players` `p` left join `player_game_stats` `pg` on(`p`.`id` = `pg`.`player_id`)) left join `schedules` `s` on(`pg`.`game_id` = `s`.`game_id`)) left join `seasons` `se` on(`s`.`season_id` = `se`.`id`)) GROUP BY `p`.`id`, `p`.`name` ;

-- --------------------------------------------------------

--
-- Structure for view `rookie_leaders`
--
DROP TABLE IF EXISTS `rookie_leaders`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `rookie_leaders`  AS SELECT `players`.`id` AS `player_id`, `players`.`name` AS `player_name`, `teams`.`name` AS `team_name`, count(case when `player_game_stats`.`minutes` > 0 then `player_game_stats`.`game_id` end) AS `games_played`, round(sum(`player_game_stats`.`points`) / nullif(count(case when `player_game_stats`.`minutes` > 0 then 1 end),0),2) AS `points_per_game`, round(sum(`player_game_stats`.`rebounds`) / nullif(count(case when `player_game_stats`.`minutes` > 0 then 1 end),0),2) AS `rebounds_per_game`, round(sum(`player_game_stats`.`assists`) / nullif(count(case when `player_game_stats`.`minutes` > 0 then 1 end),0),2) AS `assists_per_game`, round(sum(`player_game_stats`.`steals`) / nullif(count(case when `player_game_stats`.`minutes` > 0 then 1 end),0),2) AS `steals_per_game`, round(sum(`player_game_stats`.`blocks`) / nullif(count(case when `player_game_stats`.`minutes` > 0 then 1 end),0),2) AS `blocks_per_game`, sum(`player_game_stats`.`points`) AS `total_points`, sum(`player_game_stats`.`rebounds`) AS `total_rebounds`, sum(`player_game_stats`.`assists`) AS `total_assists`, sum(`player_game_stats`.`steals`) AS `total_steals`, sum(`player_game_stats`.`blocks`) AS `total_blocks`, sum(`player_game_stats`.`points`) + sum(`player_game_stats`.`rebounds`) * 1.2 + sum(`player_game_stats`.`assists`) * 1.5 + sum(`player_game_stats`.`steals`) * 2 + sum(`player_game_stats`.`blocks`) * 2 AS `performance_score` FROM ((`players` join `player_game_stats` on(`players`.`id` = `player_game_stats`.`player_id`)) join `teams` on(`players`.`team_id` = `teams`.`id`)) WHERE `players`.`is_rookie` = 1 GROUP BY `players`.`id`, `players`.`name`, `teams`.`name` ORDER BY sum(`player_game_stats`.`points`) + sum(`player_game_stats`.`rebounds`) * 1.2 + sum(`player_game_stats`.`assists`) * 1.5 + sum(`player_game_stats`.`steals`) * 2 + sum(`player_game_stats`.`blocks`) * 2 DESC ;

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
-- Structure for view `top_10_players_by_decade`
--
DROP TABLE IF EXISTS `top_10_players_by_decade`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `top_10_players_by_decade`  AS WITH player_achievements AS (SELECT `p`.`id` AS `player_id`, `p`.`name` AS `player_name`, `t`.`name` AS `team_name`, floor((`se`.`id` - 1) / 10) * 10 + 1 AS `decade_starting_season`, sum(`pg`.`points`) AS `total_points`, sum(`pg`.`assists`) AS `total_assists`, sum(`pg`.`rebounds`) AS `total_rebounds`, sum(`pg`.`steals`) AS `total_steals`, sum(`pg`.`blocks`) AS `total_blocks`, sum(`pg`.`turnovers`) AS `total_turnovers`, count(distinct case when `s`.`round` = 'finals' and (`s`.`home_id` = `t`.`id` and `s`.`home_score` > `s`.`away_score` or `s`.`away_id` = `t`.`id` and `s`.`away_score` > `s`.`home_score`) then `s`.`id` end) AS `championships_won`, count(distinct case when `se`.`finals_mvp_id` = `p`.`id` then `se`.`id` end) AS `finals_mvp_count` FROM ((((`player_game_stats` `pg` left join `players` `p` on(`pg`.`player_id` = `p`.`id`)) left join `teams` `t` on(`pg`.`team_id` = `t`.`id`)) left join `schedules` `s` on(`pg`.`game_id` = `s`.`game_id`)) left join `seasons` `se` on(`s`.`season_id` = `se`.`id`)) GROUP BY `p`.`id`, `p`.`name`, `t`.`name`, floor((`se`.`id` - 1) / 10) * 10 + 1), ranked_players AS (SELECT `pa`.`player_id` AS `player_id`, `pa`.`player_name` AS `player_name`, `pa`.`team_name` AS `team_name`, `pa`.`decade_starting_season` AS `decade_starting_season`, `pa`.`total_points` AS `total_points`, `pa`.`total_assists` AS `total_assists`, `pa`.`total_rebounds` AS `total_rebounds`, `pa`.`total_steals` AS `total_steals`, `pa`.`total_blocks` AS `total_blocks`, `pa`.`total_turnovers` AS `total_turnovers`, `pa`.`championships_won` AS `championships_won`, `pa`.`finals_mvp_count` AS `finals_mvp_count`, row_number() over ( partition by `pa`.`team_name`,`pa`.`decade_starting_season` order by `pa`.`total_points` desc,`pa`.`total_assists` desc,`pa`.`total_rebounds` desc,`pa`.`total_steals` desc,`pa`.`total_blocks` desc,`pa`.`total_turnovers`,`pa`.`championships_won` desc,`pa`.`finals_mvp_count` desc) AS `rank_in_decade` FROM `player_achievements` AS `pa`)  SELECT `ranked_players`.`player_id` AS `player_id`, `ranked_players`.`player_name` AS `player_name`, `ranked_players`.`team_name` AS `team_name`, `ranked_players`.`decade_starting_season` AS `decade_starting_season`, `ranked_players`.`total_points` AS `total_points`, `ranked_players`.`total_assists` AS `total_assists`, `ranked_players`.`total_rebounds` AS `total_rebounds`, `ranked_players`.`total_steals` AS `total_steals`, `ranked_players`.`total_blocks` AS `total_blocks`, `ranked_players`.`total_turnovers` AS `total_turnovers`, `ranked_players`.`championships_won` AS `championships_won`, `ranked_players`.`finals_mvp_count` AS `finals_mvp_count` FROM `ranked_players` WHERE `ranked_players`.`rank_in_decade` <= 10 ORDER BY `ranked_players`.`decade_starting_season` ASC, `ranked_players`.`rank_in_decade` ASC`rank_in_decade`  ;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
