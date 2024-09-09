CREATE VIEW player_season_stats AS
SELECT
    p.id AS player_id,
    p.name AS player_name,
    p.age,
    p.team_id,
    t.name AS team_name,
    p.is_active,
    p.is_rookie,
    p.retirement_age,
    pr.role,
    pr.overall_rating,
    pgs.season_id,
    COALESCE(AVG(pgs.points), 0) AS avg_points_per_game,
    COALESCE(AVG(pgs.rebounds), 0) AS avg_rebounds_per_game,
    COALESCE(AVG(pgs.assists), 0) AS avg_assists_per_game,
    COALESCE(AVG(pgs.steals), 0) AS avg_steals_per_game,
    COALESCE(AVG(pgs.blocks), 0) AS avg_blocks_per_game,
    COALESCE(AVG(pgs.turnovers), 0) AS avg_turnovers_per_game,
    COALESCE(AVG(pgs.fouls), 0) AS avg_fouls_per_game
FROM
    players p
JOIN
    player_ratings pr ON p.id = pr.player_id
LEFT JOIN
    player_game_stats pgs ON p.id = pgs.player_id
    AND pgs.season_id = pr.season_id
JOIN
    teams t ON p.team_id = t.id
GROUP BY
    p.id, p.name, p.age, p.team_id, t.name, p.is_active, p.is_rookie, p.retirement_age, pr.role, pr.overall_rating, pgs.season_id;
