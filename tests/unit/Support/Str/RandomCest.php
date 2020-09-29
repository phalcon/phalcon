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

namespace Phalcon\Tests\Unit\Support\Str;

use Codeception\Example;
use Phalcon\Support\Str\Random;
use UnitTester;

use function strlen;

class RandomCest
{
    /**
     * Tests Phalcon\Support\Str :: random() - constants
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrRandomConstants(UnitTester $I)
    {
        $I->wantToTest('Support\Str - random() - constants');

        $I->assertEquals(0, Random::RANDOM_ALNUM);
        $I->assertEquals(1, Random::RANDOM_ALPHA);
        $I->assertEquals(2, Random::RANDOM_HEXDEC);
        $I->assertEquals(3, Random::RANDOM_NUMERIC);
        $I->assertEquals(4, Random::RANDOM_NOZERO);
        $I->assertEquals(5, Random::RANDOM_DISTINCT);
    }

    /**
     * Tests Phalcon\Support\Str :: random() - alnum
     *
     * @dataProvider oneToTenProvider
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrRandomAlnum(UnitTester $I, Example $example)
    {
        $I->wantToTest('Support\Str - random() - alnum');

        $object = new Random();
        $i      = $example[0];
        $source = $object(Random::RANDOM_ALNUM, $i);

        $I->assertEquals(
            1,
            preg_match('/[a-zA-Z0-9]+/', $source, $matches)
        );

        $I->assertEquals($source, $matches[0]);
        $I->assertEquals($i, strlen($source));
    }

    /**
     * Tests Phalcon\Support\Str :: random() - alpha
     *
     * @dataProvider oneToTenProvider
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrRandomAlpha(UnitTester $I, Example $example)
    {
        $I->wantToTest('Support\Str - random() - alpha');

        $object = new Random();
        $i      = $example[0];
        $source = $object(Random::RANDOM_ALPHA, $i);

        $I->assertEquals(
            1,
            preg_match('/[a-zA-Z]+/', $source, $matches)
        );

        $I->assertEquals($source, $matches[0]);
        $I->assertEquals($i, strlen($source));
    }

    /**
     * Tests Phalcon\Support\Str :: random() - hexdec
     *
     * @dataProvider oneToTenProvider
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrRandomHexDec(UnitTester $I, Example $example)
    {
        $I->wantToTest('Support\Str - random() - hexdex');

        $object = new Random();
        $i      = $example[0];
        $source = $object(Random::RANDOM_HEXDEC, $i);

        $I->assertEquals(
            1,
            preg_match('/[a-f0-9]+/', $source, $matches)
        );

        $I->assertEquals($source, $matches[0]);
        $I->assertEquals($i, strlen($source));
    }

    /**
     * Tests Phalcon\Support\Str :: random() - numeric
     *
     * @dataProvider oneToTenProvider
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrRandomNumeric(UnitTester $I, Example $example)
    {
        $I->wantToTest('Support\Str - random() - numeric');

        $object = new Random();
        $i      = $example[0];
        $source = $object(Random::RANDOM_NUMERIC, $i);

        $I->assertEquals(
            1,
            preg_match('/[0-9]+/', $source, $matches)
        );

        $I->assertEquals($source, $matches[0]);
        $I->assertEquals($i, strlen($source));
    }

    /**
     * Tests Phalcon\Support\Str :: random() - non zero
     *
     * @dataProvider oneToTenProvider
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrRandomNonZero(UnitTester $I, Example $example)
    {
        $I->wantToTest('Support\Str - random() - non zero');

        $object = new Random();
        $i      = $example[0];
        $source = $object(Random::RANDOM_NOZERO, $i);

        $I->assertEquals(
            1,
            preg_match('/[1-9]+/', $source, $matches)
        );

        $I->assertEquals($source, $matches[0]);
        $I->assertEquals($i, strlen($source));
    }

    /**
     * Tests Phalcon\Support\Str :: random() - distinct type
     *
     * @dataProvider supportStrRandomDistinctProvider
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrRandomDistinct(UnitTester $I, Example $example)
    {
        $I->wantToTest('Support\Str - random() - distinct');

        $object = new Random();
        $i      = $example[0];
        $source = $object(Random::RANDOM_DISTINCT, $i);

        $I->assertRegExp(
            '#^[2345679ACDEFHJKLMNPRSTUVWXYZ]+$#',
            $source
        );

        $I->assertEquals($i, strlen($source));
    }

    /**
     * @return \int[][]
     */
    private function oneToTenProvider(): array
    {
        return [
            [1],
            [2],
            [3],
            [4],
            [5],
            [6],
            [7],
            [8],
            [9],
            [10],
        ];
    }

    /**
     * @return \int[][]
     */
    private function supportStrRandomDistinctProvider(): array
    {
        return [
            [1],
            [10],
            [100],
            [200],
            [500],
            [1000],
            [2000],
            [3000],
            [4000],
            [5000],
        ];
    }
}
