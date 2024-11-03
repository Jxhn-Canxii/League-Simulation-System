CREATE OR REPLACE VIEW streak_view AS
SELECT
    s.id,
    s.team_id,
    t.name AS team_name, -- Team's name
    s.best_winning_streak,
    s.best_losing_streak,
    s.best_winning_streak_start_id,
    s.best_winning_streak_end_id,
    s.best_losing_streak_start_id,
    s.best_losing_streak_end_id,
    s.created_at,
    s.updated_at,

    -- Get opponent name for the best winning streak
    (SELECT
        CASE
            WHEN sv.home_id = s.team_id THEN t2.name
            ELSE t2.name
        END
     FROM
        schedule_view sv
     JOIN
        teams t2 ON (sv.home_id = t2.id OR sv.away_id = t2.id)
     WHERE
        sv.id = s.best_winning_streak_end_id
     LIMIT 1) AS last_winning_opponent,

    -- Get opponent name for the best losing streak
    (SELECT
        CASE
            WHEN sv.home_id = s.team_id THEN t2.name
            ELSE t2.name
        END
     FROM
        schedule_view sv
     JOIN
        teams t2 ON (sv.home_id = t2.id OR sv.away_id = t2.id)
     WHERE
        sv.id = s.best_losing_streak_end_id
     LIMIT 1) AS last_losing_opponent,

    -- Get the season name for the winning streak
    (SELECT
        se.name
     FROM
        schedule_view sv
     JOIN
        seasons se ON sv.season_id = se.id
     WHERE
        sv.id = s.best_winning_streak_end_id
     LIMIT 1) AS winning_streak_season,

    -- Get the season name for the losing streak
    (SELECT
        se.name
     FROM
        schedule_view sv
     JOIN
        seasons se ON sv.season_id = se.id
     WHERE
        sv.id = s.best_losing_streak_end_id
     LIMIT 1) AS losing_streak_season

FROM
    streak s
JOIN
    teams t ON s.team_id = t.id;
