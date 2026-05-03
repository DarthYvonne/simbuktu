<?php

namespace App\Console\Commands\Personas;

use App\Models\Persona;
use App\Models\Population;
use Illuminate\Console\Command;

class MigrateToDb extends Command
{
    protected $signature   = 'personas:migrate-to-db {--population= : Population ID to assign (creates "Standard" population if omitted)}';
    protected $description = 'Import existing personas.json into the database';

    public function handle(): int
    {
        $path = config('personas.output_path') . '/personas.json';
        if (!is_file($path)) {
            $this->info('No personas.json found — nothing to import.');
            return 0;
        }

        $data = json_decode(file_get_contents($path), true) ?: [];
        if (empty($data)) {
            $this->info('personas.json is empty — nothing to import.');
            return 0;
        }

        $populationId = $this->option('population');
        if (!$populationId) {
            $pop = Population::firstOrCreate(
                ['name' => 'Standard'],
                ['description' => 'Importeret fra personas.json']
            );
            $populationId = $pop->id;
            $this->info("Using population #{$populationId} \"{$pop->name}\"");
        }

        $imported = 0;
        $skipped  = 0;
        foreach ($data as $p) {
            if (empty($p['id'])) { $skipped++; continue; }
            if (Persona::where('id', $p['id'])->exists()) { $skipped++; continue; }

            Persona::create([
                'id'           => $p['id'],
                'population_id' => $populationId,
                'name'         => $p['name'] ?? 'Ukendt',
                'bio'          => $p['bio'] ?? null,
                'image_file'   => $p['image_file'] ?? null,
                'age'          => $p['demographics']['age'] ?? 0,
                'gender'       => $p['demographics']['gender'] ?? '',
                'region'       => $p['demographics']['region'] ?? '',
                'party'        => $p['politics']['party'] ?? '',
                'subcultures'  => $p['subcultures'] ?? [],
                'persona_data' => $p,
            ]);
            $imported++;
        }

        $this->info("Imported {$imported} personas, skipped {$skipped}.");
        return 0;
    }
}
