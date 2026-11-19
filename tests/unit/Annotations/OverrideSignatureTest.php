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

namespace Phalcon\Tests\Unit\Annotations;

use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Annotations\Fake\FakeAnnotationsFactoryOverride;

final class OverrideSignatureTest extends AbstractUnitTestCase
{
    /**
     * A factory can override load() with the cphalcon signature.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/841
     */
    public function testAnnotationsFactoryLoadOverride(): void
    {
        $factory = new FakeAnnotationsFactoryOverride();

        $adapter = $factory->load(['adapter' => 'memory', 'options' => []]);

        $this->assertInstanceOf(Memory::class, $adapter);
    }
}
