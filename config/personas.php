<?php

return [
    'output_path' => storage_path('app/personas'),
    'image_path'  => storage_path('app/public/personas'),

    // Danish online population — approximate distributions (Danmarks Statistik / DR Medieforskning).
    // Personality / political / subcultural variation now lives in Personlighedsstrukturer (blueprints).
    'demographics' => [
        'age_brackets' => [
            ['range' => [16, 24], 'weight' => 13],
            ['range' => [25, 34], 'weight' => 17],
            ['range' => [35, 44], 'weight' => 16],
            ['range' => [45, 54], 'weight' => 17],
            ['range' => [55, 64], 'weight' => 16],
            ['range' => [65, 79], 'weight' => 18],
            ['range' => [80, 89], 'weight' => 3],
        ],
        'gender' => ['mand' => 49, 'kvinde' => 49, 'andet' => 2],
        'region' => [
            'Hovedstaden' => 32,
            'Sjælland'    => 14,
            'Syddanmark'  => 21,
            'Midtjylland' => 23,
            'Nordjylland' => 10,
        ],
        'city_type' => ['storby' => 35, 'mellemstor by' => 30, 'mindre by' => 25, 'landdistrikt' => 10],
        'education' => [
            'folkeskole'                => 18,
            'gymnasial'                 => 12,
            'erhvervsuddannelse'        => 32,
            'kort videregående'         => 6,
            'mellemlang videregående'   => 18,
            'lang videregående'         => 14,
        ],
        'heritage' => [
            'dansk'                                    => 86,
            'vestlig indvandrer/efterkommer'           => 4,
            'ikke-vestlig indvandrer/efterkommer'      => 10,
        ],
    ],
];
