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

namespace Phalcon\Tests\Unit\Queue\Adapter;

use Phalcon\Queue\Adapter\MessageEnvelope;
use Phalcon\Queue\Adapter\Memory\MemoryMessage;
use Phalcon\Tests\AbstractUnitTestCase;

use function serialize;

final class MessageEnvelopeTest extends AbstractUnitTestCase
{
    public function testDecodeAppliesDefaultsForMissingKeys(): void
    {
        $payload = serialize(['body' => 'partial']);

        $message = MessageEnvelope::decode($payload, $this->messageFactory());

        $this->assertNotNull($message);
        $this->assertSame('partial', $message->getBody());
        $this->assertSame([], $message->getProperties());
        $this->assertSame([], $message->getHeaders());
    }

    public function testDecodeReturnsNullForNonArrayPayload(): void
    {
        $this->assertNull(
            MessageEnvelope::decode(serialize('just a string'), $this->messageFactory())
        );
    }

    public function testEncodeDecodeRoundTrip(): void
    {
        $original = new MemoryMessage('payload', ['attempt' => 2], ['x-trace' => 'abc']);

        $message = MessageEnvelope::decode(
            MessageEnvelope::encode($original),
            $this->messageFactory()
        );

        $this->assertNotNull($message);
        $this->assertSame('payload', $message->getBody());
        $this->assertSame(['attempt' => 2], $message->getProperties());
        $this->assertSame(['x-trace' => 'abc'], $message->getHeaders());
    }

    private function messageFactory(): callable
    {
        return static fn (string $body, array $properties, array $headers): MemoryMessage
            => new MemoryMessage($body, $properties, $headers);
    }
}
