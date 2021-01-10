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

namespace Phiz\Di;

use Phiz\Di\Traits\InjectionAwareTrait;

/**
 * This abstract class offers common access to the DI in a class
 *
 * Class AbstractInjectionAware
 *
 * @package Phiz\Di
 */
abstract class AbstractInjectionAware implements InjectionAwareInterface
{
    use InjectionAwareTrait;
}
