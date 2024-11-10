CREATE TABLE all_time_top_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_category VARCHAR(50) NOT NULL, -- e.g., 'points', 'rebounds', 'assists', etc.
    player_id INT NOT NULL,
    player_name VARCHAR(100),            -- Stores player name for easy reference
    game_id INT NOT NULL,
    team_id INT NOT NULL,
    opponent_id INT NOT NULL,            -- ID of the opposing team
    season_id INT NOT NULL,
    stat_value INT NOT NULL,             -- Value of the stat (e.g., points scored)
    recorded_at DATE NOT NULL,           -- Date of the game
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
);
