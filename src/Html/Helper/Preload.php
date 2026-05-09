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

namespace Phalcon\Html\Helper;

use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Html\Link\Link;
use Phalcon\Html\Link\Serializer\Header;
use Phalcon\Http\ResponseInterface;

use function array_merge;

/**
 * Generates a <link rel="preload"> tag for resource hinting.
 * If a ResponseInterface is provided, also sets the HTTP Link header.
 */
class Preload extends AbstractHelper
{
    /**
     * @param EscaperInterface       $escaper
     * @param ResponseInterface|null $response
     */
    public function __construct(
        EscaperInterface $escaper,
        protected ?ResponseInterface $response = null
    ) {
        parent::__construct($escaper);
    }

    /**
     * @param string $href
     * @param string $type
     * @param array  $attributes
     *
     * @return string
     */
    public function __invoke(
        string $href,
        string $type = 'style',
        array $attributes = []
    ): string {
        $overrides = [
            'rel'  => 'preload',
            'href' => $href,
            'as'   => $type,
        ];

        unset($attributes['rel'], $attributes['href'], $attributes['as']);

        $overrides = array_merge($overrides, $attributes);

        if (null !== $this->response) {
            $link   = new Link('preload', $href, ['as' => $type]);
            $header = 'Link: ' . (new Header())->serialize([$link]);

            $this->response->setRawHeader($header);
        }

        return $this->selfClose('link', $overrides);
    }
}
