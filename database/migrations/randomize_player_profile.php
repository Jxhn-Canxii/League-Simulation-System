UPDATE `players`
SET
    `role` = CASE
        WHEN `overall_rating` BETWEEN 85 AND 99 THEN 'star player'
        WHEN `overall_rating` BETWEEN 75 AND 84 THEN 'starter'
        WHEN `overall_rating` BETWEEN 60 AND 74 THEN 'role player'
        ELSE 'bench'
    END,
    `is_rookie` = 1,
    `is_active` = 1,
    `team_id` = 0,
    `contract_years` = 0,
    `age` = FLOOR(RAND() * (40 - 20 + 1)) + 20,  -- Random age between 20 and 40
    `retirement_age` = FLOOR(RAND() * (40 - 30 + 1)) + 30,  -- Random retirement age between 30 and 40
    `injury_prone_percentage` = FLOOR(RAND() * 100) + 1,  -- Random injury prone percentage between 1 and 100
    `shooting_rating` = FLOOR(RAND() * (99 - 60 + 1)) + 60,  -- Random shooting rating between 60 and 99
    `defense_rating` = FLOOR(RAND() * (99 - 60 + 1)) + 60,  -- Random defense rating between 60 and 99
    `passing_rating` = FLOOR(RAND() * (99 - 60 + 1)) + 60,  -- Random passing rating between 60 and 99
    `rebounding_rating` = FLOOR(RAND() * (99 - 60 + 1)) + 60,  -- Random rebounding rating between 60 and 99
    `overall_rating` = (
        (`shooting_rating` + `defense_rating` + `passing_rating` + `rebounding_rating`) / 4
    ),
    `updated_at` = NOW()
