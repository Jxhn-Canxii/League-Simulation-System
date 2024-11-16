-- Insert the top 10 points players
INSERT INTO all_time_top_stats (stat_category, player_id, player_name, game_id, team_id, opponent_id, season_id, stat_value)
SELECT
    'points' AS stat_category,
    pgs.player_id,
    players.name AS player_name,
    pgs.game_id,
    pgs.team_id,
    CASE
        WHEN pgs.team_id = schedule_view.home_id THEN schedule_view.away_id
        ELSE schedule_view.home_id
    END AS opponent_id,
    pgs.season_id,
    pgs.points AS stat_value
FROM player_game_stats pgs
JOIN players ON pgs.player_id = players.id
JOIN schedule_view ON pgs.game_id = schedule_view.game_id
ORDER BY pgs.points DESC
LIMIT 10;

-- Insert the top 10 rebounds players
INSERT INTO all_time_top_stats (stat_category, player_id, player_name, game_id, team_id, opponent_id, season_id, stat_value)
SELECT
    'rebounds' AS stat_category,
    pgs.player_id,
    players.name AS player_name,
    pgs.game_id,
    pgs.team_id,
    CASE
        WHEN pgs.team_id = schedule_view.home_id THEN schedule_view.away_id
        ELSE schedule_view.home_id
    END AS opponent_id,
    pgs.season_id,
    pgs.rebounds AS stat_value
FROM player_game_stats pgs
JOIN players ON pgs.player_id = players.id
JOIN schedule_view ON pgs.game_id = schedule_view.game_id
ORDER BY pgs.rebounds DESC
LIMIT 10;

-- Insert the top 10 assists players
INSERT INTO all_time_top_stats (stat_category, player_id, player_name, game_id, team_id, opponent_id, season_id, stat_value)
SELECT
    'assists' AS stat_category,
    pgs.player_id,
    players.name AS player_name,
    pgs.game_id,
    pgs.team_id,
    CASE
        WHEN pgs.team_id = schedule_view.home_id THEN schedule_view.away_id
        ELSE schedule_view.home_id
    END AS opponent_id,
    pgs.season_id,
    pgs.assists AS stat_value
FROM player_game_stats pgs
JOIN players ON pgs.player_id = players.id
JOIN schedule_view ON pgs.game_id = schedule_view.game_id
ORDER BY pgs.assists DESC
LIMIT 10;

-- Insert the top 10 steals players
INSERT INTO all_time_top_stats (stat_category, player_id, player_name, game_id, team_id, opponent_id, season_id, stat_value)
SELECT
    'steals' AS stat_category,
    pgs.player_id,
    players.name AS player_name,
    pgs.game_id,
    pgs.team_id,
    CASE
        WHEN pgs.team_id = schedule_view.home_id THEN schedule_view.away_id
        ELSE schedule_view.home_id
    END AS opponent_id,
    pgs.season_id,
    pgs.steals AS stat_value
FROM player_game_stats pgs
JOIN players ON pgs.player_id = players.id
JOIN schedule_view ON pgs.game_id = schedule_view.game_id
ORDER BY pgs.steals DESC
LIMIT 10;

-- Insert the top 10 blocks players
INSERT INTO all_time_top_stats (stat_category, player_id, player_name, game_id, team_id, opponent_id, season_id, stat_value)
SELECT
    'blocks' AS stat_category,
    pgs.player_id,
    players.name AS player_name,
    pgs.game_id,
    pgs.team_id,
    CASE
        WHEN pgs.team_id = schedule_view.home_id THEN schedule_view.away_id
        ELSE schedule_view.home_id
    END AS opponent_id,
    pgs.season_id,
    pgs.blocks AS stat_value
FROM player_game_stats pgs
JOIN players ON pgs.player_id = players.id
JOIN schedule_view ON pgs.game_id = schedule_view.game_id
ORDER BY pgs.blocks DESC
LIMIT 10;
