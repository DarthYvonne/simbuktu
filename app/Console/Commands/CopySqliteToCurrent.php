<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;

/**
 * One-shot helper used during the SQLite → Postgres migration.
 *
 * Reads every table from a source SQLite file (raw PDO) and writes the
 * rows into the app's current default DB connection. Order respects FK
 * dependencies. Postgres sequences for id columns are reset after each
 * insert so future inserts don't collide.
 *
 * Usage:
 *   php artisan db:copy-sqlite --from=database/database.sqlite [--truncate]
 */
class CopySqliteToCurrent extends Command
{
    protected $signature = 'db:copy-sqlite {--from= : path to source sqlite file} {--truncate : truncate target tables before copying}';
    protected $description = 'Copy data from a SQLite file into the app default DB connection.';

    /** Tables in dependency-safe insert order (parents before children). */
    private array $tables = [
        // Laravel defaults
        'users',
        'sessions',
        'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs',

        // App
        'cms_settings',
        'cms_pages',
        'prompts',
        'library_parameters',
        'personality_rules',
        'news_headlines',
        'populations',
        'blueprints',           // depends on populations
        'courses',              // depends on populations + users (manager)
        'course_memberships',   // depends on courses + users
        'personas',             // depends on populations
        'friendships',          // depends on personas
        'posts',                // depends on courses + users
        'post_exposures',       // depends on posts + personas
        'post_feedback_messages', // depends on posts + users
        'comments',             // depends on posts + users
        'comment_reactions',    // depends on comments + users + personas
        'conversations',        // depends on courses + users + personas
        'conversation_messages',// depends on conversations
        'llm_calls',
    ];

    public function handle(): int
    {
        $from = $this->option('from');
        if (!$from || !is_file($from)) {
            $this->error("--from must point to an existing sqlite file (got: {$from})");
            return self::FAILURE;
        }

        $driver = DB::getDriverName();
        $this->info("Source: {$from}");
        $this->info("Target: " . config('database.default') . " ({$driver})");

        $src = new PDO("sqlite:{$from}");
        $src->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $totals = ['copied' => 0, 'skipped' => 0];

        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                $this->line(str_pad($table, 28) . ' — target has no such table, skipping');
                continue;
            }
            try {
                $stmt = $src->query("SELECT * FROM {$table}");
            } catch (\Throwable $e) {
                $this->line(str_pad($table, 28) . ' — source has no such table, skipping');
                continue;
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($rows)) {
                $this->line(str_pad($table, 28) . ' 0 rows');
                continue;
            }

            if ($this->option('truncate')) {
                if ($driver === 'pgsql') {
                    DB::statement("TRUNCATE TABLE \"{$table}\" RESTART IDENTITY CASCADE");
                } else {
                    DB::table($table)->delete();
                }
            }

            // Coerce types where SQLite's dynamic typing diverges from Postgres' strict.
            $rows = array_map(fn ($r) => $this->coerceRow($table, $r, $driver), $rows);

            // Bulk insert in chunks to keep statement size sane.
            try {
                foreach (array_chunk($rows, 200) as $chunk) {
                    DB::table($table)->insert($chunk);
                }
                $totals['copied'] += count($rows);
                $this->info(str_pad($table, 28) . count($rows) . ' rows ✓');
            } catch (\Throwable $e) {
                $totals['skipped'] += count($rows);
                $this->error(str_pad($table, 28) . 'FAILED: ' . substr($e->getMessage(), 0, 200));
            }

            // Reset Postgres sequence after inserting explicit ids
            if ($driver === 'pgsql') {
                $this->resetSequence($table);
            }
        }

        $this->info('');
        $this->info("Done. Copied: {$totals['copied']} rows. Failed: {$totals['skipped']} rows.");
        return self::SUCCESS;
    }

    /**
     * SQLite stores everything as text/integer. Postgres types are strict.
     * Coerce known patterns: booleans (0/1 ints → bool), JSON columns
     * (already valid JSON strings; pass through).
     */
    private function coerceRow(string $table, array $row, string $driver): array
    {
        if ($driver !== 'pgsql') return $row;

        // Boolean columns we know about. SQLite stores 0/1; Postgres expects bool.
        $boolCols = [
            'users'                => ['is_admin'],
            'cms_pages'            => ['is_visible'],
            'cms_settings'         => [],
            'comment_reactions'    => [],
            'library_parameters'   => ['default_in_new_blueprints'],
            'course_memberships'   => [],
        ];
        foreach ($boolCols[$table] ?? [] as $col) {
            if (array_key_exists($col, $row) && $row[$col] !== null) {
                $row[$col] = ((int) $row[$col]) === 1;
            }
        }
        return $row;
    }

    private function resetSequence(string $table): void
    {
        try {
            // Only meaningful for tables with an integer 'id' primary key
            $maxId = DB::table($table)->max('id');
            if ($maxId === null || !is_numeric($maxId)) return;
            DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), {$maxId})");
        } catch (\Throwable $e) {
            // Table has no 'id' or no sequence — fine.
        }
    }
}
