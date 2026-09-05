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
    protected string $content = "";

    /**
     * @phpstan-var array<string, mixed>
     */
    protected array $registeredEngines = [];

    /**
     * @phpstan-var array<string, mixed>
     */
    protected array $viewParams = [];

    /**
     * Returns output from another view stage
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Returns parameters to views
     *
     * @phpstan-return array<string, mixed>
     */
    public function getParamsToView(): array
    {
        return $this->viewParams;
    }

    /**
     * @phpstan-return array<string, mixed>
     */
    public function getRegisteredEngines(): array
    {
        return $this->registeredEngines;
    }

    /**
     * Returns a parameter previously set in the view
     *
     * @return mixed
     */
    public function getVar(string $key)
    {
        return $this->viewParams[$key] ?? null;
    }

    /**
     * Externally sets the view content
     *
     *```php
     * $this->view->setContent("<h1>hello</h1>");
     *```
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
     */
    public function setVar(string $key, mixed $value): static
    {
        $this->viewParams[$key] = $value;

        return $this;
    }
}
