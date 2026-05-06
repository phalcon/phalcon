<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by sinbadxiii/cphalcon-auth
 * @link    https://github.com/sinbadxiii/cphalcon-auth
 */

declare(strict_types=1);

namespace Phalcon\Auth\Guard\Config;

use Phalcon\Auth\Exception;

class TokenGuardConfig extends AbstractGuardConfig
{
    /**
     * @throws Exception
     */
    public function __construct(
        protected readonly string $inputKey,
        protected readonly string $storageKey,
    ) {
        if ($inputKey === '') {
            throw new Exception(
                "Token guard requires a non-empty 'inputKey'"
            );
        }

        if ($storageKey === '') {
            throw new Exception(
                "Token guard requires a non-empty 'storageKey'"
            );
        }
    }

    public function getInputKey(): string
    {
        return $this->inputKey;
    }

    public function getStorageKey(): string
    {
        return $this->storageKey;
    }
}
