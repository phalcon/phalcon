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

class SavePathUnavailable extends \Phalcon\Session\Exception
{
    public function __construct(string $path)
    {
        parent::__construct('The session save path [' . $path . '] is not writable');
    }
}
