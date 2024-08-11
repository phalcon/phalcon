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

namespace Phalcon\Tests\Unit\Image\Adapter\Gd;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Tests\Fixtures\Traits\GdTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    use GdTrait;

    /**
     * Tests Phalcon\Image\Adapter\Gd :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testImageAdapterGdConstruct(): void
    {
        foreach ($this->getImages() as $image) {
            $gd = new Gd($image);

            $class = Gd::class;
            $this->assertInstanceOf($class, $gd);
        }
    }
}
