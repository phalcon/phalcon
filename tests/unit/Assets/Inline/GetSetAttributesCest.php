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

namespace Phalcon\Tests\Unit\Assets\Inline;

use Codeception\Example;
use Phalcon\Assets\Inline;
use UnitTester;

/**
 * Class GetSetAttributesCest
 *
 * @package Phalcon\Tests\Unit\Assets\Inline
 */
class GetSetAttributesCest
{
    /**
     * Tests Phalcon\Assets\Inline :: getAttributes()/setAttributes()
     *
     * @dataProvider provider
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function assetsInlineGetSetAttributes(UnitTester $I, Example $example)
    {
        $I->wantToTest('Assets\Inline - getAttributes()/setAttributes()');

        $asset    = new Inline($example['type'], $example['content']);
        $expected = [
            'data-key' => 'phalcon',
        ];

        $asset->setAttributes($expected);
        $actual = $asset->getAttributes();

        $I->assertSame($expected, $actual);
    }

    /**
     * @return string[][]
     */
    protected function provider(): array
    {
        return [
            [
                'type'    => 'css',
                'content' => 'p {color: #000099}',
            ],
            [
                'type'    => 'js',
                'content' => '<script>alert("Hello");</script>',
            ],
        ];
    }
}
