CREATE OR REPLACE VIEW player_experience_status AS
SELECT
    p.id AS player_id,
    p.name AS player_name,
    p.age,
    p.role,
    p.is_active,
    IF(p.is_active = 0 AND p.retirement_age <= p.age, 'retired', 'active') AS overall_status,
    CASE
        WHEN COUNT(DISTINCT ps.season_id) > 5 THEN 'veteran'
        WHEN COUNT(DISTINCT ps.season_id) > 1 THEN 'sophomore'
        ELSE 'rookie'
    END AS experience_status,
    SUM(ps.total_points) AS total_points,
    SUM(ps.total_rebounds) AS total_rebounds,
    SUM(ps.total_assists) AS total_assists,
    SUM(ps.total_steals) AS total_steals,
    SUM(ps.total_blocks) AS total_blocks,
    SUM(ps.total_turnovers) AS total_turnovers,
    SUM(ps.total_fouls) AS total_fouls,
    COUNT(DISTINCT ps.season_id) AS seasons_played
FROM players p
LEFT JOIN (
    SELECT
        player_id,
        season_id,
        SUM(points) AS total_points,
        SUM(rebounds) AS total_rebounds,
        SUM(assists) AS total_assists,
        SUM(steals) AS total_steals,
        SUM(blocks) AS total_blocks,
        SUM(turnovers) AS total_turnovers,
        SUM(fouls) AS total_fouls
    FROM player_game_stats
    GROUP BY player_id, season_id
) ps ON p.id = ps.player_id
GROUP BY p.id, p.name, p.age, p.role, p.is_active, p.retirement_age;
