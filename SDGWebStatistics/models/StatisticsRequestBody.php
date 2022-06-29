<?php

namespace Piwik\Plugins\SDGWebStatistics\models;

class StatisticsRequestBody {
    const DATE_FORMAT = "Y-m-d\TH:i:s\Z";

    /**
     * @var string
     */
    public $uniqueId;

    /**
     * @var StatisticsReferencePeriod
     */
    public $referencePeriod;

    /**
     * @var string
     */
    public $transferDate;

    /**
     * @var string
     */
    public $transferType;

    /**
     * @var int
     */
    public $nbEntries;

    /**
     * @var Source[]
     */
    public $sources;

    public function __construct(string $uniqueId, StatisticsReferencePeriod $referencePeriod, array $sources, int $nbEntries = null, string $transferDate = null, string $transferType = 'API')
    {
        $this->uniqueId = $uniqueId;
        $this->referencePeriod = $referencePeriod;
        $this->sources = $sources;
        $this->nbEntries = $nbEntries ?? \count($this->sources);
        $this->transferDate = $transferDate ?? \date_create("now")->format(self::DATE_FORMAT);
        $this->transferType = $transferType;
    }
}