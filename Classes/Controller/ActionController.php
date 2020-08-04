<?php
namespace Cjel\TemplatesAide\Controller;

/***
 *
 * This file is part of the "Templates Aide" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018 Philipp Dieter <philippdieter@attic-media.net>
 *
 ***/

use \Opis\JsonSchema\{
    Validator, ValidationResult, ValidationError, Schema
};
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController as BaseController;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ActionController extends BaseController
{

    /*
     * page type
     */
    protected $pageType = null;

    /*
     * content object uid
     */
    protected $contentObjectUid = null;

    /*
     * cacheManager
     */
    protected $cacheManager = null;

    /*
     * cache
     */
    protected $cache = null;

    /**
     * data mapper
     */
    protected $dataMapper = null;

    /*
     * logManager
     */
    protected $logManager = null;

    /*
     * logger
     */
    protected $importLogger = null;

    /*
     * logger
     */
    protected $generalLogger = null;

    /**
     * request body
     * will only be set if page request action is post
     */
    protected $requestBody = null;

    /**
     * page type for ajax requests
     */
    protected $ajaxPageType = 5000;

    /**
     * response stus
     */
    protected $responseStatus = 200;

    /**
     * redirect url
     */
    protected $redirect = null;

    /**
     * errors
     */
    protected $errors = [];

    /**
     * errors labels
     */
    protected $errorLabels = [];

    /**
     * ajaxEnv
     */
    protected $ajaxEnv = [];

    /**
     * @var \TYPO3\CMS\Extbase\Service\ExtensionService
     */
    protected $extensionService;

    /**
     * uribuilder
     */
    protected $uriBuilder = null;

    /**
     * @param \TYPO3\CMS\Extbase\Service\ExtensionService $extensionService
     */
    public function injectExtensionService(ExtensionService $extensionService)
    {
        $this->extensionService = $extensionService;
    }

    /*
     * initialize action
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->pageType = GeneralUtility::_GP('type');
        if (!is_numeric($this->pageType)) {
            $this->pageType = 0;
        }
        if ($this->request->getMethod() == 'POST') {
            $this->requestBody = json_decode(
                file_get_contents('php://input')
            );
        }
        $this->contentObjectUid =
            $this->configurationManager->getContentObject()->data['uid'];
        $this->cacheManager = $this->objectManager->get(
            CacheManager::class
        );
        //$this->cache = $this->cacheManager->getCache(
        //    'tobereplaced' //TODO: Replaceme
        //);
        $this->logManager = $this->objectManager->get(
            LogManager::Class
        );
        $this->importLogger = $this->logManager->getLogger(
            'importLogger'
        );
        $this->generalLogger = $this->logManager->getLogger(
            __CLASS__
        );
        $this->dataMapper = $this->objectManager->get(
            DataMapper::Class
        );
        $this->arguments->addNewArgument('step', 'string', false, false);
    }

    /**
     * shortcut to get to know if request is submittet via post
     *
     * @return void
     */
    protected function isPost()
    {
        if ($this->request->getMethod() == 'POST'){
            return true;
        }
        return false;
    }

    /**
     * shortcut to get to know if request is submittet via post and specific
     * step is set
     *
     * @return void
     */
    protected function isPostStep(
        $testStep = null
    ) {
        $step = null;
        if ($this->arguments->hasArgument('step')){
            $step = $this->arguments->getArgument('step')->getValue();
        }
        if (
            $this->request->getMethod() == 'POST'
            && $step == $testStep
        ){
            return true;
        }
        return false;
    }

    /**
     * shortcut to get translation
     *
     * @return void
     */
    protected function getTranslation($key, $arguments = null)
    {
        return LocalizationUtility::translate(
            $key,
            'tobereplaced', //TODO: Replace me
            $arguments
        );
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
     * function to add validation error manually in the controller
     */
    protected function addValidationError($field, $keyword) {
        $this->responseStatus = [400 => 'validationError'];
        $this->errors[$field] = [
            'keyword' => $keyword,
        ];
        $this->errorLabels[$field] = $this->getErrorLabel(
            $field,
            $keyword
        );
    }

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

    public static function arrayToObject($array) {
        if (is_array($array)) {
            return (object) array_map([__CLASS__, __METHOD__], $array);
        } else {
            return $array;
        }
    }

    /**
     * validate objects
     *
     * @param $input
     * @param schema
     * @return void
     */
    protected function validateInput($input, $schema)
    {
        $validator = new Validator();
        $input = $this->arrayRemoveEmptyStrings($input);
        $input = $this->arrayToObject($input);
        $validationResult = $validator->dataValidation(
            $input,
            json_encode($schema),
            -1
        );
        if (!$validationResult->isValid()) {
            $this->responseStatus = [400 => 'validationError'];
            foreach ($validationResult->getErrors() as $error){
                $errorLabel = null;
                $field = implode('.', $error->dataPointer());
                if ($error->keyword() == 'required') {
                    $tmp = $error->dataPointer();
                    array_push($tmp, $error->keywordArgs()['missing']);
                    $field = implode('.', $tmp);
                }
                if ($error->keyword() == 'additionalProperties') {
                    continue;
                }
                $this->errors[$field] = [
                    'keyword' => $error->keyword(),
                    'details' => $error->keywordArgs()
                ];
                if ($error->keyword() != 'required') {
                    $errorLabel = $this->getTranslation(
                        'error.' . $field . '.' . $error->keyword()
                    );
                    //if ($errorLabel == null) {
                    //    $errorLabel = $this->getTranslation(
                    //        'error.' . $field . '.required'
                    //    );
                    //}
                    if ($errorLabel == null) {
                        $errorLabel = 'error.'
                            . $field
                            . '.'
                            . $error->keyword();
                    }
                    $this->errorLabels[$field] = $errorLabel;
                } else {
                    $errorLabel = $this->getTranslation(
                        'error.' . $field . '.required'
                    );
                    if ($errorLabel == null) {
                        $errorLabel = 'error.'
                            . $field
                            . '.'
                            . $error->keyword();
                    }
                    $this->errorLabels[$field] = $errorLabel;
                }
            }
        }
        return $validationResult->isValid();
    }

    /**
     * returns plugin namespace to build js post request
     *
     * @return void
     */
    protected function getPluginNamespace()
    {
        $extensionName = $this->request->getControllerExtensionName();
        $pluginName = $this->request->getPluginName();
        return $this->extensionService->getPluginNamespace(
            $extensionName,
            $pluginName
        );
    }

    /**
     * sets vars which are needed by the ajax requests
     *
     * @return void
     */
    protected function setAjaxEnv($object = null)
    {
        if ($object == null) {
            $object = $this->arguments->getArgumentNames()[0];
        }
        $uri = $this->getControllerContext()
            ->getUriBuilder()
            ->reset()
            ->setCreateAbsoluteUri(true)
            ->setTargetPageType($this->ajaxPageType)
            ->setArguments(['cid' => $this->contentObjectUid])
            ->uriFor($this->request->getControllerActionName());
        $this->ajaxEnv = [
            'uri' => $uri,
            'object' => $object,
            'namespace' => $this->getPluginNamespace(),
        ];
    }

    /**
     * return function, checks for page type and decides
     *
     * @param array $result
     * @return void
     */
    protected function returnFunction($result = [], $errorStatus = null)
    {
        $this->setAjaxEnv();
        if ($result == null) {
            $result = [];
        }
        if (!empty($this->errors)) {
            $result = array_merge(
                $result,
                ['errors' => $this->errors]
            );
        }
        if (!empty($this->errorLabels)) {
            $result = array_merge(
                $result,
                ['errorLabels' => $this->errorLabels]
            );
        }
        if (is_array($this->responseStatus)) {
            $result = array_merge(
                $result,
                ['errorType' => reset($this->responseStatus)]
            );
        }
        if ($this->pageType) {
            if (is_array($this->responseStatus)) {
                $this->response->setStatus(
                    array_key_first($this->responseStatus)
                );
            } else {
                $this->response->setStatus($this->responseStatus);
            }
            $this->response->setHeader(
                'Content-type',
                'application/json'
            );
            unset($result['data']);
            if ($this->redirect) {
                $result['redirect'] = $this->redirect;
            }
            return json_encode($result);
        }
        $result = array_merge($result, ['cid' => $this->contentObjectUid]);
        if (!empty($this->ajaxEnv)) {
            $result = array_merge(
                $result,
                ['ajaxEnv' => $this->ajaxEnv]
            );
        }
        $this->view->assignMultiple($result);
    }

}
