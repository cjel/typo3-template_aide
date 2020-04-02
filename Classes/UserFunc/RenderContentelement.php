<?php
namespace Cjel\TemplatesAide\UserFunc;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\RecordsContentObject;

class RenderContentelement
{
    /**
     */
    public function render()
    {
        $cid = GeneralUtility::_GP('cid');
        $this->objectManager = GeneralUtility::makeInstance(
            ObjectManager::class
        );
        return trim($this->objectManager->get(
            RecordsContentObject::class
        )->render([
            'tables'       => 'tt_content',
            'source'       => $cid,
            'dontCheckPid' => 1
        ]));
    }
}
