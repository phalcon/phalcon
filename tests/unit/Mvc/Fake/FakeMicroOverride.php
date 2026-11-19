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

use Phalcon\Mvc\Micro;

/**
 * Overrides setActiveHandler() with the cphalcon signature
 */
class FakeMicroOverride extends Micro
{
    public function setActiveHandler($activeHandler): self
    {
        parent::setActiveHandler($activeHandler);

        return $this;
    }
}
