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

namespace Phalcon\Tests\Controllers\Micro\Collections;

class CustomersController
{
    protected int $entered = 0;

    /**
     * @return void
     */
    public function index()
    {
        $this->entered++;
    }

    /**
     * @param int $number
     *
     * @return void
     */
    public function edit(int $number)
    {
        $this->entered += $number;
    }

    /**
     * @return int
     */
    public function getEntered(): int
    {
        return $this->entered;
    }
}
