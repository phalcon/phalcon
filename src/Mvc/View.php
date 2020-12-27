<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Mvc;

use Closure;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Events\ManagerInterface;
use Phalcon\Helper\Arr;
use Phalcon\Mvc\View\Exception;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;

/**
 * Phalcon\Mvc\View
 *
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
class View extends Injectable implements ViewInterface, EventsAwareInterface {

    /**
     * Render Level: To the action view
     */
    const LEVEL_ACTION_VIEW = 1;

    /**
     * Render Level: To the templates "before"
     */
    const LEVEL_BEFORE_TEMPLATE = 2;

    /**
     * Render Level: To the controller layout
     */
    const LEVEL_LAYOUT = 3;

    /**
     * Render Level: To the main layout
     */
    const LEVEL_MAIN_LAYOUT = 5;

    /**
     * Render Level: No render any view
     */
    const LEVEL_NO_RENDER = 0;

    /**
     * Render Level: Render to the templates "after"
     */
    const LEVEL_AFTER_TEMPLATE = 4;

    protected $actionName;
    protected $activeRenderPaths;
    protected $basePath = "";
    protected $content = "";
    protected $controllerName;
    protected $currentRenderLevel = 0; // TODO: { get };
    protected $disabled = false;
    protected $disabledLevels;
    protected $engines = false;
    protected $eventsManager;
    protected $layout;
    protected $layoutsDir = "";
    protected $mainView = "index";
    protected $options = [];
    protected $params;
    protected $pickView;
    protected $partialsDir = "";
    protected $registeredEngines = []; // TODO:{ get };
    protected $renderLevel = 5; // TODO:{ get };
    protected $templatesAfter = [];
    protected $templatesBefore = [];
    protected $viewsDirs = [];
    protected $viewParams = [];

    /**
     * Phalcon\Mvc\View constructor
     */
    public function __construct(array $options = []) {
        $this->options = $options;
    }

    /**
     * Magic method to retrieve a variable passed to the view
     * 
     * ```php
     * echo $this->view->products;
     * ```
     */
    public function __get(string $key) {
        return $this->getVar($key);
    }

    /**
     * Magic method to retrieve if a variable is set in the view
     *
     * ```php
     * echo isset($this->view->products);
     * ```
     */
    public function __isset(string $key): bool {
        return isset($this->viewParams[$key]);
    }

    /**
     * Magic method to pass variables to the views
     *
     * ```php
     * $this->view->products = $products;
     * ```
     */
    public function __set(string $key, $value) {
        $this->setVar($key, $value);
    }

    /**
     * Resets any template before layouts
     */
    public function cleanTemplateAfter(): View {
        $this->templatesAfter = [];

        return $this;
    }

    /**
     * Resets any "template before" layouts
     */
    public function cleanTemplateBefore(): View {
        $this->templatesBefore = [];

        return $this;
    }

    /**
     * Disables a specific level of rendering
     *
     * ```php
     * // Render all levels except ACTION level
     * $this->view->disableLevel(
     *     View::LEVEL_ACTION_VIEW
     * );
     * ```
     */
    public function disableLevel($level): ViewInterface {
        if (is_array($level)) {
            $this->disabledLevels = $level;
        } else {
            $this->disabledLevels[$level] = true;
        }

        return $this;
    }

    /**
     * Disables the auto-rendering process
     */
    public function disable(): View {
        $this->disabled = true;

        return $this;
    }

    /**
     * Enables the auto-rendering process
     */
    public function enable(): View {
        $this->disabled = false;

        return $this;
    }

    /**
     * Checks whether view exists
     */
    public function exists(string $view): bool {
        //var basePath, viewsDir, engines, extension;

        $basePath = $this->basePath;
        $engines = $this->registeredEngines;

        if (empty($engines)) {
            $engines = [
                ".phtml" => "Phalcon\\Mvc\\View\\Engine\\Php"
            ];

            $this->registerEngines(engines);
        }

        foreach ($this->getViewsDirs() as $viewDir) {
            foreach ($engines as $extension => $_) {
                if (file_exists($basePath . $viewsDir . $view . $extension)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Finishes the render process by stopping the output buffering
     */
    public function finish(): View {
        ob_end_clean();

        return $this;
    }

    /**
     * Gets the name of the action rendered
     */
    public function getActionName(): string {
        return $this->actionName;
    }

    /**
     * Returns the path (or paths) of the views that are currently rendered
     * TODO return string | array
     */
    public function getActiveRenderPath() {
        /*         * var activeRenderPath;
          int viewsDirsCount;* */

        $viewsDirsCount = count($this->getViewsDirs());
        $activeRenderPath = $this->activeRenderPaths;

        if ($viewsDirsCount === 1) {
            if (is_array($activeRenderPath) && count($activeRenderPath)) {
                $activeRenderPath = $activeRenderPath[0];
            }
        }

        if ($activeRenderPath === null) {
            $activeRenderPath = "";
        }

        return $activeRenderPath;
    }

    /**
     * Gets base path
     */
    public function getBasePath(): string {
        return $this->basePath;
    }

    /**
     * Returns output from another view stage
     */
    public function getContent(): string {
        return $this->content;
    }

    /**
     * Gets the name of the controller rendered
     */
    public function getControllerName(): string {
        return $this->controllerName;
    }

    /**
     * Returns the internal event manager
     */
    public function getEventsManager(): ?ManagerInterface {
        return $this->eventsManager;
    }

    /**
     * Returns the name of the main view
     */
    public function getLayout(): string {
        return $this->layout;
    }

    /**
     * Gets the current layouts sub-directory
     */
    public function getLayoutsDir(): string {
        return $this->layoutsDir;
    }

    /**
     * Returns the name of the main view
     */
    public function getMainView(): string {
        return $this->mainView;
    }

    /**
     * Returns parameters to views
     */
    public function getParamsToView(): array {
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
     */
    public function getPartial(string $partialPath, $params = null): string {
        // not liking the ob_* functions here, but it will greatly reduce the
        // amount of double code.
        ob_start();

        $this->partial($partialPath, $params);

        return ob_get_clean();
    }

    /**
     * Gets the current partials sub-directory
     */
    public function getPartialsDir(): string {
        return $this->partialsDir;
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
     * @param mixed configCallback
     */
    public function getRender(string $controllerName, string $actionName, array $params = [], $configCallback = null): string {
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
     * Returns a parameter previously set in the view
     */
    public function getVar(string $key) {
        //var value;
        return $this->viewParams[$key] ?? null;
    }

    /**
     * Gets views directory
     * TODO: retun string | array
     */
    public function getViewsDir() {
        return $this->viewsDirs;
    }

    /**
     * Gets views directories
     */
    protected function getViewsDirs(): array {
        $result = $this->viewsDirs;
        if (is_string($result)) {
            return [$result];
        }

        return $result;
    }

    /**
     * Whether automatic rendering is enabled
     */
    public function isDisabled(): bool {
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
     */
    public function partial(string $partialPath, $params = null) {
        //var viewParams;

        /**
         * If the developer pass an array of variables we create a new virtual
         * symbol table
         */
        if (is_array($params)) {

            // save original
            $viewParams = $this->viewParams;
            /**
             * Merge the new params as parameters
             */
            $this->viewParams = array_merge($this->viewParams, $params);

            /**
             * Create a virtual symbol table
             * TODO: how does this work here?
             */
            //create_symbol_table();
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
     */
    public function pick($renderView): View {
        //var pickView, layout, parts;

        if (is_array($renderView)) {
            $pickView = renderView;
        } else {
            $layout = null;

            if (strpos($renderView, "/") !== false) {
                $parts = explode("/", $renderView);
                $layout = parts[0];
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
     */
    public function registerEngines(array $engines): View {
        $this->registeredEngines = $engines;
        return $this;
    }

    /**
     * Executes render process from dispatching data
     *
     * ```php
     * // Shows recent posts view (app/views/posts/recent.phtml)
     * $view->start()->render("posts", "recent")->finish();
     * ```
     * TODO: return View | bool
     */
    public function render(
            string $controllerName,
            string $actionName,
            array $params = []
    ) {
        //var result;

        $result = $this->processRender($controllerName, $actionName, $params);

        if (!$result) {
            return false;
        }

        return $this;
    }

    /**
     * Resets the view component to its factory default values
     */
    public function reset(): View {
        $this->disabled = false;
        $this->engines = false;
        $this->renderLevel = self::LEVEL_MAIN_LAYOUT;
        $this->content = null;
        $this->templatesBefore = [];
        $this->templatesAfter = [];

        return $this;
    }

    /**
     * Sets base path. Depending of your platform, always add a trailing slash
     * or backslash
     *
     * ```php
     * $view->setBasePath(__DIR__ . "/");
     * ```
     */
    public function setBasePath(string $basePath): View {
        $this->basePath = basePath;

        return $this;
    }

    /**
     * Externally sets the view content
     *
     * ```php
     * $this->view->setContent("<h1>hello</h1>");
     * ```
     */
    public function setContent(string $content): View {
        $this->content = $content;

        return $this;
    }

    /**
     * Sets the events manager
     */
    public function setEventsManager(ManagerInterface $eventsManager): void {
        $this->eventsManager = eventsManager;
    }

    /**
     * Change the layout to be used instead of using the name of the latest
     * controller name
     *
     * ```php
     * $this->view->setLayout("main");
     * ```
     */
    public function setLayout(string $layout): View {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Sets the layouts sub-directory. Must be a directory under the views
     * directory. Depending of your platform, always add a trailing slash or
     * backslash
     *
     * ```php
     * $view->setLayoutsDir("../common/layouts/");
     * ```
     */
    public function setLayoutsDir(string $layoutsDir): View {
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
     */
    public function setMainView(string $viewPath): View {
        $this->mainView = $viewPath;

        return $this;
    }

    /**
     * Sets a partials sub-directory. Must be a directory under the views
     * directory. Depending of your platform, always add a trailing slash or
     * backslash
     *
     * ```php
     * $view->setPartialsDir("../common/partials/");
     * ```
     */
    public function setPartialsDir(string $partialsDir): View {
        $this->partialsDir = $partialsDir;

        return $this;
    }

    /**
     * Adds parameters to views (alias of setVar)
     *
     * ```php
     * $this->view->setParamToView("products", $products);
     * ```
     */
    public function setParamToView(string $key, $value): View {
        $this->viewParams[$key] = $value;

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
    public function setRenderLevel(int $level): ViewInterface {
        $this->renderLevel = level;

        return $this;
    }

    /**
     * Sets a "template after" controller layout
     */
    public function setTemplateAfter($templateAfter): View {
        if (!is_array($templateAfter)) {
            $this->templatesAfter = [$templateAfter];
        } else {
            $this->templatesAfter = $templateAfter;
        }

        return $this;
    }

    /**
     * Sets a template before the controller layout
     */
    public function setTemplateBefore($templateBefore): View {
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
     * ```php
     * $this->view->setVar("products", $products);
     * ```
     */
    public function setVar(string $key, $value): View {
        $this->viewParams[$key] = $value;

        return $this;
    }

    /**
     * Set all the render params
     *
     * ```php
     * $this->view->setVars(
     *     [
     *         "products" => $products,
     *     ]
     * );
     * ```
     */
    public function setVars(array $params, bool $merge = true): View {
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
     */
    public function setViewsDir($viewsDir): View {
        //var position, directory, newViewsDir;

        if (!is_string($viewsDir) && !is_array($viewsDir)) {
            throw new Exception("Views directory must be a string or an array");
        }

        if (is_string($viewsDir)) {
            if (!str_ends_with($viewsDir,DIRECTORY_SEPARATOR)) {
                $viewsDir .= DIRECTORY_SEPARATOR;
            }
            $this->viewsDirs = $viewsDir;   
        } else {
            $newViewsDir = [];

            foreach ($viewsDir as $position => $directory) {
                if (!is_string($directory)) {
                    throw new Exception(
                            "Views directory item must be a string"
                    );
                }
                if (!str_ends_with($directory, DIRECTORY_SEPARATOR)) {
                    $directory .= DIRECTORY_SEPARATOR;
                }
                $newViewsDir[$position] = $directory;
            }

            $this->viewsDirs = $newViewsDir;
        }

        return $this;
    }

    /**
     * Starts rendering process enabling the output buffering
     */
    public function start(): View {
        ob_start();

        $this->content = null;

        return $this;
    }

    /**
     * Renders the view and returns it as a string
     */
    public function toString(
            string $controllerName,
            string $actionName,
            array $params = []
    ): string {
        //var result;

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
     */
    protected function engineRender(
            array $engines,
            string $viewPath,
            bool $silence,
            bool $mustClean = true
    ) {
        //var basePath, engine, eventsManager, extension, viewsDir, viewsDirPath,
        //    viewEnginePath, viewEnginePaths, viewParams;

        $basePath = $this->basePath;
        $viewParams = $this->viewParams;
        $eventsManager = $this->eventsManager;
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
                    if (is_object($eventsManager)) {
                        $this->activeRenderPaths = [$viewEnginePath];

                        if ($eventsManager->fire("view:beforeRenderView", $this, $viewEnginePath) === false) {
                            continue;
                        }
                    }

                    $engine->render($viewEnginePath, $viewParams, $mustClean);

                    if (is_object($eventsManager)) {
                        $eventsManager->fire("view:afterRenderView", $this);
                    }

                    return;
                }

                $viewEnginePaths[] = $viewEnginePath;
            }
        }

        /**
         * Notify about not found views
         */
        if (is_object($eventsManager)) {
            $this->activeRenderPaths = $viewEnginePaths;

            $eventsManager->fire("view:notFoundView", $this, $viewEnginePath);
        }

        if ($silence) {
            throw new Exception(
                    "View '" . $viewPath . "' was not found in any of the views directory"
            );
        }
    }

    /**
     * Checks if a path is absolute or not
     */
    final protected function isAbsolutePath(string $path) {
        if (PHP_OS === "WINNT") {
        //if (PHP_OS === "WINNT" ) {
            return strlen($path) >= 3 && $path[1] == ':' && $path[2] == '\\';
        }

        return strlen($path) >= 1 && $path[0] == '/';
    }

    /**
     * Loads registered template engines, if none is registered it will use
     * Phalcon\Mvc\View\Engine\Php
     */
    protected function loadTemplateEngines(): array {
        //var engines, di, registeredEngines, engineService, extension;

        $engines = $this->engines;

        /**
         * If the engines aren't initialized 'engines' is false
         */
        if ($engines === false) {
            $di = $this->container;

            $engines = [];
            $registeredEngines = $this->registeredEngines;

            if (empty($registeredEngines)) {
                /**
                 * We use Phalcon\Mvc\View\Engine\Php as default
                 */
                $engines[".phtml"] = new PhpEngine($this, $di);
            } else {
                if (!is_object($di)) {
                    throw new Exception(
                            Exception::containerServiceNotFound(
                                    "application services"
                            )
                    );
                }

                foreach ($registeredEngines as $extension => $engineService) {
                    if (is_object($engineService)) {
                        /**
                         * Engine can be a closure
                         */
                        if ($engineService instanceof Closure) {
                            $engineService = Closure::bind(
                                            $engineService,
                                            $di
                            );

                            $engines[extension] = call_user_func(
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
                                    "Invalid template engine registration for extension: " . $extension
                            );
                        }

                        $engines[$extension] = $di->get(
                                $engineService,
                                [$this]
                        );
                    }
                }
            }

            $this->engines = $engines;
        }

        return $engines;
    }

    /**
     * Processes the view and templates; Fires events if needed
     */
    public function processRender(
            string $controllerName,
            string $actionName,
            array $params = [],
            bool $fireEvents = true
    ): bool {
        /* bool silence;
          int renderLevel;
          var layoutsDir, layout, pickView, layoutName, engines, renderView,
          pickViewAction, eventsManager, disabledLevels, templatesBefore,
          templatesAfter, templateBefore, templateAfter;
         */

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
        $this->actionName = $actionName;

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
            $renderView = pickView[0];

            if ($layoutName === null) {
                $pickViewAction = $pickView[1] ?? null;
                if ($pickViewAction !== null) {
                    $layoutName = $pickViewAction;
                }
            }
        }

        $eventsManager = $this->eventsManager;

        /**
         * Create a virtual symbol table.
         * Variables are shared across symbol tables in PHP5
         */
        //create_symbol_table();

        /**
         * Call beforeRender if there is an events manager
         */
        if ($fireEvents && is_object($eventsManager)) {
            if ($eventsManager->fire("view:beforeRender", $this) === false) {
                return false;
            }
        }

        /**
         * Get the current content in the buffer maybe some output from the
         * controller?
         */
        $this->content = ob_get_contents();
        $silence = true;

        /**
         * Disabled levels allow to avoid an specific level of rendering
         */
        $disabledLevels = $this->disabledLevels;

        /**
         * Render level will tell use when to stop
         */
        $renderLevel = (int) $this->renderLevel;

        if ($renderLevel) {
            /**
             * Inserts view related to action
             */
            if ($renderLevel >= self::LEVEL_ACTION_VIEW) {
                if (!isset($disabledLevels[self::LEVEL_ACTION_VIEW])) {
                    $this->currentRenderLevel = self::LEVEL_ACTION_VIEW;

                    $this->engineRender(
                            $engines,
                            $renderView,
                            $silence
                    );
                }
            }

            /**
             * Inserts templates before layout
             */
            if ($renderLevel >= self::LEVEL_BEFORE_TEMPLATE) {
                if (!isset($disabledLevels[self::LEVEL_BEFORE_TEMPLATE])) {
                    $this->currentRenderLevel = self::LEVEL_BEFORE_TEMPLATE;
                    $templatesBefore = $this->templatesBefore;
                    $silence = false;

                    foreach ($templatesBefore as $templateBefore) {
                        $this->engineRender(
                                $engines,
                                $layoutsDir . $templateBefore,
                                $silence
                        );
                    }

                    $silence = true;
                }
            }

            /**
             * Inserts controller layout
             */
            if ($renderLevel >= self::LEVEL_LAYOUT) {
                if (!isset($disabledLevels[self::LEVEL_LAYOUT])) {
                    $this->currentRenderLevel = self::LEVEL_LAYOUT;

                    $this->engineRender(
                            $engines,
                            $layoutsDir . $layoutName,
                            $silence
                    );
                }
            }

            /**
             * Inserts templates after layout
             */
            if ($renderLevel >= self::LEVEL_AFTER_TEMPLATE) {
                if (!isset($disabledLevels[self::LEVEL_AFTER_TEMPLATE])) {
                    $this->currentRenderLevel = self::LEVEL_AFTER_TEMPLATE;
                    $templatesAfter = $this->templatesAfter;
                    $silence = false;

                    foreach ($templatesAfter as $templateAfter) {
                        $this->engineRender(
                                $engines,
                                $layoutsDir . $templateAfter,
                                $silence
                        );
                    }

                    $silence = true;
                }
            }

            /**
             * Inserts main view
             */
            if ($renderLevel >= self::LEVEL_MAIN_LAYOUT) {
                if (!isset($disabledLevels[self::LEVEL_MAIN_LAYOUT])) {
                    $this->currentRenderLevel = self::LEVEL_MAIN_LAYOUT;

                    $this->engineRender(
                            $engines,
                            $this->mainView,
                            $silence
                    );
                }
            }

            $this->currentRenderLevel = 0;
        }

        /**
         * Call afterRender event
         */
        if ($fireEvents && is_object($eventsManager)) {
            $eventsManager->fire("view:afterRender", $this);
        }

        return true;
    }

}
