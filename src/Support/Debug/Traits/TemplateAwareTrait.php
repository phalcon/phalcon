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

namespace Phalcon\Support\Debug\Traits;

/**
 * Shared store for named, overridable template strings. A using class supplies
 * the embedded defaults via defaultTemplate().
 *
 * Note: this trait has no Zephir equivalent; the cphalcon mirror duplicates
 * these members in each class until Zephir supports traits.
 */
trait TemplateAwareTrait
{
    /**
     * @var array
     */
    protected array $templates = [];

    /**
     * @param string $name
     *
     * @return string
     */
    public function getTemplate(string $name): string
    {
        return $this->templates[$name] ?? $this->defaultTemplate($name);
    }

    /**
     * @param string $name
     * @param string $template
     *
     * @return static
     */
    public function setTemplate(string $name, string $template): static
    {
        $this->templates[$name] = $template;

        return $this;
    }

    /**
     * Returns the embedded default template for the given name.
     *
     * @param string $name
     *
     * @return string
     */
    abstract protected function defaultTemplate(string $name): string;
}
