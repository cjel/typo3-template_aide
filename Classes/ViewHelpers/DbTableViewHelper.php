<?php
namespace Cjel\TemplatesAide\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class DbTableViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('table', 'string', '', true);
        $this->registerArgument('fields', 'array', '', true, ['*']);
        $this->registerArgument('where', 'array', '', true);
        $this->registerArgument('orderBy', 'array', '', true);
    }

    /**
     * @param string $table
     * @param array $fields
     * @param array $where
     * @param array $orderBy
     * @return string HTML Content
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $table   = $arguments['table'];
        $fields  = $arguments['fields'];
        $where   = $arguments['where'];
        $orderBy = $arguments['orderBy'];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table)->createQueryBuilder();

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

        return $data;
    }
}
