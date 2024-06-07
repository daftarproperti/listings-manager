<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class MigrateAndSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-and-seed {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run database migrations and seeders sequentially under lock ownership';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $lockKey = 'migrate_seed_lock';

        // Lock for 120 seconds, migrate and seed should take no more than 2 minutes.
        $lock = Cache::lock($lockKey, 120);

        $lock->get(function () {
            $this->line("Lock acquired, running migrate and seed...");

            $migrateOptions = ['--force' => $this->option('force')];

            // Run migrations
            $this->info('Running migrations...');
            $migrateExitCode = Artisan::call('migrate', $migrateOptions, $this->output);

            if ($migrateExitCode !== 0) {
                $this->error('Migrate failed, not continuing to seed.');
                return $migrateExitCode;
            }

            // Run seeders
            $this->info('Running seeders...');
            Artisan::call('db:seed', $migrateOptions, $this->output);

            $this->info('Migrations and seeders completed successfully.');
        });
    }
}
