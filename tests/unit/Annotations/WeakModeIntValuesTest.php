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

namespace Phalcon\Tests\Unit\Annotations;

use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Annotations\Collection;
use Phalcon\Annotations\ReaderInterface;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use stdClass;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/900
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @issue  https://github.com/phalcon/phalcon/issues/900
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testAnnotationsAdapterGetMethodIntMethodKey(): void
    {
        $adapter = new Memory();
        $adapter->setReader(
            new class () implements ReaderInterface {
                public function parse(string $className): array
                {
                    return [
                        'methods' => [
                            '2024' => [
                                [
                                    'type' => 300,
                                    'name' => 'Get',
                                ],
                            ],
                        ],
                    ];
                }
            }
        );

        $actual = $adapter->getMethod(stdClass::class, 'foo');

        $this->assertInstanceOf(Collection::class, $actual);
        $this->assertCount(0, $actual);
    }
}
