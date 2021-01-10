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

namespace Phiz\Di;

use Phiz\Html\Escaper;
use Phiz\Events\Manager as EventsManager;
use Phiz\Filter\FilterFactory;
use Phiz\Http\Request;
use Phiz\Http\Response;
use Phiz\Mvc\Router;
use Phiz\Mvc\Dispatcher;
/**
 * This is a variant of the standard Phiz\Di. By default it automatically
 * registers all the services provided by the framework. Thanks to this, the
 * developer does not need to register each service individually providing a
 * full stack framework
 */
class FactoryDefault extends Di
{
    /**
     * Phiz\Di\FactoryDefault constructor
     */
    public function __construct()
    {
        parent::__construct();

        $filter = new FilterFactory();

        $this->services = [
            'escaper'       => new Service(Escaper::class, true),
            'eventsManager' => new Service(EventsManager::class, true),
            'filter'        => new Service($filter->newInstance(), true),
            'request' => new Service(Request::class, true),
            'response' =>  new Service(Response::class, true),
            'router' =>   new Service(Router::class, true),
             'dispatcher' =>  new Service(Dispatcher::class, true),
        ];
//        let filter = new FilterFactory();
//
//        let this->services = [
//            "annotations":        new Service("Phiz\\Annotations\\Adapter\\Memory", true),
//            "assets":             new Service("Phiz\\Assets\\Manager", true),
//            "crypt":              new Service("Phiz\\Crypt", true),
//            "cookies":            new Service("Phiz\\Http\\Response\\Cookies", true),
//            "dispatcher":         new Service("Phiz\\Mvc\\Dispatcher", true),
//            "flash":              new Service("Phiz\\Flash\\Direct", true),
//            "flashSession":       new Service("Phiz\\Flash\\Session", true),
//            "modelsManager":      new Service("Phiz\\Mvc\\Model\\Manager", true),
//            "modelsMetadata":     new Service("Phiz\\Mvc\\Model\\MetaData\\Memory", true),
//            "request":            new Service("Phiz\\Http\\Request", true),
//            "response":           new Service("Phiz\\Http\\Response", true),
//            "router":             new Service("Phiz\\Mvc\\Router", true),
//            "security":           new Service("Phiz\\Security", true),
//            "tag":                new Service("Phiz\\Tag", true),
//            "transactionManager": new Service("Phiz\\Mvc\\Model\\Transaction\\Manager", true),
//            "url":                new Service("Phiz\\Url", true)
//        ];
    }
}
