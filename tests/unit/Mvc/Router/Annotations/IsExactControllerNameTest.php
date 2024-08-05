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

namespace Phalcon\Tests\Unit\Mvc\Router\Annotations;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Mvc\Router\Annotations;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

final class IsExactControllerNameTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Mvc\Router\Annotations :: isExactControllerName()
     */
    public function testMvcRouterAnnotationsIsExactControllerName(): void
    {
        $this->newDi();
        $this->setDiService('request');
        $this->setDiService('annotations');

        $router = new Annotations(false);

        $this->assertTrue(
            $router->isExactControllerName()
        );
    }
}
