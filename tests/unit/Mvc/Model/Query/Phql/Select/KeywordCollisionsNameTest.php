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

namespace Phalcon\Tests\Unit\Mvc\Model\Query\Phql\Select;

use Phalcon\Phql\Parser;
use Phalcon\Tests\AbstractUnitTestCase;

final class KeywordCollisionsNameTest extends AbstractUnitTestCase
{
    private Parser $parser;

    public function setUp(): void
    {
        $this->parser = new Parser();
    }

    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-09
     */
    public function testMvcModelQueryPhqlSelectCollisionNames(): void
    {
        $source   = "SELECT [Order], [Group] FROM Items";
        $expected = [
            'type'   => 309,
            'select' => [
                'columns' => [
                    0 => [
                        'type'   => 354,
                        'column' => [
                            'type' => 355,
                            'name' => 'Order',
                        ],
                    ],
                    1 => [
                        'type'   => 354,
                        'column' => [
                            'type' => 355,
                            'name' => 'Group',
                        ],
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'Items',
                    ],
                ],
            ],
        ];
        $actual = $this->parser->parse($source);
        unset($actual['id']);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-09
     */
    public function testMvcModelQueryPhqlSelectCollisionNamesDeleteUpdate(): void
    {
        $source   = "SELECT [Delete], [Update] FROM AuditLog";
        $expected = [
            'type'   => 309,
            'select' => [
                'columns' => [
                    0 => [
                        'type'   => 354,
                        'column' => [
                            'type' => 355,
                            'name' => 'Delete',
                        ],
                    ],
                    1 => [
                        'type'   => 354,
                        'column' => [
                            'type' => 355,
                            'name' => 'Update',
                        ],
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'AuditLog',
                    ],
                ],
            ],
        ];
        $actual = $this->parser->parse($source);
        unset($actual['id']);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-09
     */
    public function testMvcModelQueryPhqlSelectCollisionNamesOrderBy(): void
    {
        $source   = "SELECT [Order], [Desc] FROM Items ORDER BY [Order] ASC";
        $expected = [
            'type'    => 309,
            'select'  => [
                'columns' => [
                    0 => [
                        'type'   => 354,
                        'column' => [
                            'type' => 355,
                            'name' => 'Order',
                        ],
                    ],
                    1 => [
                        'type'   => 354,
                        'column' => [
                            'type' => 355,
                            'name' => 'Desc',
                        ],
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'Items',
                    ],
                ],
            ],
            'orderBy' => [
                'column' => [
                    'type' => 355,
                    'name' => 'Order',
                ],
                'sort'   => 327,
            ],
        ];
        $actual = $this->parser->parse($source);
        unset($actual['id']);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-09
     */
    public function testMvcModelQueryPhqlSelectCollisionNamesValuesSet(): void
    {
        $source   = "SELECT [Values], [Set] FROM Config";
        $expected = [
            'type'   => 309,
            'select' => [
                'columns' => [
                    0 => [
                        'type'   => 354,
                        'column' => [
                            'type' => 355,
                            'name' => 'Values',
                        ],
                    ],
                    1 => [
                        'type'   => 354,
                        'column' => [
                            'type' => 355,
                            'name' => 'Set',
                        ],
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'Config',
                    ],
                ],
            ],
        ];
        $actual = $this->parser->parse($source);
        unset($actual['id']);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-09
     */
    public function testMvcModelQueryPhqlSelectCollisionNamesWhere(): void
    {
        $source   = "SELECT * FROM Items WHERE [Select] = 1";
        $expected = [
            'type'   => 309,
            'select' => [
                'columns' => [
                    0 => [
                        'type' => 352,
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'Items',
                    ],
                ],
            ],
            'where'  => [
                'type'  => 61,
                'left'  => [
                    'type' => 355,
                    'name' => 'Select',
                ],
                'right' => [
                    'type'  => 258,
                    'value' => '1',
                ],
            ],
        ];
        $actual = $this->parser->parse($source);
        unset($actual['id']);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-09
     */
    public function testMvcModelQueryPhqlSelectCollisionNamesWhereInt(): void
    {
        $source   = "SELECT * FROM Items WHERE [In] = 1";
        $expected = [
            'type'   => 309,
            'select' => [
                'columns' => [
                    0 => [
                        'type' => 352,
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'Items',
                    ],
                ],
            ],
            'where'  => [
                'type'  => 61,
                'left'  => [
                    'type' => 355,
                    'name' => 'In',
                ],
                'right' => [
                    'type'  => 258,
                    'value' => '1',
                ],
            ],
        ];
        $actual = $this->parser->parse($source);
        unset($actual['id']);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-09
     */
    public function testMvcModelQueryPhqlSelectCollisionNamesWhereIsNotNull(): void
    {
        $source   = "SELECT * FROM Items WHERE [From] IS NOT NULL";
        $expected = [
            'type'   => 309,
            'select' => [
                'columns' => [
                    0 => [
                        'type' => 352,
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'Items',
                    ],
                ],
            ],
            'where'  => [
                'type' => 366,
                'left' => [
                    'type' => 355,
                    'name' => 'From',
                ],
            ],
        ];
        $actual = $this->parser->parse($source);
        unset($actual['id']);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-09
     */
    public function testMvcModelQueryPhqlSelectCollisionNamesWhereIsNull(): void
    {
        $source   = "SELECT * FROM Items WHERE [Null] IS NULL";
        $expected = [
            'type'   => 309,
            'select' => [
                'columns' => [
                    0 => [
                        'type' => 352,
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'Items',
                    ],
                ],
            ],
            'where'  => [
                'type' => 365,
                'left' => [
                    'type' => 355,
                    'name' => 'Null',
                ],
            ],
        ];
        $actual = $this->parser->parse($source);
        unset($actual['id']);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-09
     */
    public function testMvcModelQueryPhqlSelectCollisionNamesWhereString(): void
    {
        $source   = "SELECT * FROM Items WHERE [Where] = 'test'";
        $expected = [
            'type'   => 309,
            'select' => [
                'columns' => [
                    0 => [
                        'type' => 352,
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'Items',
                    ],
                ],
            ],
            'where'  => [
                'type'  => 61,
                'left'  => [
                    'type' => 355,
                    'name' => 'Where',
                ],
                'right' => [
                    'type'  => 260,
                    'value' => 'test',
                ],
            ],
        ];
        $actual = $this->parser->parse($source);
        unset($actual['id']);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-09
     */
    public function testMvcModelQueryPhqlSelectCollisionNamesWhereZero(): void
    {
        $source   = "SELECT * FROM Items WHERE [Not] = 0";
        $expected = [
            'type'   => 309,
            'select' => [
                'columns' => [
                    0 => [
                        'type' => 352,
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'Items',
                    ],
                ],
            ],
            'where'  => [
                'type'  => 61,
                'left'  => [
                    'type' => 355,
                    'name' => 'Not',
                ],
                'right' => [
                    'type'  => 258,
                    'value' => '0',
                ],
            ],
        ];
        $actual = $this->parser->parse($source);
        unset($actual['id']);
        $this->assertSame($expected, $actual);
    }
}
