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

namespace Phalcon\Tests\Unit\Support\Helper\Arr;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Support\Fake\FakeFirstKeyOverride;
use Phalcon\Tests\Unit\Support\Fake\FakeLastKeyOverride;

final class OverrideSignatureTest extends AbstractUnitTestCase
{
    /**
     * A helper can override __invoke() with the cphalcon signature.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/841
     */
    public function testSupportHelperArrFirstKeyOverride(): void
    {
        $helper = new FakeFirstKeyOverride();

        $this->assertSame('one', $helper(['one' => 1, 'two' => 2]));
    }

    /**
     * A helper can override __invoke() with the cphalcon signature.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/841
     */
    public function testSupportHelperArrLastKeyOverride(): void
    {
        $helper = new FakeLastKeyOverride();

        $this->assertSame('two', $helper(['one' => 1, 'two' => 2]));
    }
}
