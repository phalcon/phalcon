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

/**
 * Interface for Phalcon\Mvc\View
 */
interface ViewInterface extends ViewBaseInterface
{
    /**
     * Resets any template before layouts
     *
     * @return static
     */
    public function cleanTemplateAfter();

    /**
     * Resets any template before layouts
     *
     * @return static
     */
    public function cleanTemplateBefore();

    /**
     * Disables the auto-rendering process
     *
     * @return static
     */
    public function disable();

    /**
     * Enables the auto-rendering process
     *
     * @return static
     */
    public function enable();

    /**
     * Finishes the render process by stopping the output buffering
     *
     * @return static
     */
    public function finish();

    /**
     * Gets the name of the action rendered
     */
    public function getActionName(): string;

    /**
     * Returns the path of the view that is currently rendered
     *
     * @phpstan-return list<string>|string
     */
    public function getActiveRenderPath(): array | string;

    /**
     * Gets base path
     */
    public function getBasePath(): string;

    /**
     * Gets the name of the controller rendered
     */
    public function getControllerName(): string;

    /**
     * Returns the name of the main view
     */
    public function getLayout(): string | null;

    /**
     * Gets the current layouts sub-directory
     */
    public function getLayoutsDir(): string;

    /**
     * Returns the name of the main view
     */
    public function getMainView(): string;

    /**
     * Gets the current partials sub-directory
     */
    public function getPartialsDir(): string;

    /**
     * Whether the automatic rendering is disabled
     */
    public function isDisabled(): bool;

    /**
     * Choose a view different to render than last-controller/last-action
     *
     * @return static
     */
    public function pick(string $renderView);

    /**
     * Register templating engines
     *
     * @phpstan-param array<string, mixed> $engines
     *
     * @return static
     */
    public function registerEngines(array $engines);

    /**
     * Executes render process from dispatching data
     *
     * @phpstan-param array<string, mixed> $params
     */
    public function render(
        string $controllerName,
        string $actionName,
        array $params = []
    ): bool | ViewInterface;

    /**
     * Resets the view component to its factory default values
     *
     * @return static
     */
    public function reset();

    /**
     * Sets base path. Depending of your platform, always add a trailing slash
     * or backslash
     *
     * @return static
     */
    public function setBasePath(string $basePath);

    /**
     * Change the layout to be used instead of using the name of the latest
     * controller name
     *
     * @return static
     */
    public function setLayout(string $layout);

    /**
     * Sets the layouts sub-directory. Must be a directory under the views
     * directory. Depending of your platform, always add a trailing slash or
     * backslash
     *
     * @return static
     */
    public function setLayoutsDir(string $layoutsDir);

    /**
     * Sets default view name. Must be a file without extension in the views
     * directory
     *
     * @return static
     */
    public function setMainView(string $viewPath);

    /**
     * Sets a partials sub-directory. Must be a directory under the views
     * directory. Depending of your platform, always add a trailing slash or
     * backslash
     *
     * @return static
     */
    public function setPartialsDir(string $partialsDir);

    /**
     * Sets the render level for the view
     */
    public function setRenderLevel(int $level): ViewInterface;

    /**
     * Appends template after controller layout
     *
     * @param mixed $templateAfter
     *
     *
     * @return static
     */
    public function setTemplateAfter(mixed $templateAfter);

    /**
     * Appends template before controller layout
     *
     * @param mixed $templateBefore
     *
     *
     * @return static
     */
    public function setTemplateBefore(mixed $templateBefore);

    /**
     * Starts rendering process enabling the output buffering
     *
     * @return static
     */
    public function start();
}
