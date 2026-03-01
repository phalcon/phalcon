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
use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\View\Exception;
use Phalcon\Mvc\View\ViewInterface;
use Phalcon\Parsers\Parser;
use Phalcon\Traits\Helper\Str\DirSeparatorTrait;

use function array_keys;
use function array_merge;
use function call_user_func;
use function call_user_func_array;
use function explode;
use function file_exists;
use function is_array;
use function is_object;
use function is_string;
use function ob_end_clean;
use function ob_get_clean;
use function ob_get_contents;
use function ob_start;
use function strlen;

use const PHP_OS;

/**
 * Phalcon\Mvc\View is a class for working with the "view" portion of the
 * model-view-controller pattern. That is, it exists to help keep the view
 * script separate from the model and controller scripts. It provides a system
 * of helpers, output filters, and variable escaping.
 *
 * ```php
 * use Phalcon\Mvc\View;
 *
 * $view = new View();
 *
 * // Setting views directory
 * $view->setViewsDir("app/views/");
 *
 * $view->start();
 *
 * // Shows recent posts view (app/views/posts/recent.phtml)
 * $view->render("posts", "recent");
 * $view->finish();
 *
 * // Printing views output
 * echo $view->getContent();
 * ```
 */
class View extends Injectable implements ViewInterface, EventsAwareInterface
{
    use DirSeparatorTrait;
    use EventsAwareTrait;

    /**
     * Render Level: To the action view
     */
    public const LEVEL_ACTION_VIEW = 1;
    /**
     * Render Level: Render to the templates "after"
     */
    public const LEVEL_AFTER_TEMPLATE = 4;
    /**
     * Render Level: To the templates "before"
     */
    public const LEVEL_BEFORE_TEMPLATE = 2;
    /**
     * Render Level: To the controller layout
     */
    public const LEVEL_LAYOUT = 3;
    /**
     * Render Level: To the main layout
     */
    public const LEVEL_MAIN_LAYOUT = 5;
    /**
     * Render Level: No render any view
     */
    public const LEVEL_NO_RENDER = 0;
    /**
     * @var string
     */
    protected string $actionName;

    /**
     * @var array
     */
    protected array $activeRenderPaths = [];

    /**
     * @var string
     */
    protected string $basePath = "";

    /**
     * @var string
     */
    protected string $content = "";

    /**
     * @var string
     */
    protected string $controllerName;

    /**
     * @var int
     */
    protected int $currentRenderLevel = 0;

    /**
     * @var bool
     */
    protected bool $disabled = false;

    /**
     * @var array
     */
    protected array $disabledLevels = [];

    /**
     * @var array|false
     */
    protected array | false $engines = false; // TODO: Make always array

    /**
     * @var string|null
     */
    protected string | null $layout = null;

    /**
     * @var string
     */
    protected string $layoutsDir = "";

    /**
     * @var string
     */
    protected string $mainView = "index";

    /**
     * @var array
     */
    protected array $params = [];

    /**
     * @var string
     */
    protected string $partialsDir = ""; // TODO: Make always array

    /**
     * @var array|null
     */
    protected array | null $pickView = null;

    /**
     * @var array
     */
    protected array $registeredEngines = [];

    /**
     * @var int
     */
    protected int $renderLevel = 5;

    /**
     * @var array
     */
    protected array $templatesAfter = [];

    /**
     * @var array
     */
    protected array $templatesBefore = [];

    /**
     * @var array
     */
    protected array $viewParams = [];

    /**
     * @var array|string
     */
    protected array | string $viewsDirs = [];

    /**
     * Phalcon\Mvc\View constructor
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
        return $this->getVar($propertyName);
    }

    /**
     * Magic method to retrieve if a variable is set in the view
     *
     *```php
     * echo isset($this->view->products);
     *```
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->viewParams[$name]);
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
    public function __set(string $key, mixed $value)
    {
        $this->setVar($key, $value);
    }

    /**
     * Resets any template before layouts
     *
     * @return $this
     */
    public function cleanTemplateAfter(): View
    {
        $this->templatesAfter = [];

        return $this;
    }

    /**
     * Resets any "template before" layouts
     *
     * @return $this
     */
    public function cleanTemplateBefore(): View
    {
        $this->templatesBefore = [];

        return $this;
    }

    /**
     * Disables the auto-rendering process
     *
     * @return $this
     */
    public function disable(): View
    {
        $this->disabled = true;

        return $this;
    }

    /**
     * Disables a specific level of rendering
     *
     *```php
     * // Render all levels except ACTION level
     * $this->view->disableLevel(
     *     View::LEVEL_ACTION_VIEW
     * );
     *```
     *
     * @param mixed $level
     *
     * @return ViewInterface
     */
    public function disableLevel(mixed $level): ViewInterface
    {
        if (is_array($level)) {
            $this->disabledLevels = $level;
        } else {
            $this->disabledLevels[$level] = true;
        }

        return $this;
    }

    /**
     * Enables the auto-rendering process
     *
     * @return $this
     */
    public function enable(): View
    {
        $this->disabled = false;

        return $this;
    }

    /**
     * Checks whether view exists
     *
     * @param string $view
     *
     * @return bool
     * @deprecated
     */
    public function exists(string $view): bool
    {
        return $this->has($view);
    }

    /**
     * Finishes the render process by stopping the output buffering
     *
     * @return $this
     */
    public function finish(): View
    {
        ob_end_clean();

        return $this;
    }

    /**
     * Gets the name of the action rendered
     *
     * @return string
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * Returns the path (or paths) of the views that are currently rendered
     *
     * @return array|string
     */
    public function getActiveRenderPath(): array | string
    {
        $viewsDirsCount   = count($this->getViewsDirs());
        $activeRenderPath = $this->activeRenderPaths;

        if (
            $viewsDirsCount === 1 &&
            is_array($activeRenderPath) &&
            !empty($activeRenderPath)
        ) {
            $activeRenderPath = $activeRenderPath[0];
        }

        if (empty($activeRenderPath)) {
            $activeRenderPath = "";
        }

        return $activeRenderPath;
    }

    /**
     * Gets base path
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
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
     * Gets the name of the controller rendered
     *
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    /**
     * @return int
     *
     * @return int
     */
    public function getCurrentRenderLevel(): int
    {
        return $this->currentRenderLevel;
    }

    /**
     * Returns the name of the main view
     *
     * @return string
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * Gets the current layouts sub-directory
     *
     * @return string
     */
    public function getLayoutsDir(): string
    {
        return $this->layoutsDir;
    }

    /**
     * Returns the name of the main view
     *
     * @return string
     */
    public function getMainView(): string
    {
        return $this->mainView;
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
     * Renders a partial view
     *
     * ```php
     * // Retrieve the contents of a partial
     * echo $this->getPartial("shared/footer");
     * ```
     *
     * ```php
     * // Retrieve the contents of a partial with arguments
     * echo $this->getPartial(
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
     * @return string
     * @throws EventsException
     * @throws Exception
     */
    public function getPartial(
        string $partialPath,
        mixed $params = null
    ): string {
        // not liking the ob_* functions here, but it will greatly reduce the
        // amount of double code.
        ob_start();

        $this->partial($partialPath, $params);

        return ob_get_clean();
    }

    /**
     * Gets the current partials sub-directory
     *
     * @return string
     */
    public function getPartialsDir(): string
    {
        return $this->partialsDir;
    }

    /**
     * @return array
     *
     * @return array
     */
    public function getRegisteredEngines(): array
    {
        return $this->registeredEngines;
    }

    /**
     * Perform the automatic rendering returning the output as a string
     *
     * ```php
     * $template = $this->view->getRender(
     *     "products",
     *     "show",
     *     [
     *         "products" => $products,
     *     ]
     * );
     * ```
     *
     * @param string     $controllerName
     * @param string     $actionName
     * @param array      $params
     * @param mixed|null $configCallback
     *
     * @return string
     */
    public function getRender(
        string $controllerName,
        string $actionName,
        array $params = [],
        mixed $configCallback = null
    ): string {
        /**
         * We must to clone the current view to keep the old state
         */
        $view = clone $this;

        /**
         * The component must be reset to its defaults
         */
        $view->reset();

        /**
         * Set the render variables
         */
        $view->setVars($params);

        /**
         * Perform extra configurations over the cloned object
         */
        if (is_object($configCallback)) {
            call_user_func_array($configCallback, [$view]);
        }

        /**
         * Start the output buffering
         */
        $view->start();

        /**
         * Perform the render passing only the controller and action
         */
        $view->render($controllerName, $actionName);

        /**
         * Stop the output buffering
         */
        $view->finish();

        /**
         * Get the processed content
         */
        return $view->getContent();
    }

    /**
     * @return int
     *
     * @return int
     */
    public function getRenderLevel(): int
    {
        return $this->renderLevel;
    }

    /**
     * Returns a parameter previously set in the view
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getVar(string $key): mixed
    {
        return $this->viewParams[$key] ?? null;
    }

    /**
     * Gets views directory
     *
     * @return string|array
     */
    public function getViewsDir(): string | array
    {
        return $this->viewsDirs;
    }

    /**
     * Checks whether view exists
     *
     * @param string $view
     *
     * @return bool
     */
    public function has(string $view): bool
    {
        $basePath = $this->basePath;
        $engines  = $this->registeredEngines;

        if (empty($engines)) {
            $engines = [
                ".phtml" => PhpEngine::class,
            ];

            $this->registerEngines($engines);
        }

        $extKeys = array_keys($engines);
        foreach ($this->getViewsDirs() as $viewsDir) {
            foreach ($extKeys as $extension) {
                if (file_exists($basePath . $viewsDir . $view . $extension)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Whether automatic rendering is enabled
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
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
    public function partial(string $partialPath, mixed $params = null)
    {
        /**
         * If the developer pass an array of variables we create a new virtual
         * symbol table
         */
        if (is_array($params)) {
            /**
             * Merge the new params as parameters
             */
            $viewParams       = $this->viewParams;
            $this->viewParams = array_merge($viewParams, $params);
        }

        /**
         * Partials are looked up under the partials directory
         * We need to check if the engines are loaded first, this method could
         * be called outside of 'render'
         * Call engine render, this checks in every registered engine for the
         * partial
         */
        $this->engineRender(
            $this->loadTemplateEngines(),
            $this->partialsDir . $partialPath,
            false,
            false
        );

        /**
         * Now we need to restore the original view parameters
         */
        if (is_array($params)) {
            /**
             * Restore the original view params
             */
            $this->viewParams = $viewParams;
        }
    }

    /**
     * Choose a different view to render instead of last-controller/last-action
     *
     * ```php
     * use Phalcon\Mvc\Controller;
     *
     * class ProductsController extends Controller
     * {
     *     public function saveAction()
     *     {
     *         // Do some save stuff...
     *
     *         // Then show the list view
     *         $this->view->pick("products/list");
     *     }
     * }
     * ```
     *
     * @param mixed $renderView
     *
     * @return $this
     */
    public function pick(mixed $renderView): View
    {
        if (is_array($renderView)) {
            $pickView = $renderView;
        } else {
            $layout = null;

            if (str_contains($renderView, "/")) {
                $parts  = explode("/", $renderView);
                $layout = $parts[0];
            }

            $pickView = [$renderView];

            if ($layout !== null) {
                $pickView[] = $layout;
            }
        }

        $this->pickView = $pickView;

        return $this;
    }

    /**
     * Processes the view and templates; Fires events if needed
     *
     * @param string $controllerName
     * @param string $actionName
     * @param array  $params
     * @param bool   $fireEvents
     *
     * @return bool
     * @throws EventsException
     * @throws Exception
     */
    public function processRender(
        string $controllerName,
        string $actionName,
        array $params = [],
        bool $fireEvents = true
    ): bool {
        $this->currentRenderLevel = 0;

        /**
         * If the view is disabled we simply update the buffer from any output
         * produced in the controller
         */
        if ($this->disabled !== false) {
            $this->content = ob_get_contents();

            return false;
        }

        $this->controllerName = $controllerName;
        $this->actionName     = $actionName;

        $this->setVars($params);

        /**
         * Check if there is a layouts directory set
         */
        $layoutsDir = $this->layoutsDir;

        if (!$layoutsDir) {
            $layoutsDir = "layouts/";
        }

        /**
         * Check if the user has defined a custom layout
         */
        $layout = $this->layout;

        if ($layout) {
            $layoutName = $layout;
        } else {
            $layoutName = $controllerName;
        }

        /**
         * Load the template engines
         */
        $engines = $this->loadTemplateEngines();

        /**
         * Check if the user has picked a view different than the automatic
         */
        $pickView = $this->pickView;

        if ($pickView === null) {
            $renderView = $controllerName . "/" . $actionName;
        } else {
            /**
             * The 'picked' view is an array, where the first element is
             * controller and the second the action
             */
            $renderView = $pickView[0];

            if (
                $layoutName === null &&
                isset($pickView[1])
            ) {
                $layoutName = $pickView[1];
            }
        }

        /**
         * Call beforeRender if there is an events manager
         */
        if (
            $fireEvents &&
            false === $this->fireManagerEvent("view:beforeRender")
        ) {
            return false;
        }

        /**
         * Get the current content in the buffer maybe some output from the
         * controller?
         */
        $this->content = ob_get_contents();
        $silence       = true;

        /**
         * Disabled levels allow to avoid an specific level of rendering
         */
        $disabledLevels = $this->disabledLevels;

        /**
         * Render level will tell use when to stop
         */
        $renderLevel = $this->renderLevel;

        if ($renderLevel) {
            /**
             * Inserts view related to action
             */
            if (
                $renderLevel >= self::LEVEL_ACTION_VIEW &&
                !isset($disabledLevels[self::LEVEL_ACTION_VIEW])
            ) {
                $this->currentRenderLevel = self::LEVEL_ACTION_VIEW;

                $this->engineRender(
                    $engines,
                    $renderView,
                    $silence
                );
            }

            /**
             * Inserts templates before layout
             */
            if (
                $renderLevel >= self::LEVEL_BEFORE_TEMPLATE &&
                !isset($disabledLevels[self::LEVEL_BEFORE_TEMPLATE])
            ) {
                $this->currentRenderLevel = self::LEVEL_BEFORE_TEMPLATE;
                $templatesBefore          = $this->templatesBefore;
                $silence                  = false;

                foreach ($templatesBefore as $templateBefore) {
                    $this->engineRender(
                        $engines,
                        $layoutsDir . $templateBefore,
                        $silence
                    );
                }

                $silence = true;
            }

            /**
             * Inserts controller layout
             */
            if (
                $renderLevel >= self::LEVEL_LAYOUT &&
                !isset($disabledLevels[self::LEVEL_LAYOUT])
            ) {
                $this->currentRenderLevel = self::LEVEL_LAYOUT;

                $this->engineRender(
                    $engines,
                    $layoutsDir . $layoutName,
                    $silence
                );
            }

            /**
             * Inserts templates after layout
             */
            if (
                $renderLevel >= self::LEVEL_AFTER_TEMPLATE &&
                !isset($disabledLevels[self::LEVEL_AFTER_TEMPLATE])
            ) {
                $this->currentRenderLevel = self::LEVEL_AFTER_TEMPLATE;
                $templatesAfter           = $this->templatesAfter;
                $silence                  = false;

                foreach ($templatesAfter as $templateAfter) {
                    $this->engineRender(
                        $engines,
                        $layoutsDir . $templateAfter,
                        $silence
                    );
                }

                $silence = true;
            }

            /**
             * Inserts main view
             */
            if (
                $renderLevel >= self::LEVEL_MAIN_LAYOUT &&
                !isset($disabledLevels[self::LEVEL_MAIN_LAYOUT])
            ) {
                $this->currentRenderLevel = self::LEVEL_MAIN_LAYOUT;

                $this->engineRender(
                    $engines,
                    $this->mainView,
                    $silence
                );
            }

            $this->currentRenderLevel = 0;
        }

        /**
         * Call afterRender event
         */
        if ($fireEvents) {
            $this->fireManagerEvent("view:afterRender");
        }

        return true;
    }

    /**
     * Register templating engines
     *
     * ```php
     * $this->view->registerEngines(
     *     [
     *         ".phtml" => \Phalcon\Mvc\View\Engine\Php::class,
     *         ".volt"  => \Phalcon\Mvc\View\Engine\Volt::class,
     *         ".mhtml" => \MyCustomEngine::class,
     *     ]
     * );
     * ```
     *
     * @param array $engines
     *
     * @return $this
     */
    public function registerEngines(array $engines): View
    {
        $this->registeredEngines = $engines;

        return $this;
    }

    /**
     * Executes render process from dispatching data
     *
     *```php
     * // Shows recent posts view (app/views/posts/recent.phtml)
     * $view->start()->render("posts", "recent")->finish();
     *```
     *
     * @param string $controllerName
     * @param string $actionName
     * @param array  $params
     *
     * @return View|bool|$this
     * @throws EventsException
     * @throws Exception
     */
    public function render(
        string $controllerName,
        string $actionName,
        array $params = []
    ): View | bool {
        $result = $this->processRender($controllerName, $actionName, $params);

        if (!$result) {
            return false;
        }

        return $this;
    }

    /**
     * Resets the view component to its factory default values
     */
    public function reset(): View
    {
        $this->disabled        = false;
        $this->engines         = false;
        $this->renderLevel     = self::LEVEL_MAIN_LAYOUT;
        $this->content         = "";
        $this->templatesBefore = [];
        $this->templatesAfter  = [];

        return $this;
    }

    /**
     * Sets base path. Depending of your platform, always add a trailing slash
     * or backslash
     *
     * ```php
     * $view->setBasePath(__DIR__ . "/");
     * ```
     *
     * @param string $basePath
     *
     * @return $this
     */
    public function setBasePath(string $basePath): View
    {
        $this->basePath = $basePath;

        return $this;
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
    public function setContent(string $content): View
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Change the layout to be used instead of using the name of the latest
     * controller name
     *
     * ```php
     * $this->view->setLayout("main");
     * ```
     *
     * @param string $layout
     *
     * @return $this
     */
    public function setLayout(string $layout): View
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Sets the layouts sub-directory. Must be a directory under the views
     * directory. Depending of your platform, always add a trailing slash or
     * backslash
     *
     *```php
     * $view->setLayoutsDir("../common/layouts/");
     *```
     *
     * @param string $layoutsDir
     *
     * @return $this
     */
    public function setLayoutsDir(string $layoutsDir): View
    {
        $this->layoutsDir = $layoutsDir;

        return $this;
    }

    /**
     * Sets default view name. Must be a file without extension in the views
     * directory
     *
     * ```php
     * // Renders as main view views-dir/base.phtml
     * $this->view->setMainView("base");
     * ```
     *
     * @param string $viewPath
     *
     * @return $this
     */
    public function setMainView(string $viewPath): View
    {
        $this->mainView = $viewPath;

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
     * @return $this
     */
    public function setParamToView(string $key, mixed $value): View
    {
        return $this->setVar($key, $value);
    }

    /**
     * Sets a partials sub-directory. Must be a directory under the views
     * directory. Depending of your platform, always add a trailing slash or
     * backslash
     *
     *```php
     * $view->setPartialsDir("../common/partials/");
     *```
     *
     * @param string $partialsDir
     *
     * @return $this
     */
    public function setPartialsDir(string $partialsDir): View
    {
        $this->partialsDir = $partialsDir;

        return $this;
    }

    /**
     * Sets the render level for the view
     *
     * ```php
     * // Render the view related to the controller only
     * $this->view->setRenderLevel(
     *     View::LEVEL_LAYOUT
     * );
     * ```
     */
    public function setRenderLevel(int $level): ViewInterface
    {
        $this->renderLevel = $level;

        return $this;
    }

    /**
     * Sets a "template after" controller layout
     *
     * @param array|string $templateAfter
     *
     * @return $this
     */
    public function setTemplateAfter(array | string $templateAfter): View
    {
        if (!is_array($templateAfter)) {
            $this->templatesAfter = [$templateAfter];
        } else {
            $this->templatesAfter = $templateAfter;
        }

        return $this;
    }

    /**
     * Sets a template before the controller layout
     *
     * @param array|string $templateBefore
     *
     * @return $this
     */
    public function setTemplateBefore(array | string $templateBefore): View
    {
        if (!is_array($templateBefore)) {
            $this->templatesBefore = [$templateBefore];
        } else {
            $this->templatesBefore = $templateBefore;
        }

        return $this;
    }

    /**
     * Set a single view parameter
     *
     *```php
     * $this->view->setVar("products", $products);
     *```
     */
    public function setVar(string $key, mixed $value): View
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
    public function setVars(array $params, bool $merge = true): View
    {
        if ($merge) {
            $this->viewParams = array_merge($this->viewParams, $params);
        } else {
            $this->viewParams = $params;
        }

        return $this;
    }

    /**
     * Sets the views directory. Depending of your platform,
     * always add a trailing slash or backslash
     *
     * @param array|string $viewsDir
     *
     * @return $this
     * @throws Exception
     */
    public function setViewsDir(array | string $viewsDir): View
    {
        if (is_string($viewsDir)) {
            $this->viewsDirs = $this->toDirSeparator($viewsDir);
        } else {
            $newViewsDir = [];

            foreach ($viewsDir as $position => $directory) {
                if (!is_string($directory)) {
                    throw new Exception(
                        "Views directory item must be a string"
                    );
                }

                $newViewsDir[$position] = $this->toDirSeparator($directory);
            }

            $this->viewsDirs = $newViewsDir;
        }

        return $this;
    }

    /**
     * Starts rendering process enabling the output buffering
     *
     * @return $this
     */
    public function start(): View
    {
        ob_start();

        $this->content = '';

        return $this;
    }

    /**
     * Renders the view and returns it as a string
     *
     * @param string $controllerName
     * @param string $actionName
     * @param array  $params
     *
     * @return string
     * @throws EventsException
     * @throws Exception
     */
    public function toString(
        string $controllerName,
        string $actionName,
        array $params = []
    ): string {
        $this->start();

        $result = $this->processRender(
            $controllerName,
            $actionName,
            $params,
            false
        );

        $this->finish();

        if (!$result) {
            return "";
        }

        return $this->getContent();
    }

    /**
     * Checks whether view exists on registered extensions and render it
     *
     * @param array  $engines
     * @param string $viewPath
     * @param bool   $silence
     * @param bool   $mustClean
     *
     * @return void
     * @throws Exception
     * @throws EventsException
     */
    protected function engineRender(
        array $engines,
        string $viewPath,
        bool $silence,
        bool $mustClean = true
    ) {
        $basePath        = $this->basePath;
        $viewParams      = $this->viewParams;
        $viewEnginePaths = [];

        foreach ($this->getViewsDirs() as $viewsDir) {
            if (!$this->isAbsolutePath($viewPath)) {
                $viewsDirPath = $basePath . $viewsDir . $viewPath;
            } else {
                $viewsDirPath = $viewPath;
            }

            /**
             * Views are rendered in each engine
             */
            foreach ($engines as $extension => $engine) {
                $viewEnginePath = $viewsDirPath . $extension;

                if (file_exists($viewEnginePath)) {
                    /**
                     * Call beforeRenderView if there is an events manager
                     * available
                     */
                    if (null !== $this->eventsManager) {
                        $this->activeRenderPaths = [$viewEnginePath];

                        if (false === $this->fireManagerEvent("view:beforeRenderView", $viewEnginePath)) {
                            continue;
                        }
                    }

                    $engine->render($viewEnginePath, $viewParams, $mustClean);
                    $this->fireManagerEvent("view:afterRenderView", $viewEnginePath);

                    return;
                }

                $viewEnginePaths[] = $viewEnginePath;
            }
        }

        /**
         * Notify about not found views
         */
        if (null !== $this->eventsManager) {
            $this->activeRenderPaths = $viewEnginePaths;

            $this->fireManagerEvent("view:notFoundView", $viewPath);
        }

        if (!$silence) {
            throw new Exception(
                "View '" . $viewPath . "' was not found in any of the views directory"
            );
        }
    }

    /**
     * Gets views directories
     *
     * @return array
     */
    protected function getViewsDirs(): array
    {
        if (is_string($this->viewsDirs)) {
            return [$this->viewsDirs];
        }

        return $this->viewsDirs;
    }

    /**
     * Checks if a path is absolute or not
     *
     * @param string $path
     *
     * @return bool
     */
    final protected function isAbsolutePath(string $path): bool
    {
        if (PHP_OS === "WINNT") {
            return strlen($path) >= 3 && $path[1] == ':' && $path[2] == '\\';
        }

        return strlen($path) >= 1 && $path[0] == '/';
    }

    /**
     * Loads registered template engines, if none is registered it will use
     * Phalcon\Mvc\View\Engine\Php
     *
     * @return array
     * @throws Exception
     */
    protected function loadTemplateEngines(): array
    {
        /**
         * If the engines aren't initialized 'engines' is false
         */
        if (false === $this->engines) {
            $engines           = [];
            $registeredEngines = $this->registeredEngines;

            if (empty($registeredEngines)) {
                /**
                 * We use Phalcon\Mvc\View\Engine\Php as default
                 */
                $engines[".phtml"] = new PhpEngine($this, $this->container);
            } else {
                $this->checkContainer(
                    Exception::class,
                    'the application services'
                );

                foreach ($registeredEngines as $extension => $engineService) {
                    if (is_object($engineService)) {
                        /**
                         * Engine can be a closure
                         */
                        if ($engineService instanceof Closure) {
                            $engineService = Closure::bind(
                                $engineService,
                                $this->container
                            );

                            $engines[$extension] = call_user_func(
                                $engineService,
                                $this
                            );
                        } else {
                            $engines[$extension] = $engineService;
                        }
                    } else {
                        /**
                         * Engine can be a string representing a service in the DI
                         */
                        if (!is_string($engineService)) {
                            throw new Exception(
                                "Invalid template engine registration for extension: "
                                . $extension
                            );
                        }

                        $engines[$extension] = $this->container->get(
                            $engineService,
                            [$this]
                        );
                    }
                }
            }

            $this->engines = $engines;
        }

        return $this->engines;
    }
}
