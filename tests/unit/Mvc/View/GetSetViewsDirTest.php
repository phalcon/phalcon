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

namespace Phalcon\Tests\Unit\Mvc\View;

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Exceptions\InvalidViewsDirType;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Talon\Talon;
use Phalcon\Tests\Unit\Mvc\Fake\ViewTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class GetSetViewsDirTest extends AbstractUnitTestCase
{
    use ViewTrait;

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidViewsDirProvider(): array
    {
        return [
            'null'    => [null],
            'integer' => [5],
            'boolean' => [false],
        ];
    }

    /**
     * Tests Phalcon\Mvc\View :: getViewsDir() / setViewsDir()
     */
    public function testMvcViewGetSetViewsDir(): void
    {
        $view = new View();

        $view->setBasePath(Talon::settings()->dataPath() . '/');

        $view->setViewsDir('views/');

        $expected = 'views/';
        $actual   = $view->getViewsDir();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\View :: setViewsDir() - a value that is not a string
     * or an array
     *
     * @issue  https://github.com/phalcon/phalcon/issues/834
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    #[DataProvider('invalidViewsDirProvider')]
    public function testMvcViewSetViewsDirInvalidType(mixed $viewsDir): void
    {
        $view = new View();

        $this->expectException(InvalidViewsDirType::class);
        $this->expectExceptionMessage(
            'Views directory must be a string or an array'
        );

        $view->setViewsDir($viewsDir);
    }
}
