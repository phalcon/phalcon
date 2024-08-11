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

namespace Phalcon\Tests\Unit\Events\Manager;

use Phalcon\Events\Manager;
use Phalcon\Tests\Fixtures\Events\ComponentOne;
use Phalcon\Tests\Fixtures\Listener\OneListener;
use Phalcon\Tests\Fixtures\Listener\ThreeListener;
use Phalcon\Tests\Fixtures\Listener\TwoListener;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetResponsesTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Events\Manager :: getResponses()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEventsManagerGetResponses(): void
    {
        $manager = new Manager();
        $one     = new OneListener();
        $two     = new TwoListener();
        $three   = new ThreeListener();

        $manager->enablePriorities(false);
        $manager->collectResponses(true);

        $manager->attach('beforeAction', $three, 10);
        $manager->attach('ab:', $two, 20);
        $manager->attach('ab', $one, 30);

        $component = new ComponentOne();
        $component->setEventsManager($manager);

        $component->doAction();

        $expected = ['one'];
        $actual   = $component->getEventsManager()
                              ->getResponses()
        ;
        $this->assertSame($expected, $actual);
    }
}
