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

namespace Phalcon\Tests\Unit\Assets\Manager;

use Codeception\Example;
use Phalcon\Assets\Manager;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Tests\UnitTestCase;

final class GetSetOptionsTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Manager :: getOptions()/setOptions()
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-03-19
     *
     *
     * @dataProvider getExamples
     */
    public function testAssetsManagerGetSetOptions(
        array $options,
        array $expected,
        array $set
    ): void {
        $manager = new Manager(new TagFactory(new Escaper()), $options);

        if (!empty($set)) {
            $manager->setOptions($set);
        }

        $actual = $manager->getOptions();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public static function getExamples(): array
    {
        return [
            [
                [],
                [],
                [],
            ],
            [
                [
                    'one'   => 'two',
                    'three' => 'four',
                ],
                [
                    'one'   => 'two',
                    'three' => 'four',
                ],
                [],
            ],
            [
                [],
                [
                    'one'   => 'two',
                    'three' => 'four',
                ],
                [
                    'one'   => 'two',
                    'three' => 'four',
                ],
            ],
        ];
    }
}
