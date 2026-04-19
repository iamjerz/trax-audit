<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DummySeeder extends Seeder
{
    public function run(): void
    {
        $data = [];

        for ($i = 0; $i < 100; $i++) {
            $data[] = [
                'submission_id' => 'SUB-' . now()->format('YmdHis') . '-' . Str::random(5),

                'recon_call_date' => fake()->date(),

                'lda_email' => fake()->safeEmail(),
                'audit_sup_email' => fake()->safeEmail(),

                'client_code' => fake()->bothify('CL###'),
                'carrier_code' => fake()->bothify('CR###'),
                'region' => fake()->randomElement(['APAC', 'EMEA', 'NA']),

                'action_item_summary' => fake()->sentence(),
                'action_item_details' => fake()->paragraph(),

                'jira_ticket' => 'JIRA-' . fake()->numberBetween(1000, 9999),

                'status' => fake()->randomElement([
                    'open',
                    'in_progress',
                    'closed'
                ]),

                'raw_data' => json_encode([
                    'source' => 'dummy',
                    'remarks' => fake()->sentence(),
                    'score' => fake()->numberBetween(1, 100)
                ]),

                'created_at' => now(), // since you only have created_at
            ];
        }

        DB::table('recon_action_items')->insert($data);
    }
}