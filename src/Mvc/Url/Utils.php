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

namespace Phalcon\Mvc\Url;

use function count;
use function is_string;
use function ord;
use function strlen;
use function substr;

class Utils
{
    /**
     * @param mixed $path
     *
     * @return string
     */
    public static function getUri(mixed $path): string
    {
        if (!is_string($path)) {
            return '';
        }

        $pathLength = strlen($path);
        if ($pathLength > 0) {
            for ($i = $pathLength; $i > 0; $i--) {
                $ch = ord($path[$i - 1]);
                if ($ch === ord('/') || $ch === ord('\\')) {
                    return substr($path, $i);
                }
            }
        }

        return '';
    }

    /**
     * @param bool   $named
     * @param array  $paths
     * @param array  $replacements
     * @param int    $position
     * @param string $pattern
     * @param int    $start
     * @param int    $end
     *
     * @return string|null
     */
    public static function replaceMarker(
        bool $named,
        array $paths,
        array $replacements,
        int &$position,
        string $pattern,
        int $start,
        int $end
    ): string | null {
        $item     = null;
        $variable = null;
        $notValid = 0;

        if ($named) {
            // Simulating pointer arithmetic for length calculation
            $length = $end - $start - 1;
            $item   = substr($pattern, $start + 1, $length);

            for ($j = 0; $j < $length; $j++) {
                $ch = ord($item[$j]);
                if ($ch === 0) {
                    $notValid = 1;
                    break;
                }
                if ($j === 0 && !(($ch >= ord('a') && $ch <= ord('z')) || ($ch >= ord('A') && $ch <= ord('Z')))) {
                    $notValid = 1;
                    break;
                }
                if (
                    ($ch >= ord('a') && $ch <= ord('z')) ||
                    ($ch >= ord('A') && $ch <= ord('Z')) ||
                    ($ch >= ord('0') && $ch <= ord('9')) ||
                    $ch == ord('-') ||
                    $ch == ord('_') ||
                    $ch == ord(':')
                ) {
                    if ($ch === ord(':')) {
                        $variableLength = strlen($item) - $j;
                        $variable       = substr($pattern, $start + 1, $variableLength);
                        break;
                    }
                } else {
                    $notValid = 1;
                    break;
                }
            }
        }

        if (!$notValid) {
            if (isset($paths[$position])) {
                if ($named) {
                    if ($variable) {
                        $item = $variable;
                    }
                    if (isset($replacements[$item])) {
                        $position++;
                        return $replacements[$item];
                    }
                } else {
                    $zv = $paths[$position];
                    if (is_string($zv)) {
                        if (isset($replacements[$zv])) {
                            $position++;
                            return $replacements[$zv];
                        }
                    }
                }
            }
            $position++;
        }

        if ($item !== null && isset($replacements[$item])) {
            $position++;
            return $replacements[$item];
        }

        return null;
    }

    /**
     * Replaces placeholders and named variables with their corresponding values in an array
     *
     * @param string $pattern
     * @param array  $paths
     * @param array  $replacements
     *
     * @return string|null
     */
    public static function replacePaths(string $pattern, array $paths, array $replacements): string | null
    {
        if (strlen($pattern) === 0) {
            return null;
        }

        if (count($paths) === 0) {
            return null;
        }

        $i = 0;
        if ($pattern[$i] == '/') {
            $i = 1;
        }

        $patternLength      = strlen($pattern);
        $bracketCount       = 0;
        $parenthesesCount   = 0;
        $lookingPlaceholder = 0;
        $intermediate       = 0;
        $position           = 1;
        $marker             = null;
        $returnStr          = '';
        for (; $i < $patternLength; $i++) {
            $ch = ord($pattern[$i]);
            if ($ch === 0) {
                break;
            }

            if ($parenthesesCount === 0 && $lookingPlaceholder === 0) {
                if ($ch === ord('{')) {
                    if ($bracketCount === 0) {
                        $marker       = $i;
                        $intermediate = 0;
                    }
                    $bracketCount++;
                }
                if ($ch === ord('}')) {
                    $bracketCount--;
                    if ($intermediate > 0) {
                        if ($bracketCount === 0) {
                            $replace = self::replaceMarker(
                                true,
                                $paths,
                                $replacements,
                                $position,
                                $pattern,
                                $marker,
                                $i
                            );
                            if ($replace) {
                                $returnStr .= $replace;
                            }
                            continue;
                        }
                    }
                }
            }

            if ($bracketCount === 0 && !$lookingPlaceholder) {
                if ($ch === ord('(')) {
                    if ($parenthesesCount == 0) {
                        $marker       = $i;
                        $intermediate = 0;
                    }
                    $parenthesesCount++;
                }
                if ($ch === ord(')')) {
                    $parenthesesCount--;
                    if ($intermediate > 0) {
                        if ($parenthesesCount == 0) {
                            $replace = self::replaceMarker(
                                false,
                                $paths,
                                $replacements,
                                $position,
                                $pattern,
                                $marker,
                                $i
                            );
                            if ($replace) {
                                $returnStr .= $replace;
                            }
                            continue;
                        }
                    }
                }
            }

            if ($bracketCount === 0 && $parenthesesCount === 0) {
                if ($lookingPlaceholder) {
                    if ($intermediate > 0) {
                        if ($ch < ord('a') || $ch > ord('z') || $i === ($patternLength - 1)) {
                            $replace = self::replaceMarker(
                                false,
                                $paths,
                                $replacements,
                                $position,
                                $pattern,
                                $marker,
                                $i
                            );

                            if ($replace) {
                                $returnStr .= $replace . (($i === $patternLength - 1) ? '' : $pattern[$i]);
                            }
                            $lookingPlaceholder = 0;
                            continue;
                        }
                    }
                } else {
                    if ($ch === ord(':')) {
                        $lookingPlaceholder = 1;
                        $marker             = $i;
                        $intermediate       = 0;
                    }
                }
            }

            if ($bracketCount > 0 || $parenthesesCount > 0 || $lookingPlaceholder) {
                $intermediate++;
            } else {
                $returnStr .= $pattern[$i];
            }
        }

        return $returnStr !== '' ? $returnStr : '';
    }
}
