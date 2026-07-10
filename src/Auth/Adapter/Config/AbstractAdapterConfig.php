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

namespace Phalcon\Auth\Adapter\Config;

use Phalcon\Auth\Adapter\Config\Traits\ModelConfigTrait;
use Phalcon\Contracts\Auth\Adapter\AdapterConfig;

abstract class AbstractAdapterConfig implements AdapterConfig
{
    use ModelConfigTrait;

    public function __construct(?string $model = null)
    {
        $this->model = $model;
    }
}
