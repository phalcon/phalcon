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

namespace Phalcon\Mvc\View\Engine;

use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Mvc\View\ViewBaseInterface;

/**
 * All the template engine adapters must inherit this class. This provides
 * basic interfacing between the engine and the Phalcon\Mvc\View component.
 */
abstract class AbstractEngine extends Injectable implements EngineInterface, EventsAwareInterface
{
    use EventsAwareTrait;

    /**
     * Phalcon\Mvc\View\Engine constructor
     *
     * @param ViewBaseInterface $view
     * @param DiInterface|null  $container
     */
    public function __construct(
        protected ViewBaseInterface $view,
        DiInterface | null $container = null
    ) {
        $this->container = $container;
    }

    /**
     * Returns cached output on another view stage
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->view->getContent();
    }

    /**
     * Returns the view component related to the adapter
     *
     * @return ViewBaseInterface
     */
    public function getView(): ViewBaseInterface
    {
        return $this->view;
    }

    /**
     * Renders a partial inside another view
     *
     * @param string     $partialPath
     * @param mixed|null $params
     *
     * @return void
     */
    public function partial(
        string $partialPath,
        mixed $params = null
    ): void {
        // TODO: Make params array
        $this->view->partial($partialPath, $params);
    }
}
