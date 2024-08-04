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

namespace Phalcon\Tests\Unit\Support\Version;

use Codeception\Example;
use Phalcon\Tests\Fixtures\Support\Version\VersionAlphaFixture;
use Phalcon\Tests\Fixtures\Support\Version\VersionBetaFixture;
use Phalcon\Tests\Fixtures\Support\Version\VersionRcFixture;
use Phalcon\Tests\Fixtures\Support\Version\VersionStableFixture;
use Phalcon\Tests\UnitTestCase;

use function is_string;

final class GetTest extends UnitTestCase
{
    /**
     * Tests get()
     *
     * @dataProvider getExamples
     *
     * @return void
     * @param Example    $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSupportVersionGet(
        string $class,
        string $expected
    ): void {
        $version = new $class();

        $actual   = $version->get();
        $this->assertTrue(is_string($actual));
        $this->assertSame($expected, $actual);
    }

    /**
     * @return string[][]
     */
    public static function getExamples(): array
    {
        return [
            [
                VersionAlphaFixture::class,
                '5.0.0alpha1',
            ],
            [
                VersionBetaFixture::class,
                '5.0.0beta2',
            ],
            [
                VersionRcFixture::class,
                '5.0.0RC3',
            ],
            [
                VersionStableFixture::class,
                '5.0.0',
            ],
        ];
    }
}
