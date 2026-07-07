<?php

namespace App\Console\Commands;

use App\Support\RailwayDatabaseConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RailwayDiagnoseCommand extends Command
{
    protected $signature = 'railway:diagnose';

    protected $description = 'Affiche la configuration base de données détectée (sans secrets)';

    public function handle(): int
    {
        RailwayDatabaseConfig::apply();

        $this->info('Variables détectées :');

        foreach (RailwayDatabaseConfig::diagnosticSnapshot() as $key => $value) {
            $this->line(sprintf('  %-22s %s', $key.':', $value ?? '—'));
        }

        $this->newLine();
        $this->info('Config Laravel active :');
        $this->line('  default: '.config('database.default'));
        $this->line('  host: '.(config('database.connections.pgsql.host') ?? '—'));
        $this->line('  database: '.(config('database.connections.pgsql.database') ?? '—'));
        $this->line('  sslmode: '.(config('database.connections.pgsql.sslmode') ?? '—'));

        $this->newLine();

        try {
            DB::connection()->getPdo();
            $this->info('Connexion PostgreSQL : OK');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Connexion PostgreSQL : ÉCHEC');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
