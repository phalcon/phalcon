<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Support\Models;

use Phalcon\Mvc\Model\MetaData;

/**
 * Model with manual metadata and a column map that does not have the not
 * null column `inv_title`.
 */
class InvoicesMetaDataColumnMap extends InvoicesMetaData
{
    public $cst_id;

    public $id;

    public $status_flag;

    public function columnMap()
    {
        return [
            'inv_id'          => 'id',
            'inv_cst_id'      => 'cst_id',
            'inv_status_flag' => 'status_flag',
        ];
    }

    public function metaData()
    {
        $metaData = parent::metaData();

        $metaData[MetaData::MODELS_DEFAULT_VALUES]      = [];
        $metaData[MetaData::MODELS_EMPTY_STRING_VALUES] = [];

        return $metaData;
    }
}
