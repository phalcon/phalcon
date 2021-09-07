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

namespace Phalcon\Tests\Unit\Filter\Filter;

use Phalcon\Filter\FilterFactory;
use UnitTester;

use function restore_error_handler;
use function set_error_handler;

use const E_USER_NOTICE;

class SanitizeMultipleCest
{
    /**
     * Tests sanitizing null value
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function filterFilterSanitizeNullValue(UnitTester $I)
    {
        $locator = new FilterFactory();
        $filter  = $locator->newInstance();

        $value    = null;
        $expected = null;
        $actual   = $filter->sanitize($value, ['string', 'trim']);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests sanitizing string with filters
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function filterFilterSanitizeStringWithMultipleFilters(UnitTester $I)
    {
        $locator = new FilterFactory();
        $filter  = $locator->newInstance();

        $value    = '    lol<<<   ';
        $expected = 'lol';
        $actual   = $filter->sanitize($value, ['string', 'trim']);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests sanitizing array with filters
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function filterFilterSanitizeArray(UnitTester $I)
    {
        $locator = new FilterFactory();
        $filter  = $locator->newInstance();

        $value    = [' 1 ', '  2', '3  '];
        $expected = ['1', '2', '3'];
        $actual   = $filter->sanitize($value, 'trim');
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests sanitizing array with multiple filters
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function filterFilterSanitizeArrayWithMultipleFilters(UnitTester $I)
    {
        $locator = new FilterFactory();
        $filter  = $locator->newInstance();

        $value    = [' <a href="a">1</a> ', '  <h1>2</h1>', '<p>3</p>'];
        $expected = ['1', '2', '3'];
        $actual   = $filter->sanitize($value, ['trim', 'striptags']);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests sanitizing array with multiple filters and more parameters
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function filterFilterSanitizeWithMultipleFiltersMoreParameters(UnitTester $I)
    {
        $locator = new FilterFactory();
        $filter  = $locator->newInstance();

        $value    = '  mary had a little lamb ';
        $filters  = [
            'trim',
            'replace' => [' ', '-'],
            'remove'  => ['mary'],
        ];
        $expected = '-had-a-little-lamb';
        $actual   = $filter->sanitize($value, $filters);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests sanitizing array with multiple filters and one not existing
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function filterFilterSanitizeWithMultipleFiltersNotExisting(UnitTester $I)
    {
        $locator = new FilterFactory();
        $filter  = $locator->newInstance();

        $value   = '  mary had a little lamb ';
        $filters = [
            'trim',
            'something',
        ];

        $error = [];
        set_error_handler(
            function ($number, $message, $file, $line, $context) use (&$error) {
                $error = [
                    'number'  => $number,
                    'message' => $message,
                    'file'    => $file,
                    'line'    => $line,
                    'context' => $context,
                ];
            }
        );

        $actual = $filter->sanitize($value, $filters);
        restore_error_handler();

        $I->assertEquals(E_USER_NOTICE, $error['number']);
        $I->assertEquals(
            'Sanitizer "something" is not registered',
            $error['message']
        );
    }
}
