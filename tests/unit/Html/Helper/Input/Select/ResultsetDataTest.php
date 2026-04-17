<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Html\Helper\Input\Select;

use ArrayIterator;
use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use Phalcon\Html\Helper\Input\Select\ResultsetData;
use Phalcon\Messages\MessageInterface;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Tests\AbstractUnitTestCase;

final class ResultsetDataTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-17
     */
    public function testConstructorThrowsOnInvalidUsingCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('requires exactly two values');

        new ResultsetData(
            $this->buildResultsetMock([]),
            ['id']
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-17
     */
    public function testGetOptionsReturnsEmptyArrayForEmptyResultset(): void
    {
        $resultset = $this->buildResultsetMock([]);
        $data      = new ResultsetData($resultset, ['id', 'name']);

        $this->assertSame([], $data->getOptions());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-17
     */
    public function testGetOptionsReturnsValueLabelPairsFromArrayRows(): void
    {
        $rows = [
            ['id' => '1', 'name' => 'Ferrari'],
            ['id' => '2', 'name' => 'Ford'],
        ];

        $resultset = $this->buildResultsetMock($rows);
        $data      = new ResultsetData($resultset, ['id', 'name']);

        $expected = ['1' => 'Ferrari', '2' => 'Ford'];
        $this->assertSame($expected, $data->getOptions());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-17
     */
    public function testGetOptionsReturnsValueLabelPairsFromObjects(): void
    {
        $row1 = $this->getMockBuilder(\stdClass::class)
                     ->addMethods(['readAttribute'])
                     ->getMock();
        $row1->method('readAttribute')
             ->willReturnMap([['id', '1'], ['name', 'Ferrari']]);

        $row2 = $this->getMockBuilder(\stdClass::class)
                     ->addMethods(['readAttribute'])
                     ->getMock();
        $row2->method('readAttribute')
             ->willReturnMap([['id', '2'], ['name', 'Ford']]);

        $resultset = $this->buildResultsetMock([$row1, $row2]);

        $data   = new ResultsetData($resultset, ['id', 'name']);
        $actual = $data->getOptions();

        $this->assertSame(['1' => 'Ferrari', '2' => 'Ford'], $actual);
    }

    /**
     * @param array $rows
     *
     * @return ResultsetInterface
     */
    private function buildResultsetMock(array $rows): ResultsetInterface
    {
        return new class ($rows) extends ArrayIterator implements ResultsetInterface {
            public function __construct(array $rows)
            {
                parent::__construct($rows);
            }

            public function delete(Closure | null $conditionCallback = null): bool
            {
                throw new BadMethodCallException('Not implemented');
            }

            public function filter(callable $filter): array
            {
                throw new BadMethodCallException('Not implemented');
            }

            public function getCache(): mixed
            {
                throw new BadMethodCallException('Not implemented');
            }

            public function getFirst(): mixed
            {
                throw new BadMethodCallException('Not implemented');
            }

            public function getHydrateMode(): int
            {
                throw new BadMethodCallException('Not implemented');
            }

            public function getLast(): ModelInterface | null
            {
                throw new BadMethodCallException('Not implemented');
            }

            public function getMessages(): array
            {
                throw new BadMethodCallException('Not implemented');
            }

            public function getType(): int
            {
                throw new BadMethodCallException('Not implemented');
            }

            public function isFresh(): bool
            {
                throw new BadMethodCallException('Not implemented');
            }

            public function setHydrateMode(int $hydrateMode): ResultsetInterface
            {
                throw new BadMethodCallException('Not implemented');
            }

            public function setIsFresh(bool $isFresh): ResultsetInterface
            {
                throw new BadMethodCallException('Not implemented');
            }

            public function toArray(): array
            {
                throw new BadMethodCallException('Not implemented');
            }

            public function update(
                mixed $data,
                Closure | null $conditionCallback = null
            ): bool {
                throw new BadMethodCallException('Not implemented');
            }
        };
    }
}
