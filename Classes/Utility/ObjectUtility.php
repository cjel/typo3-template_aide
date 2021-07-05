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

use Cjel\TemplatesAide\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 *
 */
class ObjectUtility
{
    /**
     * fills object from array
     *
     * @return void
     */
    public static function fromArray(
        &$object, $data, $storageMapping = []
    ) {
        $objectManager = GeneralUtility::makeInstance(
            ObjectManager::class
        );
        $reflectionClass = new \ReflectionClass(get_class($object));
        foreach ($data as $property => $value) {
            $methodName = 'set' . ucfirst($property);
            if (!$reflectionClass->hasMethod($methodName)) {
                continue;
            }
            $method = $reflectionClass->getMethod($methodName);
            $params = $method->getParameters();
            $methodType = $params[0]->getType();
            if (is_array($value)) {
                if (array_key_exists($property, $storageMapping)) {
                    $storage = $object->_getProperty($property);
                    $storageUpdated = $objectManager->get(
                        ObjectStorage::class
                    );
                    foreach ($value as $row) {
                        $item = null;
                        if ($row['uid']) {
                            foreach ($storage as $storageIitem) {
                                if ($storageIitem->getUid() == $row['uid']) {
                                    $item = $storageIitem;
                                }
                            }
                            $storageUpdated->attach($item);
                        }
                        if (!$item) {
                            $item = new $storageMapping[$property]();
                            $storageUpdated->attach($item);
                        }
                        self::fromArray($item, $row);
                    }
                    $object->_setProperty($property, $storageUpdated);
                }
            } else {
                if ($methodType == null) {
                    $value = StringUtility::checkAndfixUtf8($value);
                    $object->_setProperty($property, $value);
                } else {
                    $typeParts = explode('\\', (string)$methodType);
                    $typeParts[count($typeParts) - 2] = 'Repository';
                    $repositoryClass = join('\\', $typeParts);
                    $repositoryClass .= 'Repository';
                    if (class_exists($repositoryClass)) {
                        $repository = $objectManager->get($repositoryClass);
                        $relatedObject = $repository->findByUid($value);
                        $object->_setProperty($property, $relatedObject);
                    }
                }
            }
        }
    }
}
