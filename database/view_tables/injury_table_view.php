CREATE OR REPLACE VIEW injured_players_with_teams AS
SELECT
    p.id AS player_id,
    p.name AS player_name,
    t.name AS team_name,
    i.injury_type,
    i.recovery_games,
    p.injury_recovery_games
FROM
    players p
JOIN
    injury_histories i ON p.id = i.player_id
JOIN
    teams t ON p.team_id = t.id
WHERE
    p.is_injured = TRUE
    AND i.recovery_date IS NULL;  -- Ensuring the player is still injured
