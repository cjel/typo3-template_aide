<?php
namespace Cjel\TemplatesAide\Traits;

/***
 *
 * This file is part of the "Templates Aide" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2021 Philipp Dieter <philippdieter@attic-media.net>
 *
 ***/

use Cjel\TemplatesAide\Utility\ApiUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ClassSchema;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

/**
 * ValidationTrait
 */
trait DependencyInjectionTrait
{
    /*
     * storagePids
     */
    protected $storagePids = [];

    /*
     * objectManager
     */
    protected $objectManager = null;

    /**
     * @var BackendConfigurationManager
     */
    protected $configurationManager;

    /**
     * ApiUtility
     */
    protected $apiUtility = null;

    /*
     * storagePids
     */
    protected $settings = null;

    /*
     * logManager
     */
    protected $logManager = null;

    /*
     * logger
     */
    protected $importLogger = null;


    /*
     * returns the extensionkey set in the exended calss
     *
     * @return string
     */
    public function getExtensionKey() {
        return $this->extensionKey;
    }


    /**
     * Loads config and sets up extbase like dependecny injection
     *
     * @return void
     */
    public function setupDependencyInjection() {
        $this->objectManager = GeneralUtility::makeInstance(
            ObjectManager::class
        );
        $this->configurationManager = $this->objectManager->get(
            ConfigurationManagerInterface::class
        );
        $this->apiUtility = $this->objectManager->get(
            ApiUtility::class
        );
        $frameworkConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            $this->getExtensionKey()
        );
        $this->configurationManager->setConfiguration(
            $frameworkConfiguration
        );
        $this->settings = $frameworkConfiguration;
        $this->storagePids = explode(
            ',',
            str_replace(
                ' ',
                '',
                $frameworkConfiguration['persistence']['storagePid']
            )
        );
        $this->reflectionService = GeneralUtility::makeInstance(
            ReflectionService::class, GeneralUtility::makeInstance(
                CacheManager::class
            )
        );
        $classInfo = $this->reflectionService->getClassSchema(
            get_class($this)
        );
        foreach ($classInfo->getInjectMethods() as $method => $className) {
            $class = $this->objectManager->get(
                $className
            );
            $this->{$method}($class);
        }
    }

}
