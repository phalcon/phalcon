<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Mvc;

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Exceptions\InvalidConfigSource;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Mvc\View\Engine\Volt\Exceptions\InvalidHaystack;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

/**
 * Guards the Zephir source has and the PHP mirror had dropped.
 */
final class MirrorGuardsTest extends AbstractUnitTestCase
{
    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function invalidConfigProvider(): array
    {
        return [
            'string' => ['not-a-config'],
            'int'    => [42],
            'bool'   => [true],
            'object' => [new stdClass()],
        ];
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function invalidHaystackProvider(): array
    {
        return [
            'int'    => [42],
            'float'  => [1.5],
            'object' => [new stdClass()],
            'bool'   => [true],
        ];
    }

    /**
     * A config that is neither an array nor a `ConfigInterface` is rejected
     * instead of silently configuring nothing.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-09-04
     */
    #[DataProvider('invalidConfigProvider')]
    public function testMvcRouterLoadFromConfigRejectsInvalidSource(mixed $config): void
    {
        $router = new Router();

        $this->expectException(InvalidConfigSource::class);

        $router->loadFromConfig($config);
    }

    /**
     * An array haystack still resolves.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-09-04
     */
    public function testMvcViewEngineVoltIsIncludedArray(): void
    {
        $volt = new Volt($this->createMock(\Phalcon\Mvc\ViewBaseInterface::class));

        $this->assertTrue($volt->isIncluded('a', ['a', 'b']));
        $this->assertFalse($volt->isIncluded('c', ['a', 'b']));
    }

    /**
     * A haystack that is neither an array nor a string is rejected instead of
     * reaching `mb_strpos()`.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-09-04
     */
    #[DataProvider('invalidHaystackProvider')]
    public function testMvcViewEngineVoltIsIncludedRejectsInvalidHaystack(mixed $haystack): void
    {
        $volt = new Volt($this->createMock(\Phalcon\Mvc\ViewBaseInterface::class));

        $this->expectException(InvalidHaystack::class);

        $volt->isIncluded('needle', $haystack);
    }

    /**
     * A string haystack still resolves.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-09-04
     */
    public function testMvcViewEngineVoltIsIncludedString(): void
    {
        $volt = new Volt($this->createMock(\Phalcon\Mvc\ViewBaseInterface::class));

        $this->assertTrue($volt->isIncluded('ee', 'needle'));
        $this->assertFalse($volt->isIncluded('zz', 'needle'));
    }
}
