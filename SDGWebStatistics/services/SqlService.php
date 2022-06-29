<?php

namespace Piwik\Plugins\SDGWebStatistics\services;

use Exception;
use Piwik\Common;
use Piwik\Db;

/**
 * Service for handling the plugins extended database.
 */
class SqlService {
    const TABLE_NAME = "sdg_web_statistics_request";
    const REQUEST_ID_FIELD = "request_id";
    const PERIOD_ID_FIELD = "period_id";
    const START_DATE_FIELD = "start_date";
    const END_DATE_FIELD = "end_date";
    const STATUS_FIELD = "status";
    const DATE_FIELD = "date";
    const PERIOD_ID_MISSING = "Missing ID";
    const FIELDS = [self::PERIOD_ID_FIELD, self::START_DATE_FIELD, self::END_DATE_FIELD, self::STATUS_FIELD, self::DATE_FIELD];

    public function createTable() {
        $tableName = Common::prefixTable(self::TABLE_NAME);
        try {
            $sql = "
                CREATE TABLE `${tableName}` (
                    `request_id` int NOT NULL AUTO_INCREMENT ,
                    `period_id` varchar ( 50 ) ,
                    `start_date` DATETIME ,
                    `end_date` DATETIME ,
                    `status` int ,
                    `date` DATETIME ,
                    PRIMARY KEY ( `request_id` )
                ) DEFAULT CHARSET=utf8
            ";
            Db::exec($sql);
        } catch (Exception $e) {
            // ignore error if table already exists (1050 code is for 'table already exists')
            if (!Db::get()->isErrNo($e, '1050')) {
                throw $e;
            }
        }
    }

    public function dropTable() {
        Db::dropTables(Common::prefixTable(self::TABLE_NAME));
    }

    public function insertRequestInfo($periodId, $startDate, $endDate, $status, $date)
    {
        $tableName = Common::prefixTable(self::TABLE_NAME);
        $sql =  "
            INSERT INTO `${tableName}`
                (`period_id`, `start_date`, `end_date`, `status`, `date`)
            VALUES
                (?, ?, ?, ?, ?)
        ";
        Db::query($sql, [$periodId, $startDate, $endDate, $status, $date]);
    }

    public function getRequestInfo(int $limit = 10): array
    {
        $tableName = Common::prefixTable(self::TABLE_NAME);
        $sql = "
            SELECT
                `period_id`,
                `start_date`,
                `end_date`,
                `status`,
                `date`
            FROM
                `${tableName}`
            ORDER BY
                `date` DESC
            LIMIT
                ${limit}
        ";
        return Db::fetchAll($sql);
    }
}
