CREATE VIEW player_playoff_status AS
SELECT
    p.id AS player_id,
    p.name AS player_name,
    COUNT(CASE WHEN s.round = 'round_of_16' THEN 1 END) AS round_of_16_appearances,
    COUNT(CASE WHEN s.round = 'quarter_finals' THEN 1 END) AS quarter_finals_appearances,
    COUNT(CASE WHEN s.round = 'semi_finals' THEN 1 END) AS semi_finals_appearances,
    COUNT(CASE WHEN s.round = 'interconference_semi_finals' THEN 1 END) AS interconference_semi_finals_appearances,
    COUNT(CASE WHEN s.round = 'finals' THEN 1 END) AS finals_appearances,
    COUNT(DISTINCT CASE WHEN se.finals_mvp_id = p.id THEN se.id END) AS finals_mvp_count,
    GROUP_CONCAT(DISTINCT se.name ORDER BY se.name ASC SEPARATOR ', ') AS seasons
FROM
    players p
LEFT JOIN
    player_game_stats pg ON p.id = pg.player_id
LEFT JOIN
    schedules s ON pg.game_id = s.game_id
LEFT JOIN
    seasons se ON s.season_id = se.id
GROUP BY
    p.id, p.name;
