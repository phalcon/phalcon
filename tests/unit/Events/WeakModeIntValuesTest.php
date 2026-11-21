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

namespace Phalcon\Tests\Unit\Events;

use Phalcon\Contracts\Events\Subscriber;
use Phalcon\Events\Manager;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

use function array_keys;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/887
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testEventsManagerAddSubscriberListForm(): void
    {
        $manager = new Manager();
        $manager->addSubscriber($this->newListSubscriber());

        $this->assertCount(1, $manager->getListeners('0'));
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/898
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testEventsManagerGetListenerMapIntType(): void
    {
        $manager = new Manager();
        $manager->attach(
            '123',
            function () {
                return true;
            }
        );

        $map = $manager->getListenerMap();

        $this->assertSame([123], array_keys($map));
        $this->assertCount(1, $map[123]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testEventsManagerRemoveSubscriberListForm(): void
    {
        $subscriber = $this->newListSubscriber();
        $manager    = new Manager();
        $manager->addSubscriber($subscriber);
        $manager->removeSubscriber($subscriber);

        $this->assertCount(0, $manager->getListeners('0'));
    }

    /**
     * A list of events gives the int key 0 as the event name
     */
    private function newListSubscriber(): Subscriber
    {
        return new class () implements Subscriber {
            /**
             * @return array<int, string>
             */
            public static function getSubscribedEvents(): array
            {
                return ['onBoot'];
            }

            public function onBoot(): string
            {
                return 'boot';
            }
        };
    }
}
