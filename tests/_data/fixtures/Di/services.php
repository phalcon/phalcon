<?php

use Phalcon\Config\Config;
use Phalcon\Tests\Fixtures\Di\ServiceComponent;
use Phalcon\Tests\AbstractUnitTestCase;

return [
    'unit-test' => [
        'className' => AbstractUnitTestCase::class,
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
