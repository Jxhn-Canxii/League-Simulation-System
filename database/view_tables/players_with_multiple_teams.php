CREATE OR REPLACE VIEW players_multiple_teams AS
SELECT
    p.name AS player_name,
    p.role,
    pg.player_id,
    pg.season_id,
    GROUP_CONCAT(DISTINCT t.name ORDER BY t.name ASC) AS teams_played,
    COUNT(DISTINCT pg.team_id) AS total_teams
FROM
    player_game_stats pg
JOIN
    players p ON pg.player_id = p.id
JOIN
    teams t ON pg.team_id = t.id
GROUP BY
    pg.player_id, pg.season_id
HAVING
    total_teams > 1
ORDER BY
    pg.season_id DESC, player_name ASC;
