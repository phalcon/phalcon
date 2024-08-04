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

namespace Phalcon\Tests\Unit\Support\Helper\Str;

use Codeception\Example;
use Phalcon\Support\Helper\Str\Random;
use Phalcon\Tests\UnitTestCase;

use function strlen;

final class RandomTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Str :: random() - constants
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportHelperStrRandomConstants(): void
    {
        $this->assertSame(0, Random::RANDOM_ALNUM);
        $this->assertSame(1, Random::RANDOM_ALPHA);
        $this->assertSame(2, Random::RANDOM_HEXDEC);
        $this->assertSame(3, Random::RANDOM_NUMERIC);
        $this->assertSame(4, Random::RANDOM_NOZERO);
        $this->assertSame(5, Random::RANDOM_DISTINCT);
    }

    /**
     * Tests Phalcon\Support\Helper\Str :: random() - alnum
     *
     * @dataProvider oneToTenProvider
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSupportHelperStrRandomAlnum(
        int $i
    ): void {
        $object = new Random();
        $source = $object(Random::RANDOM_ALNUM, $i);

        $this->assertSame(
            1,
            preg_match('/[a-zA-Z0-9]+/', $source, $matches)
        );

        $this->assertSame($source, $matches[0]);
        $this->assertSame($i, strlen($source));
    }

    /**
     * Tests Phalcon\Support\Helper\Str :: random() - alpha
     *
     * @dataProvider oneToTenProvider
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSupportHelperStrRandomAlpha(
        int $i
    ): void {
        $object = new Random();
        $source = $object(Random::RANDOM_ALPHA, $i);

        $this->assertSame(
            1,
            preg_match('/[a-zA-Z]+/', $source, $matches)
        );

        $this->assertSame($source, $matches[0]);
        $this->assertSame($i, strlen($source));
    }

    /**
     * Tests Phalcon\Support\Helper\Str :: random() - hexdec
     *
     * @dataProvider oneToTenProvider
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSupportHelperStrRandomHexDec(
        int $i
    ): void {
        $object = new Random();
        $source = $object(Random::RANDOM_HEXDEC, $i);

        $this->assertSame(
            1,
            preg_match('/[a-f0-9]+/', $source, $matches)
        );

        $this->assertSame($source, $matches[0]);
        $this->assertSame($i, strlen($source));
    }

    /**
     * Tests Phalcon\Support\Helper\Str :: random() - numeric
     *
     * @dataProvider oneToTenProvider
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSupportHelperStrRandomNumeric(
        int $i
    ): void {
        $object = new Random();
        $source = $object(Random::RANDOM_NUMERIC, $i);

        $this->assertSame(
            1,
            preg_match('/[0-9]+/', $source, $matches)
        );

        $this->assertSame($source, $matches[0]);
        $this->assertSame($i, strlen($source));
    }

    /**
     * Tests Phalcon\Support\Helper\Str :: random() - non zero
     *
     * @dataProvider oneToTenProvider
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSupportHelperStrRandomNonZero(
        int $i
    ): void {
        $object = new Random();
        $source = $object(Random::RANDOM_NOZERO, $i);

        $this->assertSame(
            1,
            preg_match('/[1-9]+/', $source, $matches)
        );

        $this->assertSame($source, $matches[0]);
        $this->assertSame($i, strlen($source));
    }

    /**
     * Tests Phalcon\Support\Helper\Str :: random() - distinct type
     *
     * @dataProvider randomDistinctProvider
     *
     * @return void
     * @param Example    $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSupportHelperStrRandomDistinct(
        int $i
    ): void {
        $object = new Random();
        $source = $object(Random::RANDOM_DISTINCT, $i);

        $this->assertMatchesRegularExpression(
            '#^[2345679ACDEFHJKLMNPRSTUVWXYZ]+$#',
            $source
        );

        $this->assertSame($i, strlen($source));
    }

    /**
     * @return int[][]
     */
    public static function oneToTenProvider(): array
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
     * @return int[][]
     */
    public static function randomDistinctProvider(): array
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
