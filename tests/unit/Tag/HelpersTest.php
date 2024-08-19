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

namespace Phalcon\Tests\Unit\Tag;

use Phalcon\Tag;
use PHPUnit\Framework\Attributes\Test;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class HelpersTest extends AbstractUnitTestCase
{
    use DiTrait;

    private int $doctype = Tag::HTML5;

    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                'color',
                'colorField',
            ],
            [
                'date',
                'dateField',
            ],
            [
                'datetime',
                'dateTimeField',
            ],
            [
                'datetime-local',
                'dateTimeLocalField',
            ],
            [
                'file',
                'fileField',
            ],
            [
                'hidden',
                'hiddenField',
            ],
            [
                'month',
                'monthField',
            ],
            [
                'number',
                'numericField',
            ],
            [
                'password',
                'passwordField',
            ],
            [
                'search',
                'searchField',
            ],
            [
                'tel',
                'telField',
            ],
            [
                'text',
                'textField',
            ],
            [
                'time',
                'timeField',
            ],
            [
                'url',
                'urlField',
            ],
            [
                'week',
                'weekField',
            ],
        ];
    }

    public function tearDown(): void
    {
        Tag::setDocType($this->doctype);
        Tag::resetInput();
    }

    public function setUp(): void
    {
        $this->newDi();
        $this->setDiService('escaper');
        $this->setDiService('url');

        Tag::setDI($this->container);
        Tag::resetInput();
    }

    /**
     * Tests Phalcon\Tag :: weekField() - array as a parameter
     *
     * @param string $type
     * @param string $method
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    #[DataProvider('getExamples')]
    public function testTagFieldArrayParameter(
        string $type,
        string $method
    ): void {
        $options = [
            'x_name',
            'class' => 'x_class',
        ];

        $expected = '<input type="' . $type . '" id="x_name" name="x_name" class="x_class"';

        $this->testFieldParameter($method, $options, $expected);
        $this->testFieldParameter($method, $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: weekField() - array as a parameters and id in it
     *
     * @param string $type
     * @param string $method
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    #[DataProvider('getExamples')]
    public function testTagFieldArrayParameterWithId(
        string $type,
        string $method
    ): void {
        $options = [
            'x_name',
            'id'    => 'x_id',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="' . $type . '" id="x_id" name="x_name" '
            . 'class="x_class" size="10"';

        $this->testFieldParameter($method, $options, $expected);
        $this->testFieldParameter($method, $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: weekField() - name and no id in parameter
     *
     * @param string $type
     * @param string $method
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    #[DataProvider('getExamples')]
    public function testTagFieldArrayParameterWithNameNoId(
        string $type,
        string $method
    ): void {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="' . $type . '" id="x_name" name="x_other" class="x_class" size="10"';

        $this->testFieldParameter($method, $options, $expected);
        $this->testFieldParameter($method, $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: weekField() - string as a parameter
     *
     * @param string $type
     * @param string $method
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    #[DataProvider('getExamples')]
    public function testTagFieldStringParameter(
        string $type,
        string $method
    ): void {
        $options  = 'x_name';
        $expected = '<input type="' . $type . '" id="x_name" name="x_name"';

        $this->testFieldParameter($method, $options, $expected);
        $this->testFieldParameter($method, $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: weekField() - displayTo
     *
     * @param string $type
     * @param string $method
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    #[DataProvider('getExamples')]
    public function testTagFieldWithDisplayTo(
        string $type,
        string $method
    ): void {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="' . $type . '" id="x_name" '
            . 'name="x_other" value="x_value" class="x_class" size="10"';

        $this->testFieldParameter($method, $options, $expected, false, 'displayTo');
        $this->testFieldParameter($method, $options, $expected, true, 'displayTo');
    }

    /**
     * Tests Phalcon\Tag :: weekField() - displayTo and element not present
     *
     * @param string $type
     * @param string $method
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    #[DataProvider('getExamples')]
    public function testTagFieldWithDisplayToElementNotPresent(
        string $type,
        string $method
    ): void {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="' . $type . '" id="x_name" '
            . 'name="x_other" value="x_value" class="x_class" size="10"';

        $this->testFieldParameter($method, $options, $expected, false, 'displayTo');
        $this->testFieldParameter($method, $options, $expected, true, 'displayTo');
    }

    /**
     * Tests Phalcon\Tag :: weekField() - setDefault
     *
     * @param string $type
     * @param string $method
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    #[DataProvider('getExamples')]
    public function testTagFieldWithSetDefault(
        string $type,
        string $method
    ): void {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="' . $type . '" id="x_name" '
            . 'name="x_other" value="x_value" class="x_class" size="10"';

        $this->testFieldParameter($method, $options, $expected, false, 'setDefault');
        $this->testFieldParameter($method, $options, $expected, true, 'setDefault');
    }

    /**
     * Tests Phalcon\Tag :: weekField() - setDefault and element not present
     *
     * @param string $type
     * @param string $method
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    #[DataProvider('getExamples')]
    public function testTagFieldWithSetDefaultElementNotPresent(
        string $type,
        string $method
    ): void {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="' . $type . '" id="x_name" '
            . 'name="x_other" value="x_value" class="x_class" size="10"';

        $this->testFieldParameter($method, $options, $expected, false, 'setDefault');
        $this->testFieldParameter($method, $options, $expected, true, 'setDefault');
    }

    /**
     * Runs the test for a Tag::$method with $options
     *
     * @param string       $method
     * @param array|string $options
     * @param string       $expected
     * @param bool         $xhtml
     * @param string       $set
     *
     * @return void
     */
    protected function testFieldParameter(
        string $method,
        array | string $options,
        string $expected,
        bool $xhtml = false,
        string $set = ''
    ) {
        if ($xhtml) {
            Tag::setDocType(Tag::XHTML10_STRICT);
            $expected .= ' />';
        } else {
            Tag::setDocType(Tag::HTML5);
            $expected .= '>';
        }

        if ($set) {
            Tag::{$set}('x_name', 'x_value');
        }

        $actual = Tag::$method($options);

        $this->assertSame($expected, $actual);
    }
}
