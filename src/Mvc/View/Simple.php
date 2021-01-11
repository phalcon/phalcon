<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Mvc\View;

use Closure;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;
use Phalcon\Helper\Arr;

use Phalcon\Mvc\ViewBaseInterface;
use Phalcon\Mvc\View\Engine\EngineInterface;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;

/**
 * Phalcon\Mvc\View\Simple
 *
 * This component allows to render views without hierarchical levels
 *
 *```php
 * use Phalcon\Mvc\View\Simple as View;
 *
 * $view = new View();
 *
 * // Render a view
 * echo $view->render(
 *     "templates/my-view",
 *     [
 *         "some" => $param,
 *     ]
 * );
 *
 * // Or with filename with extension
 * echo $view->render(
 *     "templates/my-view.volt",
 *     [
 *         "parameter" => $here,
 *     ]
 * );
 *```
 */
class Simple extends Injectable implements ViewBaseInterface, EventsAwareInterface
{
    protected $activeRenderPath;
    protected $content;

    /**
     * @var \Phalcon\Mvc\View\EngineInterface[]|false
     */
    protected $engines = false;
    protected $eventsManager;

    protected $options;
    protected $partialsDir;

    /**
     * @var array|null
     */
    protected $registeredEngines; // TODO { get };

    protected $viewsDir;

    protected $viewParams = [];

    /**
     * Phalcon\Mvc\View\Simple constructor
     */
    public function __construct(array  $options = [])
    {
        $this->options = options;
    }

    /**
     * Magic method to retrieve a variable passed to the view
     *
     *```php
     * echo $this->view->products;
     *```
     */
    public function __get(string $key)
    {
        return $this->viewParams[$key] ?? null;
    }

    /**
     * Magic method to pass variables to the views
     *
     *```php
     * $this->view->products = $products;
     *```
     */
    public function __set(string $key, $value)
    {
        $this->viewParams[key] = value;
    }

    /**
     * Returns the path of the view that is currently rendered
     */
    public function getActiveRenderPath() : string
    {
        return $this->activeRenderPath;
    }

    /**
     * Returns output from another view stage
     */
    public function getContent() : string
    {
        return $this->content;
    }

    /**
     * Returns the internal event manager
     */
    public function getEventsManager(): ?ManagerInterface
    {
        return $this->eventsManager;
    }

    /**
     * Returns parameters to views
     */
    public function getParamsToView(): array
    {
        return $this->viewParams;
    }

    /**
     * Returns a parameter previously set in the view
     */
    public function getVar(string $key)
    {
        return $this->viewParams[$key] ?? null;
    }

    /**
     * Gets views directory
     */
    public function getViewsDir() : string
    {
        return $this->viewsDir;
    }

    /**
     * Renders a partial view
     *
     * ```php
     * // Show a partial inside another view
     * $this->partial("shared/footer");
     * ```
     *
     * ```php
     * // Show a partial inside another view with parameters
     * $this->partial(
     *     "shared/footer",
     *     [
     *         "content" => $html,
     *     ]
     * );
     * ```
     */
    public function partial(string $partialPath, $params = null)
    {
        /**
         * Start output buffering
         */
        ob_start();

        /**
         * If the developer pass an array of variables we create a new virtual
         * symbol table
         */
        if (is_array($params)) {
            $viewParams = $this->viewParams;

            /**
             * Merge or assign the new params as parameters
             */
            $mergedParams = array_merge($viewParams, $params);

            /**
             * Create a virtual symbol table
             */
            //create_symbol_table();
        } else {
            $mergedParams = $params;
        }

        /**
         * Call engine render, this checks in every registered engine for the partial
         */
        $this->internalRender(partialPath, mergedParams);

        /**
         * Now we need to restore the original view parameters
         */
        if (is_array($params)) {
            /**
             * Restore the original view params
             */
            $this->viewParams = $viewParams;
        }

        ob_end_clean();

        /**
         * Content is output to the parent view
         */
        echo $this->content;
    }

    /**
     * Register templating engines
     *
     *```php
     * $this->view->registerEngines(
     *     [
     *         ".phtml" => \Phalcon\Mvc\View\Engine\Php::class,
     *         ".volt"  => \Phalcon\Mvc\View\Engine\Volt::class,
     *         ".mhtml" => \MyCustomEngine::class,
     *     ]
     * );
     *```
     */
    public function registerEngines(array $engines)
    {
        $this->registeredEngines = $engines;
    }

    /**
     * Renders a view
     */
    public function render(string $path, array $params = []) : string
    {
      
        /**
         * Create a virtual symbol table
         */
        //create_symbol_table();

        ob_start();

        $viewParams = $this->viewParams;

        /**
         * Merge parameters
         */
        $mergedParams = array_merge($viewParams, $params);

        /**
         * internalRender is also reused by partials
         */
        $this->internalRender($path, $mergedParams);

        ob_end_clean();

        return $this->content;
    }

    /**
     * Externally sets the view content
     *
     *```php
     * $this->view->setContent("<h1>hello</h1>");
     *```
     */
    public function setContent(string $content): Simple
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Sets the events manager
     */
    public function setEventsManager( ManagerInterface $eventsManager) : void
    {
        $this->eventsManager = $eventsManager;
    }

    /**
     * Adds parameters to views (alias of setVar)
     *
     *```php
     * $this->view->setParamToView("products", $products);
     *```
     */
    public function setParamToView(string $key, $value): Simple
    {
        return $this->setVar(key, value);
    }

    /**
     * Set a single view parameter
     *
     *```php
     * $this->view->setVar("products", $products);
     *```
     */
    public function setVar(string $key, $value): Simple
    {
        $this->viewParams[$key] = $value;

        return $this;
    }

    /**
     * Set all the render params
     *
     *```php
     * $this->view->setVars(
     *     [
     *         "products" => $products,
     *     ]
     * );
     *```
     */
    public function setVars(array $params, bool $merge = true): Simple
    {
        if ($merge) {
            $params = array_merge($this->viewParams, $params);
        }

        $this->viewParams = params;

        return $this;
    }

    /**
     * Sets views directory
     */
    public function setViewsDir(string $viewsDir)
    {
        if (!str_ends_with($viewsDir,DIRECTORY_SEPARATOR)) {
            $viewsDir .= DIRECTORY_SEPARATOR;
        }
        $this->viewsDir = $viewsDir;
    }

    /**
     * Loads registered template engines, if none are registered it will use
     * Phalcon\Mvc\View\Engine\Php
     */
    protected function loadTemplateEngines():  array
    {
        /**
         * If the engines aren't initialized 'engines' is false
         */
        $engines = $this->engines;

        if ($engines === false) {
            $di = $this->container;

            $engines = [];

            $registeredEngines = $this->registeredEngines;

            if (!is_array($registeredEngines)) {
                /**
                 * We use Phalcon\Mvc\View\Engine\Php as default
                 * Use .phtml as extension for the PHP engine
                 */
                $engines[".phtml"] = new PhpEngine($this, $di);
            } else {
                if (!is_object($di)) {
                    throw new Exception(
                        Exception::containerServiceNotFound(
                            "the application services"
                        )
                    );
                }

                foreach($registeredEngines as $extension => $engineService) {
                    if (is_object($engineService)){
                        /**
                         * Engine can be a closure
                         */
                        if ($engineService instanceof Closure) {
                            $engineService = Closure::bind($engineService, $di);

                            $engineObject = call_user_func($engineService, $this);
                        } else {
                            $engineObject = $engineService;
                        }
                    } elseif (is_string($engineService)) {
                        /**
                         * Engine can be a string representing a service in the DI
                         */
                        $engineObject = $di->getShared(
                            $engineService,
                            [
                                $this
                            ]
                        );
                    } else {
                        throw new Exception(
                            "Invalid template engine registration for extension: " . $extension
                        );
                    }

                    $engines[$extension] = $engineObject;
                }
            }

            $this->engines = $engines;
        } else {
            $engines = $this->engines;
        }

        return $engines;
    }

    /**
     * Tries to render the view with every engine registered in the component
     *
     * @param array  params
     */
    final protected function internalRender(string $path, $params)
    {

        $eventsManager = $this->eventsManager;

        if ( $eventsManager){
            $this->activeRenderPath = $path;
        }

        /**
         * Call beforeRender if there is an events manager
         */
        if ($eventsManager){
            if ($eventsManager->fire("view:beforeRender", $this) === false) {
                return null;
            }
        }

        $notExists = true;
            $mustClean = true;

        $viewsDirPath =  $this->viewsDir . $path;

        /**
         * Load the template engines
         */
        $engines = $this->loadTemplateEngines();

        /**
         * Views are rendered in each engine
         */
        foreach ($engines as $extension => $engine) {
            if (file_exists($viewsDirPath . $extension)) {
                $viewEnginePath = $viewsDirPath . $extension;
            } elseif (substr($viewsDirPath, -strlen($extension)) === $extension && 
                    file_exists($viewsDirPath)) {
                /**
                 * if passed filename with engine extension
                 */

                $viewEnginePath = $viewsDirPath;
            } else {
                continue;
            }

            /**
             * Call beforeRenderView if there is an events manager available
             */
            if ( $eventsManager){
                if ($eventsManager->fire("view:beforeRenderView", $this, $viewEnginePath) === false) {
                    continue;
                }
            }

            $engine->render($viewEnginePath, $params, $mustClean);

            $notExists = false;

            /**
             * Call afterRenderView if there is an events manager available
             */
           if ( $eventsManager){
                $eventsManager->fire("view:afterRenderView", $this);
            }

            break;
        }

        /**
         * Always throw an exception if the view does not exist
         */
        if ($notExists) {
            throw new Exception(
                "View '" . $viewsDirPath . "' was not found in the views directory"
            );
        }

        /**
         * Call afterRender event
         */
        if($eventsManager) {
            $eventsManager->fire("view:afterRender", $this);
        }
    }
}
