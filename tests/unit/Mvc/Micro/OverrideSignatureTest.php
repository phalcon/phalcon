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

namespace Phalcon\Tests\Unit\Mvc\Micro;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Mvc\Fake\FakeMicroOverride;

final class OverrideSignatureTest extends AbstractUnitTestCase
{
    /**
     * An application can override setActiveHandler() with the cphalcon
     * signature.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/841
     */
    public function testMvcMicroSetActiveHandlerOverride(): void
    {
        $micro   = new FakeMicroOverride();
        $handler = function () {
            return 'ok';
        };

        $this->assertSame($micro, $micro->setActiveHandler($handler));
        $this->assertSame($handler, $micro->getActiveHandler());
    }
}
