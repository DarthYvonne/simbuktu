<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a status column so persona replies can be created in a 'pending'
 * state, dispatched to a queue, and updated when the LLM finishes.
 *
 *   pending  — job dispatched, waiting on LLM
 *   complete — body is the final reply (or for user messages, the user's text)
 *   failed   — LLM call failed; error_message holds the reason
 *
 * Existing rows are backfilled to 'complete' so the column is safe to
 * read on every row from day one.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('conversation_messages', function (Blueprint $t) {
            $t->string('status', 16)->default('complete')->after('body');
            $t->text('error_message')->nullable()->after('status');
        });

        DB::table('conversation_messages')->update(['status' => 'complete']);
    }

    public function down(): void
    {
        Schema::table('conversation_messages', function (Blueprint $t) {
            $t->dropColumn(['status', 'error_message']);
        });
    }
};
