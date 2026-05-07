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

namespace Phalcon\Auth\Access;

use Phalcon\Contracts\Auth\Access\Access;
use Phalcon\Contracts\Auth\Manager;

/**
 * @phpstan-import-type ForwardTarget from Access
 */
abstract class AbstractAccess implements Access
{
    /**
     * @var list<string>
     */
    protected array $exceptActions = [];

    /**
     * @var list<string>
     */
    protected array $onlyActions = [];

    public function __construct(
        protected Manager $manager
    ) {
    }

    /**
     * @return list<string>
     */
    public function getExceptActions(): array
    {
        return $this->exceptActions;
    }

    /**
     * @return list<string>
     */
    public function getOnlyActions(): array
    {
        return $this->onlyActions;
    }

    public function isAllowed(string $actionName): bool
    {
        $allowed = $this->allowedIf();

        if (!empty($this->exceptActions)) {
            return $allowed || in_array($actionName, $this->exceptActions, true);
        }

        if (!empty($this->onlyActions)) {
            return $allowed && in_array($actionName, $this->onlyActions, true);
        }

        return $allowed;
    }

    /**
     * @phpstan-return ForwardTarget|null
     */
    public function redirectTo(): ?array
    {
        return null;
    }

    /**
     * @param list<string> $exceptActions
     */
    public function setExceptActions(array $exceptActions = []): void
    {
        $this->exceptActions = $exceptActions;
    }

    /**
     * @param list<string> $onlyActions
     */
    public function setOnlyActions(array $onlyActions = []): void
    {
        $this->onlyActions = $onlyActions;
    }
}
