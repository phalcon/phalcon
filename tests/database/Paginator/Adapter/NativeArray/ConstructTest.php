<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\Paginator\Adapter\NativeArray;

use ArgumentCountError;
use Phalcon\Paginator\Adapter\NativeArray;
use Phalcon\Tests\AbstractDatabaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use TypeError;

use function array_merge;

#[Group('mysql')]
#[Group('pgsql')]
#[Group('sqlite')]
final class ConstructTest extends AbstractDatabaseTestCase
{
    /**
     * @return array<int, array{0: string}>
     */
    public static function getOptionNames(): array
    {
        return [
            ['limit'],
            ['page'],
        ];
    }

    /**
     * Tests Phalcon\Paginator\Adapter\NativeArray :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-03-31
     */
    public function testPaginatorAdapterNativearrayConstruct(): void
    {
        $this->expectException(ArgumentCountError::class);

        $paginator = new NativeArray();
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/873
     */
    #[DataProvider('getOptionNames')]
    public function testPaginatorAdapterNativearrayConstructNotNumeric(
        string $option
    ): void {
        $this->expectException(TypeError::class);

        new NativeArray(
            array_merge(
                ['data' => [1, 2, 3], 'limit' => 2, 'page' => 1],
                [$option => 'abc']
            )
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/873
     */
    public function testPaginatorAdapterNativearrayConstructNumericStrings(): void
    {
        $paginator = new NativeArray(
            [
                'data'  => [1, 2, 3],
                'limit' => '2',
                'page'  => '2',
            ]
        );

        $this->assertSame(2, $paginator->getLimit());

        $page = $paginator->paginate();
        $this->assertSame(2, $page->getCurrent());
        $this->assertSame([3], $page->getItems());
    }
}
