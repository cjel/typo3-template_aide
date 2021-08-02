<?php
namespace Cjel\TemplatesAide\Utility;

/***
 *
 * This file is part of the "Templates Aide" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2021 Philipp Dieter <philipp.dieter@attic-media.net>
 *
 ***/

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 *
 */
class InterfaceUtility
{

    /**
     * Get all interface constants per prefix
     */
    public static function parseInterfaceConstants(
        $interfaceClass, $prefix
    ) {
        $constants = (new \ReflectionClass($interfaceClass))
            ->getConstants();
        $constants = array_filter($constants, function($key) use ($prefix) {
            if (substr($key, 0, strlen($prefix) + 1)
                == strtoupper($prefix) . '_'
            ) {
                return true;
            }
        }, ARRAY_FILTER_USE_KEY);
        return array_values($constants);
    }
}
