CREATE OR REPLACE VIEW player_season_status AS
SELECT
    ps.player_id AS player_id,
    p.name AS player_name,
    p.age,
    p.role,
    p.is_active,
    IF(p.is_active = 0 AND p.retirement_age <= p.age, 'retired', 'active') AS overall_status,
    IF(
        COUNT(ps.season_id) > 5,
        'veteran',
        IF(COUNT(ps.season_id) > 1, 'sophomore', 'rookie')
    ) AS experience_status,
    ps.team_id,
    SUM(ps.points) AS total_points,
    SUM(ps.rebounds) AS total_rebounds,
    SUM(ps.assists) AS total_assists,
    SUM(ps.steals) AS total_steals,
    SUM(ps.blocks) AS total_blocks,
    SUM(ps.turnovers) AS total_turnovers,
    SUM(ps.fouls) AS total_fouls,
    COUNT(ps.season_id) AS games_played,
    AVG(ps.points) AS average_points_per_game,
    AVG(ps.rebounds) AS average_rebounds_per_game,
    AVG(ps.assists) AS average_assists_per_game,
    AVG(ps.steals) AS average_steals_per_game,
    AVG(ps.blocks) AS average_blocks_per_game,
    AVG(ps.turnovers) AS average_turnovers_per_game,
    AVG(ps.fouls) AS average_fouls_per_game
FROM players p
LEFT JOIN player_game_stats ps ON p.id = ps.player_id
GROUP BY p.id, ps.team_id, ps.player_id;
