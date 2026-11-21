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

namespace Phalcon\Tests\Unit\Flash;

use Phalcon\Flash\Direct;
use Phalcon\Flash\Session;
use Phalcon\Html\Escaper;
use Phalcon\Session\Adapter\Noop;
use Phalcon\Session\Manager;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

use function ob_get_clean;
use function ob_start;

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
    public function testFlashDirectMessageArrayInt(): void
    {
        $flash = new Direct(new Escaper());
        $flash->setImplicitFlush(false);

        $this->assertSame(
            '<div class="successMessage">123</div>' . PHP_EOL,
            $flash->message('success', [123])
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testFlashSessionGetMessagesTypeInt(): void
    {
        $session = new Manager();
        $session->setAdapter(new Noop());

        $flash = new Session(new Escaper(), $session);

        $this->assertSame([], $flash->getMessages(1));
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/898
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testFlashSessionOutputTypeInt(): void
    {
        $session = new Manager();
        $session->setAdapter(new Noop());
        $session->start();

        $flash = new Session(new Escaper(), $session);
        $flash->message('5', 'hi');

        ob_start();
        try {
            $flash->output();
        } finally {
            $actual = ob_get_clean();
            $session->destroy();
        }

        $this->assertSame('<div>hi</div>' . PHP_EOL, $actual);
    }
}
