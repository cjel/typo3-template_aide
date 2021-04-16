<?php
namespace Cjel\TemplatesAide\Controller;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DefaultRestrictionContainer;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;
use TYPO3\CMS\Frontend\Utility\EidUtility;

/**
 * AbstractEIDController
 */
class AbstractEIDController
{

    /**
     * @var BackendConfigurationManager
     */
    protected $configurationManager;

    /**
     * ApiUtility
     */
    protected $apiUtility = null;

    /*
     * extension Key
     */
    protected $extensionKey = null;

    /*
     * objectManager
     */
    protected $objectManager = null;

    /*
     * storagePids
     */
    protected $settings = null;

    /*
     * storagePids
     */
    protected $storagePids = [];

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
     */
    public function getExtensionKey() {
        return $this->extensionKey;
    }

    /**
     * Construct
     *
     * @param ObjectManager $objectManager
     * @param array         $configuration
     */
    public function __construct(ObjectManager $objectManager = null,
        array $configuration = [])
    {
        $this->objectManager = GeneralUtility::makeInstance(
            ObjectManager::class
        );
        $this->initFrontendController();
        $this->configurationManager = $this->objectManager->get(
            ConfigurationManagerInterface::class
        );
        $this->configurationManager->setConfiguration(array());
        $frameworkConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            $this->getExtensionKey()
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
        $this->logManager = $this->objectManager->get(
            LogManager::Class
        );
        $this->importLogger = $this->logManager->getLogger(
            'importLogger'
        );
    }

    /**
     * Initialize frontentController
     */
    private function initFrontendController()
    {
        $currentDomain = strtok(GeneralUtility::getIndpEnv('HTTP_HOST'), ':');
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_domain');
        $queryBuilder->setRestrictions(
            GeneralUtility::makeInstance(DefaultRestrictionContainer::class)
        );
        $result = $queryBuilder
            ->select('uid', 'pid', 'domainName')
            ->from('sys_domain')
            ->where(
                $queryBuilder->expr()->eq(
                    'domainName',
                    $queryBuilder->createNamedParameter(
                        $currentDomain,
                        \PDO::PARAM_STR
                    )
                )
            )
            ->orderBy('sorting', 'ASC')
            ->execute()
            ->fetchAll();
        //if (count($result) < 1) {
        //    throw new \Exception('Domain not configured');
        //}
        $frontendController = GeneralUtility::makeInstance(
            \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            null,
            0,
            true
        );
        $GLOBALS['TSFE'] = $frontendController;
        $frontendController->connectToDB();
        $frontendController->fe_user = EidUtility::initFeUser();
        $frontendController->id = $result[0]['pid'];
        $frontendController->determineId();
        $frontendController->initTemplate();
        $frontendController->getConfigArray();
        EidUtility::initTCA();
    }

    /**
     * process incoming requst
     *
     * checks if there is a method avaiable for the request and executes it, if
     * found
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    public function processRequest(ServerRequestInterface $request,
        ResponseInterface $response = null)
    {
        $apiObject = explode('/', $request->getUri()->getPath())[3];
        $apiObjectId = explode('/', $request->getUri()->getPath())[4];
        if (!$apiObject) {
            return $response->withStatus(404);
        }
        $httpMethod = strtolower($request->getMethod());
        if ($apiObjectId) {
            $requestMethod = $httpMethod
                . ucfirst($apiObject)
                . 'SingleRequest';
            $request->apiObjectId = $apiObjectId;
        } else {
            $requestMethod = $httpMethod
                . ucfirst($apiObject)
                . 'Request';
        }
        if (method_exists($this, $requestMethod)) {
            $responseData = $this->$requestMethod($request, $response);
            $response = $response->withHeader(
                'Content-Type',
                'application/json; charset=utf-8'
            );
            if (is_array($responseData)
                && array_key_exists('errors', $responseData)
            ) {
                $response = $response->withStatus(400);
            }
            $response->getBody()->write(\json_encode($responseData));
            return $response;
        } else {
            return $response->withStatus(404);
        }
    }

}
