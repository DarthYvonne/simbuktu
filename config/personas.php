<?php

return [
    'output_path' => storage_path('app/personas'),
    'image_path' => storage_path('app/public/personas'),

    // Danish online population — approximate distributions (from Danmarks Statistik / DR Medieforskning)
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
            'Sjælland' => 14,
            'Syddanmark' => 21,
            'Midtjylland' => 23,
            'Nordjylland' => 10,
        ],
        'city_type' => ['storby' => 35, 'mellemstor by' => 30, 'mindre by' => 25, 'landdistrikt' => 10],
        'education' => [
            'folkeskole' => 18,
            'gymnasial' => 12,
            'erhvervsuddannelse' => 32,
            'kort videregående' => 6,
            'mellemlang videregående' => 18,
            'lang videregående' => 14,
        ],
        'heritage' => [
            'dansk' => 86,
            'vestlig indvandrer/efterkommer' => 4,
            'ikke-vestlig indvandrer/efterkommer' => 10,
        ],
    ],

    'subcultures' => [
        'manosfære/redpill' => 5, 'hash-miljø' => 5, 'klima-aktivisme' => 5, 'boomer-Facebook' => 5,
        'Trump-sympati (DK)' => 5, 'fitness/biohacking' => 5, 'Christiania-miljø' => 5, 'jæger/friluft' => 5,
        'gaming' => 5, 'crypto/finans' => 5, 'konspirationsmiljø' => 5, 'LGBTQ+-aktivisme' => 5,
        'akademisk venstrefløj' => 5, 'håndværkerkultur' => 5, 'wellness/spiritualitet' => 5,
        'fodboldkultur' => 5, 'landbrugsmiljø' => 5, 'motorcykelmiljø' => 5, 'muslimsk miljø' => 5,
        'kristen frikirke' => 5, 'techbro' => 5, 'antifeministisk' => 5, 'feministisk' => 5,
        'mainstream' => 5, 'kulturelite' => 5, 'foreningsdanmark' => 5,
    ],

    'trigger_topics' => [
        'indvandring', 'hash', 'kønsidentitet', 'skattepolitik', 'dyrevelfærd',
        'Israel/Palæstina', 'kongehuset', 'vindmøller', 'politiet', 'metoo',
        'maskulinitet', 'islam', 'EU', 'ADHD-medicin', 'folkeskolen',
        'sundhedsvæsenet', 'boligmarked', 'klima', 'gentrificering', 'Christiania',
        'bandekriminalitet', 'woke', 'ytringsfrihed', 'landbruget',
        'akademikere', 'KBH vs provinsen', 'pension', 'unge i dag',
    ],

    'parties' => [
        'A', 'V', 'M', 'SF', 'DD', 'LA', 'K', 'EL', 'RV', 'DF', 'NB', 'Æ', 'stemmer ikke',
    ],

    'conflict_styles' => [
        'undvigende', 'passiv-aggressiv', 'direkte konfronterende', 'saglig-insisterende', 'trollende',
    ],

    'language_registers' => [
        'akademisk', 'dagligdags', 'slang-tungt', 'dialektfarvet', 'emoji-rigt', 'VERSALER',
    ],
];
