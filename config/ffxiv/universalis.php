<?php

return [
    /*
     * UNIVERSALIS SETTINGS
     */

    // How many minutes local data is cached for (minimum).
    'cache_lifetime' => 90,

    // How many minutes between requests for a given world (minimum).
    'rate_limit_lifetime' => 45,

    // Number of hours for which data is considered "recent"/valid for recommendations
    'data_lifetime' => 24,
];
