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

namespace Phalcon\Cli\Console\Exceptions;

use Phalcon\Cli\Console\Exception;

class InvalidModuleDefinition extends Exception
{
    public function __construct(?string $name = null, ?string $reason = null)
    {
        $message = 'Invalid module definition';

        if (null !== $name) {
            $message .= " for module '" . $name . "'";
        }

        if (null !== $reason) {
            $message .= ': ' . $reason;
        }

        parent::__construct($message);
    }
}
