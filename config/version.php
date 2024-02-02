<?php

// From https://gist.github.com/breadthe/ce6a815b295c485b4bd91c08a79e1bf2
// Based on this tweet by @Xewl https://twitter.com/Xewl/status/1459219464369627144

$hash = trim(exec('git log --pretty="%h" -n1 HEAD'));
$date = Carbon\Carbon::parse(trim(exec('git log -n1 --pretty=%ci HEAD')));

return [
    'date'   => $date,
    'hash'   => $hash,
    'string' => sprintf('v%s-%s', $date->format('y.m'), $hash),
];
