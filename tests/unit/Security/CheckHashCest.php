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

namespace Phalcon\Tests\Unit\Security;

use Codeception\Example;
use Phalcon\Security\Security;
use UnitTester;

/**
 * Class CheckHashCest
 *
 * @package Phalcon\Tests\Unit\Security
 */
class CheckHashCest
{
    /**
     * Tests Phalcon\Security :: checkHash()
     *
     * @dataProvider getExamples
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function securityCheckHash(UnitTester $I, Example $example)
    {
        $I->wantToTest('Security - checkHash() ' . $example[0]);

        $security = new Security();
        $password = 'PhalconROCKS!';

        $security->setDefaultHash($example[1]);

        $actual = $security->checkHash($password, $security->hash($password));
        $I->assertTrue($actual);
    }

    /**
     * @return array[]
     */
    private function getExamples(): array
    {
        return [
            ['CRYPT_DEFAULT', Security::CRYPT_DEFAULT],
            ['CRYPT_BLOWFISH', Security::CRYPT_BLOWFISH],
            ['CRYPT_BLOWFISH_A', Security::CRYPT_BLOWFISH_A],
            ['CRYPT_BLOWFISH_X', Security::CRYPT_BLOWFISH_X],
            ['CRYPT_BLOWFISH_Y', Security::CRYPT_BLOWFISH_Y],
            ['CRYPT_SHA256', Security::CRYPT_SHA256],
            ['CRYPT_SHA512', Security::CRYPT_SHA512],
        ];
    }
}
