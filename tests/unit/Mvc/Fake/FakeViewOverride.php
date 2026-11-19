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

namespace Phalcon\Tests\Unit\Mvc\Fake;

use Phalcon\Mvc\View;

/**
 * Overrides render() with the cphalcon signature
 */
class FakeViewOverride extends View
{
    public function render(string $controllerName, string $actionName, array $params = []): false | static
    {
        return parent::render($controllerName, $actionName, $params);
    }
}
