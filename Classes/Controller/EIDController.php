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
 * EIDController
 */
class EIDController extends AbstractEIDController
{

    public function scriptEnabled(
        ServerRequestInterface $request,
        ResponseInterface $response = null
    ) {
        $GLOBALS['TSFE']->fe_user->setKey('ses', 'scriptstate', 1);
        $GLOBALS["TSFE"]->storeSessionData();
        $response->getBody()->write(\json_encode([]));
        return $response;
    }

    public function scriptDisabled(
        ServerRequestInterface $request,
        ResponseInterface $response = null
    ) {
        $GLOBALS['TSFE']->fe_user->setKey('ses', 'scriptstate', 0);
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
        //    $GLOBALS['TSFE']->fe_user->getKey('ses', 'scriptstate')
        //);
        $GLOBALS["TSFE"]->storeSessionData();
        $response->getBody()->write(\json_encode([]));
        return $response;
    }

}
