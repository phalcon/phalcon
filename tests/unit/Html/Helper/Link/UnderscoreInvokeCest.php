<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Html\Helper\Link;

use Codeception\Example;
use Phalcon\Html\Escaper;
use Phalcon\Html\Helper\Link;
use Phalcon\Html\TagFactory;
use UnitTester;

use const PHP_EOL;

class UnderscoreInvokeCest
{
    /**
     * Tests Phalcon\Html\Helper\Link :: __invoke()
     *
     * @dataProvider getExamples
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function htmlHelperLinkUnderscoreInvoke(UnitTester $I, Example $example)
    {
        $I->wantToTest('Html\Helper\Link - __invoke() ' . $example['message']);

        $escaper = new Escaper();
        $helper  = new Link($escaper);

        $result = $helper($example['indent'], $example['delimiter']);
        foreach ($example['add'] as $rel => $url) {
            $result->add($rel, $url);
        }

        $expected = $example['result'];
        $actual   = (string) $result;
        $I->assertEquals($expected, $actual);

        $factory = new TagFactory($escaper);
        $locator = $factory->newInstance('link');
        $result  = $locator($example['indent'], $example['delimiter']);

        foreach ($example['add'] as $rel => $url) {
            $result->add($rel, $url);
        }

        $actual = (string) $result;
        $I->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    private function getExamples(): array
    {
        return [
            [
                'message'   => 'base',
                'add'       => [
                    'prev' => 'https://phalcon.io/page/1',
                    'next' => 'https://phalcon.io/page/2',
                ],
                'indent'    => '    ',
                'delimiter' => PHP_EOL,
                'result'    => "    <link rel=\"prev\" href=\"https://phalcon.io/page/1\" />" . PHP_EOL
                    . "    <link rel=\"next\" href=\"https://phalcon.io/page/2\" />" . PHP_EOL,
            ],
            [
                'message'   => 'image and delimiter',
                'add'       => [
                    'prev' => 'https://phalcon.io/page/1',
                    'next' => 'https://phalcon.io/page/2',
                ],
                'indent'    => '--',
                'delimiter' => '+',
                'result'    => "--<link rel=\"prev\" href=\"https://phalcon.io/page/1\" />+"
                    . "--<link rel=\"next\" href=\"https://phalcon.io/page/2\" />+",
            ],
        ];
    }
}
