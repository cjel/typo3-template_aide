<?php
namespace Cjel\TemplatesAide\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;

class DbTableViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
      * As this ViewHelper renders HTML, the output must not be escaped.
      *
      * @var bool
      */
    protected $escapeOutput = false;

    /**
     * @param string $table The filename
     * @param string $fields The filename
     * @return string HTML Content
     */
    public function render($table, $fields = ['*']){


        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages')->createQueryBuilder();
        $data = $queryBuilder->select(...$fields)->from($table)
            ->execute()
            ->fetchAll();

        return $data;
    }
}
