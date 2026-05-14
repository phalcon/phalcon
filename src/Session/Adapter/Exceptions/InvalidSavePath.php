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

namespace Phalcon\Session\Adapter\Exceptions;

class InvalidSavePath extends \Phalcon\Session\Exception
{
    public function __construct()
    {
        parent::__construct('The session save path cannot be empty');
    }
}
