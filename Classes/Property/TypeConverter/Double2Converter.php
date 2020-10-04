<?php
namespace Cjel\TemplatesAide\Property\TypeConverter;

/***
 *
 * This file is part of the "Templates Aide" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Philipp Dieter <philippdieter@attic-media.net>
 *
 ***/

use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

/**
 * Converter which transforms arrays to arrays.
 */
class Double2Converter extends AbstractTypeConverter
{
    /**
     * @var array<string>
     */
    protected $sourceTypes = ['integer', 'string'];

    /**
     * @var string
     */
    protected $targetType = 'double2';

    /**
     * @var int
     */
    protected $priority = 10;

    /**
     * @param mixed $source
     * @param string $targetType
     * @return bool
     * @internal only to be used within Extbase, not part of TYPO3 Core API.
     */
    public function canConvertFrom($source, $targetType)
    {
        return is_string($source) ||is_integer($source);
    }

    /**
     * Copied from
     * TYPO3\CMS\Core\DataHandling\DataHandler::checkValue_input_Eval
     *
     * @param string|array $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface $configuration
     * @return array
     */
    public function convertFrom(
        $source,
        $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null
    ) {
        $value = preg_replace('/[^0-9,\\.-]/', '', $source);
        $negative = $value[0] === '-';
        $value = strtr($value, [',' => '.', '-' => '']);
        if (strpos($value, '.') === false) {
            $value .= '.0';
        }
        $valueArray = explode('.', $value);
        $dec = array_pop($valueArray);
        $value = implode('', $valueArray) . '.' . $dec;
        if ($negative) {
            $value *= -1;
        }
        $value = number_format($value, 2, '.', '');
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
        //    $source, null, 3
        //);
        //if (is_string($source)) {
        //    if ($source === '') {
        //        $source = [];
        //    }
        //}
        return $value;
    }
}
