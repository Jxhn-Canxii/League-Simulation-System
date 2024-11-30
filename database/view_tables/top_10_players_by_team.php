CREATE VIEW top_10_players_by_team_all_time AS
WITH player_achievements AS (
    SELECT
        p.id AS player_id,
        p.name AS player_name,
        ps.team_id,
        t.name AS team_name,
        SUM(ps.total_points) AS total_points,
        SUM(ps.total_assists) AS total_assists,
        SUM(ps.total_rebounds) AS total_rebounds,
        SUM(ps.total_steals) AS total_steals,
        SUM(ps.total_blocks) AS total_blocks,
        SUM(ps.total_turnovers) AS total_turnovers,
        COUNT(DISTINCT CASE WHEN s.round = 'finals' AND
                            ((s.home_id = ps.team_id AND s.home_score > s.away_score) OR
                             (s.away_id = ps.team_id AND s.away_score > s.home_score)) THEN s.id END) AS championships_won,
        COUNT(DISTINCT CASE WHEN se.finals_mvp_id = p.id THEN se.id END) AS finals_mvp_count,
        COUNT(DISTINCT sa.id) AS awards_won -- Aggregate awards from season_awards
    FROM
        player_season_stats ps
    LEFT JOIN
        players p ON ps.player_id = p.id
    LEFT JOIN
        teams t ON ps.team_id = t.id
    LEFT JOIN
        schedules s ON ps.season_id = s.season_id
    LEFT JOIN
        seasons se ON s.season_id = se.id
    LEFT JOIN
        season_awards sa ON sa.player_id = p.id -- Join with season_awards table
    GROUP BY
        p.id, p.name, ps.team_id, t.name
),
ranked_players AS (
    SELECT
        pa.player_id,
        pa.player_name,
        pa.team_id,
        pa.team_name,
        pa.total_points,
        pa.total_assists,
        pa.total_rebounds,
        pa.total_steals,
        pa.total_blocks,
        pa.total_turnovers,
        pa.championships_won,
        pa.finals_mvp_count,
        pa.awards_won,
        ROW_NUMBER() OVER (PARTITION BY pa.team_id ORDER BY
            (pa.total_points * 1.0 + pa.total_assists * 0.75 + pa.total_rebounds * 0.5 + pa.total_steals * 0.5 + pa.total_blocks * 0.5 - pa.total_turnovers * 0.25) DESC,
            pa.championships_won DESC,
            pa.finals_mvp_count DESC,
            pa.awards_won DESC -- Include awards in ranking
        ) AS rank_in_team
    FROM
        player_achievements pa
)
SELECT
    player_id,
    player_name,
    team_id,
    team_name,
    total_points,
    total_assists,
    total_rebounds,
    total_steals,
    total_blocks,
    total_turnovers,
    championships_won,
    finals_mvp_count,
    awards_won
FROM
    ranked_players
WHERE
    rank_in_team <= 10
ORDER BY
    team_id, rank_in_team;
