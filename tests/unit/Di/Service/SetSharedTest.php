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

class SetSharedTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Di\Service :: setShared()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-09-09
     */
    public function testDiServiceSetShared(
        mixed $service
    ): void {
        $service->setShared(true);

        $actual = $service->isShared();
        $this->assertTrue($actual);

        $service->setShared(false);

        $actual = $service->isShared();
        $this->assertFalse($actual);
    }

    /**
     * @return Service[][]
     */
    public static function getExamples(): array
    {
        return [
            [
                new Service(Escaper::class),
            ],
            [
                new Service(Escaper::class, true),
            ],
            [
                new Service(Escaper::class, false),
            ],
        ];
    }
}
