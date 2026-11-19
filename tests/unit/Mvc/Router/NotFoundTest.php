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

namespace Phalcon\Tests\Unit\Mvc\Router;

use Phalcon\Mvc\Router\Exceptions\InvalidNotFoundPaths;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Mvc\Fake\RouterTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class NotFoundTest extends AbstractUnitTestCase
{
    use RouterTrait;

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidPathsProvider(): array
    {
        return [
            'null'    => [null],
            'integer' => [5],
            'boolean' => [true],
        ];
    }

    /**
     * A value that is not an array or a string gives the cphalcon exception.
     *
     * @issue  https://github.com/phalcon/phalcon/issues/834
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    #[DataProvider('invalidPathsProvider')]
    public function testNotFoundInvalidPaths(mixed $paths): void
    {
        $router = $this->getRouter(false);

        $this->expectException(InvalidNotFoundPaths::class);
        $this->expectExceptionMessage(
            'The not-found paths must be an array or string'
        );

        $router->notFound($paths);
    }

    /**
     * @author Andy Gutierrez <andres.gutierrez@phalcon.io>
     * @since  2013-03-01
     */
    public function testSettingNotFoundPaths(): void
    {
        $router = $this->getRouter(false);

        $router->notFound(
            [
                'module'     => 'module',
                'namespace'  => 'namespace',
                'controller' => 'controller',
                'action'     => 'action',
            ]
        );

        $router->handle('/');


        $this->assertSame(
            'controller',
            $router->getControllerName()
        );

        $this->assertSame(
            'action',
            $router->getActionName()
        );

        $this->assertSame(
            'module',
            $router->getModuleName()
        );

        $this->assertSame(
            'namespace',
            $router->getNamespaceName()
        );
    }
}
