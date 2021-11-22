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

/**
 *
 */
class ArrayUtility
{
    /**
     * function arrayTobject
     */
    public static function toObject($array) {
        if (is_array($array)) {
            if (self::isAssoc($array)) {
                return (object) array_map([__CLASS__, __METHOD__], $array);
            } else {
                return array_map([__CLASS__, __METHOD__], $array);
            }
        } else {
            return $array;
        }
    }

    /**
     * remove empty strings
     */
    public static function removeEmptyStrings($array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = self::removeEmptyStrings($value);
            } else {
                if (is_string($value) && !strlen($value)) {
                    unset($array[$key]);
                }
            }
        }
        unset($value);
        return $array;
    }


    /**
     *
     */
    public static function isAssoc(array $arr) {
        if (array() === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}
