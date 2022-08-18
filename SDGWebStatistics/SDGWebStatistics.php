<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SDGWebStatistics;

use Piwik\Plugins\SDGWebStatistics\services\SqlService;

class SDGWebStatistics extends \Piwik\Plugin
{
    const PLUGIN_NAME = "SDGWebStatistics";

    public function install()
    {
        $sqlService = new SqlService();
        $sqlService->createTable();
    }

    public function uninstall()
    {
        $sqlService = new SqlService();
        $sqlService->dropTable();
    }

    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles' => 'getJavaScriptFiles',
            'Http.sendHttpRequest.end' => 'curlErrorLog'
        );
    }

    public function getJavaScriptFiles(&$files)
    {
        $files[] = "plugins/SDGWebStatistics/angularjs/sdg-web-statistics.js";
    }

    /**
     * Callback for event Piwik::postEvent('Http.sendHttpRequest.end', array($aUrl, $httpEventParams, &$response, &$status, &$headers));
     * 
     * Log error if SDG web statistics request and status not OK.
     */
    public function curlErrorLog($aUrl, $httpEventParams, &$response, &$status, &$headers)
    {
        $settings = new SystemSettings();
        if (!$settings->isSet()) {
            return;
        }

        $url = $settings->statisticsUrl->getValue();
        if (\strpos($aUrl, $url) !== false && $status !== 200) {
            $errorInfo = array(
                "aUrl" => $aUrl,
                "httpEventParams" => $httpEventParams,
                "response" => $response,
                "status" => $status,
                "headers" => $headers
            );
    
            \error_log("Error: Failed to send SDG web statistics with the following request: " . \json_encode($errorInfo, \JSON_UNESCAPED_SLASHES));
        }
    }
}