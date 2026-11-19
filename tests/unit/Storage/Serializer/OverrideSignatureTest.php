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

namespace Phalcon\Tests\Unit\Storage\Serializer;

use Phalcon\Storage\Serializer\Php;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Storage\Fake\FakePhpSerializerOverride;

final class OverrideSignatureTest extends AbstractUnitTestCase
{
    /**
     * A serializer can override getAllowedClasses() with the cphalcon
     * signature.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/841
     */
    public function testStorageSerializerPhpGetAllowedClassesOverride(): void
    {
        $serializer = new FakePhpSerializerOverride();

        $this->assertSame((new Php())->getAllowedClasses(), $serializer->getAllowedClasses());
    }
}
