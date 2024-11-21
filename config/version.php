<?php

// From https://gist.github.com/breadthe/ce6a815b295c485b4bd91c08a79e1bf2
// Based on this tweet by @Xewl https://twitter.com/Xewl/status/1459219464369627144

if (file_exists(base_path('version'))) {
    $gitVer = file(base_path('version'))[0];
}

return [
    'gitVer' => $gitVer ?? 'unknown',
    'tag'    => Composer\InstalledVersions::getRootPackage()['pretty_version'],
];
