<?php

namespace Database\Seeders;

use App\Models\IntegrationClient;
use Illuminate\Database\Seeder;

class IntegrationClientSeeder extends Seeder
{
    public function run()
    {
        $systems = [
            'accre' => 'ACCRE',
            'dental' => 'Dental',
            'pupt_website' => 'PUPT Website',
            'ims' => 'IMS',
            'ris' => 'RIS',
        ];

        foreach ($systems as $systemKey => $systemName) {
            IntegrationClient::updateOrCreate(
                ['system_key' => $systemKey],
                [
                    'system_name' => $systemName,
                    'is_active' => true,
                ]
            );
        }
    }
}