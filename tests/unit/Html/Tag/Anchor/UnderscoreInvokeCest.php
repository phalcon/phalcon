<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Html\Helper\Anchor;

use Codeception\Example;
use Phalcon\Html\Escaper;
use Phalcon\Html\Exception;
use Phalcon\Html\Tag\Anchor;
use Phalcon\Html\TagFactory;
use UnitTester;

/**
 * Class UnderscoreInvokeCest
 *
 * @package Phalcon\Tests\Unit\Html\Helper\Anchor
 */
class UnderscoreInvokeCest
{
    /**
     * Tests Phalcon\Html\Tag\Anchor :: __invoke()
     *
     * @dataProvider getExamples
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function htmlHelperAnchorUnderscoreInvoke(UnitTester $I, Example $example)
    {
        $I->wantToTest('Html\Helper\Anchor - __invoke()');

        $escaper = new Escaper();
        $anchor  = new Anchor($escaper);

        $expected = $example[0];
        $actual   = $anchor('/myurl', 'click<>me', $example[1], $example[2]);
        $I->assertEquals($expected, $actual);

        $factory  = new TagFactory($escaper);
        $locator  = $factory->newInstance('a');
        $expected = $example[0];
        $actual   = $locator('/myurl', 'click<>me', $example[1], $example[2]);
        $I->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    private function getExamples(): array
    {
        return [
            [
                '<a href="/myurl">click&lt;&gt;me</a>',
                [],
                false,
            ],
            [
                '<a href="/myurl">click&lt;&gt;me</a>',
                [
                    'href' => '/somethingelse',
                ],
                false,
            ],
            [
                '<a href="/myurl" id="my-id" name="my-name">click&lt;&gt;me</a>',
                [
                    'id'   => 'my-id',
                    'name' => 'my-name',
                ],
                false,
            ],
            [
                '<a href="/myurl" id="my-id" name="my-name" class="my-class">click&lt;&gt;me</a>',
                [
                    'class' => 'my-class',
                    'name'  => 'my-name',
                    'id'    => 'my-id',
                ],
                false,
            ],
            [
                '<a href="/myurl">click<>me</a>',
                [],
                true,
            ],
            [
                '<a href="/myurl">click<>me</a>',
                [
                    'href' => '/somethingelse',
                ],
                true,
            ],
            [
                '<a href="/myurl" id="my-id" name="my-name">click<>me</a>',
                [
                    'id'   => 'my-id',
                    'name' => 'my-name',
                ],
                true,
            ],
            [
                '<a href="/myurl" id="my-id" name="my-name" class="my-class">click<>me</a>',
                [
                    'class' => 'my-class',
                    'name'  => 'my-name',
                    'id'    => 'my-id',
                ],
                true,
            ],
        ];
    }
}
