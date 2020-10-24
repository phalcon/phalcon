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

namespace Phalcon\Tests\Unit\Crypt;

use Phalcon\Crypt\Crypt;
use UnitTester;

/**
 * Class GetAvailableHashAlgosCest
 *
 * @package Phalcon\Tests\Unit\Crypt
 */
class GetAvailableHashAlgosCest
{
    /**
     * Tests Phalcon\Crypt\Crypt :: getAvailableHashAlgos()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cryptGetAvailableHashAlgos(UnitTester $I)
    {
        $I->wantToTest('Crypt - getAvailableHashAlgos()');

        $crypt = new Crypt();

        $I->assertTrue(is_array($crypt->getAvailableHashAlgos()));
    }
}
