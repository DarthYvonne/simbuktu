<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $t) {
            if (!Schema::hasColumn('courses', 'description')) {
                $t->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('courses', 'slug')) {
                $t->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('courses', 'algorithm_config')) {
                $t->json('algorithm_config')->nullable()->after('invite_token');
            }
        });
        // Backfill slugs for any existing row
        foreach (\DB::table('courses')->whereNull('slug')->get() as $c) {
            \DB::table('courses')->where('id', $c->id)
                ->update(['slug' => \Illuminate\Support\Str::slug($c->name) . '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4))]);
        }
    }

    public function down(): void
    {
        // Non-destructive; leave columns in place.
    }
};
