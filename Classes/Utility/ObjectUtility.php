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
    public static function fromArray(&$object, $data) {
        foreach ($data as $property => $value) {
            $object->_setProperty($property, $value);
        }
    }
}
