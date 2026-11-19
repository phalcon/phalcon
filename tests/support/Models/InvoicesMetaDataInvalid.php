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

use Phalcon\Mvc\Model;

/**
 * Model with a metaData() method that does not return an array.
 */
class InvoicesMetaDataInvalid extends Model
{
    public function initialize()
    {
        $this->setSource('co_invoices');
    }

    public function metaData()
    {
        return 'invalid';
    }
}
