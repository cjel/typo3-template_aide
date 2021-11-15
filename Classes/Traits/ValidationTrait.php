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
