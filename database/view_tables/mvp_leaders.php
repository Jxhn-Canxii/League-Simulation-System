CREATE OR REPLACE VIEW mvp_leaders AS
SELECT
    players.id AS player_id,
    players.name AS player_name,
    players.is_rookie AS is_rookie,
    players.draft_status AS draft_status,
    players.team_id AS team_id,
    teams.name AS team_name,
    -- Calculating per-game averages
    COUNT(CASE WHEN player_game_stats.minutes > 0 THEN player_game_stats.game_id END) AS games_played,
    ROUND(SUM(player_game_stats.points) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS points_per_game,
    ROUND(SUM(player_game_stats.rebounds) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS rebounds_per_game,
    ROUND(SUM(player_game_stats.assists) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS assists_per_game,
    ROUND(SUM(player_game_stats.steals) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS steals_per_game,
    ROUND(SUM(player_game_stats.blocks) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS blocks_per_game,
    ROUND(SUM(player_game_stats.turnovers) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS turnovers_per_game,
    ROUND(SUM(player_game_stats.fouls) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS fouls_per_game,
    SUM(player_game_stats.points) AS total_points,
    SUM(player_game_stats.rebounds) AS total_rebounds,
    SUM(player_game_stats.assists) AS total_assists,
    SUM(player_game_stats.steals) AS total_steals,
    SUM(player_game_stats.blocks) AS total_blocks,
    SUM(player_game_stats.turnovers) AS total_turnovers,
    SUM(player_game_stats.fouls) AS total_fouls,
    -- Calculating a performance score (adjust the weights as needed)
    (SUM(player_game_stats.points) +
    (SUM(player_game_stats.rebounds) * 1.2) +
    (SUM(player_game_stats.assists) * 1.5) +
    (SUM(player_game_stats.steals) * 2) +
    (SUM(player_game_stats.blocks) * 2) -
    (SUM(player_game_stats.turnovers) * 1) -  -- Subtracting turnovers to penalize
    (SUM(player_game_stats.fouls) * 0.5)) AS performance_score -- Subtracting fouls with less weight to penalize
FROM
    players
JOIN
    player_game_stats ON players.id = player_game_stats.player_id
JOIN
    teams ON players.team_id = teams.id
JOIN
    schedules ON player_game_stats.game_id = schedules.game_id
WHERE
    player_game_stats.season_id = (SELECT MAX(season_id) FROM player_game_stats) -- Filtering for the current season
    AND schedules.round NOT IN ('quarter_finals', 'round_of_16', 'round_of_32', 'semi_finals', 'interconference_semi_finals', 'finals') -- Excluding specified rounds
GROUP BY
    players.id, players.name, teams.name, players.is_rookie, players.draft_status, players.team_id
ORDER BY
    performance_score DESC
LIMIT 10; -- Fetching the top 10 MVP leaders
