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

namespace Phalcon\Tests\Integration\Paginator\Adapter\NativeArray;

use IntegrationTester;
use Phalcon\Paginator\Adapter\NativeArray;
use Phalcon\Paginator\Repository;

class SetCurrentPageCest
{
    /**
     * Tests Phalcon\Paginator\Adapter\NativeArray :: setCurrentPage()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function paginatorAdapterNativearraySetCurrentPage(IntegrationTester $I)
    {
        $I->wantToTest('Paginator\Adapter\NativeArray - setCurrentPage()');

        $data = [];
        for ($counter = 0; $counter < 30; $counter++) {
            $data[] = 'banana-' . $counter;
        }
        $paginator = new NativeArray(
            [
                'data'  => $data,
                'limit' => 10,
                'page'  => 1,
            ]
        );

        $paginator->setCurrentPage(2);
        $page = $paginator->paginate();

        $I->assertInstanceOf(Repository::class, $page);

        $I->assertCount(10, $page->items);
        $I->assertEquals(1, $page->previous);
        $I->assertEquals(3, $page->next);
        $I->assertEquals(3, $page->last);
        $I->assertEquals(10, $page->limit);
        $I->assertEquals(2, $page->current);
        $I->assertEquals(30, $page->total_items);
    }
}
