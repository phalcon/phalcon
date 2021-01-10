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

namespace Phiz\Di\FactoryDefault;

use Phiz\Di\FactoryDefault;
use Phiz\Di\Service;
use Phiz\Html\Escaper;
use Phiz\Events\Manager as EventsManager;
use Phiz\Filter\FilterFactory;

/**
 * Phiz\Di\FactoryDefault\Cli
 *
 * This is a variant of the standard Phiz\Di. By default it automatically
 * registers all the services provided by the framework.
 * Thanks to this, the developer does not need to register each service individually.
 * This class is specially suitable for CLI applications
 */
class Cli extends FactoryDefault
{
    /**
     * Phiz\Di\FactoryDefault\Cli constructor
     */
    public function __construct()
    {
        parent::__construct();

        $filter = new FilterFactory();

        $this->services = [
            'escaper'       => new Service(Escaper::class, true),
            'eventsManager' => new Service(EventsManager::class, true),
            'filter'        => new Service($filter->newInstance(), true),
        ];
//        let this->services = [
//            "annotations":        new Service("Phiz\\Annotations\\Adapter\\Memory", true),
//            "dispatcher":         new Service("Phiz\\Cli\\Dispatcher", true),
//            "eventsManager":      new Service("Phiz\\Events\\Manager", true),
//            "modelsManager":      new Service("Phiz\\Mvc\\Model\\Manager", true),
//            "modelsMetadata":     new Service("Phiz\\Mvc\\Model\\MetaData\\Memory", true),
//            "router":             new Service("Phiz\\Cli\\Router", true),
//            "security":           new Service("Phiz\\Security", true),
//            "transactionManager": new Service("Phiz\\Mvc\\Model\\Transaction\\Manager", true)
//        ];
    }
}
