<?php
declare(strict_types=1);

use Phalcon\Support\Debug;

$_ENV['APP_DEBUG'] = true;

$debug = new Debug();

$debug->setBlacklist(
    [
        'request' => ['some'],
        'server'  => ['hostname'],
    ]
);

$debug->listen();
