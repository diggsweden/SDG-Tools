<?php

namespace Piwik\Plugins\SDGWebStatistics\services;

use Exception;
use Piwik\Mail;
use Piwik\Plugins\SDGWebStatistics\services\SqlService;
use Piwik\Metrics;
use Piwik\Plugins\SDGWebStatistics\Archiver;
use Piwik\Plugins\SDGWebStatistics\models\Source;
use Piwik\Plugins\SDGWebStatistics\models\Statistics;
use Piwik\Plugins\SDGWebStatistics\models\StatisticsReferencePeriod;
use Piwik\Plugins\SDGWebStatistics\models\StatisticsRequestBody;
use Piwik\Plugins\SDGWebStatistics\SystemSettings;

class SendDataToApiService
{
    /**
     * @var SystemSettings
     */
    private $settings;

    /**
     * @var SqlService
     */
    private $sqlService;

    /**
     * @var HttpService
     */
    private $httpService;

    /**
     * @var ArchiveService
     */
    private $archiveService;

    private const UNIQUE_ID_PATH = '/unique-id';
    private const INFORMATION_SERVICES_STATISTICS_PATH = '/statistics/information-services';

    public function __construct($settings = null, $sqlService = null, $httpService = null, $archiveService = null)
    {
        $this->settings = $settings ?? new SystemSettings();
        $this->sqlService = $sqlService ?? new SqlService();
        $this->httpService = $httpService ?? new HttpService();
        $this->archiveService = $archiveService ?? new ArchiveService();
    }

    /**
     * Get archived statistics from db
     * 
     * @return Source[]
     */
    private function getSources(string $periodStart, string $periodEnd)
    {
        $siteId = $this->settings->siteId->getValue();
        $pageTitleIdentifier = $this->settings->pageTitleIdentifier->getValue();
        $dateString = \substr($periodStart, 0, 10) . "," . \substr($periodEnd, 0, 10);
        
        $statisticsDataTable = $this->archiveService->fetchFromArchive(Archiver::STATISTICS_RECORD_NAME, $siteId, "range", $dateString);

        $sources = [];
        foreach ($statisticsDataTable->getRowsWithoutSummaryRow() as $row) {
            $isSdg = !$pageTitleIdentifier || \strpos($row->getMetadata(Archiver::PAGE_TITLE_METADATA_INDEX), $pageTitleIdentifier) !== false;

            if ($isSdg) {
                $pageUrl = $row->getMetadata(Archiver::PAGE_URL_METADATA_INDEX);

                if (!\key_exists($pageUrl, $sources)) {
                    $sources[$pageUrl] = new Source($pageUrl);
                }

                $statistics = new Statistics($row->getColumn(Metrics::INDEX_NB_VISITS),
                    $row->getMetadata(Archiver::COUNTRY_CODE_METADATA_INDEX),
                    $row->getMetadata(Archiver::DEVICE_TYPE_METADATA_INDEX));
                $sources[$pageUrl]->addStatistics($statistics);
            }
        }

        return \array_values($sources);
    }

    /**
     * Sends a curl request to the sdgApi
     * 
     * @param string $url Path to the api request, starting with a "/" eg. /unique-id
     * @param string $httpMethod Should be "GET" or "POST"
     * @param string|null $requestBody The content that should be sent with a POST request
     * 
     * @return array Containing the result and httpStatus of the request
     */
    private function sendSdgRequest(string $url, string $httpMethod, string $requestBody = null)
    {
        $sdgApiKey = $this->settings->apiKey->getValue();

        $headers = array(
            "x-api-key: " . $sdgApiKey,
            "Content-type: application/json"
        );

        return $this->httpService->sendHttpRequest($url, $httpMethod, $headers, $requestBody);
    }

    /**
     * Send email with status of the request to the api
     * 
     * @param int|string $status The status that the mail will be based on
     */
    private function sendMail($status)
    {
        $recipients = \array_filter($this->settings->emailRecipients->getValue());
        
        if ($recipients) {
            $mail = new Mail();
            $mail->setDefaultFromPiwik();
            $mail->setSubject("SDG web statistics");
            foreach ($recipients as $recipient) {
                $mail->addTo($recipient);
            }
            
            if ($status == 200) {
                $mail->setBodyText("SDG web statistics for last month has been successfully sent.");
            } else {
                $mail->setBodyText("Failed to send SDG web statistics for last month! 
                    The plugin will automatically try to resend the statistics tomorrow.");
            }

            $mail->send();
        }
    }

    /**
     * Get statistics and send to the api.
     * 
     * @return array The response
     */
    public function sendStatistics($periodStart, $periodEnd)
    {
        if (!$this->settings->isSet()) {
            throw new Exception("settings for this plugin must be set");
        }

        $today = \date_create()->format(StatisticsRequestBody::DATE_FORMAT);
        $responses = [];
        $periodId = false;

        $requestInfo = $this->fetchLastReqestInfo($periodStart, $periodEnd);
        if ($requestInfo) {
            $periodId = $requestInfo[SqlService::PERIOD_ID_FIELD];
        } else {
            $responses[self::UNIQUE_ID_PATH] = $this->sendSdgRequest($this->settings->uniqueIdUrl->getValue(), "GET");
            if ($responses[self::UNIQUE_ID_PATH]["status"] == 200) {
                $periodId = \json_decode($responses[self::UNIQUE_ID_PATH]["data"]);
            }
            $httpStatus = $responses[self::UNIQUE_ID_PATH]["status"];
        }

        if ($periodId) {
            $sources = $this->getSources($periodStart, $periodEnd);
            $referencePeriod = new StatisticsReferencePeriod($periodStart, $periodEnd);
            $requestBody = \json_encode(new StatisticsRequestBody($periodId, $referencePeriod, $sources), \JSON_UNESCAPED_SLASHES);

            $responses[self::INFORMATION_SERVICES_STATISTICS_PATH] = $this->sendSdgRequest($this->settings->statisticsUrl->getValue(), "POST", $requestBody);
            $httpStatus = $responses[self::INFORMATION_SERVICES_STATISTICS_PATH]["status"];
        } else {
            $periodId = SqlService::PERIOD_ID_MISSING;
        }

        $this->sqlService->insertRequestInfo($periodId, $periodStart, $periodEnd, $httpStatus, $today);
        $this->sendMail($httpStatus);

        return $responses;
    }

    /**
     * Fetch last period request info if it matches the period provided.
     * 
     * @param string $periodStart Start of the period
     * @param string $periodEnd End of the period
     * 
     * @return array|bool Returns request info if it exists, otherwise returns false
     */
    public function fetchLastReqestInfo(string $periodStart, string $periodEnd)
    {
        $response = $this->sqlService->getRequestInfo(1);

        if ($response) {
            $dbPeriodId = $response[0][SqlService::PERIOD_ID_FIELD];
            $dbPeriodStart = \date_create($response[0][SqlService::START_DATE_FIELD])->format(StatisticsRequestBody::DATE_FORMAT);
            $dbPeriodEnd = \date_create($response[0][SqlService::END_DATE_FIELD])->format(StatisticsRequestBody::DATE_FORMAT);

            if ($dbPeriodStart == $periodStart && $dbPeriodEnd == $periodEnd && $dbPeriodId != SqlService::PERIOD_ID_MISSING) {
                return $response[0];
            } else {
                return false;
            }
        }
    }
    
    /**
     * Get the start date and end date of last month. Normally the date that should be used when sending data to the api.
     * 
     * @return StatisticsReferencePeriod Returns a referencePeriod with start date and end date that can be used with this service. 
     */
    public static function getPeriodLastMonth()
    {
        return new StatisticsReferencePeriod(
            \date_create('first day of last month today')->format(StatisticsRequestBody::DATE_FORMAT),
            \date_create('last day of last month 23:59:59')->format(StatisticsRequestBody::DATE_FORMAT)
        );
    }
}
