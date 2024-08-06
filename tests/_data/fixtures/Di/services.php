<?php

use Phalcon\Config\Config;
use Phalcon\Tests\Fixtures\Di\ServiceComponent;
use Phalcon\Tests\UnitTestCase;

return [
    'unit-test' => [
        'className' => UnitTestCase::class,
    ],
    'config'    => [
        'className' => Config::class,
        'shared'    => true,
    ],
    'component' => [
        'className' => ServiceComponent::class,
        'arguments' => [
            [
                'type' => 'service',
                'name' => 'config',
            ],
        ],
    ],
];
