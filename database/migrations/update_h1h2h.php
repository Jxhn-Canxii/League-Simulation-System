CREATE TABLE `head_to_head` (
    `team_id` INT NOT NULL,
    `opponent_id` INT NOT NULL,
    `wins` INT DEFAULT 0,
    `losses` INT DEFAULT 0,
    `draws` INT DEFAULT 0,
    PRIMARY KEY (`team_id`, `opponent_id`),
    FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`),
    FOREIGN KEY (`opponent_id`) REFERENCES `teams`(`id`)
);



-- Insert/update head-to-head results for both teams in a matchup (home vs away)
INSERT INTO `head_to_head` (`team_id`, `opponent_id`, `wins`, `losses`, `draws`)
SELECT
    LEAST(home_id, away_id) AS team_id,  -- The team with the smaller ID (team perspective)
    GREATEST(home_id, away_id) AS opponent_id,  -- The team with the larger ID (opponent perspective)
    CASE
        WHEN home_score > away_score THEN 1  -- Home team wins
        WHEN home_score < away_score THEN 0  -- Home team loses
        ELSE 0  -- Draw (no wins/losses)
    END AS wins,
    CASE
        WHEN home_score < away_score THEN 1  -- Home team loses
        WHEN home_score > away_score THEN 0  -- Home team wins
        ELSE 0  -- Draw (no wins/losses)
    END AS losses,
    CASE
        WHEN home_score = away_score THEN 1  -- Draw
        ELSE 0  -- No draw
    END AS draws
FROM `schedules`
WHERE status = 2  -- Only completed games
  AND home_score IS NOT NULL
  AND away_score IS NOT NULL
ON DUPLICATE KEY UPDATE
    wins = wins + VALUES(wins),  -- Accumulate wins for the team
    losses = losses + VALUES(losses),  -- Accumulate losses for the team
    draws = draws + VALUES(draws);  -- Accumulate draws for the team

-- Insert the reverse record: Opponent's perspective
INSERT INTO `head_to_head` (`team_id`, `opponent_id`, `wins`, `losses`, `draws`)
SELECT
    GREATEST(home_id, away_id) AS team_id,  -- The team with the larger ID (opponent perspective)
    LEAST(home_id, away_id) AS opponent_id,  -- The team with the smaller ID (team perspective)
    CASE
        WHEN home_score < away_score THEN 1  -- Away team wins (opponent's win)
        WHEN home_score > away_score THEN 0  -- Away team loses (opponent's loss)
        ELSE 0  -- Draw (no wins/losses)
    END AS wins,
    CASE
        WHEN home_score > away_score THEN 1  -- Away team loses (opponent's loss)
        WHEN home_score < away_score THEN 0  -- Away team wins (opponent's win)
        ELSE 0  -- Draw (no wins/losses)
    END AS losses,
    CASE
        WHEN home_score = away_score THEN 1  -- Draw
        ELSE 0  -- No draw
    END AS draws
FROM `schedules`
WHERE status = 2  -- Only completed games
  AND home_score IS NOT NULL
  AND away_score IS NOT NULL
ON DUPLICATE KEY UPDATE
    wins = wins + VALUES(wins),  -- Accumulate wins for the opponent
    losses = losses + VALUES(losses),  -- Accumulate losses for the opponent
    draws = draws + VALUES(draws);  -- Accumulate draws for the opponent
