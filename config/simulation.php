<?php

return [
    // How long a "round" is — cron fires every minute, but a post only advances
    // to the next round when this many minutes have elapsed since its last tick.
    'round_duration_minutes' => 1,

    // Reach mechanics
    'base_exposure_per_round' => 15,
    'exposure_growth_factor' => 1.5,
    'trending_threshold' => 20,

    // Persona behavior
    'max_llm_calls_per_round' => 25,
    'comments_visible_to_personas' => 8,
    'notification_return_probability' => 0.8,

    // Graph-based spread (on top of random discovery)
    'reaction_spread_rate' => 10,  // % of a reactor's friends exposed next round
    'share_spread_rate' => 40,     // % of a sharer's friends exposed next round (usually higher)

    // Round limits
    'max_rounds' => 30,
    'inactive_threshold_rounds' => 5,
];
