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

namespace Phalcon\Tests\Database\DataMapper\Table\Row;

use Phalcon\DataMapper\Table\AbstractRow;
use Phalcon\DataMapper\Table\Exception\ImmutableAfterDeletedException;
use Phalcon\DataMapper\Table\Exception\InvalidOptionException;
use Phalcon\DataMapper\Table\Exception\PropertyDoesNotExistException;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\DataMapper\Table\Invoices\InvoicesRow;

use function uniqid;

final class RowTest extends AbstractDatabaseTestCase
{
    /**
     * @return void
     *
     * @group mysql
     */
    public function testConstructor(): void
    {
        $row = new InvoicesRow();
        $this->assertInstanceOf(InvoicesRow::class, $row);
        $this->assertInstanceOf(AbstractRow::class, $row);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testConstructorWithData(): void
    {
        $title = uniqid('tit-');
        $data  = [
            'inv_id'          => 1,
            'inv_cst_id'      => 1,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.0,
            'inv_created_at'  => '2024-02-01 10:11:12',
        ];

        $row = new InvoicesRow($data);

        $expected = $data;
        $actual   = $row->getCopy();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testConstructorWithUnknownColumnsThrowsException(): void
    {
        $title = uniqid('tit-');
        $data  = [
            'inv_id'          => 1,
            'inv_cst_id'      => 1,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.0,
            'inv_created_at'  => '2024-02-01 10:11:12',
            'other_column'    => 'random stuff',
        ];

        $this->expectException(PropertyDoesNotExistException::class);
        $this->expectExceptionMessage(
            '[' . InvoicesRow::class . '::other_column] does not exist'
        );

        $row = new InvoicesRow($data);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testGetCopy(): void
    {
        $title = uniqid('tit-');
        $data  = [
            'inv_id'          => 1,
            'inv_cst_id'      => 1,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.0,
            'inv_created_at'  => '2024-02-01 10:11:12',
        ];

        $row = new InvoicesRow($data);

        $expected = $data;
        $actual   = $row->getCopy();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testGetDiff(): void
    {
        $title = uniqid('tit-');
        $data  = [
            'inv_id'          => 1,
            'inv_cst_id'      => 1,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.0,
            'inv_created_at'  => '2024-02-01 10:11:12',
        ];

        $row = new InvoicesRow($data);

        $row
            ->set('inv_id', 2)
            ->set('inv_cst_id', 2)
            ->set('inv_status_flag', 0)
        ;

        $expected = [
            'inv_id'          => 2,
            'inv_cst_id'      => 2,
            'inv_status_flag' => 0,
        ];
        $actual   = $row->getDiff();
        $this->assertSame($expected, $actual);

        $row->set('inv_status_flag', false);

        $expected = [
            'inv_id'          => 2,
            'inv_cst_id'      => 2,
            'inv_status_flag' => false,
        ];
        $actual   = $row->getDiff();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testGetInit(): void
    {
        $title = uniqid('tit-');
        $data  = [
            'inv_id'          => 1,
            'inv_cst_id'      => 1,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.0,
            'inv_created_at'  => '2024-02-01 10:11:12',
        ];

        $row = new InvoicesRow($data);

        $expected = $data;
        $actual   = $row->getInit();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testGetIterator(): void
    {
        $title = uniqid('tit-');
        $data  = [
            'inv_id'          => 1,
            'inv_cst_id'      => 1,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.0,
            'inv_created_at'  => '2024-02-01 10:11:12',
        ];

        $row = new InvoicesRow($data);

        foreach ($row as $name => $value) {
            $this->assertSame($data[$name], $value);
        }
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testHas(): void
    {
        $row = new InvoicesRow();

        $actual = $row->has('inv_id');
        $this->assertTrue($actual);

        $actual = $row->has('unknown_column');
        $this->assertFalse($actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testJsonSerialize(): void
    {
        $title = uniqid('tit-');
        $data  = [
            'inv_id'          => 1,
            'inv_cst_id'      => 1,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.0,
            'inv_created_at'  => '2024-02-01 10:11:12',
        ];

        $row = new InvoicesRow($data);

        $expected = $data;
        $actual   = $row->jsonSerialize();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testRemove(): void
    {
        $row = new InvoicesRow();

        $actual = $row->get('inv_id');
        $this->assertNull($actual);

        $row->set('inv_id', 1);

        $expected = 1;
        $actual   = $row->get('inv_id');
        $this->assertSame($expected, $actual);

        $row->remove('inv_id');

        $actual = $row->get('inv_id');
        $this->assertNull($actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testRemoveDeletedThrowsException(): void
    {
        $this->expectException(ImmutableAfterDeletedException::class);
        $this->expectExceptionMessage(
            '[' . InvoicesRow::class . '::inv_id] is immutable after the row is deleted'
        );

        $row = new InvoicesRow();

        $row->set('inv_id', 1);
        $row->setLastAction($row::DELETE);

        $row->remove('inv_id');
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testSet(): void
    {
        $title = uniqid('tit-');

        $row = new InvoicesRow();
        $row
            ->set('inv_id', 1)
            ->set('inv_cst_id', 1)
            ->set('inv_status_flag', 1)
            ->set('inv_title', $title)
            ->set('inv_total', 100.0)
            ->set('inv_created_at', '2024-02-01 10:11:12')
        ;

        $expected = [
            'inv_id'          => 1,
            'inv_cst_id'      => 1,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.0,
            'inv_created_at'  => '2024-02-01 10:11:12',
        ];
        $actual   = $row->getCopy();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testSetOnDeletedRowThrowsException(): void
    {
        $this->expectException(ImmutableAfterDeletedException::class);
        $this->expectExceptionMessage(
            '[' . InvoicesRow::class . '::inv_id] is immutable after the row is deleted.'
        );

        $row = new InvoicesRow();
        $row
            ->setLastAction($row::DELETE)
            ->set('inv_id', 1)
        ;
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testSetUnknownColumnThrowsException(): void
    {
        $this->expectException(PropertyDoesNotExistException::class);
        $this->expectExceptionMessage(
            '[' . InvoicesRow::class . '::other_column] does not exist'
        );

        $row = new InvoicesRow();
        $row
            ->set('other_column', 'random stuff')
        ;
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testLastAction(): void
    {
        $row = new InvoicesRow();

        $actual = $row->getLastAction();
        $this->assertNull($actual);

        /**
         * New row - INSERT
         */
        $expected = $row::INSERT;
        $actual   = $row->getNextAction();
        $this->assertSame($expected, $actual);

        /**
         * set Delete - Next action is null
         */
        $row->setDelete(true);
        $actual   = $row->getNextAction();
        $this->assertNull($actual);

        /**
         * unset Delete - Next action is INSERT
         */
        $row->setDelete(false);
        $expected = $row::INSERT;
        $actual   = $row->getNextAction();
        $this->assertSame($expected, $actual);

        /**
         * unset Delete - Next action is INSERT
         */
        $row
            ->setLastAction($row::INSERT)
            ->setDelete(true)
        ;
        $expected = $row::DELETE;
        $actual   = $row->getNextAction();
        $this->assertSame($expected, $actual);

        /**
         * Revert back to default
         */
        $row->setDelete(false);

        /**
         * Set action to SELECT and change a field - UPDATE
         */
        $row
            ->setLastAction($row::SELECT)
            ->set('inv_id', 1)
        ;
        $expected = $row::UPDATE;
        $actual   = $row->getNextAction();
        $this->assertSame($expected, $actual);

        /**
         * Revert the field
         */
        $row->set('inv_id', null);

        $actual   = $row->getNextAction();
        $this->assertNull($actual);

        /**
         * Set action to SELECT - null getNextAction
         */
        $row->setLastAction($row::SELECT);

        $actual   = $row->getNextAction();
        $this->assertNull($actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testLastActionInvalidThrowsException(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage(
            'Invalid option supplied [other_option]'
        );

        $row = new InvoicesRow();
        $row->setLastAction('other_option');
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testSetNumericToBool(): void
    {
        $title = uniqid('tit-');

        $data = [
            'inv_id'          => 1,
            'inv_cst_id'      => 1,
            'inv_status_flag' => 1,
            'inv_title'       => $title,
            'inv_total'       => 100.0,
            'inv_created_at'  => '2024-02-01 10:11:12',
        ];
        $row = new InvoicesRow($data);

        $row->set('inv_status_flag', true);

        $expected = [];
        $actual   = $row->getDiff();
        $this->assertSame($expected, $actual);

        $row->set('inv_status_flag', false);

        $expected = [
            'inv_status_flag' => false,
        ];
        $actual   = $row->getDiff();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testSetBoolToNumeric(): void
    {
        $title = uniqid('tit-');

        $data = [
            'inv_id'          => 1,
            'inv_cst_id'      => 1,
            'inv_status_flag' => true,
            'inv_title'       => $title,
            'inv_total'       => 100.0,
            'inv_created_at'  => '2024-02-01 10:11:12',
        ];
        $row = new InvoicesRow($data);

        $row->set('inv_status_flag', 1);

        $expected = [];
        $actual   = $row->getDiff();
        $this->assertSame($expected, $actual);

        $row->set('inv_status_flag', 0);

        $expected = [
            'inv_status_flag' => 0,
        ];
        $actual   = $row->getDiff();
        $this->assertSame($expected, $actual);
    }
}
