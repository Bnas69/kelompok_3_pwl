<?php

namespace App\Console\Commands;

use App\Services\Analytics\AnalyticsGeneratorService;
use Illuminate\Console\Command;

class AnalyticsGenerateCommand extends Command
{
    protected $signature = 'analytics:generate {--historical : Generate data dari tahun 2005}';

    protected $description = 'Generate atau sync data analytics.';

    public function handle(AnalyticsGeneratorService $generator): int
    {
        $result = $this->option('historical')
            ? $generator->generateHistorical()
            : $generator->syncDaily();

        $this->info('Generate analytics selesai.');
        $this->table(
            ['Item', 'Jumlah'],
            [
                ['Data harian baru', $result['daily_created']],
                ['Sync bulanan baru', $result['sync_created']],
                ['Sync bulanan update', $result['sync_updated']],
            ]
        );

        return self::SUCCESS;
    }
}
