<?php

namespace Piwik\Plugins\SDGWebStatistics\models;

class Statistics {
    const DESKTOP = "PC";
    const SMARTPHONE = "Smartphone";
    const TABLET = "Tablet";
    const OTHER_DEVICE = "Others";

    const DEVICES = [0 => self::DESKTOP, 1 => self::SMARTPHONE, 2 => self::TABLET];

    /**
     * @var int
     */
    public $nbVisits;

    /**
     * @var string
     */
    public $originatingCountry;

    /**
     * @var string
     */
    public $deviceType;

    protected static $isoToEurostat = [
        "GR" => "EL"
    ];

    public function __construct(int $nbVisits, string $originatingCountry, $deviceType)
    {
        $this->nbVisits = $nbVisits;
        $this->originatingCountry = $this->convertOriginatingCountry($originatingCountry);
        $this->deviceType = $this->convertDeviceType($deviceType);
    }

    protected static function convertOriginatingCountry($country)
    {
        $upperCountry = \strtoupper($country);
        if (\key_exists($upperCountry, self::$isoToEurostat)) {
            return self::$isoToEurostat[$upperCountry];
        } else {
            return $upperCountry;
        }
    }

    private function convertDeviceType($deviceType)
    {
        if (\key_exists($deviceType, self::DEVICES)) {
            return self::DEVICES[$deviceType];       
        } else {
            return self::OTHER_DEVICE;
        }
    }
}