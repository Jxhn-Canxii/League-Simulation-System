CREATE OR REPLACE VIEW injured_players_with_teams AS
SELECT
    p.id AS player_id,
    p.name AS player_name,
    COALESCE(t.name, 'Free Agent') AS team_name,  -- If no team, show 'Free Agent'
    i.injury_type,
    i.recovery_games,
    p.injury_recovery_games,
    CASE
        WHEN p.injury_recovery_games = 0 THEN 'Recovered'
        ELSE 'Injured'
    END AS status  -- Add a column that shows 'Recovered' if injury_recovery_games is 0, else 'Injured'
FROM
    players p
JOIN
    injury_histories i ON p.id = i.player_id
LEFT JOIN  -- Use LEFT JOIN to include players without a team
    teams t ON p.team_id = t.id
