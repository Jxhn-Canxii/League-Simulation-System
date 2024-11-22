<?php
// config/injuries.php

return [
    'minor' => ['recovery_games' => 2, 'performance_impact' => 0.75],
    'moderate' => ['recovery_games' => 5, 'performance_impact' => 0.5],
    'severe' => ['recovery_games' => 10, 'performance_impact' => 0.2],
    'major' => ['recovery_games' => 15, 'performance_impact' => 0.1],
    'critical' => ['recovery_games' => 20, 'performance_impact' => 0.05],
    'sprained_ankle' => ['recovery_games' => 3, 'performance_impact' => 0.85],
    'knee_sprain' => ['recovery_games' => 7, 'performance_impact' => 0.65],
    'groin_strain' => ['recovery_games' => 4, 'performance_impact' => 0.7],
    'hamstring_strain' => ['recovery_games' => 6, 'performance_impact' => 0.6],
    'shoulder_dislocation' => ['recovery_games' => 10, 'performance_impact' => 0.5],
    'concussion' => ['recovery_games' => 8, 'performance_impact' => 0.4],
    'torn_acl' => ['recovery_games' => 25, 'performance_impact' => 0.1],
    'achilles_rupture' => ['recovery_games' => 30, 'performance_impact' => 0.05],
    'fractured_leg' => ['recovery_games' => 40, 'performance_impact' => 0.05],
    'cartilage_damage' => ['recovery_games' => 15, 'performance_impact' => 0.3],
    'spinal_injury' => ['recovery_games' => 50, 'performance_impact' => 0.01],
];
