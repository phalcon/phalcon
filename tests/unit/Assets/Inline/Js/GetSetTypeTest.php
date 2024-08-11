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

namespace Phalcon\Tests\Unit\Assets\Inline\Js;

use Phalcon\Assets\Inline\Js;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class GetSetTypeTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Inline\Js :: getType()/setType()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsInlineJsGetSetType(): void
    {
        $asset   = new Js('<script>alert("Hello");</script>');
        $newType = 'js';

        $asset->setType($newType);
        $actual = $asset->getType();

        $this->assertSame($newType, $actual);
    }
}
