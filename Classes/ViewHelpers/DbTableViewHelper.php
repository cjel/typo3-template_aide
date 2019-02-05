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
     * @param string $where
     * @param string $orderBy
     * @return string HTML Content
     */
    public function render($table, $fields = ['*'], $where = [], $orderBy = []){


        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages')->createQueryBuilder();

        $whereExpressions = [];
        if (!empty($where)) {
            foreach ($where as $key => $row) {
                $whereExpressions[] = $queryBuilder->expr()->eq($key, $queryBuilder->createNamedParameter($row));
            }
        }

        $data = $queryBuilder->select(...$fields)->from($table);
        if (!empty($whereExpressions)) {
            $data = $data->where(...$whereExpressions);
        }
        if (!empty($orderBy)) {
            if (!empty($orderBy[key($orderBy)])) {
                $data = $data->orderBy(key($orderBy), $orderBy[key($orderBy)]);
            } else {
                $data = $data->orderBy(key($orderBy));
            }
        }
        $data = $data->execute()->fetchAll();

        \TYPO3\CMS\Core\Utility\DebugUtility::debug($data);

        return $data;
    }
}
