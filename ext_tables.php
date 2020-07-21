<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Cjel.TemplatesAide',
            'Dummy',
            'dummy'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('templates_aide', 'Configuration/TypoScript', 'Templates Aide');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_templatesaide_domain_model_dummy', 'EXT:templates_aide/Resources/Private/Language/locallang_csh_tx_templatesaide_domain_model_dummy.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_templatesaide_domain_model_dummy');

    }
);
## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder

call_user_func(
    function()
    {

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook'][] =
            \Cjel\TemplatesAide\Hooks\WizardItems::class;

        if(\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isDevelopment()) {
            $GLOBALS['TBE_STYLES']['skins']['backend']['stylesheetDirectories']['dev'] =
                'EXT:templates_aide/Resources/Public/Css/backend/dev';
        }

        if(\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->__toString() === 'Production/Stage') {
            $GLOBALS['TBE_STYLES']['skins']['backend']['stylesheetDirectories']['dev'] =
                'EXT:templates_aide/Resources/Public/Css/backend/production-stage';
        }

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
            'templates_aide',
            'Resources/Private/PageTSConfig/default.tsconfig',
            'Default Config'
        );

        if (TYPO3_MODE == 'BE') {
            //$pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            //    \TYPO3\CMS\Core\Page\PageRenderer::class
            //);
            //$pageRenderer->loadRequireJsModule('TYPO3/CMS/TemplatesAide/NewContentElementWizardPreview');

            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
                '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:templates_aide/Resources/Private/UserTSConfig/default.ts">'
            );
        }

    }
);
