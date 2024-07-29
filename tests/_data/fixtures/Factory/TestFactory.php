<?php

/*
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\Factory;

use Phalcon\Factory\AbstractFactory;
use Phalcon\Factory\Exception;

class TestFactory extends AbstractFactory
{
    public function __construct(array $services)
    {
        $this->init($services);
    }

    public function services(): array
    {
        return $this->getServices();
    }

    /**
     * @throws Exception
     */
    public function service(string $name): mixed
    {
        return $this->getService($name);
    }

    /**
     * @inheritDoc
     */
    protected function getServices(): array
    {
        return $this->services;
    }
}
