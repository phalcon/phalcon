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

namespace Phalcon\Tests\Unit\Mvc\Model;

use Phalcon\Container\Container;
use Phalcon\Di\Di;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\CriteriaInterface;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Mvc\Model\Fake\FakeModel;

class ModelContainerTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testQueryAcceptsContainer(): void
    {
        $container = new Container();

        $criteria = FakeModel::query($container);

        $this->assertInstanceOf(CriteriaInterface::class, $criteria);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testQueryAcceptsDi(): void
    {
        $di = new Di();
        $di->set("Phalcon\\Mvc\\Model\\Criteria", Criteria::class);

        $criteria = FakeModel::query($di);

        $this->assertInstanceOf(CriteriaInterface::class, $criteria);
    }
}
