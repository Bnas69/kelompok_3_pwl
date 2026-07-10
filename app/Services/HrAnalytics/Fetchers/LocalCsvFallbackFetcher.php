<?php

namespace App\Services\HrAnalytics\Fetchers;

use App\Models\HrDataSource;

class LocalCsvFallbackFetcher
{
    use ReadsCsvRows;

    public function fetch(HrDataSource $source, int $limit): array
    {
        $path = $source->source_url ?: config('hr_analytics.fallback_csv_path');
        $resolvedPath = str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : base_path($path);

        return $this->readCsvFile($resolvedPath, $limit);
    }

    public function fromPath(string $path, int $limit): array
    {
        return $this->readCsvFile($path, $limit);
    }

    public function iterate(HrDataSource $source, int $limit): \Generator
    {
        $path = $source->source_url ?: config('hr_analytics.fallback_csv_path');
        $resolvedPath = str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : base_path($path);

        yield from $this->iterateCsvFile($resolvedPath, $limit);
    }
}
