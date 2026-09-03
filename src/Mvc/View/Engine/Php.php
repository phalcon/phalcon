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

use function extract;
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
         * closure is bound to $this, so templates keep using it.
         */
        $include = function (string $__path, mixed $__params): void {
            if (is_array($__params)) {
                extract($__params, EXTR_SKIP);
            }

            require $__path;
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
