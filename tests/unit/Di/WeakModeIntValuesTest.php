<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Di;

use ArrayObject;
use Phalcon\Config\Config;
use Phalcon\Config\ConfigInterface;
use Phalcon\Di\Di;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use RuntimeException;
use stdClass;

use function property_exists;
use function restore_error_handler;
use function set_error_handler;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/885
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testDiArrayAccessIntOffset(): void
    {
        $container    = new Di();
        $container[1] = function () {
            return new ArrayObject([1]);
        };

        $this->assertTrue(isset($container[1]));
        $this->assertInstanceOf(ArrayObject::class, $container[1]);

        unset($container[1]);

        $this->assertFalse(isset($container[1]));
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/900
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testDiLoadFromConfigIntServiceName(): void
    {
        $container = new class () extends Di {
            public function load(ConfigInterface $config): void
            {
                $this->loadFromConfig($config);
            }
        };

        $container->load(new Config(['2024' => ['className' => stdClass::class]]));

        $this->assertInstanceOf(stdClass::class, $container->get('2024'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testDiServiceBuilderClassNameInt(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid class name');

        $container = new Di();
        $container->set('service', ['className' => 123]);
        $container->get('service');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testDiServiceBuilderPropertyNameInt(): void
    {
        $container = new Di();
        $container->set(
            'service',
            [
                'className'  => stdClass::class,
                'properties' => [
                    [
                        'name'  => 1,
                        'value' => [
                            'type'  => 'parameter',
                            'value' => 'x',
                        ],
                    ],
                ],
            ]
        );

        $warnings = [];
        set_error_handler(
            function (int $number, string $message) use (&$warnings): bool {
                $warnings[] = $message;

                return true;
            }
        );

        try {
            $service = $container->get('service');
        } finally {
            restore_error_handler();
        }

        $this->assertCount(1, $warnings);
        $this->assertStringContainsString('Property should be string', $warnings[0]);
        $this->assertFalse(property_exists($service, '1'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testDiServiceBuilderServiceNameInt(): void
    {
        $container = new Di();
        $container->set(
            '1',
            function () {
                return [7];
            }
        );
        $container->set(
            'service',
            [
                'className' => ArrayObject::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => 1,
                    ],
                ],
            ]
        );
        $container->set(
            'instance',
            [
                'className' => ArrayObject::class,
                'arguments' => [
                    [
                        'type'      => 'instance',
                        'className' => 1,
                    ],
                ],
            ]
        );

        $this->assertCount(1, $container->get('service'));
        $this->assertCount(1, $container->get('instance'));
    }
}
