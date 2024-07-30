<?php

return [
    /*
     * CRAFTER DATA
     */

    'jobs' => [
        8  => 'CRP',
        9  => 'BSM',
        10 => 'ARM',
        11 => 'GSM',
        12 => 'LTW',
        13 => 'WVR',
        14 => 'ALC',
        15 => 'CUL',
    ],

    // Item IDs for shards/crystals/clusters for convenience
    'crystals' => [
        2, 3, 4, 5, 6, 7,
        8, 9, 10, 11, 12, 13,
        14, 15, 16, 17, 18, 19,
    ],

    // Rarity level min/max to target per xpac
    'ranges' => [
        '99-100' => [
            'min' => 684,
            'max' => null,
        ],

        '89-90' => [
            'min' => 555,
            'max' => 640,
        ],
    ],
];
