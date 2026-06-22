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
use Phalcon\Auth\Exceptions\ConfigRequiresNonEmptyValue;

class ModelAdapterConfig extends AbstractAdapterConfig
{
    /**
     * @throws Exception
     */
    public function __construct(
        string $model,
        protected readonly string $idColumn = 'id',
    ) {
        ConfigRequiresNonEmptyValue::assert($model, 'Model adapter', 'model', ' class name');
        ConfigRequiresNonEmptyValue::assert($idColumn, 'Model adapter', 'idColumn');

        parent::__construct($model);
    }

    public function getIdColumn(): string
    {
        return $this->idColumn;
    }

    public function getModel(): string
    {
        /** @var string */
        return $this->model;
    }
}
