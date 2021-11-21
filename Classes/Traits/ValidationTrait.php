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

use \Opis\JsonSchema\{
    Validator, ValidationResult, ValidationError, Schema
};
use Cjel\TemplatesAide\Utility\ArrayUtility;
use Sarhan\Flatten\Flatten;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * ValidationTrait
 */
trait ValidationTrait
{

    /**
     * is valid
     */
    protected $isValid = true;

    /**
     * errors
     */
    protected $errors = [];

    /**
     * errors labels
     */
    protected $errorLabels = [];

    /**
     * validate objects
     *
     * @param $input
     * @param schema
     * @return void
     */
    protected function convertInputBySchema($input, $schema)
    {
        $flatten = new Flatten();
        $schemaFlat = $flatten->flattenToArray($schema);
        $typesList = [];
        $formatsList = [];
        foreach ($schemaFlat as $index => $row) {
            $dataIndex = preg_replace(
                '/(\.)(properties\.|items\.)/',
                '$1',
                $index
            );
            $dataIndex = preg_replace(
                '/^properties\./',
                '',
                $dataIndex
            );
            $dataIndex = preg_replace(
                '/\.(type|format)$/',
                '',
                $dataIndex
            );
            if (substr($index, -5) == '.type') {
                $typesList[$dataIndex] = $row;
            }
            if (substr($index, -7) == '.format') {
                $formatsList[$dataIndex] = $row;
            }
        }
        foreach ($input as $index => $row) {
            $rowType = $typesList[$index];
            $formatType = $formatsList[$index];
            if (!$rowType) {
                continue;
            }
            switch ($rowType) {
            case 'integer':
                if (is_numeric($row)) {
                    settype($input[$index], $rowType);
                }
                break;
            case 'boolean':
                $testResult = filter_var(
                    $row,
                    FILTER_VALIDATE_BOOLEAN,
                    [FILTER_NULL_ON_FAILURE]
                );
                if ($testResult === true || $testResult === false) {
                    $input[$index] = $testResult;
                }
            case 'string':
                switch ($formatType) {
                case 'date':
                    $row = \DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        $row . ' 00:00:00',
                    );
                    break;
                }
                break;
            }
        }
        return $input;
    }

    /**
     * validate objects
     *
     * @param $input
     * @param schema
     * @return void
     */
    protected function validateAgainstSchema($input, $schema)
    {
        $validator = new Validator();
        $input = ArrayUtility::removeEmptyStrings($input);
        //@TODO make optional when usiing rest api
        //array_walk_recursive(
        //    $input,
        //    function (&$value) {
        //        if (filter_var($value, FILTER_VALIDATE_INT)) {
        //            $value = (int)$value;
        //        }
        //    }
        //);
        $input = ArrayUtility::toObject($input);
        $validationResult = $validator->dataValidation(
            $input,
            json_encode($schema),
            -1
        );
        if (!$validationResult->isValid()) {
            $this->isValid = false;
            $this->responseStatus = [400 => 'validationError'];
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
            //    $validationResult->getErrors(), false, 9, true
            //);
            foreach ($validationResult->getErrors() as $error){
                $field = implode('.', $error->dataPointer());
                if ($error->keyword() == 'required') {
                    $tmp = $error->dataPointer();
                    array_push($tmp, $error->keywordArgs()['missing']);
                    $field = implode('.', $tmp);
                }
                if ($error->keyword() == 'additionalProperties') {
                    foreach ($error->subErrors() as $subError) {
                        $this->errors[
                            implode('.', $subError->dataPointer())
                        ] = [
                            'keyword' => 'superfluos',
                        ];
                    }
                } else {
                    $this->errors[$field] = [
                        'keyword' => $error->keyword(),
                        'details' => $error->keywordArgs()
                    ];
                }
            }
        }
        return $validationResult;
    }

    /**
     * function to add validation error manually in the controller
     */
    protected function addValidationError(
        $field, $keyword, $overwrite = false
    ) {
        $this->isValid = false;
        $this->responseStatus = [400 => 'validationError'];
        if (!array_key_exists($field, $this->errors)
            || $overwrite == true
        ) {
            $this->errors[$field] = [
                'keyword' => $keyword,
            ];
            $this->errorLabels[$field] = $this->getErrorLabel(
                $field,
                $keyword
            );
        }
    }

    /**
     * gets error label based on field and keyword, uses predefined extensionkey
     */
    protected function getErrorLabel($field, $keyword) {
        $path = 'error.' . $field . '.' . $keyword;
        $errorLabel = $this->getTranslation($path);
        if ($errorLabel == null) {
            return $path;
        }
        return $errorLabel;
    }

    /**
     * shortcut to get translation
     *
     * @return void
     */
    protected function getTranslation($key, $arguments = null)
    {
        $translation = LocalizationUtility::translate(
            $key,
            $this->getExtensionKey(),
            $arguments
        );
        if ($translation) {
            return $translation;
        }
        $translation = LocalizationUtility::translate(
            $key,
            'site_templates',
            $arguments
        );
        if ($translation) {
            return $translation;
        }
        return null;
    }


}
