<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SDGWebStatistics;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\DataTable\Map;
use Piwik\Metrics;
use Piwik\Plugins\LogViewer\Log\Parser\Piwik;
use Piwik\Plugins\SDGWebStatistics\models\Statistics;

/**
 * API for plugin SDGWebStatistics
 *
 * @method static \Piwik\Plugins\SDGWebStatistics\API getInstance()
 */
class API extends \Piwik\Plugin\API
{

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getSDGWebStatisticsReport($idSite, $period, $date, $segment = false)
    {
        $settings = new SystemSettings();
        $pageTitleIdentifier = $settings->pageTitleIdentifier->getValue();
        $archive = Archive::build($idSite, $period, $date, $segment);
        $statisticsArchiveData = $archive->getDataTable(Archiver::STATISTICS_RECORD_NAME);
        $statisticsDataTables = [];

        if ($statisticsArchiveData instanceof Map) {
            $statisticsDataTables = $statisticsArchiveData->getDataTables(); 
        } else {
            $statisticsDataTables[] = $statisticsArchiveData;
        }

        $dataTable = new DataTable();
        foreach ($statisticsDataTables as $statisticsDataTable) {
            foreach ($statisticsDataTable->getRows() as $row) {
                $isSdg = !$pageTitleIdentifier || \strpos($row->getMetadata(Archiver::PAGE_TITLE_METADATA_INDEX), $pageTitleIdentifier) !== false;

                if ($isSdg) {
                    $dataTable->addRowFromSimpleArray(array(
                        'label' => $row->getMetadata(Archiver::PAGE_URL_METADATA_INDEX),
                        'pageTitle' => $row->getMetadata(Archiver::PAGE_TITLE_METADATA_INDEX),
                        'country' => $row->getMetadata(Archiver::COUNTRY_CODE_METADATA_INDEX),
                        'deviceType' => $row->getMetadata(Archiver::DEVICE_TYPE_METADATA_INDEX),
                        'nb_visits' => $row->getColumn(Metrics::INDEX_NB_VISITS)
                    ));
                }
            }
        }

        $dataTable->queueFilter('ColumnCallbackReplace', array('country',  'Piwik\Plugins\UserCountry\countryTranslate'));
        $dataTable->queueFilter('ColumnCallbackReplace', array('deviceType',  'Piwik\Plugins\DevicesDetection\getDeviceTypeLabel'));

        return $dataTable;
    }
}