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

namespace Phalcon\Auth\Adapter;

use Phalcon\Contracts\Auth\Adapter\Adapter;
use Phalcon\Contracts\Auth\Adapter\AdapterConfig;
use Phalcon\Contracts\Auth\AuthUser;
use Phalcon\Contracts\Encryption\Security\Security;

/**
 * @phpstan-import-type AuthCredentials from Adapter
 *
 * @template TConfig of AdapterConfig
 */
abstract class AbstractAdapter implements Adapter
{
    /**
     * @phpstan-param TConfig $config
     */
    public function __construct(
        protected Security $hasher,
        protected AdapterConfig $config,
    ) {
    }

    /**
     * Returns the adapter configuration object.
     *
     * @phpstan-return TConfig
     */
    public function getConfig(): AdapterConfig
    {
        return $this->config;
    }

    /**
     * Returns the model class name, if configured.
     */
    public function getModel(): ?string
    {
        return $this->config->getModel();
    }

    /**
     * Validates the supplied plaintext password against the user's stored hash.
     * Concrete adapters share this implementation; if your data source needs
     * a different verification strategy, override it.
     *
     * @phpstan-param AuthCredentials $credentials
     */
    public function validateCredentials(AuthUser $user, array $credentials): bool
    {
        $password = $credentials['password'] ?? null;

        if (!is_string($password)) {
            return false;
        }

        return $this->hasher->checkHash($password, $user->getAuthPassword());
    }
}
