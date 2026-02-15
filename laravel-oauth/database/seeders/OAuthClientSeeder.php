<?php

namespace Database\Seeders;

use App\Models\OAuthClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OAuthClientSeeder extends Seeder
{
    /**
     * Seed the OAuth clients table.
     */
    public function run(): void
    {
        // Generate a secure client secret
        $clientSecret = Str::random(64);

        // Create Arma Battles Chat OAuth client
        OAuthClient::updateOrCreate(
            ['id' => '019c5d06-b3f3-709a-a212-b4441d609080'],
            [
                'name' => 'Arma Battles Chat',
                'secret' => hash('sha256', $clientSecret),
                'redirect_uris' => [
                    'https://chat.armabattles.com/auth/callback',
                    'http://local.revolt.chat:3000/auth/callback', // Development
                ],
                'revoked' => false,
            ]
        );

        // Output the secret (SAVE THIS - you won't see it again!)
        $this->command->info('OAuth Client Created!');
        $this->command->info('Client ID: 019c5d06-b3f3-709a-a212-b4441d609080');
        $this->command->warn('Client Secret: ' . $clientSecret);
        $this->command->warn('⚠️  SAVE THIS SECRET NOW - IT WILL NOT BE SHOWN AGAIN!');
        $this->command->info('Add this to your chat .env.prod:');
        $this->command->line('OAUTH_CLIENT_SECRET=' . $clientSecret);
    }
}
