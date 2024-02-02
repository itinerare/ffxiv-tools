<?php

// From https://gist.github.com/breadthe/ce6a815b295c485b4bd91c08a79e1bf2
// Based on this tweet by @Xewl https://twitter.com/Xewl/status/1459219464369627144

if (file_exists(base_path('version'))) {
    $hash = file_get_contents(base_path('version'));
    $date = Carbon\Carbon::parse(filemtime(base_path('version')));
    $string = sprintf('v%s-%s', $date->format('y.m'), $hash);
}

return [
    'date'   => $date ?? 'unknown',
    'hash'   => $hash ?? 'unknown',
    'string' => $string ?? 'v??.??-unknown',
];
