<?php

namespace Database\Seeders;

use App\Models\Prompt;
use App\Services\PromptRepository;
use Illuminate\Database\Seeder;

class PromptsSeeder extends Seeder
{
    public function run(): void
    {
        $repo = new PromptRepository();
        foreach ($repo->defaults() as $key => $data) {
            Prompt::updateOrCreate(
                ['key' => $key],
                [
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'placeholders' => $data['placeholders'] ?? [],
                    'body' => $data['body'],
                ]
            );
        }
    }
}
