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

namespace Phalcon\Tests\Integration\Paginator\PaginatorFactory;

use IntegrationTester;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Paginator\PaginatorFactory;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\FactoryTrait;

class LoadCest
{
    use DiTrait;
    use FactoryTrait;

    public function _before(IntegrationTester $I)
    {
        $this->init();
    }

    /**
     * Tests Phalcon\Translate\Factory :: load() - array
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2017-03-02
     */
    public function pagnatorPaginatorFactoryLoadArray(IntegrationTester $I)
    {
        $I->wantToTest('Paginator\AdapterFactory - load() - array');

        $this->setNewFactoryDefault();

        $modelsManager = $this->container->get('modelsManager');
        $options       = $this->arrayConfig['paginator'];

        $options['options']['builder'] = $modelsManager
            ->createBuilder()
            ->columns('id,name')
            ->from('Robots')
            ->orderBy('name')
        ;

        $factory = new PaginatorFactory();
        $adapter = $factory->load($options);

        $I->assertInstanceOf(QueryBuilder::class, $adapter);
    }

    /**
     * Tests Phalcon\Translate\Factory :: load() - Phalcon\Config
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2017-03-02
     */
    public function pagnatorPaginatorFactoryLoadConfig(IntegrationTester $I)
    {
        $I->wantToTest('Paginator\AdapterFactory - load() - Config');

        $this->setNewFactoryDefault();

        $modelsManager = $this->container->get('modelsManager');
        $options       = $this->config->paginator;

        $options->options->builder = $modelsManager
            ->createBuilder()
            ->columns('id,name')
            ->from('Robots')
            ->orderBy('name')
        ;

        $factory = new PaginatorFactory();

        $adapter = $factory->load($options);

        $I->assertInstanceOf(QueryBuilder::class, $adapter);
    }
}
