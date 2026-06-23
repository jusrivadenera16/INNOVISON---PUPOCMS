<?php

namespace App\Console\Commands;

use App\Models\IntegrationClient;
use Illuminate\Console\Command;

class RevokeIntegrationToken extends Command
{
    protected $signature = 'integration-token:revoke
                            {system : System key, halimbawa accre}
                            {token_id? : ID ng token na ire-revoke}
                            {--force : Huwag nang humingi ng confirmation}';

    protected $description = 'Mag-list o mag-revoke ng integration Sanctum token.';

    public function handle()
    {
        $systemKey = strtolower(trim((string) $this->argument('system')));

        $client = IntegrationClient::where('system_key', $systemKey)->first();

        if (!$client) {
            $this->error("Hindi nakita ang integration system: {$systemKey}");

            return self::FAILURE;
        }

        $tokenId = $this->argument('token_id');

        if (!$tokenId) {
            $tokens = $client->tokens()
                ->orderByDesc('created_at')
                ->get();

            if ($tokens->isEmpty()) {
                $this->info("Walang tokens para sa {$client->system_name}.");

                return self::SUCCESS;
            }

            $this->table(
                ['Token ID', 'Name', 'Abilities', 'Last Used', 'Created'],
                $tokens->map(function ($token) {
                    return [
                        $token->id,
                        $token->name,
                        implode(', ', $token->abilities ?? []),
                        optional($token->last_used_at)->format('Y-m-d H:i:s') ?: 'Never',
                        optional($token->created_at)->format('Y-m-d H:i:s') ?: 'N/A',
                    ];
                })->all()
            );

            $this->line('Para mag-revoke: php artisan integration-token:revoke SYSTEM TOKEN_ID');

            return self::SUCCESS;
        }

        $token = $client->tokens()->whereKey((int) $tokenId)->first();

        if (!$token) {
            $this->error(
                "Hindi nakita ang token ID {$tokenId} para sa {$client->system_name}."
            );

            return self::FAILURE;
        }

        if (
            !$this->option('force')
            && !$this->confirm(
                "I-revoke ang token '{$token->name}' (ID {$token->id})?",
                false
            )
        ) {
            $this->info('Walang token na binago.');

            return self::SUCCESS;
        }

        $tokenName = $token->name;
        $token->delete();

        $this->info(
            "Na-revoke ang token '{$tokenName}' para sa {$client->system_name}."
        );

        return self::SUCCESS;
    }
}