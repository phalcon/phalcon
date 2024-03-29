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

namespace Phalcon\Tests\Unit\Assets\Asset\Css;

use Codeception\Example;
use Phalcon\Assets\Asset\Css;
use UnitTester;

/**
 * Class GetSetSourcePathCest
 *
 * @package Phalcon\Tests\Unit\Assets\Asset\Css
 */
class GetSetSourcePathCest
{
    /**
     * Tests Phalcon\Assets\Asset\Css :: getSourcePath()/setSourcePath()
     *
     * @dataProvider provider
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function assetsAssetCssGetSetSourcePath(UnitTester $I, Example $example)
    {
        $I->wantToTest('Assets\Asset - getSourcePath()/setSourcePath()');

        $asset    = new Css($example['path'], $example['local']);
        $expected = '/phalcon/path';

        $asset->setSourcePath($expected);
        $actual = $asset->getSourcePath();

        $I->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    protected function provider(): array
    {
        return [
            [
                'path'  => 'css/docs.css',
                'local' => true,
            ],
            [
                'path'  => 'https://phalcon.ld/css/docs.css',
                'local' => false,
            ],
        ];
    }
}
