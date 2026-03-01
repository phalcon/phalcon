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

namespace Phalcon\Mvc;

use Closure;
use Phalcon\Application\AbstractApplication;
use Phalcon\Application\Exception as ApplicationException;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Application\Exception;
use Phalcon\Traits\Php\FileTrait;

use function call_user_func_array;
use function class_exists;
use function is_array;
use function is_object;
use function is_string;

/**
 * This component encapsulates all the complex operations behind instantiating
 * every component needed and integrating it with the rest to allow the MVC
 * pattern to operate as desired.
 *
 *```php
 * use Phalcon\Mvc\Application;
 *
 * class MyApp extends Application
 * {
 *     /**
 *      * Register the services here to make them general or register
 *      * in the ModuleDefinition to make them module-specific
 *      *\/
 *     protected function registerServices()
 *     {
 *
 *     }
 *
 *     /**
 *      * This method registers all the modules in the application
 *      *\/
 *     public function main()
 *     {
 *         $this->registerModules(
 *             [
 *                 "frontend" => [
 *                     "className" => "Multiple\\Frontend\\Module",
 *                     "path"      => "../apps/frontend/Module.php",
 *                 ],
 *                 "backend" => [
 *                     "className" => "Multiple\\Backend\\Module",
 *                     "path"      => "../apps/backend/Module.php",
 *                 ],
 *             ]
 *         );
 *     }
 * }
 *
 * $application = new MyApp();
 *
 * $application->main();
 *```
 */
class Application extends AbstractApplication
{
    use FileTrait;

    /**
     * @var bool
     */
    protected bool $implicitView = true;

    /**
     * @var bool
     */
    protected bool $sendCookies = true;

    /**
     * @var bool
     */
    protected bool $sendHeaders = true;

    /**
     * Handles a MVC request
     *
     * @param string $uri
     *
     * @return ResponseInterface|bool
     * @throws ApplicationException
     * @throws Exception
     * @throws EventsException
     */
    public function handle(string $uri): ResponseInterface | bool
    {
        $this->checkContainer(Exception::class, 'internal services');

        /**
         * Call boot event, this allows the developer to perform initialization
         * actions
         */
        if (false === $this->fireManagerEvent("application:boot")) {
            return false;
        }

        $router = $this->container->getShared("router");

        /**
         * Handle the URI pattern (if any)
         */
        $router->handle($uri);

        /**
         * If a 'match' callback was defined in the matched route
         * The whole dispatcher+view behavior can be overridden by the developer
         */
        $matchedRoute = $router->getMatchedRoute();

        if (is_object($matchedRoute)) {
            $match = $matchedRoute->getMatch();

            if (null !== $match) {
                if ($match instanceof Closure) {
                    $match = Closure::bind($match, $this->container);
                }

                /**
                 * Directly call the match callback
                 */
                $possibleResponse = call_user_func_array(
                    $match,
                    $router->getParams()
                );

                /**
                 * If the returned value is a string return it as body
                 */
                if (is_string($possibleResponse)) {
                    $response = $this->container->getShared("response");

                    $response->setContent($possibleResponse);

                    return $response;
                }

                /**
                 * If the returned string is a ResponseInterface use it as
                 * response
                 */
                if ($possibleResponse instanceof ResponseInterface) {
                    $possibleResponse->sendHeaders();
                    $possibleResponse->sendCookies();

                    return $possibleResponse;
                }
            }
        }

        /**
         * If the router doesn't return a valid module we use the default module
         */
        $moduleName = $router->getModuleName();

        if (!$moduleName) {
            $moduleName = $this->defaultModule;
        }

        $moduleObject = null;

        /**
         * Process the module definition
         */
        if ($moduleName) {
            if (false === $this->fireManagerEvent("application:beforeStartModule", $moduleName)) {
                return false;
            }

            /**
             * Gets the module definition
             */
            $module = $this->getModule($moduleName);

            /**
             * A module definition must be an array or an object
             */
            if (!is_array($module) && !is_object($module)) {
                throw new Exception("Invalid module definition");
            }

            /**
             * An array module definition contains a path to a module definition
             * class
             */
            if (is_array($module)) {
                /**
                 * Class name used to load the module definition
                 */
                $className = $module["className"] ?? "Module";

                /**
                 * If developer specify a path try to include the file
                 */
                if (isset($module["path"])) {
                    $path = $module["path"];
                    if (true !== $this->phpFileExists($path)) {
                        throw new Exception(
                            "Module definition path '" . $path . "' does not exist"
                        );
                    }

                    if (true !== class_exists($className, false)) {
                        require_once $path;
                    }
                }

                $moduleObject = $this->container->get($className);

                /**
                 * 'registerAutoloaders' and 'registerServices' are
                 * automatically called
                 */
                $moduleObject->registerAutoloaders($this->container);
                $moduleObject->registerServices($this->container);
            } else {
                /**
                 * A module definition object, can be a Closure instance
                 */
                if (!($module instanceof Closure)) {
                    throw new Exception("Invalid module definition");
                }

                $moduleObject = call_user_func_array(
                    $module,
                    [
                        $this->container,
                    ]
                );
            }

            /**
             * Calling afterStartModule event
             */
            $this->fireManagerEvent("application:afterStartModule", $moduleObject);
        }

        /**
         * Check whether to use implicit views or not
         */
        $view = null;
        if (true === $this->implicitView) {
            $view = $this->container->getShared("view");
        }

        /**
         * We get the parameters from the router and assign them to the dispatcher
         * Assign the values passed from the router
         */
        $dispatcher = $this->container->getShared("dispatcher");

        $dispatcher->setModuleName($router->getModuleName());
        $dispatcher->setNamespaceName($router->getNamespaceName());
        $dispatcher->setControllerName($router->getControllerName());
        $dispatcher->setActionName($router->getActionName());
        $dispatcher->setParams($router->getParams());

        /**
         * Start the view component (start output buffering)
         */
        if (true === $this->implicitView) {
            $view->start();
        }

        /**
         * Calling beforeHandleRequest
         */
        if (false === $this->fireManagerEvent("application:beforeHandleRequest", $dispatcher)) {
            return false;
        }

        /**
         * The dispatcher must return an object
         */
        $controller = $dispatcher->dispatch();

        /**
         * Get the latest value returned by an action
         */
        $possibleResponse = $dispatcher->getReturnedValue();

        /**
         * Returning false from an action cancels the view
         */
        if (false === $possibleResponse) {
            $response = $this->container->getShared("response");
        } else {
            /**
             * Returning a string makes use it as the body of the response
             */
            if (is_string($possibleResponse)) {
                $response = $this->container->getShared("response");

                $response->setContent($possibleResponse);
            } else {
                /**
                 * Check if the returned object is already a response
                 */
                $returnedResponse = ($possibleResponse instanceof ResponseInterface);

                /**
                 * Calling afterHandleRequest
                 */
                $this->fireManagerEvent("application:afterHandleRequest", $controller);

                /**
                 * If the dispatcher returns an object we try to render the view
                 * in auto-rendering mode
                 */
                if (
                    false === $returnedResponse &&
                    true === $this->implicitView &&
                    is_object($controller)
                ) {
                    /**
                     * This allows to make a custom view render
                     */
                    $renderStatus = $this->fireManagerEvent("application:viewRender", $view);

                    /**
                     * Check if the view process has been treated by the
                     * developer
                     */
                    if (false !== $renderStatus) {
                        /**
                         * Automatic render based on the latest controller
                         * executed
                         */
                        $view->render(
                            $dispatcher->getControllerName(),
                            $dispatcher->getActionName()
                        );
                    }
                }

                /**
                 * Finish the view component (stop output buffering)
                 */
                if (true === $this->implicitView) {
                    $view->finish();
                }

                if (true === $returnedResponse) {
                    /**
                     * We don't need to create a response because there is one
                     * already created
                     */
                    $response = $possibleResponse;
                } else {
                    $response = $this->container->getShared("response");

                    if (true === $this->implicitView) {
                        /**
                         * The content returned by the view is passed to the
                         * response service
                         */
                        $response->setContent($view->getContent());
                    }
                }
            }
        }

        /**
         * Calling beforeSendResponse
         */
        $this->fireManagerEvent("application:beforeSendResponse", $response);

        /**
         * Check whether send headers or not (by default yes)
         */
        if (true === $this->sendHeaders) {
            $response->sendHeaders();
        }

        /**
         * Check whether send cookies or not (by default yes)
         */
        if (true === $this->sendCookies) {
            $response->sendCookies();
        }

        /**
         * Return the response
         */
        return $response;
    }

    /**
     * Enables or disables sending cookies by each request handling
     *
     * @param bool $sendCookies
     *
     * @return $this
     */
    public function sendCookiesOnHandleRequest(bool $sendCookies): Application
    {
        $this->sendCookies = $sendCookies;

        return $this;
    }


    /**
     * Enables or disables sending headers by each request handling
     *
     * @param bool $sendHeaders
     *
     * @return Application
     */
    public function sendHeadersOnHandleRequest(bool $sendHeaders): Application
    {
        $this->sendHeaders = $sendHeaders;

        return $this;
    }

    /**
     * By default, the view is implicitly buffering all the output
     * You can full disable the view component using this method
     *
     * @param bool $implicitView
     *
     * @return Application
     */
    public function useImplicitView(bool $implicitView): Application
    {
        $this->implicitView = $implicitView;

        return $this;
    }
}
