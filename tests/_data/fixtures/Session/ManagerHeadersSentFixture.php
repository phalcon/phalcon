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

use Phalcon\Session\Manager;

class ManagerHeadersSentFixture extends Manager
{
    /**
     * Checks if or where headers have been sent
     *
     * @return bool
     *
     * @link https://php.net/manual/en/function.headers-sent.php
     */
    protected function phpHeadersSent(): bool
    {
        return true;
    }
}
