<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->dimensions() as $d) {
            $exists = DB::table('library_parameters')
                ->where('name', $d['name'])
                ->where('category', $d['category'])
                ->exists();
            if ($exists) continue;
            $facets = array_map(
                fn ($f) => array_merge(['id' => (string) Str::uuid()], $f),
                $d['facets']
            );
            DB::table('library_parameters')->insert([
                'name'                      => $d['name'],
                'category'                  => $d['category'],
                'description'               => $d['description'] ?? null,
                'type'                      => $d['type'],
                'default_in_new_blueprints' => true,
                'sort_order'                => $d['sort_order'] ?? 0,
                'facets'                    => json_encode($facets, JSON_UNESCAPED_UNICODE),
                'created_at'                => now(),
                'updated_at'                => now(),
            ]);
        }
    }

    public function down(): void
    {
        // No-op: leaving seeded dimensions in place is harmless.
    }

    private function dimensions(): array
    {
        return [
            // --- Demographic (mirror of legacy config/personas.php distributions) ---
            ['name' => 'Alder', 'category' => 'demografi', 'type' => 'demographic', 'sort_order' => 10,
             'description' => 'Aldersfordeling i den voksne danske befolkning.',
             'facets' => [
                ['name' => '16-24', 'text' => '', 'weight' => 13],
                ['name' => '25-34', 'text' => '', 'weight' => 17],
                ['name' => '35-44', 'text' => '', 'weight' => 16],
                ['name' => '45-54', 'text' => '', 'weight' => 17],
                ['name' => '55-64', 'text' => '', 'weight' => 16],
                ['name' => '65-79', 'text' => '', 'weight' => 18],
                ['name' => '80-89', 'text' => '', 'weight' => 3],
            ]],
            ['name' => 'Køn', 'category' => 'demografi', 'type' => 'demographic', 'sort_order' => 20,
             'facets' => [
                ['name' => 'mand',   'text' => '', 'weight' => 49],
                ['name' => 'kvinde', 'text' => '', 'weight' => 49],
                ['name' => 'andet',  'text' => '', 'weight' => 2],
            ]],
            ['name' => 'Region', 'category' => 'demografi', 'type' => 'demographic', 'sort_order' => 30,
             'facets' => [
                ['name' => 'Hovedstaden', 'text' => '', 'weight' => 32],
                ['name' => 'Sjælland',    'text' => '', 'weight' => 14],
                ['name' => 'Syddanmark',  'text' => '', 'weight' => 21],
                ['name' => 'Midtjylland', 'text' => '', 'weight' => 23],
                ['name' => 'Nordjylland', 'text' => '', 'weight' => 10],
            ]],
            ['name' => 'Bytype', 'category' => 'demografi', 'type' => 'demographic', 'sort_order' => 40,
             'facets' => [
                ['name' => 'storby',         'text' => '', 'weight' => 35],
                ['name' => 'mellemstor by',  'text' => '', 'weight' => 30],
                ['name' => 'mindre by',      'text' => '', 'weight' => 25],
                ['name' => 'landdistrikt',   'text' => '', 'weight' => 10],
            ]],
            ['name' => 'Uddannelse', 'category' => 'demografi', 'type' => 'demographic', 'sort_order' => 50,
             'facets' => [
                ['name' => 'folkeskole',              'text' => '', 'weight' => 18],
                ['name' => 'gymnasial',               'text' => '', 'weight' => 12],
                ['name' => 'erhvervsuddannelse',      'text' => '', 'weight' => 32],
                ['name' => 'kort videregående',       'text' => '', 'weight' => 6],
                ['name' => 'mellemlang videregående', 'text' => '', 'weight' => 18],
                ['name' => 'lang videregående',       'text' => '', 'weight' => 14],
            ]],
            ['name' => 'Herkomst', 'category' => 'demografi', 'type' => 'demographic', 'sort_order' => 60,
             'facets' => [
                ['name' => 'dansk',                                'text' => '', 'weight' => 86],
                ['name' => 'vestlig indvandrer/efterkommer',       'text' => '', 'weight' => 4],
                ['name' => 'ikke-vestlig indvandrer/efterkommer',  'text' => '', 'weight' => 10],
            ]],

            // --- Personality ---
            ['name' => 'Konflikthåndtering', 'category' => 'sprog_adfaerd', 'type' => 'personality', 'sort_order' => 100,
             'description' => 'Hvordan personen agerer i konflikter.',
             'facets' => [
                ['name' => 'troll-toner',           'text' => 'driller, provokerer for sjov, kort og giftig — gerne med ironi og emojis', 'weight' => 10],
                ['name' => 'undvigende',            'text' => 'undgår direkte konflikt, mumler, halve sætninger, vag', 'weight' => 25],
                ['name' => 'passiv-aggressiv',      'text' => 'vrede pakket ind i høflighed — sukker, "bare en lille bemærkning", subtile stik', 'weight' => 25],
                ['name' => 'direkte konfronterende','text' => 'siger sin mening lige ud, uden filter — kan være hård men ærlig', 'weight' => 25],
                ['name' => 'saglig-insisterende',   'text' => 'argumenterer udførligt og logisk, henviser til fakta, vil have det sidste ord', 'weight' => 15],
            ]],
            ['name' => 'Sprogregister', 'category' => 'sprog_adfaerd', 'type' => 'personality', 'sort_order' => 110,
             'description' => 'Hvilket sprog personen bruger — slang, hverdag, akademisk.',
             'facets' => [
                ['name' => 'slang-tungt',  'text' => 'bruger slang og forkortelser ("sq", "ka", "mig" i stedet for "jeg"), ufuldstændige sætninger', 'weight' => 30],
                ['name' => 'hverdagsdansk','text' => 'almindeligt talesprog, lidt sjusket, ikke pertentlig med kommaer', 'weight' => 55],
                ['name' => 'akademisk',    'text' => 'korrekt og præcist sprog, hele sætninger, gerne lange og struktureret', 'weight' => 15],
            ]],
            ['name' => 'Emoji-brug', 'category' => 'sprog_adfaerd', 'type' => 'personality', 'sort_order' => 120,
             'description' => 'Hvor meget personen bruger emojis.',
             'facets' => [
                ['name' => 'ingen emoji',  'text' => 'bruger ikke emojis', 'weight' => 30],
                ['name' => 'sparsom emoji','text' => 'enkelte emojis, mest 😂 eller 🙄', 'weight' => 50],
                ['name' => 'emoji-rigt',   'text' => 'mange emojis, gerne flere i træk, og VERSALER til at understrege', 'weight' => 20],
            ]],
        ];
    }
};
