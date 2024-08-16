CREATE TABLE `player_ratings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `player_id` BIGINT UNSIGNED NOT NULL,
    `season_id` BIGINT UNSIGNED NOT NULL,
    `role` ENUM('star player', 'starter', 'role player', 'bench') NOT NULL,
    `shooting_rating` TINYINT UNSIGNED NOT NULL,
    `defense_rating` TINYINT UNSIGNED NOT NULL,
    `passing_rating` TINYINT UNSIGNED NOT NULL,
    `rebounding_rating` TINYINT UNSIGNED NOT NULL,
    `overall_rating` TINYINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
