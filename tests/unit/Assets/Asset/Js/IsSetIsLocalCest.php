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

namespace Phalcon\Tests\Unit\Assets\Asset\Js;

use Codeception\Example;
use Phalcon\Assets\Asset\Js;
use UnitTester;

/**
 * Class IsSetLocalCest
 *
 * @package Phalcon\Tests\Unit\Assets\Asset\Js
 */
class IsSetIsLocalCest
{
    /**
     * Tests Phalcon\Assets\Asset\Js :: isLocal()/setIsLocal()
     *
     * @dataProvider provider
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function assetsAssetJsSetLocal(UnitTester $I, Example $example)
    {
        $I->wantToTest('Assets\Asset\Js - isLocal()/setIsLocal()');

        $asset = new Js($example['path'], $example['local']);

        $asset->setIsLocal($example['newLocal']);
        $expected = $example['newLocal'];
        $actual   = $asset->isLocal();
        $I->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    protected function provider(): array
    {
        return [
            [
                'path'     => 'js/jquery.js',
                'local'    => true,
                'newLocal' => false,
            ],
            [
                'path'     => 'https://phalcon.ld/js/jquery.js',
                'local'    => false,
                'newLocal' => true,
            ],
        ];
    }
}
