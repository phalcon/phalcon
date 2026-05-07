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

namespace Phalcon\Contracts\Auth;

use Phalcon\Contracts\Auth\Access\Access;
use Phalcon\Contracts\Auth\Guard\Guard;

interface Manager
{
    public function access(string $accessName): self;

    /**
     * @param array<string, class-string<Access>> $accessList
     */
    public function addAccessList(array $accessList): self;

    public function addGuard(string $nameGuard, Guard $guard, bool $isDefault = false): self;

    /**
     * Restricts the active access gate to skip the listed action names.
     */
    public function except(string ...$actions): self;

    public function getAccess(): ?Access;

    /**
     * @return array<string, class-string<Access>>
     */
    public function getAccessList(): array;

    public function getDefaultGuard(): ?Guard;

    /**
     * @return array<string, Guard>
     */
    public function getGuards(): array;

    /**
     * Returns the named guard, or the default guard when $name is null.
     */
    public function guard(?string $name = null): Guard;

    /**
     * Restricts the active access gate to apply only to the listed action names.
     */
    public function only(string ...$actions): self;

    public function setAccess(Access $access): self;

    /**
     * @param array<string, class-string<Access>> $accessList
     */
    public function setAccessList(array $accessList): self;

    public function setDefaultGuard(Guard $guard): self;
}
