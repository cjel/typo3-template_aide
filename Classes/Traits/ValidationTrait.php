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
        $input = $this->arrayRemoveEmptyStrings($input);
        //@TODO make optional when usiing rest api
        //array_walk_recursive(
        //    $input,
        //    function (&$value) {
        //        if (filter_var($value, FILTER_VALIDATE_INT)) {
        //            $value = (int)$value;
        //        }
        //    }
        //);
        $input = $this->arrayToObject($input);
        $validationResult = $validator->dataValidation(
            $input,
            json_encode($schema),
            -1
        );
        if (!$validationResult->isValid()) {
            $this->isValid = false;
            $this->responseStatus = [400 => 'validationError'];
            foreach ($validationResult->getErrors() as $error){
                $field = implode('.', $error->dataPointer());
                if ($error->keyword() == 'required') {
                    $tmp = $error->dataPointer();
                    array_push($tmp, $error->keywordArgs()['missing']);
                    $field = implode('.', $tmp);
                }
                if ($error->keyword() == 'additionalProperties') {
                    foreach ($error->subErrors() as $subError) {
                        $this->errors[$subError->dataPointer()[0]] = [
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
     * remove empty strings
     */
    public function arrayRemoveEmptyStrings($array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->arrayRemoveEmptyStrings($value);
            } else {
                if (is_string($value) && !strlen($value)) {
                    unset($array[$key]);
                }
            }
        }
        unset($value);
        return $array;
    }

    /**
     * function arrayTObject
     */
    public static function arrayToObject($array) {
        if (is_array($array)) {
            return (object) array_map([__CLASS__, __METHOD__], $array);
        } else {
            return $array;
        }
    }

}
