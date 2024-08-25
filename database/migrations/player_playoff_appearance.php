CREATE VIEW player_playoff_appearances AS
SELECT
    p.id AS player_id,
    p.name AS player_name,
    GROUP_CONCAT(DISTINCT t.name ORDER BY s.game_id SEPARATOR ', ') AS teams_played_for_in_playoffs,
    COUNT(DISTINCT t.id) AS distinct_teams_played_for,
    COUNT(CASE WHEN s.round = 'round_of_32' THEN 1 END) AS round_of_32_appearances,
    COUNT(CASE WHEN s.round = 'round_of_16' THEN 1 END) AS round_of_16_appearances,
    COUNT(CASE WHEN s.round = 'quarter_finals' THEN 1 END) AS quarter_finals_appearances,
    COUNT(CASE WHEN s.round = 'semi_finals' THEN 1 END) AS semi_finals_appearances,
    COUNT(CASE WHEN s.round = 'interconference_semi_finals' THEN 1 END) AS interconference_semi_finals_appearances,
    COUNT(CASE WHEN s.round = 'finals' THEN 1 END) AS finals_appearances,
    COUNT(*) AS total_playoff_appearances
FROM
    player_game_stats pg
JOIN
    schedules s ON pg.game_id = s.game_id
JOIN
    players p ON pg.player_id = p.id
JOIN
    teams t ON pg.team_id = t.id  -- This captures the team the player was playing for
WHERE
    s.round IN ('round_of_32', 'round_of_16', 'quarter_finals', 'semi_finals', 'interconference_semi_finals', 'finals')
GROUP BY
    p.id, p.name
ORDER BY
    total_playoff_appearances DESC;
