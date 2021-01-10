<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Mvc\View\Engine;

use Phiz\Di\DiInterface;
use Phiz\Di\Injectable;
use Phiz\Mvc\ViewBaseInterface;

/**
 * All the template engine adapters must inherit this class. This provides
 * basic interfacing between the engine and the Phiz\Mvc\View component.
 */
abstract class AbstractEngine extends Injectable implements EngineInterface
{
    protected $view;

    /**
     * Phiz\Mvc\View\Engine constructor
     */
    public function __construct(ViewBaseInterface $view,  DiInterface $container = null)
    {
        $this->view = $view;
        $this->container = $container;
    }

    /**
     * Returns cached output on another view stage
     */
    public function getContent(): string
    {
        return $this->view->getContent();
    }

    /**
     * Returns the view component related to the adapter
     */
    public function getView(): ViewBaseInterface
    {
        return $this->view;
    }

    /**
     * Renders a partial inside another view
     *
     * @param array params
     */
    public function partial(string $partialPath, $params = null): void
    {
        $this->view->partial($partialPath, $params);
    }
}
