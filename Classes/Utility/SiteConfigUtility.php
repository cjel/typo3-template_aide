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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Utility to work with site config
 */
class SiteConfigUtility
{
    /**
     * Gets site config by typoscript path
     *
     * @var string $path
     * @return string
     */
    public static function getByPath(
        $path,
        $limitToSiteConfig = true
    ) {
        $pathParts = explode('.', $path);
        $objectManager = GeneralUtility::makeInstance(
            ObjectManager::class
        );
        $configurationManager = $objectManager->get(
            ConfigurationManagerInterface::class
        );
        $typoscript = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $typoscript = GeneralUtility::removeDotsFromTS($typoscript);
        $siteConfig = $typoscript;
        if ($limitToSiteConfig) {
            $siteConfig = $typoscript['config']['site'];
        }
        $current = &$siteConfig;
        foreach ($pathParts as $key) {
            $current = &$current[$key];
        }
        if (is_array($current)
            && array_key_exists('value', $current)
            && count($current) === 1
        ) {
            $current = $current['value'];
        }
        return $current;
    }
}
