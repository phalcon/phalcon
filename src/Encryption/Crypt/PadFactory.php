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

namespace Phalcon\Encryption\Crypt;

use Exception as BaseException;
use Phalcon\Encryption\Crypt;
use Phalcon\Encryption\Crypt\Exception\Exception;
use Phalcon\Encryption\Crypt\Padding\Ansi;
use Phalcon\Encryption\Crypt\Padding\Iso10126;
use Phalcon\Encryption\Crypt\Padding\IsoIek;
use Phalcon\Encryption\Crypt\Padding\Noop;
use Phalcon\Encryption\Crypt\Padding\PadInterface;
use Phalcon\Encryption\Crypt\Padding\Pkcs7;
use Phalcon\Encryption\Crypt\Padding\Space;
use Phalcon\Encryption\Crypt\Padding\Zero;
use Phalcon\Traits\Factory\FactoryTrait;

/**
 * Factory for creating pad classes
 */
class PadFactory
{
    use FactoryTrait;

    /**
     * AdapterFactory constructor.
     *
     * @param array<string, string> $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * Create a new instance of the adapter
     *
     * @param string $name
     *
     * @return PadInterface
     * @throws BaseException
     */
    public function newInstance(string $name): PadInterface
    {
        /** @var PadInterface $definition */
        $definition = $this->getService($name);

        return new $definition();
    }

    /**
     * Gets a Crypt pad constant and returns the unique service name for the
     * padding class
     *
     * @param int $number
     *
     * @return string
     */
    public function padNumberToService(int $number): string
    {
        $map = [
            Crypt::PADDING_ANSI_X_923     => "ansi",
            Crypt::PADDING_ISO_10126      => "iso10126",
            Crypt::PADDING_ISO_IEC_7816_4 => "isoiek",
            Crypt::PADDING_PKCS7          => "pjcs7",
            Crypt::PADDING_SPACE          => "space",
            Crypt::PADDING_ZERO           => "zero",
        ];

        return $map[$number] ?? "noop";
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    /**
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            "ansi"     => Ansi::class,
            "iso10126" => Iso10126::class,
            "isoiek"   => IsoIek::class,
            "noop"     => Noop::class,
            "pjcs7"    => Pkcs7::class,
            "space"    => Space::class,
            "zero"     => Zero::class,
        ];
    }
}
