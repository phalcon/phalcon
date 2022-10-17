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

namespace Phalcon\Mvc\Model\Binder;

/**
 * Interface for bindable classes
 */
interface BindableInterface
{
    /**
     * Return the model name or models names and parameters keys associated with
     * this class
     */
    public function getModelName(): array | string;
}
