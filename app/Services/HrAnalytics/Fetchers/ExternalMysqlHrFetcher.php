<?php

namespace App\Services\HrAnalytics\Fetchers;

use App\Models\HrDataSource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExternalMysqlHrFetcher
{
    public function fetch(HrDataSource $source, int $limit): array
    {
        $parts = parse_url((string) $source->source_url);

        if (($parts['scheme'] ?? '') !== 'mysql' || empty($parts['host']) || empty($parts['path'])) {
            throw new InvalidArgumentException('URL external MySQL harus berbentuk mysql://host:3306/database?table=employees.');
        }

        parse_str($parts['query'] ?? '', $query);
        $table = $query['table'] ?? 'employees';
        if (! preg_match('/^[A-Za-z0-9_\.]+$/', $table)) {
            throw new InvalidArgumentException('Nama tabel external MySQL tidak valid.');
        }
        $credentials = $this->credentials($source);

        Config::set('database.connections.hr_external_sync', [
            'driver' => 'mysql',
            'host' => $parts['host'],
            'port' => $parts['port'] ?? 3306,
            'database' => ltrim($parts['path'], '/'),
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ]);

        DB::purge('hr_external_sync');

        return DB::connection('hr_external_sync')
            ->table($table)
            ->limit($limit)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function credentials(HrDataSource $source): array
    {
        $apiKey = (string) $source->api_key;

        if ($apiKey === '') {
            throw new InvalidArgumentException('Credential external MySQL belum diisi.');
        }

        $json = json_decode($apiKey, true);
        if (is_array($json)) {
            return [
                'username' => $json['username'] ?? '',
                'password' => $json['password'] ?? '',
            ];
        }

        if (str_contains($apiKey, ':')) {
            [$username, $password] = explode(':', $apiKey, 2);

            return compact('username', 'password');
        }

        throw new InvalidArgumentException('Credential external MySQL harus JSON username/password atau username:password.');
    }
}
