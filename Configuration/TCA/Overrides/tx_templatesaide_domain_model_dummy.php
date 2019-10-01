<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function() {
    unset($GLOBALS['TCA']['tx_templatesaide_domain_model_dummy']);
});
