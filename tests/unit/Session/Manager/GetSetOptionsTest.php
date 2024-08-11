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

namespace Phalcon\Tests\Unit\Session\Manager;

use Phalcon\Session\Manager;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetSetOptionsTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Manager :: getOptions()/setOptions()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerGetSetOptions(): void
    {
        $options = [
            'test1' => 'option1',
            'test2' => 'option2',
        ];

        $manager = new Manager($options);
        $options = [
            'test3' => 'option3',
        ];


        $manager->setOptions($options);

        $actual = $manager->getOptions();
        $this->assertEquals($options, $actual);
    }

    /**
     * Tests Phalcon\Session\Manager :: getOptions()/setOptions() - constructor
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerGetSetOptionsConstructor(): void
    {
        $options = [
            'test1' => 'option1',
            'test2' => 'option2',
        ];

        $manager = new Manager($options);

        $actual = $manager->getOptions();
        $this->assertEquals($options, $actual);
    }
}
