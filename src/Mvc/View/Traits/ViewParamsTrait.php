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

namespace Phalcon\Mvc\View\Traits;

/**
 * Shared view parameter and content accessors
 *
 * @todo v7 - inspect the View/Simple interfaces (ViewInterface vs
 *       ViewBaseInterface) to see whether these accessors can be unified behind
 *       a shared contract
 */
trait ViewParamsTrait
{
    /**
     * @var string
     */
    protected string $content = "";

    /**
     * @var array
     */
    protected array $registeredEngines = [];

    /**
     * @var array
     */
    protected array $viewParams = [];

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
     * @param string $key
     *
     * @return mixed
     */
    public function getVar(string $key): mixed
    {
        return $this->viewParams[$key] ?? null;
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
     * @return static
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
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
     * @return static
     */
    public function setVar(string $key, mixed $value): static
    {
        $this->viewParams[$key] = $value;

        return $this;
    }
}
