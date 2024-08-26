CREATE VIEW player_playoff_appearances AS
SELECT
    p.id AS player_id,
    p.name AS player_name,
    COALESCE(GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ', '), 'Free Agent') AS teams_played_for_in_playoffs,
    COALESCE(GROUP_CONCAT(DISTINCT t.acronym ORDER BY t.acronym SEPARATOR ', '), 'N/A') AS team_acronyms,
    COALESCE(MAX(t2.name), 'Free Agent') AS current_team_name,
    p.is_active AS active_status,
    COUNT(DISTINCT CASE WHEN s.round = 'round_of_32' THEN s.game_id END) AS round_of_32_appearances,
    COUNT(DISTINCT CASE WHEN s.round = 'round_of_16' THEN s.game_id END) AS round_of_16_appearances,
    COUNT(DISTINCT CASE WHEN s.round = 'quarter_finals' THEN s.game_id END) AS quarter_finals_appearances,
    COUNT(DISTINCT CASE WHEN s.round = 'semi_finals' THEN s.game_id END) AS semi_finals_appearances,
    COUNT(DISTINCT CASE WHEN s.round = 'interconference_semi_finals' THEN s.game_id END) AS interconference_semi_finals_appearances,
    COUNT(DISTINCT CASE WHEN s.round = 'finals' THEN s.game_id END) AS finals_appearances,
    COUNT(DISTINCT s.game_id) AS total_playoff_appearances,
    COUNT(DISTINCT CASE WHEN s.round IN ('round_of_32', 'round_of_16', 'quarter_finals', 'semi_finals', 'interconference_semi_finals', 'finals') THEN s.season_id END) AS seasons_played_in_playoffs,
    COUNT(DISTINCT all_s.season_id) AS total_seasons_played,
    COUNT(DISTINCT CASE
        WHEN s.round = 'finals' AND
             ((pg.team_id = s.home_id AND s.home_score > s.away_score) OR
              (pg.team_id = s.away_id AND s.away_score > s.home_score))
        THEN s.game_id
        END) AS championships_won
FROM
    players p
LEFT JOIN
    player_game_stats pg ON p.id = pg.player_id
LEFT JOIN
    schedules s ON pg.game_id = s.game_id
LEFT JOIN
    teams t ON pg.team_id = t.id
LEFT JOIN
    teams t2 ON p.team_id = t2.id  -- Join again to get the current team name
LEFT JOIN
    (SELECT DISTINCT player_id, season_id FROM player_game_stats) all_s ON all_s.player_id = p.id
WHERE
    s.round IN ('round_of_32', 'round_of_16', 'quarter_finals', 'semi_finals', 'interconference_semi_finals', 'finals')
GROUP BY
    p.id, p.name, p.is_active
ORDER BY
    total_playoff_appearances DESC;
