CREATE OR REPLACE VIEW finals_mvp_with_stats AS
SELECT
    p.id AS player_id,
    p.name AS player_name,
    p.role AS player_role,
    -- Concatenate all current team names with MVP season name
    GROUP_CONCAT(DISTINCT CONCAT(t1.name, ' (', s.name, ')') ORDER BY s.name) AS current_team_names, 
    -- Concatenate all MVP-winning team names with MVP season name
    GROUP_CONCAT(DISTINCT CONCAT(t2.name, ' (', s.name, ')') ORDER BY s.name) AS mvp_winning_team_names,
    -- Get most recent player stats
    MAX(ps.total_games) AS total_games,
    MAX(ps.total_games_played) AS total_games_played,
    MAX(ps.avg_minutes_per_game) AS avg_minutes_per_game,
    MAX(ps.avg_points_per_game) AS avg_points_per_game,
    MAX(ps.avg_rebounds_per_game) AS avg_rebounds_per_game,
    MAX(ps.avg_assists_per_game) AS avg_assists_per_game,
    MAX(ps.avg_steals_per_game) AS avg_steals_per_game,
    MAX(ps.avg_blocks_per_game) AS avg_blocks_per_game,
    MAX(ps.avg_turnovers_per_game) AS avg_turnovers_per_game,
    MAX(ps.avg_fouls_per_game) AS avg_fouls_per_game,
    MAX(ps.total_points) AS total_points,
    MAX(ps.total_rebounds) AS total_rebounds,
    MAX(ps.total_assists) AS total_assists,
    MAX(ps.total_steals) AS total_steals,
    MAX(ps.total_blocks) AS total_blocks,
    MAX(ps.total_turnovers) AS total_turnovers,
    MAX(ps.total_fouls) AS total_fouls,
    MAX(ps.created_at) AS stats_created_at,
    MAX(ps.updated_at) AS stats_updated_at
FROM `seasons` s
LEFT JOIN `players` p ON s.finals_mvp_id = p.id  -- Join MVP player
LEFT JOIN `player_season_stats` ps ON ps.player_id = p.id  -- Get player stats
LEFT JOIN `teams` t1 ON p.team_id = t1.id  -- Player's current team
LEFT JOIN `teams` t2 ON s.finals_winner_id = t2.id  -- MVP-winning team
WHERE s.finals_mvp_id IS NOT NULL  -- Only include seasons with an MVP
GROUP BY p.id, p.name, p.role  -- Group by player to merge duplicate rows
ORDER BY player_name;  -- You can change this to any other field if necessary
