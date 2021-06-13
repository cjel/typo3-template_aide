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
class TcaUtility
{
    /**
     * fills object from array
     *
     * @return void
     */
    public static function configureSelect(
        &$tca, $column, $options, $extensionKey = null
    ) {
        foreach ($options as &$option) {
            $translation = self::getTranslation(
                'option.' . $option[0], $extensionKey
            );
            if ($translation) {
                $option[0] = $translation;
            }
        }
        $tca['columns'][$column]['config']['type']       = 'select';
        $tca['columns'][$column]['config']['renderType'] = 'selectSingle';
        $tca['columns'][$column]['config']['size']       = 6;
        $tca['columns'][$column]['config']['appearance'] = [];
        $tca['columns'][$column]['config']['items']      = $options;
    }

    /**
     * shortcut to get translation
     *
     * @return void
     */
    public static function getTranslation($key, $extensionKey)
    {
        if ($extensionKey) {
            $translation = LocalizationUtility::translate(
                $key,
                $extensionKey
            );
            if ($translation) {
                return $translation;
            }
        }
        $translation = LocalizationUtility::translate(
            $key,
            'site_templates'
        );
        if ($translation) {
            return $translation;
        }
        return null;
    }
}
