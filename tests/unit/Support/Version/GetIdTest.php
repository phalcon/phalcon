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

use Phalcon\Tests\Fixtures\Support\Version\VersionAlphaFixture;
use Phalcon\Tests\Fixtures\Support\Version\VersionBetaFixture;
use Phalcon\Tests\Fixtures\Support\Version\VersionRcFixture;
use Phalcon\Tests\Fixtures\Support\Version\VersionStableFixture;
use Phalcon\Tests\Fixtures\Traits\VersionTrait;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

use function is_string;

final class GetIdTest extends AbstractUnitTestCase
{
    use VersionTrait;

    /**
     * @return string[][]
     */
    public static function getExamples(): array
    {
        return [
            [
                VersionAlphaFixture::class,
                '5000011',
            ],
            [
                VersionBetaFixture::class,
                '5000022',
            ],
            [
                VersionRcFixture::class,
                '5000033',
            ],
            [
                VersionStableFixture::class,
                '5000000',
            ],
        ];
    }

    /**
     * Tests get()
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    #[Test]
    #[DataProvider('getExamples')]
    public function testSupportVersionGetId(
        string $class,
        string $expected
    ): void {
        $version = new $class();

        $actual = $version->getId();
        $this->assertTrue(is_string($actual));
        $this->assertSame($expected, $actual);
    }
}
