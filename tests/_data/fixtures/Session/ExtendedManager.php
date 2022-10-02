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

namespace Phalcon\Tests\Fixtures\Session;

use Phalcon\Session\Adapter\Noop;
use Phalcon\Session\Manager;

class ExtendedManager extends Manager
{
    public function __construct(array $options = [])
    {
        parent::__construct();

        $sessionAdapter = new Noop();

        $this->setAdapter($sessionAdapter);
    }
}
