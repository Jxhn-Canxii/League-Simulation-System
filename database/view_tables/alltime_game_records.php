CREATE OR REPLACE VIEW game_statistics_combined AS
SELECT
    -- Lowest score by a team (only games with status = 2)
    (SELECT
        LEAST(s.home_score, s.away_score)
    FROM schedules s
    WHERE s.status = 2
    ORDER BY LEAST(s.home_score, s.away_score) ASC
    LIMIT 1) AS lowest_score_by_team,

    -- Team that achieved the lowest score
    (SELECT
        CASE
            WHEN s.home_score = LEAST(s.home_score, s.away_score) THEN t_home.name
            ELSE t_away.name
        END
    FROM schedules s
    JOIN teams t_home ON s.home_id = t_home.id
    JOIN teams t_away ON s.away_id = t_away.id
    WHERE s.status = 2
    ORDER BY LEAST(s.home_score, s.away_score) ASC
    LIMIT 1) AS team_with_lowest_score,

    -- Game and season for the lowest score by a team
    (SELECT
        s.game_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY LEAST(s.home_score, s.away_score) ASC
    LIMIT 1) AS lowest_score_game_id,

    (SELECT
        s.season_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY LEAST(s.home_score, s.away_score) ASC
    LIMIT 1) AS lowest_score_season_id,

    -- Highest score by a team (only games with status = 2)
    (SELECT
        GREATEST(s.home_score, s.away_score)
    FROM schedules s
    WHERE s.status = 2
    ORDER BY GREATEST(s.home_score, s.away_score) DESC
    LIMIT 1) AS highest_score_by_team,

    -- Team that achieved the highest score
    (SELECT
        CASE
            WHEN s.home_score = GREATEST(s.home_score, s.away_score) THEN t_home.name
            ELSE t_away.name
        END
    FROM schedules s
    JOIN teams t_home ON s.home_id = t_home.id
    JOIN teams t_away ON s.away_id = t_away.id
    WHERE s.status = 2
    ORDER BY GREATEST(s.home_score, s.away_score) DESC
    LIMIT 1) AS team_with_highest_score,

    -- Game and season for the highest score by a team
    (SELECT
        s.game_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY GREATEST(s.home_score, s.away_score) DESC
    LIMIT 1) AS highest_score_game_id,

    (SELECT
        s.season_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY GREATEST(s.home_score, s.away_score) DESC
    LIMIT 1) AS highest_score_season_id,

    -- Lowest combined score (only games with status = 2)
    (SELECT
        (s.home_score + s.away_score)
    FROM schedules s
    WHERE s.status = 2
    ORDER BY (s.home_score + s.away_score) ASC
    LIMIT 1) AS lowest_combined_score,

    -- Highest combined score (only games with status = 2)
    (SELECT
        (s.home_score + s.away_score)
    FROM schedules s
    WHERE s.status = 2
    ORDER BY (s.home_score + s.away_score) DESC
    LIMIT 1) AS highest_combined_score,

    -- Biggest winning margin (only games with status = 2)
    (SELECT
        ABS(s.home_score - s.away_score)
    FROM schedules s
    WHERE s.status = 2
    ORDER BY ABS(s.home_score - s.away_score) DESC
    LIMIT 1) AS biggest_winning_margin,

    -- Home team for the game with the lowest score (only games with status = 2)
    (SELECT
        t_home.name
    FROM schedules s
    JOIN teams t_home ON s.home_id = t_home.id
    WHERE s.status = 2
    ORDER BY LEAST(s.home_score, s.away_score) ASC
    LIMIT 1) AS home_team_for_lowest_score,

    -- Away team for the game with the lowest score (only games with status = 2)
    (SELECT
        t_away.name
    FROM schedules s
    JOIN teams t_away ON s.away_id = t_away.id
    WHERE s.status = 2
    ORDER BY LEAST(s.home_score, s.away_score) ASC
    LIMIT 1) AS away_team_for_lowest_score,

    -- Game and season for the game with the lowest score
    (SELECT
        s.game_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY LEAST(s.home_score, s.away_score) ASC
    LIMIT 1) AS lowest_score_game_id_2,

    (SELECT
        s.season_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY LEAST(s.home_score, s.away_score) ASC
    LIMIT 1) AS lowest_score_season_id_2,

    -- Home team for the game with the highest score (only games with status = 2)
    (SELECT
        t_home.name
    FROM schedules s
    JOIN teams t_home ON s.home_id = t_home.id
    WHERE s.status = 2
    ORDER BY GREATEST(s.home_score, s.away_score) DESC
    LIMIT 1) AS home_team_for_highest_score,

    -- Away team for the game with the highest score (only games with status = 2)
    (SELECT
        t_away.name
    FROM schedules s
    JOIN teams t_away ON s.away_id = t_away.id
    WHERE s.status = 2
    ORDER BY GREATEST(s.home_score, s.away_score) DESC
    LIMIT 1) AS away_team_for_highest_score,

    -- Game and season for the game with the highest score
    (SELECT
        s.game_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY GREATEST(s.home_score, s.away_score) DESC
    LIMIT 1) AS highest_score_game_id_2,

    (SELECT
        s.season_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY GREATEST(s.home_score, s.away_score) DESC
    LIMIT 1) AS highest_score_season_id_2,

    -- Home team for the game with the lowest combined score (only games with status = 2)
    (SELECT
        t_home.name
    FROM schedules s
    JOIN teams t_home ON s.home_id = t_home.id
    WHERE s.status = 2
    ORDER BY (s.home_score + s.away_score) ASC
    LIMIT 1) AS home_team_for_lowest_combined_score,

    -- Away team for the game with the lowest combined score (only games with status = 2)
    (SELECT
        t_away.name
    FROM schedules s
    JOIN teams t_away ON s.away_id = t_away.id
    WHERE s.status = 2
    ORDER BY (s.home_score + s.away_score) ASC
    LIMIT 1) AS away_team_for_lowest_combined_score,

    -- Game and season for the game with the lowest combined score
    (SELECT
        s.game_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY (s.home_score + s.away_score) ASC
    LIMIT 1) AS lowest_combined_score_game_id,

    (SELECT
        s.season_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY (s.home_score + s.away_score) ASC
    LIMIT 1) AS lowest_combined_score_season_id,

    -- Home team for the game with the highest combined score (only games with status = 2)
    (SELECT
        t_home.name
    FROM schedules s
    JOIN teams t_home ON s.home_id = t_home.id
    WHERE s.status = 2
    ORDER BY (s.home_score + s.away_score) DESC
    LIMIT 1) AS home_team_for_highest_combined_score,

    -- Away team for the game with the highest combined score (only games with status = 2)
    (SELECT
        t_away.name
    FROM schedules s
    JOIN teams t_away ON s.away_id = t_away.id
    WHERE s.status = 2
    ORDER BY (s.home_score + s.away_score) DESC
    LIMIT 1) AS away_team_for_highest_combined_score,

    -- Game and season for the game with the highest combined score
    (SELECT
        s.game_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY (s.home_score + s.away_score) DESC
    LIMIT 1) AS highest_combined_score_game_id,

    (SELECT
        s.season_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY (s.home_score + s.away_score) DESC
    LIMIT 1) AS highest_combined_score_season_id,

    -- Home team for the game with the biggest winning margin (only games with status = 2)
    (SELECT
        t_home.name
    FROM schedules s
    JOIN teams t_home ON s.home_id = t_home.id
    WHERE s.status = 2
    ORDER BY ABS(s.home_score - s.away_score) DESC
    LIMIT 1) AS home_team_for_biggest_margin,

    -- Away team for the game with the biggest winning margin (only games with status = 2)
    (SELECT
        t_away.name
    FROM schedules s
    JOIN teams t_away ON s.away_id = t_away.id
    WHERE s.status = 2
    ORDER BY ABS(s.home_score - s.away_score) DESC
    LIMIT 1) AS away_team_for_biggest_margin,

    -- Game and season for the game with the biggest winning margin
    (SELECT
        s.game_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY ABS(s.home_score - s.away_score) DESC
    LIMIT 1) AS biggest_margin_game_id,

    (SELECT
        s.season_id
    FROM schedules s
    WHERE s.status = 2
    ORDER BY ABS(s.home_score - s.away_score) DESC
    LIMIT 1) AS biggest_margin_season_id;
