<?php

namespace App\Console\Commands;

use App\Services\Personas\Thumbnails;
use Illuminate\Console\Command;

class GenerateThumbnailsCommand extends Command
{
    protected $signature = 'slophub:thumbs {--size=128}';
    protected $description = 'Generate (or refresh) persona image thumbnails.';

    public function handle(): int
    {
        $dir = config('personas.image_path');
        if (!is_dir($dir)) { $this->error("No dir: $dir"); return self::FAILURE; }
        $files = glob($dir . '/*.png') ?: [];
        $size = (int) $this->option('size');
        $count = 0;
        foreach ($files as $f) {
            $id = pathinfo($f, PATHINFO_FILENAME);
            if (Thumbnails::path($id, $size)) $count++;
        }
        $this->info("Generated {$count} thumbnails at {$size}px.");
        return self::SUCCESS;
    }
}
