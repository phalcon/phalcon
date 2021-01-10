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

use Phiz\Di\Traits\InjectionAwareTrait;
use Phiz\Events\Manager as EventsManager;
use Phiz\Events\ManagerInterface as EventsManagerInterface;

/**
 * This class allows to access services in the services container by just only
 * accessing a public property with the same name of a registered service
 *
 * @property DiInterface|null                     $container
 * @property DiInterface|null                     $di
 * @property EventsManager|EventsManagerInterface $eventsManager
// * @property \Phiz\Mvc\Dispatcher|\Phiz\Mvc\DispatcherInterface $dispatcher
// * @property \Phiz\Mvc\Router|\Phiz\Mvc\RouterInterface $router
// * @property \Phiz\Url\Url|\Phiz\Url\UrlInterface $url
// * @property \Phiz\Http\Request|\Phiz\Http\RequestInterface $request
// * @property \Phiz\Http\Response|\Phiz\Http\ResponseInterface $response
// * @property \Phiz\Http\Response\Cookies|\Phiz\Http\Response\CookiesInterface $cookies
// * @property \Phiz\Filter $filter
// * @property \Phiz\Flash\Direct $flash
// * @property \Phiz\Flash\Session $flashSession
// * @property \Phiz\Session\ManagerInterface $session
// * @property \Phiz\Db\Adapter\AdapterInterface $db
// * @property \Phiz\Security $security
// * @property \Phiz\Crypt|\Phiz\CryptInterface $crypt
// * @property \Phiz\Tag $tag
// * @property \Phiz\Escaper|\Phiz\Html\EscaperInterface $escaper
// * @property \Phiz\Annotations\Adapter\Memory|\Phiz\Annotations\Adapter $annotations
// * @property \Phiz\Mvc\Model\Manager|\Phiz\Mvc\Model\ManagerInterface $modelsManager
// * @property \Phiz\Mvc\Model\MetaData\Memory|\Phiz\Mvc\Model\MetadataInterface $modelsMetadata
// * @property \Phiz\Mvc\Model\Transaction\ManagerInterface $transactionManager
// * @property \Phiz\Assets\Manager $assets
// * @property \Phiz\Di|\Phiz\Di\DiInterface $di
// * @property \Phiz\Session\Bag|\Phiz\Session\BagInterface $persistent
// * @property \Phiz\Mvc\View|\Phiz\Mvc\ViewInterface $view
 */

abstract class Injectable implements InjectionAwareInterface
{
    // member $container in trait
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
            $service = $bucket->getShared($propertyName);
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
        return $this->getDI()->has($name);
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
