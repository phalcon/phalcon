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

namespace Phalcon\Tests\Fixtures\Traits;

use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\Exception;
use Phalcon\Translate\InterpolatorFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

trait TranslateNativeArrayHelperTrait
{
    /**
     * Data provider for the query one variable substitution
     *
     * @return array[]
     */
    public static function getQueryOneVariable(): array
    {
        return [
            [
                'en',
                [
                    'hello-key' => 'Hello my friend',
                ],
            ],
            [
                'es',
                [
                    'hello-key' => 'Hola my friend',
                ],
            ],
            [
                'fr',
                [
                    'hello-key' => 'Bonjour my friend',
                ],
            ],
        ];
    }

    /**
     * Data provider for the query tests
     *
     * @return array[]
     */
    public static function getQueryProvider(): array
    {
        return [
            [
                'en',
                [
                    'hi'  => 'Hello',
                    'bye' => 'Good Bye',
                ],
            ],
            [
                'es',
                [
                    'hi'  => 'Hola',
                    'bye' => 'Adiós',
                ],
            ],
            [
                'fr',
                [
                    'hi'  => 'Bonjour',
                    'bye' => 'Au revoir',
                ],
            ],
        ];
    }

    /**
     * Data provider for the query one variable substitution
     *
     * @return array[]
     */
    public static function getQueryTwoVariables(): array
    {
        return [
            [
                'en',
                [
                    'song-key' => 'This song is Dust in the wind (Kansas)',
                ],
            ],
            [
                'es',
                [
                    'song-key' => 'La canción es Dust in the wind (Kansas)',
                ],
            ],
            [
                'fr',
                [
                    'song-key' => 'La chanson est Dust in the wind (Kansas)',
                ],
            ],
        ];
    }

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: query()
     *
     * @throws Exception
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    #[Test]
    #[DataProvider('getQueryProvider')]
    public function testTranslateAdapterNativearrayQuery(
        string $code,
        array $tests
    ): void {
        $language   = $this->getArrayConfig()[$code];
        $translator = new NativeArray(
            new InterpolatorFactory(),
            [
                'content' => $language,
            ]
        );

        foreach ($tests as $key => $expected) {
            $actual = $translator->{$this->func()}($key);

            $this->assertSame($expected, $actual);
        }
    }

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: query() -
     * variable substitution in string with no variables
     *
     * @dataProvider getQueryProvider
     *
     * @throws Exception
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    #[Test]
    #[DataProvider('getQueryProvider')]
    public function testTranslateAdapterNativearrayVariableSubstitutionNoVariables(
        string $code,
        array $tests
    ): void {
        $language   = $this->getArrayConfig()[$code];
        $translator = new NativeArray(
            new InterpolatorFactory(),
            [
                'content' => $language,
            ]
        );

        foreach ($tests as $key => $expected) {
            $actual = $translator->{$this->func()}(
                $key,
                [
                    'name' => 'my friend',
                ]
            );

            $this->assertSame($expected, $actual);
        }
    }

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: query() -
     * variable substitution in string (one variable)
     *
     * @dataProvider getQueryOneVariable
     *
     * @throws Exception
     */
    #[Test]
    #[DataProvider('getQueryOneVariable')]
    public function testTranslateAdapterNativearrayVariableSubstitutionOneVariable(
        string $code,
        array $tests
    ): void {
        $language   = $this->getArrayConfig()[$code];
        $translator = new NativeArray(
            new InterpolatorFactory(),
            [
                'content' => $language,
            ]
        );

        foreach ($tests as $key => $expected) {
            $actual = $translator->{$this->func()}($key, ['name' => 'my friend']);
            $this->assertSame($expected, $actual);
        }
    }

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: query() -
     * variable substitution in string (two variables)
     *
     * @dataProvider getQueryTwoVariables
     *
     * @throws Exception
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    #[Test]
    #[DataProvider('getQueryTwoVariables')]
    public function testTranslateAdapterNativearrayVariableSubstitutionTwoVariable(
        string $code,
        array $tests
    ): void {
        $language   = $this->getArrayConfig()[$code];
        $translator = new NativeArray(
            new InterpolatorFactory(),
            [
                'content' => $language,
            ]
        );

        $vars = [
            'song'   => 'Dust in the wind',
            'artist' => 'Kansas',
        ];

        foreach ($tests as $key => $expected) {
            $actual = $translator->{$this->func()}($key, $vars);

            $this->assertSame($expected, $actual);
        }
    }

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: query() - array access
     * and UTF8 strings
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testWithArrayAccessAndUTF8Strings(): void
    {
        $language = $this->getArrayConfig()['ru'];

        $translator = new NativeArray(
            new InterpolatorFactory(),
            [
                'content' => $language,
            ]
        );

        $vars     = [
            'fname' => 'John',
            'lname' => 'Doe',
            'mname' => 'D.',
        ];
        $expected = 'Привет, John D. Doe!';
        $actual   = $translator->{$this->func()}('Hello %fname% %mname% %lname%!', $vars);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return string
     */
    abstract protected function func(): string;

    /**
     * @return array
     */
    abstract protected function getArrayConfig(): array;
}
