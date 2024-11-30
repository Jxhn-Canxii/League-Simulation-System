CREATE OR REPLACE VIEW draft_player_statistics AS
SELECT
    draft_id,
    COUNT(*) AS total_players,
    SUM(CASE WHEN is_active = 1 AND team_id IS NOT NULL THEN 1 ELSE 0 END) AS active_players_with_team,
    ROUND(
        (SUM(CASE WHEN is_active = 1 AND team_id IS NOT NULL THEN 1 ELSE 0 END) * 100.0) / COUNT(*),
        2
    ) AS active_percentage
FROM
    players
WHERE
    draft_id IS NOT NULL
GROUP BY
    draft_id
ORDER BY
    draft_id;
