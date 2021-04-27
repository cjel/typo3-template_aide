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

use Cjel\TemplatesAide\Traits\ValidationTrait;
use Cjel\TemplatesAide\Utility\ApiUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController as BaseController;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ActionController extends BaseController
{

    use ValidationTrait {
        validateAgainstSchema as traitValidateAgainstSchema;
    }

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
     * component mode, used in frontend
     */
    protected $componentMode = 'default';

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
     * general config
     */
    protected $config = [];

    /**
     * propertyMappginConfigrtationBuolder
     */
    protected $propertyMapperConfigurationBuilder;

    /**
     * @param \TYPO3\CMS\Extbase\Service\ExtensionService $extensionService
     */
    public function injectExtensionService(ExtensionService $extensionService)
    {
        $this->extensionService = $extensionService;
    }

    /**
     * propertyMapper
     *
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @param
     */
    public function injectPropertyMapper(
        PropertyMapper $propertyMapper
    ) {
        $this->propertyMapper = $propertyMapper;
    }

    /**
     * propertyMappingConfigurationBuilder
     *
     * @var PropertyMappingConfigurationBuilder
     */
    protected $propertyMappingConfigurationBuilder;

    /**
     * @param
     */
    public function injectPropertyMappingConfigurationBuilder(
        PropertyMappingConfigurationBuilder $propertyMappingConfigurationBuilder
    ) {
        $this->propertyMappingConfigurationBuilder
            = $propertyMappingConfigurationBuilder;
    }

    /**
     * ApiUtility
     *
     * @var ApiUtility
     */
    protected $apiUtility;

    /**
     * @param
     */
    public function injectApiUtility(
        ApiUtility $apiUtility
    ) {
        $this->apiUtility = $apiUtility;
    }

    /*
     * initialize action
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->config = GeneralUtility::removeDotsFromTS(
            $GLOBALS['TSFE']->config['config']
        );
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
        $this->arguments->addNewArgument('submit', 'string', false, false);
    }

    /**
     * returns an instance of uribuilder
     */
    public function getUriBuilder()
    {
        return $this->objectManager->get(
            UriBuilder::class
        );
    }

    /**
     * shortcut
     *
     * @return void
     */
    protected function getExtensionKey()
    {
        return $this->request->getControllerExtensionKey();
    }

    /**
     * shortcut function to recieve typoscript
     *
     * @return array
     */
    protected function getPluginTyposcript()
    {
        return $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            str_replace('_', '', $this->getExtensionKey),
            $this->request->getPluginName()
        );
    }

    /**
     * shortcut function to recieve typoscript
     *
     * @return array
     */
    protected function getTyposcript()
    {
        return $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
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
        $testValue = null
    ) {
        return $this->isPostAndArgumentMatches('step', $testValue);
    }

    /**
     *
     */
    protected function isPostSubmit(
        $testValue = null
    ) {
        return $this->isPostAndArgumentMatches('submit', $testValue);
    }

    /**
     *
     */
    protected function isPostAndArgumentMatches(
        $argument,
        $testValue
    ) {
        $value = null;
        if ($this->arguments->hasArgument($argument)){
            $value = $this->arguments->getArgument($argument)->getValue();
        }
        if (
            $this->request->getMethod() == 'POST'
            && $value == $testValue
        ){
            return true;
        }
        return false;
    }

    protected function einTest(
        $actions = []
    ) {

    }

    /**
     *
     */
    protected function getPostSubmit()
    {
        return explode('#', $this->getPostValue('submit'))[0];
    }

    /**
     *
     */
    protected function getPostSubmitItem()
    {
        return explode('#', $this->getPostValue('submit'))[1];
    }

    /**
     *
     */
    protected function getPostValue(
        $argument
    ) {
        if ($this->arguments->hasArgument($argument)){
            return $this->arguments->getArgument($argument)->getValue();
        }
        return false;
    }

    /**
     *
     */
    protected function getGetValue(
        $argument
    ) {
        if (GeneralUtility::_GP($argument)) {
            return GeneralUtility::_GP($argument);
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
    protected function addValidationError(
        $field, $keyword, $overwrite = false
    ) {
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
     * legacy function to prevent beaking old code
     *
     * @deprecated
     */
    protected function validateInput($input, $schema)
    {
        return $this->validateAgainstSchema($input, $schema, true);
    }

    /**
     * validate input and translate error messages
     */
    protected function validateAgainstSchema(
        $input, $schema, $translate = false
    ) {
        $validationResult = $this->traitValidateAgainstSchema(
            $input,
            $schema
        );
        if (!$validationResult->isValid()) {
            //foreach ($validationResult->getErrors() as $error){
            //    $field = implode('.', $error->dataPointer());
            //    if ($error->keyword() == 'required') {
            //        $tmp = $error->dataPointer();
            //        array_push($tmp, $error->keywordArgs()['missing']);
            //        $field = implode('.', $tmp);
            //    }
            //    if ($error->keyword() == 'additionalProperties') {
            //        continue;
            //    }
            //    $this->errors[$field] = [
            //        'keyword' => $error->keyword(),
            //        'details' => $error->keywordArgs()
            //    ];
            //}
            if ($translate) {
                $this->translateErrorMessages($validationResult);
            }
        }
        return $validationResult->isValid();
    }

    /**
     * translate error messages to user readable strings
     */
    protected function translateErrorMessages($validationResult)
    {
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
            if ($error->keyword() != 'required') {
                $errorLabel = $this->getTranslation(
                    'error.' . $field . '.' . $error->keyword()
                );
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
     * The hash service class to use
     *
     * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
     */
    protected $hashService;

    /**
     * @param \TYPO3\CMS\Extbase\Security\Cryptography\HashService $hashService
     */
    public function injectHashService(\TYPO3\CMS\Extbase\Security\Cryptography\HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    /**
     * get property mapper config
     *
     * @return void
     */
    protected function getPropertyMappingConfiguration($attribute)
    {
        $propertyMappingConfiguration = $this
            ->propertyMappingConfigurationBuilder->build();
        $this->initializePropertyMappingConfigurationFromRequest(
            $this->request,
            $propertyMappingConfiguration,
            $attribute
        );
        return $propertyMappingConfiguration;
    }

    /**
     * Initialize the property mapping configuration in $controllerArguments if
     * the trusted properties are set inside the request.
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Request $request
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\Arguments $controllerArguments
     * @throws BadRequestException
     */
    public function initializePropertyMappingConfigurationFromRequest(\TYPO3\CMS\Extbase\Mvc\Request $request, $propertyMappingConfiguration, $propertyNameTest)
    {
        $trustedPropertiesToken = $request->getInternalArgument('__trustedProperties');
        if (!is_string($trustedPropertiesToken)) {
            return;
        }

        try {
            $serializedTrustedProperties = $this->hashService->validateAndStripHmac($trustedPropertiesToken);
        } catch (InvalidHashException | InvalidArgumentForHashGenerationException $e) {
            throw new BadRequestException('The HMAC of the form could not be validated.', 1581862822);
        }
        $trustedProperties = unserialize($serializedTrustedProperties, ['allowed_classes' => false]);
        foreach ($trustedProperties as $propertyName => $propertyConfiguration) {

            //if (!$controllerArguments->hasArgument($propertyName)) {
            //    continue;
            //}
            if ($propertyName != $propertyNameTest) {
                continue;
            }
            //$propertyMappingConfiguration = $controllerArguments->getArgument($propertyName)->getPropertyMappingConfiguration();
            $this->modifyPropertyMappingConfiguration($propertyConfiguration, $propertyMappingConfiguration);
        }
    }

    /**
     * Modify the passed $propertyMappingConfiguration according to the $propertyConfiguration which
     * has been generated by Fluid. In detail, if the $propertyConfiguration contains
     * an __identity field, we allow modification of objects; else we allow creation.
     *
     * All other properties are specified as allowed properties.
     *
     * @param array $propertyConfiguration
     * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration $propertyMappingConfiguration
     */
    protected function modifyPropertyMappingConfiguration($propertyConfiguration, \TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration $propertyMappingConfiguration)
    {
        if (!is_array($propertyConfiguration)) {
            return;
        }

        if (isset($propertyConfiguration['__identity'])) {
            $propertyMappingConfiguration->setTypeConverterOption(\TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter::class, \TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, true);
            unset($propertyConfiguration['__identity']);
        } else {
            $propertyMappingConfiguration->setTypeConverterOption(\TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter::class, \TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
        }

        foreach ($propertyConfiguration as $innerKey => $innerValue) {
            if (is_array($innerValue)) {
                $this->modifyPropertyMappingConfiguration($innerValue, $propertyMappingConfiguration->forProperty($innerKey));
            }
            $propertyMappingConfiguration->allowProperties($innerKey);
        }
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
            if ($this->pageType == $this->ajaxPageType) {
                $GLOBALS['TSFE']->setContentType('application/json');
            }
            unset($result['data']);
            if ($this->redirect) {
                $result['redirect'] = $this->redirect;
            }
            return json_encode($result);
        }
        $result = array_merge(
            $result,
            ['cid'           => $this->contentObjectUid],
            ['isValid'       => $this->isValid],
            ['componentMode' => $this->componentMode]
        );
        if (!empty($this->ajaxEnv)) {
            $result = array_merge(
                $result,
                ['ajaxEnv' => $this->ajaxEnv]
            );
        }
        $this->view->assignMultiple($result);
    }

}
