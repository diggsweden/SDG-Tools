<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SDGWebStatistics;

use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\FieldConfig\ArrayField;
use Piwik\Validators\NotEmpty;
use Piwik\Validators\NumberRange;

/**
 * Defines Settings for SDGWebStatistics.
 *
 * Usage like this:
 * $settings = new SystemSettings();
 * $settings->metric->getValue();
 * $settings->description->getValue();
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $apiKey;

    /** @var Setting */
    public $siteId;

    /** @var Setting */
    public $uniqueIdUrl;

    /** @var Setting */
    public $statisticsUrl;

    /** @var Setting */
    public $pageTitleIdentifier;

    /** @var Setting */
    public $emailRecipients;

    /** @var Setting[] */
    private $mandatoryFields = [];

    protected function init()
    {
        $this->apiKey = $this->createApiKeySetting();
        $this->siteId = $this->createSiteIdSetting();
        $this->uniqueIdUrl = $this->createUniqueIdUrlSetting();
        $this->statisticsUrl = $this->createStatisticsUrlSetting();
        $this->pageTitleIdentifier = $this->createPageTitleIdentifierSetting();
        $this->emailRecipients = $this->createEmailRecipientsSetting();
        
        $this->mandatoryFields = [ $this->apiKey, $this->siteId, $this->uniqueIdUrl, $this->statisticsUrl ];
    }

    private function createApiKeySetting()
    {
        $default = '';

        return $this->makeSetting('apiKey', $default, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'API Key';
            $field->uiControl = FieldConfig::UI_CONTROL_PASSWORD;
            $field->description = 'API key to identify the request.';
            $field->validators[] = new NotEmpty();
        });
    }

    private function createSiteIdSetting()
    {
        $default = 1;

        return $this->makeSetting('siteId', $default, FieldConfig::TYPE_INT, function (FieldConfig $field) {
            $field->title = 'Site ID';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = 'Enter Id of site to collect data';
            $field->validators[] = new NotEmpty();
            $field->validators[] = new NumberRange(1);
        });
    }
    
    private function createUniqueIdUrlSetting()
    {
        $default = '';

        return $this->makeSetting('uniqueIdUrl', $default, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Unique ID URL';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = 'URL to get unique-id from SDG-API.';
            $field->validators[] = new NotEmpty();
        });
    }

    private function createStatisticsUrlSetting()
        {
            $default = '';

            return $this->makeSetting('statisticsUrl', $default, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
                $field->title = 'Statistics URL';
                $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
                $field->description = 'URL to post statistics to SDG-API.';
                $field->validators[] = new NotEmpty();
            });
        }

    private function createPageTitleIdentifierSetting()
    {
        $default = '';

        return $this->makeSetting('pageTitleIdentifier', $default, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'Page title identifier';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->description = 'An identifier that should be placed in the page title of SDG pages. If empty, statistics from all pages will be reported.';
        });
    }

    private function createEmailRecipientsSetting()
    {
        $default = [];

        return $this->makeSetting('emailRecipients', $default, FieldConfig::TYPE_ARRAY, function (FieldConfig $field) {
            $field->title = 'Email recipients';
            $field->description = 'Email addresses that send web statistics status should be sent to.';
            $field->uiControl = FieldConfig::UI_CONTROL_FIELD_ARRAY;
            $arrayField = new ArrayField('Email address', FieldConfig::UI_CONTROL_TEXT);
            $field->uiControlAttributes['field'] = $arrayField->toArray();
        });
    }

    public function isSet()
    {
        $set = true;
        for ($i = 0; $i <= \count($this->mandatoryFields) - 1 && $set; $i++) {
            if (!isset($this->mandatoryFields[$i])) {
                $set = false;
            } else {
                $set = !!$this->mandatoryFields[$i]->getValue();
            }
        }

        return $set;
    }
}