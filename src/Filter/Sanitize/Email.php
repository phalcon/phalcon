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

namespace Phalcon\Filter\Sanitize;

use function filter_var;

use const FILTER_FLAG_EMAIL_UNICODE;
use const FILTER_SANITIZE_EMAIL;

/**
 * Phalcon\Filter\Sanitize\Email
 *
 * Sanitizes an email string
 */
class Email
{
    /**
     * @param mixed $input The text to sanitize
     *
     * @return string
     */
    public function __invoke(mixed $input): string
    {
        return (string) filter_var(
            $input,
            FILTER_SANITIZE_EMAIL,
            FILTER_FLAG_EMAIL_UNICODE
        );
    }
}
