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

namespace Phalcon\Tests\Unit\Messages;

use Phalcon\Messages\Message;
use Phalcon\Messages\Messages;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

/**
 * cphalcon skips an entry that is not an object when it filters the messages.
 *
 * @issue https://github.com/phalcon/phalcon/issues/887
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testMessagesMessagesFilterEntryInt(): void
    {
        $messages = new Messages([1, new Message('m', 'f')]);

        $this->assertCount(1, $messages->filter('f'));
    }
}
