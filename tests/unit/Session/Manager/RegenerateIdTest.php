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

use Phalcon\Session\Manager;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class RegenerateIdTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Manager :: regenerateId()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerRegenerateId(): void
    {
        $manager = new Manager();

        $files = $this->newService('sessionStream');

        $manager->setAdapter($files);
        $manager->start();

        $current = $manager->getId();

        $manager->regenerateId(true);

        $this->assertNotEquals(
            $current,
            $manager->getId()
        );

        $manager->destroy();
    }
}
