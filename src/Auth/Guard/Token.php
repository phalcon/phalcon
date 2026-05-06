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

namespace Phalcon\Auth\Guard;

use Phalcon\Auth\Guard\Config\TokenGuardConfig;
use Phalcon\Contracts\Auth\Adapter\Adapter;
use Phalcon\Contracts\Auth\AuthUser;
use Phalcon\Http\RequestInterface;

/**
 * @phpstan-import-type AuthCredentials from Adapter
 *
 * @extends AbstractGuard<TokenGuardConfig>
 */
class Token extends AbstractGuard
{
    public function __construct(
        Adapter $adapter,
        protected RequestInterface $request,
        TokenGuardConfig $config,
    ) {
        parent::__construct($adapter, $config);
    }

    public function getTokenForRequest(): ?string
    {
        $token = $this->request->get($this->config->getInputKey(), null, null);

        if (empty($token)) {
            $header = (string) $this->request->getHeader('Authorization');
            if ($header !== '' && str_starts_with($header, 'Bearer ')) {
                $token = mb_substr($header, 7, null, 'UTF-8');
            }
        }

        if (empty($token)) {
            return null;
        }

        return (string) $token;
    }

    public function setRequest(RequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    public function user(): ?AuthUser
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $token = $this->getTokenForRequest();
        if ($token === null) {
            return null;
        }

        $found = $this->adapter->retrieveByCredentials([
            $this->config->getStorageKey() => $token,
        ]);

        if ($found !== null) {
            $this->user = $found;
        }

        return $this->user;
    }

    /**
     * @phpstan-param AuthCredentials $credentials
     */
    public function validate(array $credentials = []): bool
    {
        $inputKey = $this->config->getInputKey();

        if (!isset($credentials[$inputKey])) {
            return false;
        }

        $token = $credentials[$inputKey];

        return $this->adapter->retrieveByCredentials([
            $this->config->getStorageKey() => $token,
        ]) !== null;
    }
}
