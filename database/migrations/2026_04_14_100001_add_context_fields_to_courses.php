<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $t) {
            if (!Schema::hasColumn('courses', 'context_mode')) {
                $t->string('context_mode', 20)->default('auto')->after('algorithm_config');
            }
            if (!Schema::hasColumn('courses', 'manual_context')) {
                $t->text('manual_context')->nullable()->after('context_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $t) {
            $t->dropColumn(['context_mode', 'manual_context']);
        });
    }
};
