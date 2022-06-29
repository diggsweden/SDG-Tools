<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SDGWebStatistics;

use Piwik\Plugins\SDGWebStatistics\services\SendDataToApiService;
use Piwik\Plugins\SDGWebStatistics\services\SqlService;

class Tasks extends \Piwik\Plugin\Tasks
{
    public function schedule()
    {
        $this->daily("sendDataToApi");
    }

    public function sendDataToApi()
    {
        $settings = new SystemSettings();
        if ($settings->isSet()) {
            $service = new SendDataToApiService();
            $period = $service->getPeriodLastMonth();

            $requestInfo = $service->fetchLastReqestInfo($period->startDate, $period->endDate);
            if (!$requestInfo || $requestInfo[SqlService::STATUS_FIELD] != 200) {
                $service->sendStatistics($period->startDate, $period->endDate);
            }
        }
    }
}
