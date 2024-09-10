CREATE VIEW top_20_players_all_time AS
WITH player_stats AS (
    SELECT
        p.id AS player_id,
        p.name AS player_name,
        t.name AS team_name,
        SUM(pg.points) AS total_points,
        SUM(pg.assists) AS total_assists,
        SUM(pg.rebounds) AS total_rebounds,
        SUM(pg.steals) AS total_steals,
        SUM(pg.blocks) AS total_blocks,
        SUM(pg.turnovers) AS total_turnovers
    FROM
        player_game_stats pg
    LEFT JOIN
        players p ON pg.player_id = p.id
    LEFT JOIN
        teams t ON pg.team_id = t.id
    GROUP BY
        p.id, p.name, t.name
),
player_achievements AS (
    SELECT
        p.id AS player_id,
        p.name AS player_name,
        COUNT(DISTINCT CASE WHEN se.finals_mvp_id = p.id THEN se.id END) AS finals_mvp_count,
        COUNT(DISTINCT CASE WHEN s.round = 'finals' AND
                            ((s.home_id = t.id AND s.home_score > s.away_score) OR
                             (s.away_id = t.id AND s.away_score > s.home_score)) THEN s.id END) AS championships_won
    FROM
        player_game_stats pg
    LEFT JOIN
        players p ON pg.player_id = p.id
    LEFT JOIN
        schedules s ON pg.game_id = s.game_id
    LEFT JOIN
        seasons se ON s.season_id = se.id
    LEFT JOIN
        teams t ON pg.team_id = t.id
    GROUP BY
        p.id, p.name
),
player_teams AS (
    SELECT
        p.id AS player_id,
        GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ', ') AS teams_played
    FROM
        player_game_stats pg
    LEFT JOIN
        players p ON pg.player_id = p.id
    LEFT JOIN
        teams t ON pg.team_id = t.id
    GROUP BY
        p.id
),
player_active_status AS (
    SELECT
        p.id AS player_id,
        p.is_active AS is_active
    FROM
        players p
),
merged_player_data AS (
    SELECT
        ps.player_id,
        ps.player_name,
        MAX(ps.team_name) AS team_name,  -- Choose the team_name, adjust if needed
        MAX(pt.teams_played) AS teams_played,
        SUM(ps.total_points) AS total_points,
        SUM(ps.total_assists) AS total_assists,
        SUM(ps.total_rebounds) AS total_rebounds,
        SUM(ps.total_steals) AS total_steals,
        SUM(ps.total_blocks) AS total_blocks,
        SUM(ps.total_turnovers) AS total_turnovers,
        MAX(pa.finals_mvp_count) AS finals_mvp_count,
        MAX(pa.championships_won) AS championships_won,
        MAX(pas.is_active) AS is_active
    FROM
        player_stats ps
    LEFT JOIN
        player_achievements pa ON ps.player_id = pa.player_id
    LEFT JOIN
        player_teams pt ON ps.player_id = pt.player_id
    LEFT JOIN
        player_active_status pas ON ps.player_id = pas.player_id
    GROUP BY
        ps.player_id, ps.player_name
),
ranked_players AS (
    SELECT
        player_id,
        player_name,
        team_name,
        teams_played,
        total_points,
        total_assists,
        total_rebounds,
        total_steals,
        total_blocks,
        total_turnovers,
        finals_mvp_count,
        championships_won,
        is_active,
        RANK() OVER (
            ORDER BY
                (total_points * 1.0 + total_assists * 0.75 + total_rebounds * 0.5 + total_steals * 0.5 + total_blocks * 0.5 - total_turnovers * 0.25) DESC
        ) AS stat_rank,
        RANK() OVER (
            ORDER BY
                (finals_mvp_count * 1.5 + championships_won * 1.0) DESC
        ) AS achievement_rank
    FROM
        merged_player_data
),
combined_ranks AS (
    SELECT
        player_id,
        player_name,
        team_name,
        teams_played,
        total_points,
        total_assists,
        total_rebounds,
        total_steals,
        total_blocks,
        total_turnovers,
        finals_mvp_count,
        championships_won,
        is_active,
        (stat_rank * 0.5 + achievement_rank * 0.5) AS combined_rank
    FROM
        ranked_players
)
SELECT
    player_id,
    player_name,
    team_name,
    teams_played,
    total_points,
    total_assists,
    total_rebounds,
    total_steals,
    total_blocks,
    total_turnovers,
    finals_mvp_count,
    championships_won,
    is_active
FROM
    combined_ranks
ORDER BY
    combined_rank
LIMIT 20;
