<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Restructure: personlighed becomes per-population.
 * - blueprints gets population_id (1:1 with populations).
 * - populations gets context_mode + manual_context (moved from courses).
 * - populations.narrative_prompt dropped (dead since prompt-snapshot refactor).
 * - courses.context_mode + manual_context dropped.
 * - Existing data wiped (per user decision — populations/blueprints/personas are stale).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blueprints', function (Blueprint $t) {
            if (!Schema::hasColumn('blueprints', 'population_id')) {
                $t->foreignId('population_id')->nullable()->after('id')->constrained('populations')->cascadeOnDelete();
            }
        });

        Schema::table('populations', function (Blueprint $t) {
            if (!Schema::hasColumn('populations', 'context_mode')) {
                $t->string('context_mode', 20)->default('manual')->after('config_overrides');
            }
            if (!Schema::hasColumn('populations', 'manual_context')) {
                $t->text('manual_context')->nullable()->after('context_mode');
            }
        });

        if (Schema::hasColumn('populations', 'narrative_prompt')) {
            Schema::table('populations', function (Blueprint $t) {
                $t->dropColumn('narrative_prompt');
            });
        }

        // Drop dead columns on courses: context/blueprint now flow through population.
        Schema::table('courses', function (Blueprint $t) {
            if (Schema::hasColumn('courses', 'context_mode'))   $t->dropColumn('context_mode');
            if (Schema::hasColumn('courses', 'manual_context')) $t->dropColumn('manual_context');
        });
        if (Schema::hasColumn('courses', 'blueprint_id')) {
            // Foreign key on courses.blueprint_id needs to be dropped explicitly.
            Schema::table('courses', function (Blueprint $t) {
                try { $t->dropForeign(['blueprint_id']); } catch (\Throwable $e) {}
                $t->dropColumn('blueprint_id');
            });
        }

        // Wipe stale data. Per the restructure, populations/blueprints/personas no longer
        // map cleanly onto the new 1:1 ownership model — start fresh.
        $this->wipe();
    }

    public function down(): void
    {
        Schema::table('blueprints', function (Blueprint $t) {
            $t->dropConstrainedForeignId('population_id');
        });
        Schema::table('populations', function (Blueprint $t) {
            $t->dropColumn(['context_mode', 'manual_context']);
            $t->longText('narrative_prompt')->nullable();
        });
        Schema::table('courses', function (Blueprint $t) {
            $t->string('context_mode', 20)->default('auto');
            $t->text('manual_context')->nullable();
        });
    }

    private function wipe(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        }

        $tables = [
            'comment_reactions', 'comments',
            'post_exposures', 'post_feedback_messages', 'posts',
            'conversation_messages', 'conversations',
            'personas', 'blueprints', 'populations',
        ];
        foreach ($tables as $t) {
            if (Schema::hasTable($t)) DB::table($t)->delete();
        }

        // Detach any course that pointed at the now-gone population.
        if (Schema::hasTable('courses')) {
            DB::table('courses')->update(['population_id' => null]);
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
};
