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

namespace Phalcon\Mvc\View;

use Closure;
use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Mvc\View\Engine\EngineInterface;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Parsers\Parser;
use Phalcon\Traits\Helper\Str\DirSeparatorTrait;

use function array_merge;
use function call_user_func;
use function file_exists;
use function is_array;
use function is_object;
use function is_string;
use function ob_end_clean;
use function ob_start;

/**
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
    use EventsAwareTrait;
    use DirSeparatorTrait;

    /**
     * @var string
     */
    protected string $activeRenderPath;

    /**
     * @var string
     */
    protected string $content;

    /**
     * @var EngineInterface[]|false
     */
    protected array | false $engines = false; // TODO: Change to default null or empty array

    /**
     * @var array
     */
    protected array $registeredEngines = [];
    /**
     * @var array
     */
    protected array $viewParams = [];
    /**
     * @var string
     */
    protected string $viewsDir;

    /**
     * Phalcon\Mvc\View\Simple constructor
     *
     * @param array $options
     */
    public function __construct(
        protected array $options = []
    ) {
    }

    /**
     * Magic method to retrieve a variable passed to the view
     *
     *```php
     * echo $this->view->products;
     *```
     *
     * @param string $propertyName
     *
     * @return mixed
     */
    public function __get(string $propertyName): mixed
    {
        return $this->viewParams[$propertyName] ?? null;
    }

    /**
     * Magic method to pass variables to the views
     *
     *```php
     * $this->view->products = $products;
     *```
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function __set(string $key, mixed $value): void
    {
        $this->viewParams[$key] = $value;
    }

    /**
     * Returns the path of the view that is currently rendered
     *
     * @return string
     */
    public function getActiveRenderPath(): string
    {
        return $this->activeRenderPath;
    }

    /**
     * Returns output from another view stage
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Returns parameters to views
     *
     * @return array
     */
    public function getParamsToView(): array
    {
        return $this->viewParams;
    }

    /**
     * @return array
     */
    public function getRegisteredEngines(): array
    {
        return $this->registeredEngines;
    }

    /**
     * Returns a parameter previously set in the view
     *
     * @return mixed|null
     */
    public function getVar(string $key): mixed
    {
        return $this->viewParams[$key] ?? null;
    }

    /**
     * Gets views directory
     *
     * @return string
     */
    public function getViewsDir(): string
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
     *
     * @param string     $partialPath
     * @param mixed|null $params
     *
     * @return void
     * @throws EventsException
     * @throws Exception
     */
    public function partial(string $partialPath, mixed $params = null): void
    {
        $viewParams = $this->viewParams;

        /**
         * Start output buffering
         */
        ob_start();

        /**
         * If the developer pass an array of variables we create a new virtual
         * symbol table
         */
        if (is_array($params)) {
            /**
             * Merge or assign the new params as parameters
             */
            $mergedParams = array_merge($viewParams, $params);
        } else {
            $mergedParams = $params;
        }

        /**
         * Call engine render, this checks in every registered engine for the partial
         */
        $this->internalRender($partialPath, $mergedParams);

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
     *
     * @param array $engines
     *
     * @return void
     */
    public function registerEngines(array $engines): void
    {
        $this->registeredEngines = $engines;
    }

    /**
     * Renders a view
     *
     * @param string $path
     * @param array  $params
     *
     * @return string
     * @throws EventsException
     * @throws Exception
     */
    public function render(string $path, array $params = []): string
    {
        ob_start();

        /**
         * Merge parameters
         */
        $mergedParams = array_merge($this->viewParams, $params);

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
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent(string $content): Simple
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Adds parameters to views (alias of setVar)
     *
     *```php
     * $this->view->setParamToView("products", $products);
     *```
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return Simple
     */
    public function setParamToView(string $key, mixed $value): Simple
    {
        return $this->setVar($key, $value);
    }

    /**
     * Set a single view parameter
     *
     *```php
     * $this->view->setVar("products", $products);
     *```
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setVar(string $key, mixed $value): Simple
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
     *
     * @param array $params
     * @param bool  $merge
     *
     * @return $this
     */
    public function setVars(array $params, bool $merge = true): Simple
    {
        if (true === $merge) {
            $params = array_merge($this->viewParams, $params);
        }

        $this->viewParams = $params;

        return $this;
    }

    /**
     * Sets views directory
     *
     * @param string $viewsDir
     *
     * @return void
     */
    public function setViewsDir(string $viewsDir): void
    {
        $this->viewsDir = $this->toDirSeparator($viewsDir);
    }

    /**
     * Tries to render the view with every engine registered in the component
     *
     * @param string $path
     * @param mixed  $params
     *
     * @return void
     * @throws Exception
     * @throws EventsException
     */
    final protected function internalRender(string $path, mixed $params): void
    {
        if (null !== $this->eventsManager) {
            $this->activeRenderPath = $path;
        }

        /**
         * Call beforeRender if there is an events manager
         */
        if (false === $this->fireManagerEvent("view:beforeRender")) {
            return;
        }

        $notExists    = true;
        $mustClean    = true;
        $viewsDirPath = $this->viewsDir . $path;

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
            } elseif (
                str_ends_with($viewsDirPath, $extension) &&
                file_exists($viewsDirPath)
            ) {
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
            if (false === $this->fireManagerEvent("view:beforeRenderView", $viewEnginePath)) {
                continue;
            }

            $engine->render($viewEnginePath, $params, $mustClean);

            $notExists = false;

            /**
             * Call afterRenderView if there is an events manager available
             */
            $this->fireManagerEvent("view:afterRenderView");

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
        $this->fireManagerEvent("view:afterRender");
    }

    /**
     * Loads registered template engines, if none are registered it will use
     * Phalcon\Mvc\View\Engine\Php
     *
     * @return array|EngineInterface[]
     * @throws Exception
     */
    protected function loadTemplateEngines(): array
    {
        /**
         * If the engines aren't initialized 'engines' is false
         */
        if (false === $this->engines) {
            $engines = [];
            if (empty($this->registeredEngines)) {
                /**
                 * We use Phalcon\Mvc\View\Engine\Php as default
                 * Use .phtml as extension for the PHP engine
                 */
                $engines[".phtml"] = new PhpEngine($this, $this->container);
            } else {
                $this->checkContainer(
                    Exception::class,
                    'the application services'
                );

                foreach ($this->registeredEngines as $extension => $engineService) {
                    if (is_object($engineService)) {
                        /**
                         * Engine can be a closure
                         */
                        if ($engineService instanceof Closure) {
                            $engineService = Closure::bind(
                                $engineService,
                                $this->container
                            );

                            $engineObject = call_user_func(
                                $engineService,
                                $this
                            );
                        } else {
                            $engineObject = $engineService;
                        }
                    } elseif (is_string($engineService)) {
                        /**
                         * Engine can be a string representing a service in the DI
                         */
                        $engineObject = $this->container->getShared(
                            $engineService,
                            [
                                $this,
                            ]
                        );
                    } else {
                        throw new Exception(
                            "Invalid template engine registration for extension: "
                            . $extension
                        );
                    }

                    $engines[$extension] = $engineObject;
                }
            }

            $this->engines = $engines;
        }

        return $this->engines;
    }
}
