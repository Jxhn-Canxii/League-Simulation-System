<?php
// config/injuries.php

return [
    // Specific injuries with recovery games and performance impact
    'sprained_ankle' => ['recovery_games' => 3, 'performance_impact' => 0.85, 'miss_game_chance' => 0.95],
    'knee_sprain' => ['recovery_games' => 7, 'performance_impact' => 0.65, 'miss_game_chance' => 0.90],
    'groin_strain' => ['recovery_games' => 4, 'performance_impact' => 0.7, 'miss_game_chance' => 0.85],
    'hamstring_strain' => ['recovery_games' => 6, 'performance_impact' => 0.6, 'miss_game_chance' => 0.80],
    'shoulder_dislocation' => ['recovery_games' => 10, 'performance_impact' => 0.5, 'miss_game_chance' => 0.70],

    // Other injury types
    'sick' => ['recovery_games' => 1, 'performance_impact' => 0.75],
    'thumb_pain' => ['recovery_games' => 2, 'performance_impact' => 0.75],
    'headache' => ['recovery_games' => 2, 'performance_impact' => 0.75],
    'toothache' => ['recovery_games' => 2, 'performance_impact' => 0.75],
    'colds' => ['recovery_games' => 3, 'performance_impact' => 0.75],
    'concussion' => ['recovery_games' => 8, 'performance_impact' => 0.4],
    'torn_acl' => ['recovery_games' => 25, 'performance_impact' => 0.1],
    'achilles_rupture' => ['recovery_games' => 30, 'performance_impact' => 0.05],
    'fractured_leg' => ['recovery_games' => 40, 'performance_impact' => 0.05],
    'cartilage_damage' => ['recovery_games' => 15, 'performance_impact' => 0.3],
    'spinal_injury' => ['recovery_games' => 50, 'performance_impact' => 0.01],
    'broken_wrist' => ['recovery_games' => 12, 'performance_impact' => 0.6],
    'elbow_dislocation' => ['recovery_games' => 18, 'performance_impact' => 0.4],
    'fractured_ribs' => ['recovery_games' => 15, 'performance_impact' => 0.3],
    'torn_meniscus' => ['recovery_games' => 20, 'performance_impact' => 0.2],
    'pulled_hip_flexor' => ['recovery_games' => 6, 'performance_impact' => 0.7],
    'stress_fracture' => ['recovery_games' => 12, 'performance_impact' => 0.5],
    'dislocated_shoulder' => ['recovery_games' => 14, 'performance_impact' => 0.4],
    'fractured_finger' => ['recovery_games' => 10, 'performance_impact' => 0.6],
    'torn_rotator_cuff' => ['recovery_games' => 35, 'performance_impact' => 0.1],
    'pulled_quadriceps' => ['recovery_games' => 8, 'performance_impact' => 0.55],
    'calf_strain' => ['recovery_games' => 7, 'performance_impact' => 0.65],
    'hernia' => ['recovery_games' => 20, 'performance_impact' => 0.25],
    'dislocated_knee' => ['recovery_games' => 30, 'performance_impact' => 0.15],
    'broken_finger' => ['recovery_games' => 8, 'performance_impact' => 0.6],
    'neck_injury' => ['recovery_games' => 40, 'performance_impact' => 0.05],
    'achilles_tendonitis' => ['recovery_games' => 18, 'performance_impact' => 0.3],
    'broken_foot' => ['recovery_games' => 20, 'performance_impact' => 0.3],

    // Non-injury factors (e.g., suspensions, rest, personal reasons)
    'resting' => ['recovery_games' => 2, 'performance_impact' => 0.9, 'miss_game_chance' => 0.85],  // Player rested for recovery or rotation
    'suspension' => ['recovery_games' => 5, 'performance_impact' => 0.0, 'miss_game_chance' => 1.0],  // Suspended for misconduct
    'personal_reason' => ['recovery_games' => 3, 'performance_impact' => 0.5, 'miss_game_chance' => 0.75],  // Player misses games for personal matters
    'logistics_issue' => ['recovery_games' => 1, 'performance_impact' => 0.7, 'miss_game_chance' => 0.9],  // Travel or weather-related issues

    // Additional Non-Injury Factors:
    'family_emergency' => ['recovery_games' => 3, 'performance_impact' => 0.4, 'miss_game_chance' => 0.85],  // Personal emergency at home
    'contract_dispute' => ['recovery_games' => 4, 'performance_impact' => 0.6, 'miss_game_chance' => 0.75],  // Dispute regarding contract terms
    'mental_health' => ['recovery_games' => 6, 'performance_impact' => 0.5, 'miss_game_chance' => 0.6],  // Mental health days off
    'player_protest' => ['recovery_games' => 1, 'performance_impact' => 0.6, 'miss_game_chance' => 0.9],  // Player involved in protest or activism affecting availability
    'travel_fatigue' => ['recovery_games' => 1, 'performance_impact' => 0.65, 'miss_game_chance' => 0.7],  // Fatigue due to long travel
];
