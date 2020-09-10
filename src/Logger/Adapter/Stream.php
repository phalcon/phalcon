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

namespace Phalcon\Logger\Adapter;

use LogicException;
use Phalcon\Logger\Exception;
use Phalcon\Logger\Item;

use function fclose;
use function fopen;
use function fwrite;
use function is_resource;
use function mb_strpos;

use const PHP_EOL;

/**
 * Phalcon\Logger\Adapter\Stream
 *
 * Adapter to store logs in plain text files
 *
 *```php
 * $logger = new \Phalcon\Logger\Adapter\Stream('app/logs/test.log');
 *
 * $logger->log('This is a message');
 * $logger->log(\Phalcon\Logger::ERROR, 'This is an error');
 * $logger->error('This is another error');
 *
 * $logger->close();
 *```
 *
 * @property resource|null $handler
 * @property string        $mode
 * @property string        $name
 * @property array         $options
 */
class Stream extends AbstractAdapter
{
    /**
     * Stream handler resource
     *
     * @var resource|null
     */
    protected $handler = null;

    /**
     * The file open mode. Defaults to 'ab'
     *
     * @var string
     */
    protected string $mode = 'ab';

    /**
     * Stream name
     *
     * @var string
     */
    protected string $name;

    /**
     * Path options
     *
     * @var array
     */
    protected array $options;

    /**
     * Stream constructor.
     *
     * @param string $name
     * @param array  $options
     *
     * @throws Exception
     */
    public function __construct(string $name, array $options = [])
    {
        $mode = $options['mode'] ?? 'ab';
        if (false !== mb_strpos($mode, 'r')) {
            throw new Exception('Adapter cannot be opened in read mode');
        }

        $this->name = $name;
        $this->mode = $mode;
    }

    /**
     * Closes the stream
     */
    public function close(): bool
    {
        $result = true;

        if (is_resource($this->handler)) {
            $result = $this->fclose($this->handler);
        }

        $this->handler = null;

        return $result;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Processes the message i.e. writes it to the file
     *
     * @param Item $item
     */
    public function process(Item $item): void
    {
        if (!is_resource($this->handler)) {
            $fileHandler = $this->fopen($this->name, $this->mode);

            if (!is_resource($fileHandler)) {
                $this->handler = null;

                throw new LogicException(
                    'The file "' .
                    $this->name .
                    '" cannot be opened with mode "' .
                    $this->mode .
                    '"',
                );
            }

            $this->handler = $fileHandler;
        }

        $formatter        = $this->getFormatter();
        $formattedMessage = (string) $formatter->format($item) . PHP_EOL;

        $this->fwrite($this->handler, $formattedMessage);
    }

    /**
     * Closes an open file pointer
     *
     * @link https://php.net/manual/en/function.fclose.php
     *
     * @param resource $handle
     *
     * @return bool
     */
    public function fclose($handle)
    {
        return fclose($handle);
    }

    /**
     * Opens file or URL
     *
     * @link https://php.net/manual/en/function.fopen.php
     *
     * @param string $filename
     * @param string $mode
     *
     * @return false|resource
     */
    protected function fopen(string $filename, string $mode)
    {
        return fopen($filename, $mode);
    }

    /**
     * Binary-safe file write
     *
     * @link https://php.net/manual/en/function.fwrite.php
     *
     * @param resource $handle
     * @param string   $data
     *
     * @return int|false
     */
    protected function fwrite($handle, string $data)
    {
        return fwrite($handle, $data);
    }
}
