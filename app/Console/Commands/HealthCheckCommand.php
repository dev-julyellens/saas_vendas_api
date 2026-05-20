<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthCheckCommand extends Command
{
    protected $signature = 'health:check';

    protected $description = 'Verifica conectividade com PostgreSQL e Redis (Docker healthcheck)';

    public function handle(): int
    {
        try
        {
            DB::connection()->getPdo();
            DB::connection()->select('select 1');
        }
        catch (\Throwable $e)
        {
            $this->error('Database: ' . $e->getMessage());

            return self::FAILURE;
        }

        try
        {
            Redis::connection()->ping();
            Cache::store('redis')->put('health_check', true, 10);
        }
        catch (\Throwable $e)
        {
            $this->error('Redis: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('ok');

        return self::SUCCESS;
    }
}
