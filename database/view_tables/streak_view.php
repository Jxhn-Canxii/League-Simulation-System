CREATE OR REPLACE VIEW streak_view AS
SELECT
    s.id,
    s.team_id,
    t.name as team_name, -- Adjust to the actual column name for the team's name in the teams table
    s.best_winning_streak,
    s.best_losing_streak,
    s.created_at,
    s.updated_at
FROM
    streak s
JOIN
    teams t ON s.team_id = t.id;
