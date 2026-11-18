<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Mvc\View\Engine;

use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Mvc\ViewBaseInterface;

/**
 * All the template engine adapters must inherit this class. This provides
 * basic interfacing between the engine and the Phalcon\Mvc\View component.
 */
abstract class AbstractEngine extends Injectable implements EngineInterface, EventsAwareInterface
{
    use EventsAwareTrait;

    /**
     * Variables that all the templates of this engine share, the same as
     * the shared symbol table in cphalcon
     *
     * @phpstan-var array<string, mixed>
     */
    protected array $templateScope = [];

    /**
     * Phalcon\Mvc\View\Engine constructor
     */
    public function __construct(
        protected ViewBaseInterface $view,
        DiInterface | null $container = null
    ) {
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
     */
    public function partial(
        string $partialPath,
        mixed $params = null
    ): void {
        // TODO: Make params array
        $this->view->partial($partialPath, $params);
    }

    /**
     * Adds the variables in $scope (the variables of the calling template) to
     * the variables that the templates share, so that the next partial can
     * use them. Returns the engine, so that a call to partial() can follow:
     *
     * ```php
     * $this->withScope(get_defined_vars())->partial('footer');
     * ```
     *
     * @phpstan-param array<string, mixed> $scope
     */
    public function withScope(array $scope): static
    {
        unset($scope['__path'], $scope['__params']);

        $this->templateScope = $scope + $this->templateScope;

        return $this;
    }
}
