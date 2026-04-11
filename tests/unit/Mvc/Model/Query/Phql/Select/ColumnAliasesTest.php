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

final class ColumnAliasesTest extends AbstractUnitTestCase
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
    public function testMvcModelQueryPhqlSelectAliasInt(): void
    {
        $source   = "SELECT inv_id AS id FROM Invoices";
        $expected = [
            'type'   => 309,
            'select' => [
                'columns' => [
                    0 => [
                        'type'   => 354,
                        'column' => [
                            'type' => 355,
                            'name' => 'inv_id',
                        ],
                        'alias'  => 'id',
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'Invoices',
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
    public function testMvcModelQueryPhqlSelectAliasStringFloat(): void
    {
        $source   = "SELECT inv_title AS title, inv_total AS total "
            . "FROM Invoices";
        $expected = [
            'type'   => 309,
            'select' => [
                'columns' => [
                    0 => [
                        'type'   => 354,
                        'column' => [
                            'type' => 355,
                            'name' => 'inv_title',
                        ],
                        'alias'  => 'title',
                    ],
                    1 => [
                        'type'   => 354,
                        'column' => [
                            'type' => 355,
                            'name' => 'inv_total',
                        ],
                        'alias'  => 'total',
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'Invoices',
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
    public function testMvcModelQueryPhqlSelectAliasTableAlias(): void
    {
        $source   = "SELECT i.inv_id AS id, i.inv_title title "
            . "FROM Invoices AS i";
        $expected = [
            'type'   => 309,
            'select' => [
                'columns' => [
                    0 => [
                        'type'   => 354,
                        'column' => [
                            'type'   => 355,
                            'domain' => 'i',
                            'name'   => 'inv_id',
                        ],
                        'alias'  => 'id',
                    ],
                    1 => [
                        'type'   => 354,
                        'column' => [
                            'type'   => 355,
                            'domain' => 'i',
                            'name'   => 'inv_title',
                        ],
                        'alias'  => 'title',
                    ],
                ],
                'tables'  => [
                    'qualifiedName' => [
                        'type' => 355,
                        'name' => 'Invoices',
                    ],
                    'alias'         => 'i',
                ],
            ],
        ];
        $actual = $this->parser->parse($source);
        unset($actual['id']);
        $this->assertSame($expected, $actual);
    }
}
