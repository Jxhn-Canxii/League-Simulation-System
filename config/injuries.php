<?php
// config/injuries.php

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
    'broken_wrist' => ['recovery_games' => 12, 'performance_impact' => 0.6], // Long recovery, moderate impact
    'elbow_dislocation' => ['recovery_games' => 18, 'performance_impact' => 0.4], // Moderate to severe injury
    'fractured_ribs' => ['recovery_games' => 15, 'performance_impact' => 0.3], // Rib fractures can take a while to heal
    'torn_meniscus' => ['recovery_games' => 20, 'performance_impact' => 0.2], // Common knee injury
    'pulled_hip_flexor' => ['recovery_games' => 6, 'performance_impact' => 0.7], // Moderate to severe hip strain
    'stress_fracture' => ['recovery_games' => 12, 'performance_impact' => 0.5], // Stress fractures, common in lower body
    'dislocated_shoulder' => ['recovery_games' => 14, 'performance_impact' => 0.4], // Shoulder dislocations can vary in severity
    'fractured_finger' => ['recovery_games' => 10, 'performance_impact' => 0.6], // Minor but still a serious impact in some sports
    'torn_rotator_cuff' => ['recovery_games' => 35, 'performance_impact' => 0.1], // Severe shoulder injury
    'pulled_quadriceps' => ['recovery_games' => 8, 'performance_impact' => 0.55], // Quadriceps strain
    'calf_strain' => ['recovery_games' => 7, 'performance_impact' => 0.65], // Moderate calf muscle strain
    'hernia' => ['recovery_games' => 20, 'performance_impact' => 0.25], // Abdominal injury, can be severe
    'dislocated_knee' => ['recovery_games' => 30, 'performance_impact' => 0.15], // Serious knee injury
    'broken_finger' => ['recovery_games' => 8, 'performance_impact' => 0.6], // Finger fractures
    'neck_injury' => ['recovery_games' => 40, 'performance_impact' => 0.05], // Severe neck injury, rare but serious
    'achilles_tendonitis' => ['recovery_games' => 18, 'performance_impact' => 0.3], // Chronic condition
    'broken_foot' => ['recovery_games' => 20, 'performance_impact' => 0.3], // Severe foot fractures
];

