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
use Phalcon\Di\Traits\InjectionAwareTrait;
use Phalcon\Encryption\Crypt;
use Phalcon\Encryption\Crypt\CryptInterface;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;
use Phalcon\Filter\Filter;
use Phalcon\Filter\FilterInterface;
use Phalcon\Flash\Direct;
use Phalcon\Flash\Session;
use Phalcon\Html\Escaper;
use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Http\RequestInterface;
use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Session\Bag;
use Phalcon\Session\BagInterface;
use Phalcon\Session\ManagerInterface as SessionManager;
use Phalcon\Support\HelperFactory;

use function spl_object_hash;

//use Phalcon\Annotations\Adapter;
//use Phalcon\Db\Adapter\AdapterInterface;
//use Phalcon\Http\RequestInterface;
//use Phalcon\Http\Response;
//use Phalcon\Http\Response\Cookies;
//use Phalcon\Http\Response\CookiesInterface;
//use Phalcon\Http\ResponseInterface;
//use Phalcon\Mvc\Dispatcher;
//use Phalcon\Mvc\DispatcherInterface;
//use Phalcon\Mvc\Model\MetaData\Memory;
//use Phalcon\Mvc\Model\MetadataInterface;
//use Phalcon\Mvc\Model\Transaction\ManagerInterface;
//use Phalcon\Mvc\Router;
//use Phalcon\Mvc\RouterInterface;
//use Phalcon\Mvc\View;
//use Phalcon\Mvc\ViewInterface;
//use Phalcon\Url;
//use Phalcon\Url\UrlInterface;

/**
 * This class allows to access services in the services container by just only
 * accessing a public property with the same name of a registered service
 *
 * @property AssetsManager                        $assets
 * @property DiInterface|null                     $container
 * @property DiInterface|null                     $di
 * @property Crypt|CryptInterface                 $crypt
 * @property EventsManager|EventsManagerInterface $eventsManager
 * @property Escaper|EscaperInterface             $escaper
 * @property Direct                               $flash
 * @property Session                              $flashSession
 * @property Filter|FilterInterface               $filter
 * @property HelperFactory                        $helper
 * @property Security                             $security
 * @property SessionManager                       $session
 * // * @property Router|RouterInterface $router
 * // * @property Url|UrlInterface $url
 * // * @property Request|RequestInterface $request
 * // * @property Response|ResponseInterface $response
 * // * @property Cookies|CookiesInterface $cookies
 * // * @property AdapterInterface $db
 * // * @property Adapter\Memory|Adapter $annotations
 * // * @property \Phalcon\Mvc\Model\Manager|ManagerInterface $modelsManager
 * // * @property Memory|MetadataInterface $modelsMetadata
 * // * @property ManagerInterface $transactionManager
 * // * @property Bag|BagInterface $persistent
 * // * @property View|ViewInterface $view
 */
abstract class Injectable implements InjectionAwareInterface
{
    use InjectionAwareTrait;

    /**
     * Magic method __get
     *
     * @param string $propertyName
     *
     * @return mixed|DiInterface|void
     */
    public function __get(string $propertyName)
    {
        $bucket = $this->getDI();

        if ('di' === $propertyName) {
            $this->di = $bucket;

            return $bucket;
        }

        /**
         * Accessing the persistent property will create a session bag on any class
         */
        if ('persistent' === $propertyName) {
            $this->persistent = $bucket->get(
                'sessionBag',
                [
                    get_class($this)
                ]
            );

            return $this->persistent;
        }

        /**
         * Fallback to the PHP userland if the cache is not available
         */
        if (true === $bucket->has($propertyName)) {
            $service             = $bucket->getShared($propertyName);
            $this->$propertyName = $service;

            return $service;
        }

        /**
         * A notice is shown if the property is not defined and isn't a valid service
         */
        trigger_error('Access to undefined property ' . $propertyName);
    }

    /**
     * Magic method __isset
     */
    public function __isset(string $name): bool
    {
        return $this->getDI()
                    ->has($name)
        ;
    }

    /**
     * Returns the internal dependency injector
     */
    public function getDI(): DiInterface
    {
        if (null === $this->container) {
            $this->container = Di::getDefault();
        }

        return $this->container;
    }
}
