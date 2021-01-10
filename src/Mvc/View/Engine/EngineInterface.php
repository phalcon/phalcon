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

/**
 * Interface for Phiz\Mvc\View engine adapters
 */
interface EngineInterface
{
    /**
     * Returns cached output on another view stage
     */
    public function getContent(): string;

    /**
     * Renders a partial inside another view
     */
    public function partial(string $partialPath, $params = null): void;

    /**
     * Renders a view using the template engine
     */
    public function render(string $path, $params, bool $mustClean = false);
}
