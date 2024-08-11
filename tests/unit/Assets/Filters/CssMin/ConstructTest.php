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

namespace Phalcon\Tests\Unit\Assets\Filters\CssMin;

use Phalcon\Assets\FilterInterface;
use Phalcon\Assets\Filters\CssMin;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Filters\CssMin :: __construct() - no string
     * exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testAssetsFiltersCssMinConstructNonString(): void
    {
        $cssMin = new Cssmin();
        $this->assertInstanceOf(Cssmin::class, $cssMin);
        $this->assertInstanceOf(FilterInterface::class, $cssMin);
    }
}
