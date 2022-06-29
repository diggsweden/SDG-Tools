<?php

namespace Piwik\Plugins\SDGWebStatistics\models;

class StatisticsReferencePeriod {
    /**
     * @var string
     */
    public $startDate;

    /**
     * @var string
     */
    public $endDate;

    public function __construct(string $startDate, string $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
}
