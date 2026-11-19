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

namespace Phalcon\Tests\Unit\Di;

use Phalcon\Events\Manager;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Di\Fake\FakeDiOverride;

final class OverrideSignatureTest extends AbstractUnitTestCase
{
    /**
     * A container can override setInternalEventsManager() with the cphalcon
     * signature.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/841
     */
    public function testDiSetInternalEventsManagerOverride(): void
    {
        $container = new FakeDiOverride();
        $manager   = new Manager();

        $container->setInternalEventsManager($manager);

        $this->assertSame($manager, $container->getInternalEventsManager());
    }
}
