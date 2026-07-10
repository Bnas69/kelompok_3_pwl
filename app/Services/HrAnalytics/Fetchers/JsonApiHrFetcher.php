<?php

namespace App\Services\HrAnalytics\Fetchers;

use App\Models\HrDataSource;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class JsonApiHrFetcher
{
    public function fetch(HrDataSource $source, int $limit): array
    {
        if (! filter_var($source->source_url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('URL JSON API tidak valid.');
        }

        $request = Http::acceptJson()
            ->timeout(config('hr_analytics.http_timeout'))
            ->retry(config('hr_analytics.http_retry_times'), config('hr_analytics.http_retry_sleep'));

        if ($source->auth_type === 'bearer' && $source->api_key) {
            $request = $request->withToken($source->api_key);
        }

        $url = $source->source_url;
        if ($source->auth_type === 'query_key' && $source->api_key) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator.'api_key='.urlencode($source->api_key);
        }

        if ($source->auth_type === 'basic' && $source->api_key && str_contains($source->api_key, ':')) {
            [$username, $password] = explode(':', $source->api_key, 2);
            $request = $request->withBasicAuth($username, $password);
        }

        $payload = $request->get($url)->throw()->json();
        $rows = $payload['data'] ?? $payload['employees'] ?? $payload;

        if (! is_array($rows)) {
            throw new InvalidArgumentException('Response JSON tidak berisi array data karyawan.');
        }

        return collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->take($limit)
            ->values()
            ->all();
    }
}
