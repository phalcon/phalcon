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

use Phalcon\Assets\Inline;
use Phalcon\Tests\UnitTestCase;

final class ConstructTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Asset :: __construct() - css
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsInlineConstructCss(): void
    {
        $content = 'p {color: #000099}';
        $asset   = new Inline('css', $content);

        $expected = 'css';
        $actual   = $asset->getType();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Assets\Asset :: __construct() - css attributes
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsInlineConstructCssAttributes(): void
    {
        $content = 'p {color: #000099}';
        $asset   = new Inline('css', $content);

        $expected = [];
        $actual   = $asset->getAttributes();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Assets\Asset :: __construct() - css attributes set
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsInlineConstructCssAttributesSet(): void
    {
        $content    = 'p {color: #000099}';
        $attributes = [
            'data' => 'phalcon',
        ];

        $asset = new Inline(
            'css',
            $content,
            true,
            $attributes
        );

        $actual = $asset->getAttributes();
        $this->assertSame($attributes, $actual);
    }

    /**
     * Tests Phalcon\Assets\Asset :: __construct() - css filter
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsInlineConstructCssFilter(): void
    {
        $content = 'p {color: #000099}';
        $asset   = new Inline('css', $content);
        $actual  = $asset->getFilter();
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Assets\Asset :: __construct() - css filter set
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsInlineConstructCssFilterSet(): void
    {
        $content = 'p {color: #000099}';
        $asset   = new Inline('css', $content, false);
        $actual  = $asset->getFilter();
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Assets\Asset :: __construct() - js
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsInlineConstructJs(): void
    {
        $content = '<script>alert("Hello");</script>';
        $asset   = new Inline('js', $content);

        $expected = 'js';
        $actual   = $asset->getType();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Assets\Asset :: __construct() - js attributes
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsInlineConstructJsAttributes(): void
    {
        $content = '<script>alert("Hello");</script>';
        $asset   = new Inline('js', $content);

        $expected = [];
        $actual   = $asset->getAttributes();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Assets\Asset :: __construct() - js attributes set
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsInlineConstructJsAttributesSet(): void
    {
        $content    = '<script>alert("Hello");</script>';
        $attributes = [
            'data' => 'phalcon',
        ];
        $asset      = new Inline('js', $content, true, $attributes);

        $actual = $asset->getAttributes();
        $this->assertSame($attributes, $actual);
    }

    /**
     * Tests Phalcon\Assets\Asset :: __construct() - js filter
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsInlineConstructJsFilter(): void
    {
        $content = '<script>alert("Hello");</script>';
        $asset   = new Inline('js', $content);
        $actual  = $asset->getFilter();
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Assets\Asset :: __construct() - js filter set
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAssetsInlineConstructJsFilterSet(): void
    {
        $content = '<script>alert("Hello");</script>';
        $asset   = new Inline('js', $content, false);
        $actual  = $asset->getFilter();
        $this->assertFalse($actual);
    }
}
