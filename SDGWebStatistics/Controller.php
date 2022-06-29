<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SDGWebStatistics;

use Piwik\Piwik;
use Piwik\Plugins\SDGWebStatistics\services\SendDataToApiService;
use Piwik\Plugins\SDGWebStatistics\services\SqlService;
use Piwik\View;

/**
 * A controller lets you for example create a page that can be added to a menu. For more information read our guide
 * http://developer.piwik.org/guides/mvc-in-piwik or have a look at the our API references for controller and view:
 * http://developer.piwik.org/api-reference/Piwik/Plugin/Controller and
 * http://developer.piwik.org/api-reference/Piwik/View
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    public function index()
    {
        // Render the Twig template templates/index.twig and assign the view variable answerToLife to the view.
        $sqlService = new SqlService();
        $view = new View("@SDGWebStatistics/index.twig");
        $this->setGeneralVariablesView($view);

        $settings = new SystemSettings();
        $view->requestInfo = $sqlService->getRequestInfo();
        $view->requestInfoFields = SqlService::FIELDS;
        $view->settingsSet = $settings->isSet();

        return $view->render();
    }

    /**
     * Trigger sending last months statistics to the sdg-api
     * 
     * @return string The httpCode of the response
     */
    public function sendDataToApi()
    {
        $settings = new SystemSettings();
        Piwik::checkUserHasAdminAccess($settings->siteId->getValue());
        
        $service = new SendDataToApiService();
        $period = $service->getPeriodLastMonth();
        $responses = $service->sendStatistics($period->startDate, $period->endDate);
        return \json_encode($responses);
    }
}
