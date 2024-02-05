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

/**
 * Interface for Phalcon\Mvc\View and Phalcon\Mvc\View\Simple
 */
interface ViewBaseInterface
{
    /**
     * Returns cached output from another view stage
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Returns parameters to views
     *
     * @return array
     */
    public function getParamsToView(): array;

    /**
     * Gets views directory
     *
     * @return string|array
     */
    public function getViewsDir(): string | array;

    /**
     * Renders a partial view
     *
     * @param string     $partialPath
     * @param mixed|null $params
     *
     * @return mixed
     */
    public function partial(string $partialPath, mixed $params = null);

    /**
     * Externally sets the view content
     *
     * @param string $content
     *
     * @return mixed
     */
    public function setContent(string $content);

    /**
     * Adds parameters to views (alias of setVar)
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setParamToView(string $key, mixed $value);

    /**
     * Adds parameters to views
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setVar(string $key, mixed $value);

    /**
     * Sets views directory. Depending of your platform, always add a trailing
     * slash or backslash
     *
     * @param string $viewsDir
     *
     * @return mixed
     */
    public function setViewsDir(string $viewsDir);
}
