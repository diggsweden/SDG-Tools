<?php

namespace Piwik\Plugins\SDGWebStatistics\models;

class Source {
    /**
     * @var string
     */
    public $sourceUrl;

    /**
     * @var Statistics[]
     */
    public $statistics;

    public function __construct(string $sourceUrl, array $statistics = [])
    {
        $this->sourceUrl = $sourceUrl;
        $this->statistics = $statistics;
    }

    public function addStatistics(Statistics $statistics)
    {
        $this->statistics[] = $statistics;
    }
}