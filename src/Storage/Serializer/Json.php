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

namespace Phalcon\Storage\Serializer;

use InvalidArgumentException;
use JsonSerializable;
use Phalcon\Support\Helper\Json\Decode;
use Phalcon\Support\Helper\Json\Encode;

use function is_object;

class Json extends AbstractSerializer
{
    private Decode $decode;
    private Encode $encode;

    /**
     * AbstractSerializer constructor.
     *
     * @param null $data
     */
    public function __construct($data = null)
    {
        $this->encode = new Encode();
        $this->decode = new Decode();

        parent::__construct($data);
    }

    /**
     * Serializes data
     *
     * @return JsonSerializable|mixed|string
     */
    public function serialize(): mixed
    {
        if (is_object($this->data) && !($this->data instanceof JsonSerializable)) {
            throw new InvalidArgumentException(
                "Data for the JSON serializer cannot be of type 'object' " .
                "without implementing 'JsonSerializable'"
            );
        }

        if (true !== $this->isSerializable($this->data)) {
            return $this->data;
        }

        return $this->encode->__invoke($this->data);
    }

    /**
     * Unserializes data
     *
     * @param string $data
     *
     * @return void
     */
    public function unserialize(mixed $data): void
    {
        if (true !== $this->isSerializable($data)) {
            $this->data = $data;
        } else {
            $this->data = $this->decode->__invoke($data);
        }
    }
}
