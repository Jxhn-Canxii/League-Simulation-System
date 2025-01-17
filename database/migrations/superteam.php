-- Set the team_id and season_id variables
SET @team_id = 44;  -- Change this value to update a different team
SET @season_id = 10;  -- Change this value to the desired season ID

-- Update players with the specified team_id
UPDATE players
JOIN teams ON players.team_id = teams.id  -- Join with teams table to get team_name
SET
    shooting_rating = CASE
        WHEN role = 'star player' THEN FLOOR(98 + RAND()) -- Star players: 99-98
        WHEN role = 'starter' THEN FLOOR(90 + RAND() * 6) -- Starters: 90-95
        WHEN role = 'role player' THEN FLOOR(85 + RAND() * 5) -- Role players: 85-89
        WHEN role = 'bench' THEN FLOOR(80 + RAND() * 5) -- Bench: 80-84
        ELSE shooting_rating
    END,
    defense_rating = CASE
        WHEN role = 'star player' THEN FLOOR(98 + RAND())
        WHEN role = 'starter' THEN FLOOR(90 + RAND() * 6)
        WHEN role = 'role player' THEN FLOOR(85 + RAND() * 5)
        WHEN role = 'bench' THEN FLOOR(80 + RAND() * 5)
        ELSE defense_rating
    END,
    passing_rating = CASE
        WHEN role = 'star player' THEN FLOOR(98 + RAND())
        WHEN role = 'starter' THEN FLOOR(90 + RAND() * 6)
        WHEN role = 'role player' THEN FLOOR(85 + RAND() * 5)
        WHEN role = 'bench' THEN FLOOR(80 + RAND() * 5)
        ELSE passing_rating
    END,
    rebounding_rating = CASE
        WHEN role = 'star player' THEN FLOOR(98 + RAND())
        WHEN role = 'starter' THEN FLOOR(90 + RAND() * 6)
        WHEN role = 'role player' THEN FLOOR(85 + RAND() * 5)
        WHEN role = 'bench' THEN FLOOR(80 + RAND() * 5)
        ELSE rebounding_rating
    END,
    injury_prone_percentage = FLOOR(10 + RAND() * 10), -- Injury percentage: 0-10
    contract_years = FLOOR(4 + RAND() * 4), -- Contract years: 4-7
    overall_rating = (
        (shooting_rating + defense_rating + passing_rating + rebounding_rating + (99 - injury_prone_percentage)) / 5
    )
WHERE players.team_id = @team_id;

-- Insert transaction records with the same team_id for from_team_id and to_team_id
INSERT INTO transactions (player_id, season_id, details, from_team_id, to_team_id, status)
SELECT
    p.id AS player_id,
    @season_id AS season_id,  -- Using the season_id variable here
    CONCAT('Has restructured contract with ', t.name, ' For ', p.contract_years, ' years') AS details,
    @team_id AS from_team_id,  -- Using the @team_id variable for both from_team_id and to_team_id
    @team_id AS to_team_id,    -- Same team_id for both from and to team
    'resigned' AS status
FROM players p
JOIN teams t ON p.team_id = t.id  -- Join with teams table to get team_name
WHERE p.team_id = @team_id;
