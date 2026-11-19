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

use Phalcon\Mvc\Router\Annotations;

/**
 * Overrides the setters with the cphalcon signatures
 */
class FakeRouterAnnotationsOverride extends Annotations
{
    public function setActionPreformatCallback($callback = null): self
    {
        parent::setActionPreformatCallback($callback);

        return $this;
    }

    public function setActionSuffix(string $actionSuffix): self
    {
        parent::setActionSuffix($actionSuffix);

        return $this;
    }

    public function setControllerSuffix(string $controllerSuffix): self
    {
        parent::setControllerSuffix($controllerSuffix);

        return $this;
    }
}
