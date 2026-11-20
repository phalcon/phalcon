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

namespace Phalcon\Tests\Unit\Session\Manager;

use Phalcon\Session\Adapter\Stream;
use Phalcon\Session\Manager;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Talon\Talon;
use Phalcon\Tests\Support\Traits\DiTrait;

use function array_keys;

final class GetSetOptionsTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
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

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/883
     */
    public function testSessionManagerGetSetOptionsUniqueIdInt(): void
    {
        $manager = new Manager(['uniqueId' => 123]);
        $manager->setAdapter(
            new Stream(['savePath' => Talon::settings()->outputPath()])
        );

        $this->assertTrue($manager->start());

        $manager->set('test', 'value');

        $this->assertSame(['123#test'], array_keys($_SESSION));
        $this->assertSame('value', $manager->get('test'));

        $manager->destroy();
    }
}
