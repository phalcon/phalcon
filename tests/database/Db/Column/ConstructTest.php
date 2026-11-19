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

namespace Phalcon\Tests\Database\Db\Column;

use Phalcon\Db\Column;
use Phalcon\Db\ColumnInterface;
use Phalcon\Tests\AbstractDatabaseTestCase;
use PHPUnit\Framework\Attributes\Group;

final class ConstructTest extends AbstractDatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Column :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbColumnConstruct(): void
    {
        $options = [
            'type'          => Column::TYPE_INTEGER,
            'isNumeric'     => true,
            'size'          => 11,
            'scale'         => 0,
            'default'       => null,
            'unsigned'      => false,
            'notNull'       => true,
            'autoIncrement' => true,
            'primary'       => true,
            'first'         => true,
            'after'         => null,
            'bindType'      => Column::BIND_PARAM_INT,
        ];

        $column = new Column('field_primary', $options);
        $this->assertInstanceOf(Column::class, $column);
        $this->assertInstanceOf(ColumnInterface::class, $column);
    }

    /**
     * Tests Phalcon\Db\Column :: __construct() - a subclass reads the
     * properties with the cphalcon names
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     *
     * @issue  https://github.com/phalcon/phalcon/issues/833
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbColumnConstructSubclassProperties(): void
    {
        $column = new class (
            'field_primary',
            [
                'type'          => Column::TYPE_INTEGER,
                'unsigned'      => true,
                'notNull'       => true,
                'autoIncrement' => true,
                'primary'       => true,
                'first'         => true,
                'invisible'     => true,
            ]
        ) extends Column {
            /**
             * @return array<string, bool>
             */
            public function readProperties(): array
            {
                return [
                    'autoIncrement' => $this->autoIncrement,
                    'first'         => $this->first,
                    'invisible'     => $this->invisible,
                    'notNull'       => $this->notNull,
                    'primary'       => $this->primary,
                    'unsigned'      => $this->unsigned,
                ];
            }
        };

        $expected = [
            'autoIncrement' => true,
            'first'         => true,
            'invisible'     => true,
            'notNull'       => true,
            'primary'       => true,
            'unsigned'      => true,
        ];
        $this->assertSame($expected, $column->readProperties());
    }
}
