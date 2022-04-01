<?php
namespace Cjel\TemplatesAide\Utility;

/***
 *
 * This file is part of the "Templates Aide" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2022 Philipp Dieter
 *
 ***/

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Holds functions to help with database interactions
 */
class DatabaseUtility
{

    /**
     * Mysql date format
     */
    const MYSQL_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Returns table name by model
     *
     * @param $model object model
     * @return string table name
     */
    public static function getTableNameFromModelClass($class)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $dataMapper = $objectManager->get(DataMapper::class);
        return $dataMapper->getDataMap($class)->getTableName();
    }

    /**
     * Creates a new query builder and returns it
     *
     * @param $tablename string table name
     * @return object queryBuilder
     */
    public static function getQueryBuilderFromTableName($tableName)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($tableName)
            ->createQueryBuilder();
        return $queryBuilder;
    }
}
