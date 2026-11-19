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

namespace Phalcon\Tests\Unit\Mvc\View;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Mvc\Fake\FakeViewOverride;

final class OverrideSignatureTest extends AbstractUnitTestCase
{
    /**
     * A view can override render() with the cphalcon signature.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/841
     */
    public function testMvcViewRenderOverride(): void
    {
        $view = new FakeViewOverride();
        $view->disable();

        $this->assertFalse($view->render('index', 'index'));
    }
}
