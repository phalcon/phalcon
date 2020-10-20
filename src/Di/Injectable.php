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

//use Phalcon\Di;
//use Phalcon\Session\BagInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;

use function is_object;

/**
 * This class allows to access services in the services container by just only
 * accessing a public property with the same name of a registered service
 *
 * @property DiInterface|null                     $container
 * @property DiInterface|null                     $di
 * @property EventsManager|EventsManagerInterface $eventsManager
// * @property \Phalcon\Mvc\Dispatcher|\Phalcon\Mvc\DispatcherInterface $dispatcher
// * @property \Phalcon\Mvc\Router|\Phalcon\Mvc\RouterInterface $router
// * @property \Phalcon\Url|\Phalcon\Url\UrlInterface $url
// * @property \Phalcon\Http\Request|\Phalcon\Http\RequestInterface $request
// * @property \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface $response
// * @property \Phalcon\Http\Response\Cookies|\Phalcon\Http\Response\CookiesInterface $cookies
// * @property \Phalcon\Filter $filter
// * @property \Phalcon\Flash\Direct $flash
// * @property \Phalcon\Flash\Session $flashSession
// * @property \Phalcon\Session\ManagerInterface $session
// * @property \Phalcon\Db\Adapter\AdapterInterface $db
// * @property \Phalcon\Security $security
// * @property \Phalcon\Crypt|\Phalcon\CryptInterface $crypt
// * @property \Phalcon\Tag $tag
// * @property \Phalcon\Escaper|\Phalcon\Escaper\EscaperInterface $escaper
// * @property \Phalcon\Annotations\Adapter\Memory|\Phalcon\Annotations\Adapter $annotations
// * @property \Phalcon\Mvc\Model\Manager|\Phalcon\Mvc\Model\ManagerInterface $modelsManager
// * @property \Phalcon\Mvc\Model\MetaData\Memory|\Phalcon\Mvc\Model\MetadataInterface $modelsMetadata
// * @property \Phalcon\Mvc\Model\Transaction\Manager|\Phalcon\Mvc\Model\Transaction\ManagerInterface $transactionManager
// * @property \Phalcon\Assets\Manager $assets
// * @property \Phalcon\Di|\Phalcon\Di\DiInterface $di
// * @property \Phalcon\Session\Bag|\Phalcon\Session\BagInterface $persistent
// * @property \Phalcon\Mvc\View|\Phalcon\Mvc\ViewInterface $view
 */
abstract class Injectable implements InjectionAwareInterface
{
    /**
     * Dependency Injector
     *
     * @var DiInterface
     */
    protected DiInterface $container;

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

//        /**
//         * Accessing the persistent property will create a session bag on any class
//         */
//        if propertyName == "persistent" {
//            let this->{"persistent"} = <BagInterface> container->get(
//                "sessionBag",
//                [
//                    get_class(this)
//                ]
//            );
//
//            return this->{"persistent"};
//        }

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
        if (true !== is_object($this->container)) {
            $this->container = Di::getDefault();
        }

        return $this->container;
    }

    /**
     * Sets the dependency injector
     */
    public function setDI(DiInterface $container): void
    {
        $this->container = $container;
    }
}
