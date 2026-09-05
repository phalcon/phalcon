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
 * Interface for Phalcon\Mvc\View and Phalcon\Mvc\View\Simple
 */
interface ViewBaseInterface
{
    /**
     * Returns cached output from another view stage
     */
    public function getContent(): string;

    /**
     * Returns parameters to views
     *
     * @phpstan-return array<string, mixed>
     */
    public function getParamsToView(): array;

    /**
     * Gets views directory
     *
     * @phpstan-return list<string>|string
     */
    public function getViewsDir(): array | string;

    /**
     * Renders a partial view
     *
     * @return mixed
     */
    public function partial(string $partialPath, mixed $params = null);

    /**
     * Externally sets the view content
     *
     * @return mixed
     */
    public function setContent(string $content);

    /**
     * Adds parameters to views (alias of setVar)
     *
     * @return mixed
     */
    public function setParamToView(string $key, mixed $value);

    /**
     * Adds parameters to views
     *
     * @return mixed
     */
    public function setVar(string $key, mixed $value);

    /**
     * Sets views directory. Depending of your platform, always add a trailing
     * slash or backslash
     *
     * @return mixed
     */
    public function setViewsDir(string $viewsDir);
}
