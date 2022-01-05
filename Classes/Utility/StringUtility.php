<?php
namespace Cjel\TemplatesAide\Utility;

/***
 *
 * This file is part of the "" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2021 Philipp Dieter
 *
 ***/

use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 */
class StringUtility
{
    public static function convertToSaveString($string, $spaceCharacter = '-')
    {
        $csConvertor = GeneralUtility::makeInstance(CharsetConverter::class);
        $string = mb_strtolower($string, 'UTF-8');
        $string = strip_tags($string);
        $string = preg_replace(
            '/[ \t\x{00A0}\-+_]+/u',
            $spaceCharacter,
            $string
        );
        $string = $csConvertor->specCharsToASCII('utf-8', $string);
        $string = preg_replace(
            '/[^\p{L}0-9' . preg_quote($spaceCharacter) . ']/u',
            '',
            $string
        );
        $string = preg_replace(
            '/' . preg_quote($spaceCharacter) . '{2,}/',
            $spaceCharacter,
            $string
        );
        $string = trim($string, $spaceCharacter);
        return $string;
    }

    function getRandomString(
        int $length = 64,
        string $keyspace = null
    ): string {
        if (!$keyspace) {
            $keyspace = '0123456789'
                . 'abcdefghijklmnopqrstuvwxyz'
                . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    public static function checkAndfixUtf8($string){
        if (!mb_detect_encoding($string, 'UTF-8', true)) {
            $string = mb_convert_encoding($string , 'UTF-8', 'ASCII');
        }
        return $string;
    }

    public static function removeCHashIfOnlyParameter($uri) {
        $parsedUri = parse_url($uri);
        parse_str($parsedUri['query'], $parsedQuery);
        if (
            count($parsedQuery) == 1
            && array_key_exists('cHash', $parsedQuery)
        ) {
            unset($parsedQuery['cHash']);
        }
        $updatedQuery = http_build_query($parsedQuery);
        return $parsedUri['scheme'] . '://'
            . $parsedUri['host']
            . $parsedUri['path']
            . ($updatedQuery ? '?' . $updatedQuery : '');
    }
}
