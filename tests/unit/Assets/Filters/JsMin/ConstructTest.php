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

namespace Phalcon\Tests\Unit\Assets\Filters\JsMin;

use Phalcon\Assets\FilterInterface;
use Phalcon\Assets\Filters\JsMin;
use Phalcon\Tests\UnitTestCase;

final class ConstructTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Filters\JsMin :: __construct() - no string exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsFiltersJsminConstruct(): void
    {
        $jsMin = new Jsmin();
        $this->assertInstanceOf(Jsmin::class, $jsMin);
        $this->assertInstanceOf(FilterInterface::class, $jsMin);
    }
}
