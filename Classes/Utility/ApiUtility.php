<?php
namespace Cjel\TemplatesAide\Utility;

/***
 *
 * This file is part of the "" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019 Philipp Dieter 
 *
 ***/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ImageService;

/**
 *
 */
class ApiUtility
{
    /**
     * @var \TYPO3\CMS\Extbase\Service\ImageService
     */
    protected $imageService;

    /*
     * objectManager
     */
    protected $objectManager = null;

    /*
     *
     */
    public function queryResultToArray(
        $queryResult,
        $additionalAttributes = [],
        $mapping              = [],
        $rootRowClass         = null
    ) {
        $httpHost = GeneralUtility::getIndpEnv('HTTP_HOST');
        $requestHost = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');
        $this->objectManager = GeneralUtility::makeInstance(
            ObjectManager::class
        );
        $this->imageService = $this->objectManager->get(
            imageService::class
        );
        if (1 == 0) {
            $rows = $queryResult->toArray();
        } else {
            $rows = $queryResult;
        }
        $result = [];
        foreach ($rows as $row) {
            if (!$row) {
                continue;
            }
            $rowClass = (new \ReflectionClass($row))->getShortName();
            $methods = get_class_methods($row);
            $rowResult = [];
            // Prevent endless recursion?
            //@todo: improve, dont rely on classes
            if ($rootRowClass == $rowClass) {
                $rowResult['uid'] = $row->getUid();
                $result[] = $rowResult;
                continue;
            }

            $propertieResults = [];
            foreach ($methods as $method) {
                if (substr($method, 0, 3) === 'get') {
                    $methodResult = call_user_func([$row, $method]);
                    $attributeName = lcfirst(substr($method, 3));
                    $propertieResults[$attributeName] = $methodResult;
                }
            }
            foreach ((array)$additionalAttributes as $attribute => $value) {
                if (
                    !array_key_exists($attribute, $propertieResults)
                    && $row->$attribute
                ) {
                    $propertieResults[$attribute]
                        = $row->$attribute;
                }
            }
            foreach ($propertieResults as $attributeName => $methodResult) {
                if (gettype($methodResult) == 'string'
                    || gettype($methodResult) == 'integer'
                    || gettype($methodResult) == 'boolean'
                ) {
                    $rowResult[$attributeName] = $methodResult;
                }
            }
            // ---
            if (array_key_exists($rowClass, $mapping)) {
                foreach ($mapping[$rowClass] as $attributeName => $function) {
                    $rowResult[$attributeName] = $function(
                        $rowResult[$attributeName],
                        $row
                    );
                }
            }
            // ---
            foreach ($propertieResults as $attributeName => $methodResult) {
                if (gettype($methodResult) == 'object'
                    && get_class($methodResult) == 'DateTime'
                ) {
                    $rowResult[$attributeName] = $methodResult->format('c');
                }
                if (gettype($methodResult) == 'object'
                    && get_class($methodResult)
                        == 'TYPO3\CMS\Extbase\Persistence\ObjectStorage'
                ) {
                    if ($rootRowClass == null) {
                        $nextLevelClass = $rowClass;
                    } else {
                        $nextLevelClass = $rootRowClass;
                    }
                    $rowResult[$attributeName] = self::queryResultToArray(
                        $methodResult,
                        $additionalAttributes[$attributeName],
                        $mapping,
                        $nextLevelClass
                    );
                }
                //@todo: build unversal solution without fixed class name
                if (gettype($methodResult) == 'object'
                    && get_class($methodResult)
                        == 'Glanzstueck\FesCustomerportal\Domain\Model'
                            .  '\FrontendUser'
                ) {
                    if ($rootRowClass == null) {
                        $nextLevelClass = $rowClass;
                    } else {
                        $nextLevelClass = $rootRowClass;
                    }
                    $rowResult[$attributeName] = self::queryResultToArray(
                        [$methodResult],
                        $additionalAttributes[$attributeName],
                        $mapping,
                        $nextLevelClass
                    )[0];
                }
                if (gettype($methodResult) == 'object'
                    && get_class($methodResult)
                        == 'TYPO3\CMS\Extbase\Persistence\Generic'
                            . '\LazyObjectStorage'
                ) {
                    $rowResult[$attributeName] = [];
                    foreach ($methodResult as $object) {
                        $publicUrl = $object->getOriginalResource()
                            ->getPublicUrl();
                        $absoluteUrl = $requestHost
                            . '/'
                            . $publicUrl;
                        $imagePreview = $this->imageService->getImage(
                            $publicUrl,
                            null,
                            0
                        );
                        $processingInstructionsPreview = array(
                            //'width' => '1024c',
                            //'height' => '768c',
                            //'minWidth' => $minWidth,
                            //'minHeight' => $minHeight,
                            'maxWidth' => '1024',
                            'maxHeight' => '768',
                            //'crop' => $crop,
                        );
                        $processedImagePreview = $this->imageService
                           ->applyProcessingInstructions(
                               $imagePreview,
                               $processingInstructionsPreview
                           );
                        $publicUrlPreview = $this->imageService
                             ->getImageUri(
                                $processedImagePreview
                            );
                        $absoluteUrlPreview = $requestHost
                            . '/'
                            . $publicUrlPreview;
                        $rowResult[$attributeName][] = [
                            'uid' => $object->getUid(),
                            'publicUrl' => $publicUrl,
                            'absoluteUrl' => $absoluteUrl,
                            'publicUrlPreview' => $publicUrlPreview,
                            'absoluteUrlPreview' => $absoluteUrlPreview,
                        ];
                    }
                }
            }
            $result[] = $rowResult;
        }
        return $result;
    }
}
