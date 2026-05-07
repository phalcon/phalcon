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

use Phalcon\Auth\Exception;

class ModelAdapterConfig extends AbstractAdapterConfig
{
    /**
     * @throws Exception
     */
    public function __construct(string $model)
    {
        if ($model === '') {
            throw Exception::configRequiresNonEmptyValue(
                'Model adapter',
                'model',
                ' class name'
            );
        }

        parent::__construct($model);
    }

    public function getModel(): string
    {
        /** @var string */
        return $this->model;
    }
}
