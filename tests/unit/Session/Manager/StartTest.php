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

namespace Phalcon\Tests\Unit\Session\Manager;

use Codeception\Stub;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Session\Manager;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

final class StartTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Manager :: start() - headers sent
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerStartHeadersSent(): void
    {
        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);

        $mock = Stub::make(
            $manager,
            [
                'phpHeadersSent' => true,
            ]
        );

        $actual = $mock->start();
        $this->assertFalse($actual);

        $manager->destroy();
    }
}
