<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Config\Adapter\Grouped;

use Phalcon\Config\Adapter\Grouped;
use Phalcon\Config\Config;
use Phalcon\Config\Exception;
use Phalcon\Tests\Fixtures\Traits\ConfigTrait;
use Phalcon\Tests\AbstractUnitTestCase;

use function dataDir;

final class ConstructTest extends AbstractUnitTestCase
{
    use ConfigTrait;

    public function tearDown(): void
    {
        unset($this->config['test']['property']); //Removing Extra Property
    }

    /**
     * Tests Phalcon\Config\Adapter\Grouped :: __construct() - complex instance
     *
     * @author fenikkusu
     * @since  2017-06-06
     */
    public function testConfigAdapterGroupedConstructComplexInstance(): void
    {
        $this->config['test']['property2'] = 'something-else';
        $this->config['test']['property']  = 'blah';

        $config = [
            dataDir('assets/config/config.php'),
            [
                'adapter'  => 'json',
                'filePath' => dataDir('assets/config/config.json'),
            ],
            [
                'adapter' => 'array',
                'config'  => [
                    'test' => [
                        'property2' => 'something-else',
                    ],
                ],
            ],
            new Config(
                [
                    'test' => [
                        'property' => 'blah',
                    ],
                ]
            ),
        ];

        foreach ([[], ['']] as $parameters) {
            $this->compareConfig(
                $this->config,
                new Grouped($config, ...$parameters)
            );
        }
    }

    /**
     * Tests Phalcon\Config\Adapter\Grouped :: __construct() - default adapter
     *
     * @author fenikkusu
     * @since  2017-06-06
     */
    public function testConfigAdapterGroupedConstructDefaultAdapter(): void
    {
        $this->config['test']['property2'] = 'something-else';

        $config = [
            [
                'filePath' => dataDir('assets/config/config.json'),
            ],
            [
                'adapter' => 'array',
                'config'  => [
                    'test' => [
                        'property2' => 'something-else',
                    ],
                ],
            ],
        ];

        $object = new Grouped($config, 'json');

        $this->compareConfig(
            $this->config,
            $object
        );
    }

    /**
     * Tests Phalcon\Config\Adapter\Grouped :: __construct() - exception
     *
     * @author Fenikkusu
     * @since  2017-06-06
     */
    public function testConfigAdapterGroupedConstructThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "To use 'array' adapter you have to specify the 'config' as an array."
        );

        (new Grouped(
            [
                [
                    'adapter' => 'array',
                ],
            ]
        ));
    }
}
