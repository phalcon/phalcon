<?php

use Phalcon\Config;
use Phalcon\Test\Module\UnitTest;
use Phalcon\Tests\Fixtures\Di\SomeComponent;

return [
    'unit-test' => [
        'className' => UnitTest::class,
    ],
    'config'    => [
        'className' => Config::class,
        'shared'    => true,
    ],
    'component' => [
        'className' => SomeComponent::class,
        'arguments' => [
            [
                'type' => 'service',
                'name' => 'config',
            ],
        ],
    ],
];
