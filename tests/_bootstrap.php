<?php

declare(strict_types=1);

$root = dirname(realpath(__DIR__) . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
//define('PROJECT_PATH', $root);

require_once $root . 'tests/_ci/functions.php';

$folders = [
    cacheDir(),
    logsDir(),
    outputDir('assets'),
];

foreach ($folders as $folder) {
    if (true !== file_exists($folder)) {
        mkdir($folder);
    }
}

loadDefined();
