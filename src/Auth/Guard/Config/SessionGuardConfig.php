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
use Phalcon\Auth\Exceptions\ConfigRequiresNonEmptyValue;

/**
 * Configuration for the Session guard. Holds the names under which the
 * session key and remember-me cookie are stored. Defaults to 'auth' and
 * 'remember'; multi-guard apps can pass a $suffix ('web', 'admin', ...)
 * to derive 'auth_web' / 'remember_web' style names, or override either
 * full name explicitly.
 */
class SessionGuardConfig extends AbstractGuardConfig
{
    private readonly string $name;

    private readonly string $rememberName;

    /**
     * @throws Exception
     */
    public function __construct(
        ?string $suffix = null,
        ?string $name = null,
        ?string $rememberName = null,
    ) {
        $this->validateNonEmpty('suffix', $suffix);
        $this->validateNonEmpty('name', $name);
        $this->validateNonEmpty('rememberName', $rememberName);

        $this->name         = $name ?? $this->derive('auth', $suffix);
        $this->rememberName = $rememberName ?? $this->derive('remember', $suffix);

        if ($this->name === $this->rememberName) {
            throw new Exception(
                "Session guard 'name' and 'rememberName' must differ"
            );
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRememberName(): string
    {
        return $this->rememberName;
    }

    private function derive(string $prefix, ?string $suffix): string
    {
        return $suffix === null ? $prefix : ($prefix . '_' . $suffix);
    }

    /**
     * @throws Exception
     */
    private function validateNonEmpty(string $param, ?string $value): void
    {
        if ($value === null) {
            return;
        }

        if ($value === '') {
            throw new ConfigRequiresNonEmptyValue('Session guard', $param);
        }
    }
}
