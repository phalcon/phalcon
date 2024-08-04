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

namespace Phalcon\Tests\Fixtures\Translate\Adapter;

use Phalcon\Translate\Adapter\Gettext;

class GettextFileExistsFixture extends Gettext
{
    /**
     * Return true if the given function has been defined
     *
     * @param string $function
     *
     * @return bool
     *
     * @link https://php.net/manual/en/function.function-exists.php
     */
    protected function phpFunctionExists(string $function)
    {
        return false;
    }
}
