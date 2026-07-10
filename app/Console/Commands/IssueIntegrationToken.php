<?php

namespace App\Console\Commands;

use App\Models\IntegrationClient;
use Illuminate\Console\Command;

class IssueIntegrationToken extends Command
{
    protected $signature = 'integration-token:issue
                            {system : System key, halimbawa accre}
                            {--name= : Pangalan ng token}
                            {--abilities= : Comma-separated abilities, halimbawa external-admin:read,medical-status:read}';

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

        $allowedAbilities = [
            'external-admin:read',
            'external-admin:update',
            'medical-status:read',
        ];

        $requestedAbilities = trim((string) $this->option('abilities'));

        $abilities = $requestedAbilities === ''
            ? $allowedAbilities
            : collect(explode(',', $requestedAbilities))
                ->map(fn ($ability) => trim((string) $ability))
                ->filter()
                ->unique()
                ->values()
                ->all();

        $invalidAbilities = array_values(array_diff($abilities, $allowedAbilities));

        if (!empty($invalidAbilities)) {
            $this->error('May invalid abilities: ' . implode(', ', $invalidAbilities));
            $this->line('Allowed abilities: ' . implode(', ', $allowedAbilities));

            return self::FAILURE;
        }

        if (empty($abilities)) {
            $this->error('Kailangan ng kahit isang ability.');

            return self::FAILURE;
        }

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
