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

use Phalcon\Annotations\Adapter\Memory as AnnotationsMemory;
use Phalcon\Assets\Manager as AssetsManager;
use Phalcon\Encryption\Crypt;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Filter\Filter;
use Phalcon\Filter\FilterFactory;
use Phalcon\Flash\Direct;
use Phalcon\Flash\Session;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Http\Response\Cookies;
use Phalcon\Mvc\Url;
use Phalcon\Support\HelperFactory;

/**
 * This is a variant of the standard Phalcon\Di\Di. By default it automatically
 * registers all the services provided by the framework. Thanks to this, the
 * developer does not need to register each service individually providing a
 * full stack framework
 *
 * @property AnnotationsMemory  $annotations
 * @property AssetsManager      $assets
 * @property Crypt              $crypt
 * @property Cookies            $cookies
 * @property Dispatcher         $dispatcher
 * @property Escaper            $escaper
 * @property EventsManager      $eventsManager
 * @property Direct             $flash
 * @property Session            $flashSession
 * @property Filter             $filter
 * @property HelperFactory      $helper
 * @property ModelsManager      $modelsManager
 * @property MetadataManager    $modelsMetadata
 * @property Request            $request
 * @property Response           $response
 * @property Router             $router
 * @property Security           $security
 * @property TagFactory         $tag
 * @property TransactionManager $transactionManager
 * @property Url                $url
 */
class FactoryDefault extends Di
{
    /**
     * Phalcon\Di\FactoryDefault constructor
     */
    public function __construct()
    {
        parent::__construct();

        $filterFactory = new FilterFactory();

        $this->services = [
            "annotations"        => new Service(AnnotationsMemory::class, true),
            "assets"        => new Service(
                [
                    "className" => AssetsManager::class,
                    "arguments" => [
                        [
                            "type" => "service",
                            "name" => "tag"
                        ]
                    ]
                ],
                true
            ),
            "crypt"         => new Service(Crypt::class, true),
            "cookies"       => new Service(Cookies::class, true),
//            "dispatcher"         => new Service("Phalcon\\Mvc\\Dispatcher", true),
            "escaper"       => new Service(Escaper::class, true),
            "eventsManager" => new Service(EventsManager::class, true),
            "flash"         => new Service(Direct::class, true),
            "flashSession"  => new Service(Session::class, true),
            "filter"        => new Service($filterFactory->newInstance(), true),
            "helper"        => new Service(HelperFactory::class, true),
//            "modelsManager"      => new Service("Phalcon\\Mvc\\Model\\Manager", true),
//            "modelsMetadata"     => new Service("Phalcon\\Mvc\\Model\\MetaData\\Memory", true),
            "request"            => new Service(Request::class, true),
            "response"           => new Service(Response::class, true),
//            "router"             => new Service("Phalcon\\Mvc\\Router", true),
            "security"      => new Service(Security::class, true),
            "tag"           => new Service(
                [
                    "className" => TagFactory::class,
                    "arguments" => [
                        [
                            "type" => "service",
                            "name" => "escaper"
                        ]
                    ]
                ],
                true
            ),
//            "transactionManager" : new Service("Phalcon\\Mvc\\Model\\Transaction\\Manager", true),
            "url"           => new Service(Url::class, true),
        ];
    }
}
