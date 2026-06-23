<?php

namespace App\Console\Commands;

use App\Models\IntegrationClient;
use Illuminate\Console\Command;

class IssueIntegrationToken extends Command
{
    protected $signature = 'integration-token:issue
                            {system : System key, halimbawa accre}
                            {--name= : Pangalan ng token}';

    protected $description = 'Gumawa ng bagong Sanctum token para sa integration system.';

    public function handle()
    {
        $systemKey = strtolower(trim((string) $this->argument('system')));

        $client = IntegrationClient::where('system_key', $systemKey)->first();

        if (!$client) {
            $this->error("Hindi nakita ang integration system: {$systemKey}");

            return self::FAILURE;
        }

        if (!$client->is_active) {
            $this->error("Inactive ang integration system: {$systemKey}");

            return self::FAILURE;
        }

        $tokenName = trim((string) $this->option('name'));

        if ($tokenName === '') {
            $tokenName = 'rotation-' . now()->format('Ymd-His');
        }

        $abilities = [
            'external-admin:read',
            'external-admin:update',
            'medical-status:read',
        ];

        $newToken = $client->createToken($tokenName, $abilities);

        $this->info("Token created for {$client->system_name}.");
        $this->warn('I-save agad ang token. Isang beses lang ito ipapakita.');
        $this->newLine();
        $this->line($newToken->plainTextToken);
        $this->newLine();
        $this->line('Token ID: ' . $newToken->accessToken->id);
        $this->line('Abilities: ' . implode(', ', $abilities));

        return self::SUCCESS;
    }
}