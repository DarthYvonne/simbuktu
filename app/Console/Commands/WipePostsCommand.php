<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WipePostsCommand extends Command
{
    protected $signature = 'slophub:wipe-posts';
    protected $description = 'Truncate posts, comments and post_exposures.';

    public function handle(): int
    {
        DB::statement('DELETE FROM post_exposures');
        DB::statement('DELETE FROM comments');
        DB::statement('DELETE FROM posts');
        $this->info('Wiped posts, comments, post_exposures.');
        return self::SUCCESS;
    }
}
