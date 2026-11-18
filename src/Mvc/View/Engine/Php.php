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

use function extract;
use function get_defined_vars;
use function is_array;
use function ob_clean;
use function ob_get_contents;

/**
 * Adapter to use PHP itself as templating engine
 */
class Php extends AbstractEngine
{
    /**
     * Renders a view using the template engine
     */
    public function render(
        string $path,
        mixed $params,
        bool $mustClean = false
    ) {
        if (true === $mustClean) {
            ob_clean();
        }

        /**
         * Include the template inside a closure whose only locals carry
         * reserved names, so a parameter cannot replace the file path. The
         * closure is bound to $this, so templates keep using it. The shared
         * scope goes in first and the variables of the template go back to
         * it, so the next templates can use them.
         */
        $include = function (string $__path, mixed $__params): void {
            extract(
                is_array($__params) ? $__params + $this->getScope() : $this->getScope(),
                EXTR_SKIP
            );

            require $__path;

            unset($__path, $__params);

            $this->setScope(get_defined_vars() + $this->getScope());
        };

        $include->call($this, $path, $params);

        if (true === $mustClean) {
            // The view starts the buffer before it calls the engine.
            /** @var string $contents */
            $contents = ob_get_contents();

            $this->view->setContent($contents);
        }
    }
}
