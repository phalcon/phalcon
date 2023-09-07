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

namespace Phalcon\Di\FactoryDefault;

use Phalcon\Cli\Dispatcher;
use Phalcon\Cli\Router;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\Service;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Filter\FilterFactory;
use Phalcon\Html\Escaper;
use Phalcon\Support\HelperFactory;

/**
 * Phalcon\Di\FactoryDefault\Cli
 *
 * This is a variant of the standard Phalcon\Di. By default, it automatically
 * registers all the services provided by the framework.
 * Thanks to this, the developer does not need to register each service individually.
 * This class is specially suitable for CLI applications
 *
 * @property Router $router
 */
class Cli extends FactoryDefault
{
    /**
     * Phalcon\Di\FactoryDefault\Cli constructor
     */
    public function __construct()
    {
        parent::__construct();

        $filter = new FilterFactory();

        $this->services = [
//            "annotations"        => new Service("Phalcon\\Annotations\\Adapter\\Memory", true),
        "dispatcher"    => new Service(Dispatcher::class, true),
        "escaper"       => new Service(Escaper::class, true),
        "eventsManager" => new Service(EventsManager::class, true),
        "filter"        => new Service($filter->newInstance(), true),
        "helper"        => new Service(HelperFactory::class, true),
//            "modelsManager"      => new Service("Phalcon\\Mvc\\Model\\Manager", true),
//            "modelsMetadata"     => new Service("Phalcon\\Mvc\\Model\\MetaData\\Memory", true),
        "router"        => new Service(Router::class, true),
        "security"      => new Service(Security::class, true),
//            "transactionManager" => new Service("Phalcon\\Mvc\\Model\\Transaction\\Manager", true)
        ];
    }
}
