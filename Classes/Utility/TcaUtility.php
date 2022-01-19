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

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        &$tca, $column, $element, $options, $extensionKey = null
    ) {
        foreach ($options as &$option) {
            $translationKey = "option.$element.$column.$option[0]";
            $translation = self::getTranslation(
                $translationKey,
                $extensionKey
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
     * fills object from array
     *
     * @return void
     */
    public static function configureSelectFromArray(
        &$tca,
        $column,
        $element,
        $options,
        $extensionKey = null,
        $addEmpty     = false
    ) {
        $items = [];
        if ($addEmpty) {
            $items[] = ['-', ''];
        }
        foreach ($options as $option) {
            $translationKey = "option.$element.$column.$option";
            $translation = self::getTranslation(
                $translationKey,
                $extensionKey
            );
            if ($translation) {
                $items[] = [$translation, $option];
            } else {
                $items[] = [$translationKey, $option];
            }
        }
        $tca['columns'][$column]['config']['type']       = 'select';
        $tca['columns'][$column]['config']['renderType'] = 'selectSingle';
        $tca['columns'][$column]['config']['size']       = 6;
        $tca['columns'][$column]['config']['appearance'] = [];
        $tca['columns'][$column]['config']['items']      = $items;
    }

    /**
     * shortcut to get translation
     *
     * @return void
     */
    public static function getTranslation($key, $extensionKey)
    {
        if (version_compare(TYPO3_branch, '10.0', '>=')) {
            if (!$extensionKey) {
                $extensionKey = 'site_templates';
            }
            return implode([
                'LLL:EXT:',
                 $extensionKey,
                 '/Resources/Private/Language/locallang_db.xlf:',
                 $key
            ]);
        } else {
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

    /**
     * change position of element in fieldlist
     *
     * @return array
     */
    public static function listMoveFieldAfterField(
        $fieldList, $field, $fieldAfter
    ) {
        $fieldlist = GeneralUtility::trimExplode(
            ',',
            $fieldList
        );
        unset($fieldlist[(array_search($field, $fieldlist))]);
        array_splice(
            $fieldlist,
            array_search($fieldAfter, $fieldlist) + 1,
            0,
            $field
        );
        return implode(', ', $fieldlist);
    }

    /**
     * remove element from fieldlist
     *
     * @return array
     */
    public static function listRemoveField(
        $fieldList, $field
    ) {
        $fieldlist = GeneralUtility::trimExplode(
            ',',
            $fieldList
        );
        unset($fieldlist[(array_search($field, $fieldlist))]);
        return implode(', ', $fieldlist);
    }

}
