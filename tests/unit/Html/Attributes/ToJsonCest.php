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

namespace Phalcon\Tests\Unit\Html\Attributes;

use Phalcon\Html\Attributes;
use UnitTester;

class ToJsonCest
{
    /**
     * Tests Phalcon\Html\Attributes :: toJson()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-06-02
     */
    public function htmlAttributesToJson(UnitTester $I)
    {
        $I->wantToTest('Html\Attributes - toJson()');

        $data = [
            'type'  => 'text',
            'class' => 'form-control',
            'name'  => 'q',
            'value' => '',
        ];

        $attributes = new Attributes($data);

        $I->assertSame(
            json_encode($data),
            $attributes->toJson()
        );

        $I->assertSame(
            json_encode($data, JSON_PRETTY_PRINT),
            $attributes->toJson(JSON_PRETTY_PRINT)
        );
    }
}
