CREATE TABLE trade_proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    team_to_id INT NOT NULL,
    player_from_id INT NOT NULL,
    player_to_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
