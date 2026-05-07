<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by sinbadxiii/cphalcon-auth
 * @link    https://github.com/sinbadxiii/cphalcon-auth
 */

declare(strict_types=1);

namespace Phalcon\Auth\Adapter;

use InvalidArgumentException;
use Phalcon\Auth\Adapter\Config\StreamAdapterConfig;
use Phalcon\Auth\Exception;
use Phalcon\Auth\Internal\Options;
use Phalcon\Contracts\Encryption\Security\Security;
use Phalcon\Support\Helper\Json\Decode;
use Phalcon\Traits\Php\FileTrait;

use function array_values;
use function is_array;

/**
 * JSON file-backed adapter.
 *
 * The file must contain a JSON array of user records:
 *   [{"id":1,"email":"a@b","password":"<hashed>"}, ...]
 *
 * @phpstan-import-type AuthUserRow from AbstractArrayAdapter
 *
 * @extends AbstractArrayAdapter<StreamAdapterConfig>
 */
class Stream extends AbstractArrayAdapter
{
    use FileTrait;

    public function __construct(Security $hasher, StreamAdapterConfig $config)
    {
        parent::__construct($hasher, $config);
    }

    public static function fromOptions(Security $hasher, array $options): static
    {
        return new static(
            $hasher,
            new StreamAdapterConfig(
                Options::requireString($options, 'file', 'stream adapter'),
                Options::stringOrNull($options, 'model')
            )
        );
    }

    /**
     * Loads and decodes the JSON users file. Re-read on every call — if you
     * need caching, wrap it.
     *
     * @phpstan-return list<AuthUserRow>
     *
     * @throws Exception
     */
    protected function loadUsers(): array
    {
        $path = $this->config->getFile();

        if (!$this->phpFileExists($path)) {
            throw Exception::streamFileDoesNotExist($path);
        }

        $contents = $this->phpFileGetContents($path);

        if ($contents === false) {
            throw Exception::streamFileCannotRead($path);
        }

        try {
            $data = (new Decode())($contents, true);
        } catch (InvalidArgumentException $ex) {
            throw Exception::streamFileNotValidJson($path, $ex);
        }

        if (!is_array($data)) {
            throw Exception::streamFileDoesNotContainJson($path);
        }

        /** @var list<AuthUserRow> $rows */
        $rows = array_values($data);

        return $rows;
    }
}
