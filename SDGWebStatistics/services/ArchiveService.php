<?php

namespace Piwik\Plugins\SDGWebStatistics\services;

use Piwik\Archive;

/**
 * For handling fetching data from the archive.
 * 
 * Wrapped for testability.
 * 
 * @param string $recordName The name of the record to be fetched
 * @param int|string $idSite The site id
 * @param string $period The period, as from the reporting api
 * @param string $date Date string, as from the reporting api
 * @param string|bool $segment The segment, as from the reporting api. 
 */
class ArchiveService
{
    public function fetchFromArchive(string $recordName, $idSite, string $period, string $date, $segment = false)
    {
        $archive = Archive::build($idSite, $period, $date, $segment);
        return $archive->getDataTable($recordName);
    }
}