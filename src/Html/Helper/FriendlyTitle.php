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

use Exception as BaseException;
use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Html\Exception;
use Phalcon\Support\Helper\Str\Friendly;

/**
 * Converts text to a URL-friendly slug.
 */
class FriendlyTitle extends AbstractHelper
{
    /**
     * @var Friendly
     */
    protected Friendly $friendly;

    /**
     * @param EscaperInterface $escaper
     */
    public function __construct(EscaperInterface $escaper)
    {
        parent::__construct($escaper);

        $this->friendly = new Friendly();
    }

    /**
     * @param string     $text
     * @param string     $separator
     * @param bool       $lowercase
     * @param mixed|null $replace
     *
     * @return string
     * @throws Exception
     */
    public function __invoke(
        string $text,
        string $separator = '-',
        bool $lowercase = true,
        array|string $replace = []
    ): string {
        try {
            return ($this->friendly)($text, $separator, $lowercase, $replace);
        } catch (BaseException $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
