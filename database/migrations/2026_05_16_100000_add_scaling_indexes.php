<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes added as part of the scalability pass:
 *
 *   posts(course_id, created_at)
 *     The hot feed query is Post::where('course_id', X)->orderByDesc('created_at').
 *     Postgres does NOT auto-index FK columns, and SQLite never does, so without
 *     this we table-scan + sort the entire posts table on every feed poll.
 *
 *   post_exposures(persona_id)
 *     Existing unique(post_id, persona_id) cannot be used for queries that look
 *     up exposures by persona alone (B-tree prefix rule). Admin "trace this
 *     persona's history" pages will table-scan without it.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $t) {
            $t->index(['course_id', 'created_at'], 'posts_course_id_created_at_index');
        });

        Schema::table('post_exposures', function (Blueprint $t) {
            $t->index('persona_id', 'post_exposures_persona_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $t) {
            $t->dropIndex('posts_course_id_created_at_index');
        });
        Schema::table('post_exposures', function (Blueprint $t) {
            $t->dropIndex('post_exposures_persona_id_index');
        });
    }
};
