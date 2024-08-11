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

namespace Phalcon\Tests\Unit\Container\Lazy;

use Phalcon\Container\Container;
use Phalcon\Container\Definitions\Definitions;
use Phalcon\Container\Lazy\AbstractLazy;
use Phalcon\Tests\AbstractUnitTestCase;

abstract class AbstractLazyBase extends AbstractUnitTestCase
{
    protected Container $container;

    public function setUp(): void
    {
        $this->container = new Container($this->definitions());
    }

    /**
     * @param AbstractLazy $lazy
     *
     * @return mixed
     */
    protected function actual(AbstractLazy $lazy): mixed
    {
        return $lazy($this->container);
    }

    /**
     * @return Definitions
     */
    protected function definitions(): Definitions
    {
        return new Definitions();
    }
}
