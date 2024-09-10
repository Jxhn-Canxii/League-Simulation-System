CREATE VIEW top_10_players_by_decade AS
WITH player_achievements AS (
    SELECT
        p.id AS player_id,
        p.name AS player_name,
        t.name AS team_name,
        (FLOOR((se.id - 1) / 10) * 10 + 1) AS decade_starting_season,
        SUM(pg.points) AS total_points,
        SUM(pg.assists) AS total_assists,
        SUM(pg.rebounds) AS total_rebounds,
        SUM(pg.steals) AS total_steals,
        SUM(pg.blocks) AS total_blocks,
        SUM(pg.turnovers) AS total_turnovers,
        COUNT(DISTINCT CASE WHEN s.round = 'finals' AND
                            ((s.home_id = t.id AND s.home_score > s.away_score) OR
                             (s.away_id = t.id AND s.away_score > s.home_score)) THEN s.id END) AS championships_won,
        COUNT(DISTINCT CASE WHEN se.finals_mvp_id = p.id THEN se.id END) AS finals_mvp_count
    FROM
        player_game_stats pg
    LEFT JOIN
        players p ON pg.player_id = p.id
    LEFT JOIN
        teams t ON pg.team_id = t.id
    LEFT JOIN
        schedules s ON pg.game_id = s.game_id
    LEFT JOIN
        seasons se ON s.season_id = se.id
    GROUP BY
        p.id, p.name, t.name, decade_starting_season
),
ranked_players AS (
    SELECT
        pa.player_id,
        pa.player_name,
        pa.team_name,
        pa.decade_starting_season,
        pa.total_points,
        pa.total_assists,
        pa.total_rebounds,
        pa.total_steals,
        pa.total_blocks,
        pa.total_turnovers,
        pa.championships_won,
        pa.finals_mvp_count,
        ROW_NUMBER() OVER (PARTITION BY pa.team_name, pa.decade_starting_season
                           ORDER BY
                               pa.total_points DESC,
                               pa.total_assists DESC,
                               pa.total_rebounds DESC,
                               pa.total_steals DESC,
                               pa.total_blocks DESC,
                               pa.total_turnovers ASC,
                               pa.championships_won DESC,
                               pa.finals_mvp_count DESC
        ) AS rank_in_decade
    FROM
        player_achievements pa
)
SELECT
    player_id,
    player_name,
    team_name,
    decade_starting_season,
    total_points,
    total_assists,
    total_rebounds,
    total_steals,
    total_blocks,
    total_turnovers,
    championships_won,
    finals_mvp_count
FROM
    ranked_players
WHERE
    rank_in_decade <= 10
ORDER BY
    decade_starting_season, rank_in_decade;
