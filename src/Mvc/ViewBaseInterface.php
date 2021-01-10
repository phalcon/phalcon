<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Mvc;

use Phiz\Cache\Adapter\AdapterInterface;

/**
 * Phiz\Mvc\ViewInterface
 *
 * Interface for Phiz\Mvc\View and Phiz\Mvc\View\Simple
 */
interface ViewBaseInterface
{
    /**
     * Returns cached output from another view stage
     */
    public function getContent():string;

    /**
     * Returns parameters to views
     */
    public function getParamsToView():array;

    /**
     * Gets views directory
     * TODO: return string | array
     */
    public function getViewsDir();

    /**
     * Renders a partial view
     */
    public function partial(string $partialPath, $params = null);

    /**
     * Externally sets the view content
     */
    public function setContent(string $content);

    /**
     * Adds parameters to views (alias of setVar)
     */
    public function setParamToView(string $key, $value);

    /**
     * Adds parameters to views
     */
    public function setVar(string $key, $value);

    /**
     * Sets views directory. Depending of your platform, always add a trailing
     * slash or backslash
     */
    public function setViewsDir(string $viewsDir);

}
