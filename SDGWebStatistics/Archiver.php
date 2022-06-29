<?php

namespace Piwik\Plugins\SDGWebStatistics;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\DbHelper;
use Piwik\Metrics;
use Piwik\Plugin\Archiver as PluginArchiver;
use Piwik\Tracker\Action;

class Archiver extends PluginArchiver
{
    const STATISTICS_RECORD_NAME = "SDGWebStatistics_statistics"; 

    protected const TABLE_LOG_ACTIONS = "log_link_visit_action";
    protected const TABLE_ACTIONS = "log_action";
    protected const TABLE_VISITS = "log_visit";

    protected const SERVER_TIME_FIELD = "server_time";
    protected const COUNTRY_FIELD = "location_country";
    protected const DEVICE_TYPE_FIELD = "config_device_type";

    //Metadata indexes for archive
    const PAGE_URL_METADATA_INDEX = 1;
    const PAGE_TITLE_METADATA_INDEX = 2;
    const COUNTRY_CODE_METADATA_INDEX = 3;
    const DEVICE_TYPE_METADATA_INDEX = 4;

    public function aggregateDayReport()
    {
        $statisticsDataTable = $this->aggregateStatistics();
        
        $this->getProcessor()->insertBlobRecord(self::STATISTICS_RECORD_NAME, $statisticsDataTable->getSerialized());
    }

    public function aggregateMultipleReports()
    {
        $this->getProcessor()->aggregateDataTableRecords([self::STATISTICS_RECORD_NAME]);
    }

    private function aggregateStatistics()
    {
        $logAggregator = $this->getLogAggregator();

        $from = array(self::TABLE_LOG_ACTIONS);
        $from[] = array( "table" => self::TABLE_ACTIONS, "tableAlias" => "urls", "joinOn" => self::TABLE_LOG_ACTIONS . ".idaction_url=urls.idaction");
        $from[] = array( "table" => self::TABLE_ACTIONS, "tableAlias" => "titles", "joinOn" => self::TABLE_LOG_ACTIONS . ".idaction_name=titles.idaction");
        $from[] = array( "table" => self::TABLE_VISITS, "tableAlias" => "visits", "joinOn" => self::TABLE_LOG_ACTIONS . ".idvisit=visits.idvisit");

        $where = "titles.type=" . Action::TYPE_PAGE_TITLE . " and urls.type=" . Action::TYPE_PAGE_URL . " and urls.url_prefix!=0";
        $where = $logAggregator->getWhereStatement(self::TABLE_LOG_ACTIONS, self::SERVER_TIME_FIELD, $where);

        $select = "urls.name as pageUrl
            , titles.name as pageTitle
            , visits." . self::COUNTRY_FIELD . " as countryCode
            , visits." . self::DEVICE_TYPE_FIELD . " as deviceType
            , count(distinct visits.idvisit) as nb_visits";

        $groupBy = "urls.name, titles.name, visits." . self::COUNTRY_FIELD . ", visits." . self::DEVICE_TYPE_FIELD;

        $query = $logAggregator->generateQuery($select, $from, $where, $groupBy, "");

        $query['sql'] = DbHelper::addMaxExecutionTimeHintToQuery($query['sql'], -1);
        $result = $logAggregator->getDb()->query($query["sql"], $query["bind"]);
        
        $dataTable = new DataTable();
        while ($row = $result->fetch()) {
            $dataTable->addRowFromArray(array(
                Row::COLUMNS => array(
                    "label" => $row["pageUrl"] . "_" . $row["countryCode"] . "_" . $row["deviceType"],
                    Metrics::INDEX_NB_VISITS => $row["nb_visits"] 
                ),
                Row::METADATA => array(
                    self::PAGE_URL_METADATA_INDEX => $row["pageUrl"],
                    self::PAGE_TITLE_METADATA_INDEX => $row["pageTitle"],
                    self::COUNTRY_CODE_METADATA_INDEX => $row["countryCode"],
                    self::DEVICE_TYPE_METADATA_INDEX => $row["deviceType"]
                )
            ));
        }

        return $dataTable;
    }
}