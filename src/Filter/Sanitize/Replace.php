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

namespace Phalcon\Filter\Sanitize;

use Phalcon\Contracts\Filter\Sanitizer;
use Phalcon\Support\Traits\WeakTypesTrait;

use function is_array;
use function is_string;
use function trigger_error;

use const E_USER_WARNING;

/**
 * Sanitizes a value replacing parts of a string
 */
class Replace implements Sanitizer
{
    use WeakTypesTrait;

    /**
     * @param string|string[] $from
     * @param string|string[] $to
     *
     * @return string|string[]|null
     */
    public function __invoke(mixed $input, mixed $from, mixed $to)
    {
        /**
         * The str_replace() of cphalcon gives a warning and null for a value
         * that is not a string or an array
         */
        if (!is_string($input) && !is_array($input)) {
            trigger_error('Invalid arguments supplied for str_replace()', E_USER_WARNING);

            return null;
        }

        /** @var string|string[] */
        return self::weakCallArray('str_replace', [$from, $to, $input]);
    }
}
