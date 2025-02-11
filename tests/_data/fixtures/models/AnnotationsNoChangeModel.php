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

namespace Phalcon\Tests\Fixtures\models;

use Phalcon\Annotations\Models\MetaData\Column;
use Phalcon\Annotations\Models\MetaData\Identity;
use Phalcon\Annotations\Models\MetaData\Primary;
use Phalcon\Mvc\Model;

class AnnotationsNoChangeModel extends Model
{
    #[Primary]
    #[Identity]
    #[Column(column: 'inv_id', type: 'integer', nullable: false)]
    public int $inv_id;

    #[Column(type: 'integer', nullable: false)]
    public int $inv_cst_id;

    #[Column(type: 'integer', nullable: false, default: 0)]
    public int $inv_status_flag;

    #[Column(type: 'string', length: 70, nullable: false)]
    public string $inv_title;

    #[Column(type: 'float', nullable: false)]
    public float $inv_total;

    #[Column(
        type: 'datetime',
        nullable: false,
        skipOnInsert: true,
        skipOnUpdate: true,
        allowEmptyString: true
    )]
    public string $inv_created_at;

    #[Column(type: 'integer', nullable: false, skipOnInsert: true, skipOnUpdate: true)]
    public int $inv_created_by;

    #[Column(type: 'datetime', nullable: false, skipOnInsert: true, skipOnUpdate: true, allowEmptyString: true)]
    public string $inv_updated_at;

    #[Column(type: 'integer', nullable: false, skipOnInsert: true, skipOnUpdate: true)]
    public int $inv_updated_by;
}
