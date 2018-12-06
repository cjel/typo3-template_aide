<?php
namespace Cjel\TemplatesAide\Hooks;

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

//use GridElementsTeam\Gridelements\Backend\LayoutSetup;
//use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;
//use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;
//use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
//use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
//use TYPO3\CMS\Core\Imaging\IconRegistry;
//use TYPO3\CMS\Core\Utility\GeneralUtility;
//use TYPO3\CMS\Core\Utility\StringUtility;
//use TYPO3\CMS\Lang\LanguageService;

/**
 * Class/Function which manipulates the rendering of items within the new content element wizard
 *
 * @author Jo Hasenau <info@cybercraft.de>, Tobias Ferger <tobi@tt36.de>
 * @package TYPO3
 * @subpackage tx_gridelements
 */
class WizardItems implements NewContentElementWizardHookInterface
{

    /**
     * @param array $wizardItems The array containing the current status of the wizard item list before rendering
     * @param NewContentElementController $parentObject The parent object that triggered this hook
     */
    public function manipulateWizardItems(&$wizardItems, &$parentObject)
    {
    }


}
