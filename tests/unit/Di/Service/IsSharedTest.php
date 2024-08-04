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

namespace Phalcon\Tests\Unit\Di\Service;

use Codeception\Example;
use Phalcon\Di\Service;
use Phalcon\Html\Escaper;
use Phalcon\Tests\UnitTestCase;

class IsSharedTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Di\Service :: isShared()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-09-09
     */
    public function testDiServiceIsShared(
        mixed $service,
        bool $expected
    ): void {
        $actual   = $service->isShared();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                new Service(Escaper::class),
                false,
            ],
            [
                new Service(Escaper::class, true),
                true,
            ],
            [
                new Service(Escaper::class, false),
                false,
            ],
        ];
    }
}
