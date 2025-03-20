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

namespace Phalcon\Acl\Adapter;

use Phalcon\Acl\Enum;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Traits\EventsAwareTrait;

/**
 * Functionality common to all adapters
 *
 * @property string|null $activeAccess
 * @property string|null $activeComponent
 * @property string|null $activeRole
 * @property bool        $accessGranted
 * @property int         $defaultAccess
 */
abstract class AbstractAdapter implements AdapterInterface, EventsAwareInterface
{
    use EventsAwareTrait;

    /**
     * Access Granted
     *
     * @var bool
     */
    protected bool $accessGranted = false;

    /**
     * Active access which the list is checking if some role can access it
     *
     * @var string|null
     */
    protected string | null $activeAccess = null;

    /**
     * Component which the list is checking if some role can access it
     *
     * @var string|null
     */
    protected string | null $activeComponent = null;

    /**
     * Role which the list is checking if it's allowed to certain
     * component/access
     *
     * @var string|null
     */
    protected string | null $activeRole = null;

    /**
     * Default access
     *
     * @var int
     */
    protected int $defaultAccess = Enum::DENY;

    /**
     * Returns the access which the list is checking if a role can access it
     *
     * @return string|null
     */
    public function getActiveAccess(): string | null
    {
        return $this->activeAccess;
    }

    /**
     * Returns the component which the list is checking if some role can access
     * it
     *
     * @return string|null
     */
    public function getActiveComponent(): string | null
    {
        return $this->activeComponent;
    }

    /**
     * Returns the role which the list is checking if 's allowed to certain
     * component/access
     *
     * @return string|null
     */
    public function getActiveRole(): string | null
    {
        return $this->activeRole;
    }

    /**
     * Returns the default action
     *
     * @return int
     */
    public function getDefaultAction(): int
    {
        return $this->defaultAccess;
    }

    /**
     * Sets the default access level
     * (Phalcon\Acl\Enum::ALLOW or Phalcon\Acl\Enum::DENY)
     *
     * @param int $defaultAccess
     */
    public function setDefaultAction(int $defaultAccess): void
    {
        $this->defaultAccess = $defaultAccess;
    }
}
