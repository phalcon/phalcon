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

namespace Phalcon\Di;

use Phalcon\Assets\Manager as AssetsManager;
use Phalcon\Encryption\Crypt;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Filter\FilterFactory;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;

/**
 * This is a variant of the standard Phalcon\Di. By default it automatically
 * registers all the services provided by the framework. Thanks to this, the
 * developer does not need to register each service individually providing a
 * full stack framework
 */
class FactoryDefault extends Di
{
    /**
     * Phalcon\Di\FactoryDefault constructor
     */
    public function __construct()
    {
        parent::__construct();

        $filter     = new FilterFactory();
        $escaper    = new Escaper();
        $tagFactory = new TagFactory($escaper);
        $assets     = new AssetsManager($tagFactory);

        $this->services = [
            'assets'        => new Service($assets, true),
            'crypt'         => new Service(Crypt::class, true),
            'escaper'       => new Service(Escaper::class, true),
            'eventsManager' => new Service(EventsManager::class, true),
            'filter'        => new Service($filter->newInstance(), true),
            'security'      => new Service(Security::class, true),
            'tagFactory'    => new Service($tagFactory, true),
        ];
//        let filter = new FilterFactory();
//
//        let this->services = [
//            "annotations":        new Service("Phalcon\\Annotations\\Adapter\\Memory", true),
//            "cookies":            new Service("Phalcon\\Http\\Response\\Cookies", true),
//            "dispatcher":         new Service("Phalcon\\Mvc\\Dispatcher", true),
//            "flash":              new Service("Phalcon\\Flash\\Direct", true),
//            "flashSession":       new Service("Phalcon\\Flash\\Session", true),
//            "modelsManager":      new Service("Phalcon\\Mvc\\Model\\Manager", true),
//            "modelsMetadata":     new Service("Phalcon\\Mvc\\Model\\MetaData\\Memory", true),
//            "request":            new Service("Phalcon\\Http\\Request", true),
//            "response":           new Service("Phalcon\\Http\\Response", true),
//            "router":             new Service("Phalcon\\Mvc\\Router", true),
//            "transactionManager": new Service("Phalcon\\Mvc\\Model\\Transaction\\Manager", true),
//            "url":                new Service("Phalcon\\Url", true)
//        ];
    }
}
